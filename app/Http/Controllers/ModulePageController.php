<?php

namespace App\Http\Controllers;

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

        return view('modules.show', ['slug' => $slug, 'stepSlug' => $stepSlug]);
    }
}