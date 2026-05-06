<?php

namespace App\Http\Controllers\ConfigurableValue;

use App\Models\User;
use App\Models\ConfigurableValue;
use App\Models\Workshop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Casts\Attribute;

// TODO: Extend to support configuring 'number' type values as well if needed.
class ConfigurableValueController extends Controller
{
    public const CUSTOM_TEXT_FIELDS = [
        'APPLICANT_REGISTRATION',
        'TENANT_REGISTRATION',
        'START_APPLICATION',
        'APPLICATION_INFORMATION_PRIOR_TO_FINALIZATION',
        'APPLICATION_FILES',
        'APPLICATION_INFORMATION_AFTER_FINALIZATION',
    ];

    public function index()
    {
        $this->authorize('editAny', ConfigurableValue::class);
        $text_fields = [];
        foreach (self::CUSTOM_TEXT_FIELDS as $configurable_text_field) {
            $configurable_text = ConfigurableValue::getConfigurableValue($configurable_text_field);
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        foreach (Workshop::all() as $workshop) {
            $configurable_text = ConfigurableValue::getConfigurableValue("APPLICATION_INFORMATION_PER_WORKSHOP_AFTER_FINALIZATION", $workshop->id);
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        foreach (\App\Enums\FileType::cases() as $type) {
            if ($type == \App\Enums\FileType::PROFILE_PICTURE || $type == \App\Enums\FileType::RECEIPT) {
                continue; // Used internally, no user-visible description
            }

            $configurable_text = ConfigurableValue::getConfigurableValue("APPLICATION_FILE_" . strtoupper($type->value));
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        return view(
            'configurable_texts.manage',
            [
                'text_fields' => $text_fields,
            ]
        );
    }

    public function store(Request $request)
    {
        $this->authorize('editAny', ConfigurableValue::class);

        $configurableValues = [];
        foreach ($request->except('_token') as $key => $value) {
            $configurableValue = ConfigurableValue::fromSummary($key);
            if ($configurableValue?->exists) {
                $this->authorize('edit', $configurableValue);
                $configurableValues[] = ['model' => $configurableValue, 'value' => $value];
            }
        }
        foreach ($configurableValues as $entry) {
            $entry['model']->update(["raw_value" => $entry['value']]);
        }


        return redirect()->back()->with('success', 'Configurable texts updated successfully.');
    }
}
