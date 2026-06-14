<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialItem;
use App\Models\StudentProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * GET /api/materials
     * Daftar semua materi (dengan filter kategori).
     */
    public function index(Request $request): JsonResponse
    {
        $materials = Material::with('items', 'creator')
            ->published()
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->orderBy('order')
            ->get()
            ->map(fn($m) => $this->formatMaterial($m));

        return response()->json(['data' => $materials]);
    }

    /**
     * GET /api/materials/{id}
     */
    public function show(int $id): JsonResponse
    {
        $material = Material::with('items', 'creator')->findOrFail($id);

        // Tandai progress siswa
        if (auth()->check() && auth()->user()->isSiswa()) {
            StudentProgress::updateOrCreate(
                ['user_id' => auth()->id(), 'material_id' => $material->id],
                ['last_accessed_at' => now()]
            );
        }

        return response()->json(['data' => $this->formatMaterial($material)]);
    }

    /**
     * POST /api/materials
     * Guru: tambah materi baru.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeGuru();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|in:huruf_dasar,rarangken,angka,contoh_kata',
            'order'       => 'integer|min:0',
            'is_published' => 'boolean',
            'items'       => 'array',
            'items.*.sunda_script'   => 'required|string',
            'items.*.latin_name'     => 'required|string',
            'items.*.pronunciation'  => 'required|string',
            'items.*.description'    => 'nullable|string',
            'items.*.examples'       => 'nullable|array',
            'items.*.order'          => 'integer|min:0',
        ]);

        $material = Material::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $material->items()->create($item);
            }
        }

        return response()->json([
            'message' => 'Materi berhasil ditambahkan.',
            'data'    => $this->formatMaterial($material->load('items')),
        ], 201);
    }

    /**
     * PUT /api/materials/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeGuru();

        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|in:huruf_dasar,rarangken,angka,contoh_kata',
            'order'        => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        $material->update($validated);

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data'    => $this->formatMaterial($material->fresh('items')),
        ]);
    }

    /**
     * DELETE /api/materials/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizeGuru();

        $material = Material::findOrFail($id);
        $material->delete();

        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }

    // ─────────── MATERIAL ITEMS ───────────

    /**
     * POST /api/materials/{id}/items
     */
    public function storeItem(Request $request, int $materialId): JsonResponse
    {
        $this->authorizeGuru();

        $material = Material::findOrFail($materialId);

        $validated = $request->validate([
            'sunda_script'  => 'required|string',
            'latin_name'    => 'required|string',
            'pronunciation' => 'required|string',
            'description'   => 'nullable|string',
            'examples'      => 'nullable|array',
            'order'         => 'integer|min:0',
        ]);

        $item = $material->items()->create($validated);

        return response()->json([
            'message' => 'Item materi berhasil ditambahkan.',
            'data'    => $item,
        ], 201);
    }

    /**
     * PUT /api/materials/{materialId}/items/{itemId}
     */
    public function updateItem(Request $request, int $materialId, int $itemId): JsonResponse
    {
        $this->authorizeGuru();

        $item = MaterialItem::where('material_id', $materialId)->findOrFail($itemId);

        $validated = $request->validate([
            'sunda_script'  => 'required|string',
            'latin_name'    => 'required|string',
            'pronunciation' => 'required|string',
            'description'   => 'nullable|string',
            'examples'      => 'nullable|array',
            'order'         => 'integer|min:0',
        ]);

        $item->update($validated);

        return response()->json(['message' => 'Item materi berhasil diperbarui.', 'data' => $item]);
    }

    /**
     * DELETE /api/materials/{materialId}/items/{itemId}
     */
    public function destroyItem(int $materialId, int $itemId): JsonResponse
    {
        $this->authorizeGuru();

        $item = MaterialItem::where('material_id', $materialId)->findOrFail($itemId);
        $item->delete();

        return response()->json(['message' => 'Item materi berhasil dihapus.']);
    }

    /**
     * PUT /api/materials/{id}/complete
     * Siswa: tandai materi selesai.
     */
    public function markComplete(int $id): JsonResponse
    {
        $material = Material::findOrFail($id);

        StudentProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'material_id' => $material->id],
            [
                'is_completed'       => true,
                'progress_percentage' => 100,
                'completed_at'       => now(),
                'last_accessed_at'   => now(),
            ]
        );

        return response()->json(['message' => 'Materi ditandai selesai.']);
    }

    private function formatMaterial(Material $material): array
    {
        return [
            'id'           => $material->id,
            'title'        => $material->title,
            'description'  => $material->description,
            'category'     => $material->category,
            'order'        => $material->order,
            'is_published' => $material->is_published,
            'created_by'   => $material->creator?->name,
            'items_count'  => $material->items->count(),
            'items'        => $material->items,
            'created_at'   => $material->created_at?->format('d/m/Y'),
        ];
    }

    private function authorizeGuru(): void
    {
        abort_unless(
            auth()->user()->isGuru() || auth()->user()->isAdmin(),
            403,
            'Hanya guru atau admin yang dapat melakukan tindakan ini.'
        );
    }
}
