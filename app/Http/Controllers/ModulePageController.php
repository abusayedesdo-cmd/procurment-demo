<?php

namespace App\Http\Controllers;

use App\Support\ActivePrContext;

class ModulePageController extends Controller
{
    public function index()
    {
        return view('modules.index');
    }

    public function show(string $slug)
    {
        $stepSlug = null;
        foreach (ProcessStepPageController::STEPS as $sSlug => $step) {
            foreach ($step['modules'] as $m) {
                if (($m['slug'] ?? null) === $slug) {
                    $stepSlug = $sSlug;
                    break 2;
                }
            }
        }


        $contextFilterValue = null;
        $contextLabel = null;
        if (! request()->boolean('history')) {
            $prefillField = ProcessStepPageController::PREFILL_FIELD_BY_SLUG[$slug] ?? null;
            if ($prefillField) {
                $context = ActivePrContext::resolve();
                $contextFilterValue = ActivePrContext::valueForField($prefillField, $context);
                if ($contextFilterValue && $context['pr']) {
                    $contextLabel = $context['pr']->pr_number ?? ('PR-' . $context['pr']->id);
                }
            }
        }

        return view('modules.show', [
            'slug' => $slug,
            'stepSlug' => $stepSlug,
            'contextFilterValue' => $contextFilterValue,
            'contextLabel' => $contextLabel,
        ]);
    }
}