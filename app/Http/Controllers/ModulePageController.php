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

        // "Back" destination: normally the Process Step this module belongs
        // to (so the officer returns to the step's module list). But when
        // arriving straight from a Dashboard card (?from=dashboard) —
        // rather than via Process Steps — that intermediate step page was
        // never part of the officer's path, so Back should return them
        // straight to the Dashboard instead of inserting an extra hop.
        if (request()->query('from') === 'dashboard') {
            $backUrl = route('dashboard');
            $backLabel = 'Dashboard';
        } elseif ($stepSlug) {
            $backUrl = route('process-steps.show', ['slug' => $stepSlug, 'skip_redirect' => 1]);
            $backLabel = 'Back to Step';
        } else {
            $backUrl = route('dashboard');
            $backLabel = 'Dashboard';
        }

        return view('modules.show', [
            'slug' => $slug,
            'stepSlug' => $stepSlug,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
            'contextFilterValue' => $contextFilterValue,
            'contextLabel' => $contextLabel,
        ]);
    }
}