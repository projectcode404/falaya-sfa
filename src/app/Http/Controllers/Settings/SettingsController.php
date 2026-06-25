<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Setting\UpdateSettingAction;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->keyBy('setting_key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request, UpdateSettingAction $action): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            $action->execute($key, $value ?? '');
        }

        return back()->with('success', 'Settings berhasil disimpan.');
    }
}
