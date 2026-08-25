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
        return view('admin.settings.index', compact('settings'));
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

        return redirect()->route('admin.settings.index')
            ->with('success', '¡Información y datos del negocio actualizados exitosamente!');
    }
}
