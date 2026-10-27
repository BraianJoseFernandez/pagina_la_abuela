<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory;

    public const POPUP_FREQUENCIES = ['always', 'session', 'daily'];

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'badge_left_emoji',
        'badge_right_emoji',
        'confetti_emojis',
        'confetti_colors',
        'whatsapp_custom_text',
        'is_active',
        'auto_popup',
        'popup_frequency',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'auto_popup' => 'boolean',
        ];
    }

    /**
     * Datos mínimos que necesita la carta pública (modal, confeti y apertura automática).
     */
    public function toPublicPayload(): array
    {
        $imagePath = $this->image_path ?: 'imagenes/eventos/mundial/oferta_mundial.jpeg';

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image_path' => $imagePath,
            'image_url' => asset($imagePath),
            'confetti_emojis' => $this->confetti_emojis,
            'confetti_colors' => $this->confetti_colors,
            'whatsapp_custom_text' => $this->whatsapp_custom_text,
            'auto_popup' => $this->auto_popup ?? true,
            'popup_frequency' => in_array($this->popup_frequency, self::POPUP_FREQUENCIES, true) ? $this->popup_frequency : 'always',
            // Cambia cada vez que se guarda el evento: así "una vez por visita/día" se reinicia con un flyer nuevo.
            'version' => optional($this->updated_at)->timestamp ?? 0,
        ];
    }
}
