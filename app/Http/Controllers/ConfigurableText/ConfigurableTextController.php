<?php

namespace App\Http\Controllers\ConfigurableText;

use App\Models\User;
use App\Models\ConfigurableText;
use App\Models\Workshop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ConfigurableTextController extends Controller
{
    public const CUSTOM_TEXT_FIELDS = [
        'APPLICANT_REGISTRATION',
        'TENANT_REGISTRATION',
        'START_APPLICATION',
        'APPLICATION_INFORMATION_PRIOR_TO_FINALIZATION',
        'APPLICATION_FILES',
        'APPLICATION_INFORMATION_AFTER_FINALIZATION'
    ];

    public function index()
    {
        $this->authorize('editAny', ConfigurableText::class);
        $text_fields = [];
        foreach (self::CUSTOM_TEXT_FIELDS as $configurable_text_field) {
            $configurable_text = ConfigurableText::getConfigurableText($configurable_text_field);
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        foreach (Workshop::all() as $workshop) {
            $configurable_text = ConfigurableText::getConfigurableText("APPLICATION_INFORMATION_PER_WORKSHOP_AFTER_FINALIZATION", $workshop->id);
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        foreach (\App\Enums\FileType::cases() as $type) {
            if ($type == \App\Enums\FileType::PROFILE_PICTURE || $type == \App\Enums\FileType::RECEIPT) {
                continue; // Used internally, no user-visible description
            }

            $configurable_text = ConfigurableText::getConfigurableText("APPLICATION_FILE_" . strtoupper($type->value));
            if (user()->can('edit', $configurable_text)) {
                $text_fields[] = $configurable_text;
            }
        }
        return view(
            'configurable_texts.manage',
            [
                'text_fields' => $text_fields
            ]
        );
    }

    public function store(Request $request)
    {
        $this->authorize('editAny', ConfigurableText::class);
        $validated = array();
        foreach ($request->toArray() as $key => $value) {
            $configurableText = ConfigurableText::getConfigurableTextFromSummary($key);
            if ($configurableText) {
                $this->authorize('edit', $configurableText);
                $validated[$key] = $value;
            }
        }
        foreach ($validated as $key => $value) {
            $configurableText = ConfigurableText::getConfigurableTextFromSummary($key);
            if ($configurableText) {
                $configurableText->update(
                    ["rawtext" => $value]
                );
            }
        }

        return redirect()->back()->with('success', 'Configurable texts updated successfully.');
    }
}
