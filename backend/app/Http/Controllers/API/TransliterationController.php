<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransliterationHistory;
use App\Services\TransliterationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransliterationController extends Controller
{
    public function __construct(private TransliterationService $service) {}

    /**
     * POST /api/transliterate
     * Endpoint transliterasi dua arah.
     */
    public function transliterate(Request $request): JsonResponse
    {
        $request->validate([
            'text'      => 'required|string|max:2000',
            'direction' => 'required|in:latin_to_sunda,sunda_to_latin',
        ]);

        $input  = $request->text;
        $output = match ($request->direction) {
            'latin_to_sunda' => $this->service->convertLatinToSunda($input),
            'sunda_to_latin' => $this->service->convertSundaToLatin($input),
        };

        // Simpan riwayat transliterasi
        TransliterationHistory::create([
            'user_id'    => auth()->id(),
            'direction'  => $request->direction,
            'input_text' => $input,
            'output_text' => $output,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'input'     => $input,
            'output'    => $output,
            'direction' => $request->direction,
        ]);
    }

    /**
     * GET /api/transliterate/mappings
     * Mendapatkan semua mapping aksara Sunda untuk referensi.
     */
    public function getMappings(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getAllMappings(),
        ]);
    }

    /**
     * GET /api/transliterate/huruf-dasar
     * Data huruf dasar untuk modul pembelajaran.
     */
    public function getHurufDasar(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getHurufDasar(),
        ]);
    }

    /**
     * GET /api/transliterate/rarangken
     */
    public function getRarangken(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getRarangken(),
        ]);
    }

    /**
     * GET /api/transliterate/angka
     */
    public function getAngkaSunda(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getAngkaSunda(),
        ]);
    }

    /**
     * GET /api/transliterate/history
     * Riwayat transliterasi pengguna yang sedang login.
     */
    public function history(Request $request): JsonResponse
    {
        $history = TransliterationHistory::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json($history);
    }
}
