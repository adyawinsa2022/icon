<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CopierController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Ambil raw body dari Power Automate
        $rawData = $request->getContent();

        // Decode dari URL encoded ke JSON string
        $decodedString = urldecode($rawData);

        // Decode lagi ke array PHP
        $dataArray = json_decode($decodedString, true);

        if (!is_array($dataArray)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
            ], 400);
        }

        // Tambahkan key "tanggal" untuk mencatat waktu update terakhir
        $finalData = [
            'date' => Carbon::now()->format('Y-m-d H:i:s'), // Contoh: 2025-09-30 14:35:00
            'data' => $dataArray
        ];

        // Simpan di storage laravel (storage/app/copier.json)
        Storage::disk('local')->put('copier.json', json_encode($finalData, JSON_PRETTY_PRINT));

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'received' => $finalData
        ]);
    }
}
