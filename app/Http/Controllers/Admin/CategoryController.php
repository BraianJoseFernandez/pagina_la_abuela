<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')
            ->with('images')
            ->orderBy('order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'icon' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'slug.unique' => 'Ya existe una categoría con ese enlace permanente (slug).',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Asegurar unicidad del slug si se autogenera
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?: 'fas fa-utensils',
            'subtitle' => $validated['subtitle'] ?? null,
            'order' => $validated['order'] ?? (Category::max('order') + 1),
            'is_active' => $request->has('is_active'),
        ]);

        // Procesar subida de imágenes para el carrusel de la categoría
        if ($request->hasFile('carousel_images')) {
            foreach ($request->file('carousel_images') as $idx => $file) {
                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('imagenes/uploads'), $filename);

                CategoryImage::create([
                    'category_id' => $category->id,
                    'image_path' => 'imagenes/uploads/' . $filename,
                    'alt_text' => $category->name,
                    'order' => $idx + 1,
                ]);
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', '¡Categoría "' . $category->name . '" creada exitosamente!');
    }

    public function edit(Category $category): View
    {
        $category->load('images');
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'icon' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'slug.unique' => 'Ya existe otra categoría con ese enlace permanente (slug).',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?: 'fas fa-utensils',
            'subtitle' => $validated['subtitle'] ?? null,
            'order' => $validated['order'] ?? $category->order,
            'is_active' => $request->has('is_active'),
        ]);

        // Procesar subida de imágenes nuevas
        if ($request->hasFile('carousel_images')) {
            $currentMaxOrder = $category->images()->max('order') ?? 0;
            foreach ($request->file('carousel_images') as $idx => $file) {
                $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('imagenes/uploads'), $filename);

                CategoryImage::create([
                    'category_id' => $category->id,
                    'image_path' => 'imagenes/uploads/' . $filename,
                    'alt_text' => $category->name,
                    'order' => $currentMaxOrder + $idx + 1,
                ]);
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', '¡Categoría "' . $category->name . '" actualizada exitosamente!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', '¡Categoría "' . $name . '" eliminada exitosamente!');
    }

    public function deleteImage(CategoryImage $image): RedirectResponse
    {
        $categoryId = $image->category_id;
        $image->delete();

        return redirect()->route('admin.categories.edit', $categoryId)
            ->with('success', 'Foto de carrusel eliminada.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        foreach ($request->order as $position => $id) {
            Category::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => '¡Orden de categorías actualizado!']);
    }

    public function reorderImages(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:category_images,id',
        ]);

        foreach ($request->order as $position => $id) {
            CategoryImage::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => '¡Orden de fotos actualizado!']);
    }
}
