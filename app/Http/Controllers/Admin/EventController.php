<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    /** Formatos permitidos para el flyer (también para el Base64 del recorte). */
    private const ALLOWED_IMAGE_TYPES = ['jpeg', 'jpg', 'png', 'webp'];

    public function index(): View
    {
        $event = EventSetting::firstOrCreate(['id' => 1], [
            'title' => 'Oferta Especial',
            'subtitle' => '¡Aprovechá nuestras mejores promociones!',
            'image_path' => 'imagenes/eventos/mundial/oferta_mundial.jpeg',
            'badge_left_emoji' => '⚽🇦🇷',
            'badge_right_emoji' => '⚽🇦🇷',
            'confetti_emojis' => '⚽,🇦🇷,🏆,🎉',
            'confetti_colors' => '#75AADB,#FFFFFF,#F6B40E',
            'whatsapp_custom_text' => 'Hola! Quiero consultar por la promo especial ⚽🇦🇷',
            'is_active' => true,
            'auto_popup' => true,
            'popup_frequency' => 'always',
        ]);

        return view('admin.events.index', compact('event'));
    }

    public function update(Request $request): RedirectResponse
    {
        $event = EventSetting::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'badge_left_emoji' => 'nullable|string|max:50',
            'badge_right_emoji' => 'nullable|string|max:50',
            'confetti_emojis' => 'nullable|string|max:255',
            'confetti_colors' => 'nullable|string|max:255',
            'whatsapp_custom_text' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:6291456',
            'cropped_image_base64' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'auto_popup' => 'nullable|boolean',
            'popup_frequency' => ['nullable', Rule::in(EventSetting::POPUP_FREQUENCIES)],
        ], [
            'title.required' => 'El título del evento o promoción es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'subtitle.max' => 'El subtítulo no puede superar los 500 caracteres.',
            'image_file.image' => 'El archivo de la promoción debe ser una imagen válida.',
            'image_file.mimes' => 'La imagen debe ser de formato JPG, JPEG, PNG o WEBP.',
            'image_file.max' => 'La imagen no puede superar los 6 GB.',
            'popup_frequency.in' => 'La frecuencia del modal automático no es válida.',
        ]);

        $imagePath = $event->image_path;

        // 1) Flyer ajustado con Cropper.js (llega en Base64, completo o recortado)
        if (!empty($request->input('cropped_image_base64'))) {
            $savedPath = $this->storeBase64Image($request->input('cropped_image_base64'));
            if ($savedPath === null) {
                return back()->withInput()->withErrors([
                    'image_file' => 'No se pudo procesar la imagen ajustada. Probá subirla nuevamente (JPG, PNG o WEBP).',
                ]);
            }
            $imagePath = $savedPath;
        // 2) Flyer original tal cual se subió (sin pasar por el recorte)
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            // extension() se basa en el contenido real del archivo, no en el nombre que manda el navegador
            $extension = $file->extension() === 'jpeg' ? 'jpg' : $file->extension();
            $filename = 'event_' . time() . '_' . Str::random(8) . '.' . $extension;
            $file->move($this->uploadsPath(), $filename);
            $imagePath = 'imagenes/uploads/' . $filename;
        }

        $event->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_left_emoji' => $validated['badge_left_emoji'] ?? '⚽🇦🇷',
            'badge_right_emoji' => $validated['badge_right_emoji'] ?? '⚽🇦🇷',
            'confetti_emojis' => $validated['confetti_emojis'] ?: '⚽,🇦🇷,🏆,🎉',
            'confetti_colors' => $validated['confetti_colors'] ?: '#75AADB,#FFFFFF,#F6B40E',
            'whatsapp_custom_text' => $validated['whatsapp_custom_text'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active'),
            'auto_popup' => $request->boolean('auto_popup'),
            'popup_frequency' => $validated['popup_frequency'] ?? 'always',
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', '¡Configuración de la sección de Eventos / Promociones guardada con éxito!');
    }

    /**
     * Guarda una imagen en Base64 validando que realmente sea JPG/PNG/WEBP.
     */
    private function storeBase64Image(string $data): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
            return null;
        }

        $type = strtolower($matches[1]);
        if (!in_array($type, self::ALLOWED_IMAGE_TYPES, true)) {
            return null;
        }

        $binary = base64_decode(substr($data, strpos($data, ',') + 1), true);
        if ($binary === false || @getimagesizefromstring($binary) === false) {
            return null;
        }

        $extension = $type === 'jpeg' ? 'jpg' : $type;
        $filename = 'event_' . time() . '_' . Str::random(8) . '.' . $extension;
        file_put_contents($this->uploadsPath() . '/' . $filename, $binary);

        return 'imagenes/uploads/' . $filename;
    }

    private function uploadsPath(): string
    {
        $destinationPath = public_path('imagenes/uploads');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        return $destinationPath;
    }
}
