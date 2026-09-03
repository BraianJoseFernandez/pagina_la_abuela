<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('order')->get();
        $selectedCategoryId = $request->query('category_id');
        $search = $request->query('search');

        $query = Product::with(['category', 'variants' => fn($q) => $q->orderBy('order')]);

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('badge', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category_id')->orderBy('order')->paginate(25);

        return view('admin.products.index', compact('products', 'categories', 'selectedCategoryId', 'search'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'badge' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'has_cooking_options' => 'nullable|boolean',
            'cooking_options' => 'nullable|array',
            'cooking_options.*' => 'nullable|string|max:100',
            'is_available' => 'nullable|boolean',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cropped_image_base64' => 'nullable|string',
            'variant_names' => 'nullable|array',
            'variant_names.*' => 'nullable|string|max:255',
            'variant_prices' => 'nullable|array',
            'variant_prices.*' => 'nullable|numeric|min:0',
        ], [
            'category_id.required' => 'Debes seleccionar una categoría para el producto.',
            'category_id.exists' => 'La categoría seleccionada no existe en el sistema.',
            'name.required' => 'El nombre del producto o plato es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'image_file.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'image_file.mimes' => 'La imagen debe ser de formato JPG, JPEG, PNG o WEBP.',
            'image_file.max' => 'La imagen no puede superar los 5 MB.',
            'variant_prices.*.numeric' => 'El precio de cada variante debe ser un número válido.',
            'variant_prices.*.min' => 'El precio de la variante no puede ser negativo.',
        ]);

        $imagePath = null;

        // Si viene imagen recortada en Base64 (con Cropper.js)
        if (!empty($request->input('cropped_image_base64'))) {
            $data = $request->input('cropped_image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                $filename = 'dish_' . time() . '_' . Str::random(8) . '.' . $type;
                $destinationPath = public_path('imagenes/uploads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                file_put_contents($destinationPath . '/' . $filename, $data);
                $imagePath = 'imagenes/uploads/' . $filename;
            }
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'dish_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('imagenes/uploads');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = 'imagenes/uploads/' . $filename;
        }

        $hasCookingOptions = $request->has('has_cooking_options');
        $cookingOptions = null;
        if ($hasCookingOptions) {
            $rawOptions = $request->input('cooking_options', ['Al Horno', 'Frita']);
            $cookingOptions = !empty($rawOptions) ? array_values(array_filter($rawOptions)) : ['Al Horno', 'Frita'];
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'has_cooking_options' => $hasCookingOptions,
            'cooking_options' => $cookingOptions,
            'order' => $validated['order'] ?? (Product::where('category_id', $validated['category_id'])->max('order') + 1),
            'is_available' => $request->has('is_available'),
        ]);

        // Guardar variantes de precio si se enviaron
        if (!empty($request->input('variant_names'))) {
            $names = $request->input('variant_names');
            $prices = $request->input('variant_prices');

            foreach ($names as $idx => $vName) {
                if (!empty($vName) && isset($prices[$idx]) && is_numeric($prices[$idx])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vName,
                        'price' => $prices[$idx],
                        'order' => $idx + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index', ['category_id' => $product->category_id])
            ->with('success', '¡Plato "' . $product->name . '" creado exitosamente!');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->orderBy('order')->get();
        $product->load(['variants' => fn($q) => $q->orderBy('order')]);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'badge' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'has_cooking_options' => 'nullable|boolean',
            'cooking_options' => 'nullable|array',
            'cooking_options.*' => 'nullable|string|max:100',
            'is_available' => 'nullable|boolean',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cropped_image_base64' => 'nullable|string',
            'variant_names' => 'nullable|array',
            'variant_names.*' => 'nullable|string|max:255',
            'variant_prices' => 'nullable|array',
            'variant_prices.*' => 'nullable|numeric|min:0',
        ], [
            'category_id.required' => 'Debes seleccionar una categoría para el producto.',
            'category_id.exists' => 'La categoría seleccionada no existe en el sistema.',
            'name.required' => 'El nombre del producto o plato es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'image_file.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'image_file.mimes' => 'La imagen debe ser de formato JPG, JPEG, PNG o WEBP.',
            'image_file.max' => 'La imagen no puede superar los 5 MB.',
            'variant_prices.*.numeric' => 'El precio de cada variante debe ser un número válido.',
            'variant_prices.*.min' => 'El precio de la variante no puede ser negativo.',
        ]);

        $imagePath = $product->image_path;

        // Si viene nueva imagen recortada en Base64
        if (!empty($request->input('cropped_image_base64'))) {
            $data = $request->input('cropped_image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                $filename = 'dish_' . time() . '_' . Str::random(8) . '.' . $type;
                $destinationPath = public_path('imagenes/uploads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                file_put_contents($destinationPath . '/' . $filename, $data);
                $imagePath = 'imagenes/uploads/' . $filename;
            }
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'dish_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('imagenes/uploads');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = 'imagenes/uploads/' . $filename;
        }

        $hasCookingOptions = $request->has('has_cooking_options');
        $cookingOptions = null;
        if ($hasCookingOptions) {
            $rawOptions = $request->input('cooking_options', ['Al Horno', 'Frita']);
            $cookingOptions = !empty($rawOptions) ? array_values(array_filter($rawOptions)) : ['Al Horno', 'Frita'];
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'has_cooking_options' => $hasCookingOptions,
            'cooking_options' => $cookingOptions,
            'order' => $validated['order'] ?? $product->order,
            'is_available' => $request->has('is_available'),
        ]);

        // Reemplazar o actualizar variantes
        $product->variants()->delete();
        if (!empty($request->input('variant_names'))) {
            $names = $request->input('variant_names');
            $prices = $request->input('variant_prices');

            foreach ($names as $idx => $vName) {
                if (!empty($vName) && isset($prices[$idx]) && is_numeric($prices[$idx])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vName,
                        'price' => $prices[$idx],
                        'order' => $idx + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index', ['category_id' => $product->category_id])
            ->with('success', '¡Plato "' . $product->name . '" actualizado exitosamente!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $categoryId = $product->category_id;
        $product->delete();

        return redirect()->route('admin.products.index', ['category_id' => $categoryId])
            ->with('success', '¡Plato "' . $name . '" eliminado exitosamente!');
    }

    /**
     * Alternar disponibilidad del producto de forma rápida (Agotado / Disponible).
     */
    public function toggleAvailability(Product $product): JsonResponse
    {
        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json([
            'success' => true,
            'is_available' => $product->is_available,
            'message' => 'Disponibilidad de "' . $product->name . '" actualizada a ' . ($product->is_available ? 'Disponible' : 'Agotado') . '.'
        ]);
    }

    /**
     * Actualización rápida de precios desde la tabla.
     */
    public function quickPriceUpdate(Request $request, Product $product): JsonResponse
    {
        if ($request->has('variant_id')) {
            $variant = ProductVariant::where('product_id', $product->id)->find($request->input('variant_id'));
            if ($variant) {
                $variant->price = $request->input('price');
                $variant->save();
                return response()->json(['success' => true, 'message' => 'Precio de variante actualizado.']);
            }
        } else {
            $product->price = $request->input('price');
            $product->save();
            return response()->json(['success' => true, 'message' => 'Precio base actualizado.']);
        }

        return response()->json(['success' => false, 'message' => 'No se pudo actualizar el precio.'], 400);
    }

    /**
     * Reordenar platos vía Drag and Drop.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:products,id',
        ]);

        foreach ($request->order as $position => $id) {
            Product::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => '¡Orden de platos actualizado exitosamente!']);
    }
}
