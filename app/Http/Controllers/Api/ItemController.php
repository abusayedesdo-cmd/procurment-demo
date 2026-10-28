<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();
        $query->with(['chartOfAccount']);
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

    public function show(Item $item)
    {
        $item->load(['chartOfAccount']);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['name' => self::cleanName($request->input('name'))]);

        $validated = $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'name' => 'required|string|max:255',
            'specification' => 'nullable|string'
        ]);

        $this->assertNotDuplicate((int) $validated['chart_of_account_id'], $validated['name']);

        $item = Item::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, Item $item)
    {
        if ($request->has('name')) {
            $request->merge(['name' => self::cleanName($request->input('name'))]);
        }

        $validated = $request->validate([
            'chart_of_account_id' => 'sometimes|required|exists:chart_of_accounts,id',
            'name' => 'sometimes|required|string|max:255',
            'specification' => 'nullable|string'
        ]);

        $this->assertNotDuplicate(
            (int) ($validated['chart_of_account_id'] ?? $item->chart_of_account_id),
            $validated['name'] ?? $item->name,
            $item->id
        );

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => $item,
        ]);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
        ]);
    }

    /** Trim and collapse repeated spaces so "Laptop  " and "Laptop" are the same name. */
    private static function cleanName($name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $name));
    }

    /** No two items with the same name (ignoring case/extra spaces) inside one group. */
    private function assertNotDuplicate(int $groupId, string $name, ?int $ignoreId = null): void
    {
        $taken = Item::where('chart_of_account_id', $groupId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'name' => 'This item already exists in the selected group. Please pick it from the list instead of adding it again.',
            ]);
        }
    }
}
