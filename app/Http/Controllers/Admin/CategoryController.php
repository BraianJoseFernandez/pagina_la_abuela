<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
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
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:6291456',
            'carousel_titles' => 'nullable|array',
            'carousel_titles.*' => 'nullable|string|max:120',
            'image_titles' => 'nullable|array',
            'image_titles.*' => 'nullable|string|max:120',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'slug.unique' => 'Ya existe una categoría con ese enlace permanente (slug).',
            'carousel_images.*.image' => 'Cada archivo del carrusel debe ser una imagen válida.',
            'carousel_images.*.mimes' => 'Las imágenes del carrusel deben ser de formato JPG, JPEG, PNG o WEBP.',
            'carousel_images.*.max' => 'Ninguna imagen del carrusel puede superar los 6 GB.',
            'carousel_titles.*.max' => 'El título de cada foto no puede superar los 120 caracteres.',
            'image_titles.*.max' => 'El título de cada foto no puede superar los 120 caracteres.',
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

        // Procesar subida de imágenes para el carrusel de la categoría (cada una con su título)
        $this->storeCarouselImages($request, $category, 0);

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
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:6291456',
            'carousel_titles' => 'nullable|array',
            'carousel_titles.*' => 'nullable|string|max:120',
            'image_titles' => 'nullable|array',
            'image_titles.*' => 'nullable|string|max:120',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'slug.unique' => 'Ya existe otra categoría con ese enlace permanente (slug).',
            'carousel_images.*.image' => 'Cada archivo del carrusel debe ser una imagen válida.',
            'carousel_images.*.mimes' => 'Las imágenes del carrusel deben ser de formato JPG, JPEG, PNG o WEBP.',
            'carousel_images.*.max' => 'Ninguna imagen del carrusel puede superar los 6 GB.',
            'carousel_titles.*.max' => 'El título de cada foto no puede superar los 120 caracteres.',
            'image_titles.*.max' => 'El título de cada foto no puede superar los 120 caracteres.',
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

        // Actualizar títulos de las fotos existentes (solo las que pertenecen a esta categoría)
        foreach ((array) $request->input('image_titles', []) as $imageId => $title) {
            $category->images()
                ->whereKey((int) $imageId)
                ->update(['alt_text' => $this->normalizeTitle($title)]);
        }

        // Procesar subida de imágenes nuevas (cada una con su título)
        $this->storeCarouselImages($request, $category, (int) ($category->images()->max('order') ?? 0));

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

    /**
     * Cambiar el título de una foto del carrusel (se muestra debajo de la miniatura y en el SweetAlert).
     */
    public function updateImageTitle(Request $request, CategoryImage $image): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:120',
        ], [
            'title.max' => 'El título no puede superar los 120 caracteres.',
        ]);

        $image->alt_text = $this->normalizeTitle($validated['title'] ?? null);
        $image->save();

        return response()->json([
            'success' => true,
            'title' => $image->alt_text,
            'message' => $image->alt_text ? 'Título de la foto guardado.' : 'Título quitado: se mostrará el nombre de la categoría.',
        ]);
    }

    /**
     * Guarda las fotos nuevas del carrusel respetando el título que se escribió para cada una.
     */
    private function storeCarouselImages(Request $request, Category $category, int $startOrder): void
    {
        if (!$request->hasFile('carousel_images')) {
            return;
        }

        $destinationPath = public_path('imagenes/uploads');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        $titles = array_values((array) $request->input('carousel_titles', []));
        $files = array_values($request->file('carousel_images'));

        foreach ($files as $idx => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }
            // extension() se basa en el contenido real del archivo, no en el nombre que manda el navegador
            $extension = $file->extension() === 'jpeg' ? 'jpg' : $file->extension();
            $filename = time() . '_' . Str::random(8) . '.' . $extension;
            $file->move($destinationPath, $filename);

            CategoryImage::create([
                'category_id' => $category->id,
                'image_path' => 'imagenes/uploads/' . $filename,
                'alt_text' => $this->normalizeTitle($titles[$idx] ?? null),
                'order' => $startOrder + $idx + 1,
            ]);
        }
    }

    /**
     * Título vacío = null (la carta muestra el nombre de la categoría como respaldo).
     */
    private function normalizeTitle(?string $title): ?string
    {
        // Sin saltos de línea ni espacios dobles (el título se muestra en una sola línea en la carta)
        $title = trim(preg_replace('/\s+/u', ' ', (string) $title));

        return $title === '' ? null : Str::limit($title, 120, '');
    }

    /**
     * Alternar visibilidad de una imagen del carrusel (Ocultar / Mostrar al público).
     */
    public function toggleImageVisibility(CategoryImage $image): JsonResponse
    {
        $image->is_visible = !$image->is_visible;
        $image->save();

        return response()->json([
            'success' => true,
            'is_visible' => $image->is_visible,
            'message' => 'Foto ' . ($image->is_visible ? 'visible para el público.' : 'ocultada del público.')
        ]);
    }
}
