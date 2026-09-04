<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
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
        ], [
            'title.required' => 'El título del evento o promoción es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'subtitle.max' => 'El subtítulo no puede superar los 500 caracteres.',
            'image_file.image' => 'El archivo de la promoción debe ser una imagen válida.',
            'image_file.mimes' => 'La imagen debe ser de formato JPG, JPEG, PNG o WEBP.',
            'image_file.max' => 'La imagen no puede superar los 6 GB.',
        ]);

        $imagePath = $event->image_path;

        // Si viene imagen recortada en Base64 (con Cropper.js)
        if (!empty($request->input('cropped_image_base64'))) {
            $data = $request->input('cropped_image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                $filename = 'event_' . time() . '_' . Str::random(8) . '.' . $type;
                $destinationPath = public_path('imagenes/uploads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                file_put_contents($destinationPath . '/' . $filename, $data);
                $imagePath = 'imagenes/uploads/' . $filename;
            }
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'event_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('imagenes/uploads');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
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
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', '¡Configuración de la sección de Eventos / Promociones guardada con éxito!');
    }
}
