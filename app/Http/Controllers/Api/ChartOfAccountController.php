<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();
        $query->with(['category', 'items']);
        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['category', 'items']);

        return response()->json([
            'success' => true,
            'data' => $chartOfAccount,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge($this->cleaned($request));

        $validated = $request->validate([
            'category_id' => 'required|exists:procurement_categories,id',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255'
        ]);

        $this->assertCodeFree((int) $validated['category_id'], $validated['code']);

        $chartOfAccount = ChartOfAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount created successfully',
            'data' => $chartOfAccount,
        ], 201);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $request->merge($this->cleaned($request));

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:procurement_categories,id',
            'code' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255'
        ]);

        $this->assertCodeFree(
            (int) ($validated['category_id'] ?? $chartOfAccount->category_id),
            $validated['code'] ?? $chartOfAccount->code,
            $chartOfAccount->id
        );

        $chartOfAccount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount updated successfully',
            'data' => $chartOfAccount,
        ]);
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount deleted successfully',
        ]);
    }

    /** Trim and collapse repeated spaces in whichever of code/name was sent. */
    private function cleaned(Request $request): array
    {
        $out = [];
        foreach (['code', 'name'] as $field) {
            if ($request->has($field)) {
                $out[$field] = trim((string) preg_replace('/\s+/u', ' ', (string) $request->input($field)));
            }
        }

        return $out;
    }

    /** A group code is unique inside its category (ignoring case/extra spaces). */
    private function assertCodeFree(int $categoryId, string $code, ?int $ignoreId = null): void
    {
        $taken = ChartOfAccount::where('category_id', $categoryId)
            ->whereRaw('LOWER(TRIM(code)) = ?', [mb_strtolower($code)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'code' => 'This group code already exists in the selected category.',
            ]);
        }
    }
}
