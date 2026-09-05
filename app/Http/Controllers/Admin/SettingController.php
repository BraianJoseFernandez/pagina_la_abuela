<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        $rawCadetes = json_decode($settings['delivery_cadetes'] ?? '[]', true) ?: [];
        $cadetes = array_map(function ($c) {
            $c['is_active'] = isset($c['is_active']) ? (bool)$c['is_active'] : true;
            return $c;
        }, $rawCadetes);

        return view('admin.settings.index', compact('settings', 'cadetes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = [
            'restaurant_name',
            'restaurant_slogan',
            'whatsapp_phone',
            'display_phone',
            'address',
            'maps_url',
            'instagram_user',
            'instagram_url',
        ];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field));
        }

        // Procesar y guardar lista dinámica de motomandados / cadetes (sin límite)
        $rawCadetes = $request->input('cadetes', []);
        $defaultColors = ['#059669', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#db2777', '#d97706'];
        $cleanedCadetes = [];

        if (is_array($rawCadetes)) {
            $index = 0;
            foreach ($rawCadetes as $item) {
                $name = trim($item['name'] ?? '');
                $phone = trim($item['phone'] ?? '');
                $color = trim($item['color'] ?? ($defaultColors[$index % count($defaultColors)] ?? '#059669'));
                $isActive = isset($item['is_active']) && ($item['is_active'] === '1' || $item['is_active'] === 1 || $item['is_active'] === true || $item['is_active'] === 'true');

                if ($name !== '' || $phone !== '') {
                    $index++;
                    $cleanedCadetes[] = [
                        'id' => $index,
                        'name' => $name,
                        'phone' => $phone,
                        'color' => $color,
                        'is_active' => $isActive,
                    ];
                }
            }
        }

        Setting::set('delivery_cadetes', json_encode(array_values($cleanedCadetes)));

        return redirect()->route('admin.settings.index')
            ->with('success', '¡Información y datos del negocio actualizados exitosamente!');
    }
}
