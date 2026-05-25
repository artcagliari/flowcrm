<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request)
    {
        return $this->success(Setting::where('company_id', $request->attributes->get('current_company')->id)->pluck('setting_value', 'setting_key'));
    }

    public function update(UpdateSettingsRequest $request)
    {
        $companyId = $request->attributes->get('current_company')->id;
        foreach ($request->validated('settings') as $key => $value) {
            Setting::updateOrCreate(['company_id' => $companyId, 'setting_key' => $key], ['setting_value' => $value]);
        }

        return $this->success(Setting::where('company_id', $companyId)->pluck('setting_value', 'setting_key'), 'Configurações salvas.');
    }
}
