<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Copier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CopierController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Ambil raw body dari Power Automate
        $rawData = $request->getContent();

        if ($request->isJson()) {
            $dataArray = $request->json()->all();
        } else {
            $decodedString = urldecode($rawData);
            $dataArray = json_decode($decodedString, true);
        }

        if (!is_array($dataArray)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
            ], 400);
        }

        $usageDate = Carbon::today();
        $results = [];

        // 3. Proses dan Simpan ke Database (Dalam Transaksi)
        DB::beginTransaction();

        try {
            foreach ($dataArray as $userData) {
                $userName = $userData['name'];

                // Pencegahan duplikasi: Cek apakah user sudah dicatat hari ini
                $existingReading = Copier::where('name', $userName)
                    ->whereDate('usage_date', $usageDate)
                    ->first();

                // if ($existingReading) {
                //     $results[] = ['user' => $userName, 'status' => 'Skipped', 'message' => 'Data hari ini sudah ada.'];
                //     continue; // Lewati ke user berikutnya
                // }

                // Ambil data H-1 untuk user yang sama
                $yesterdayReading = Copier::where('name', $userName)
                    ->where('usage_date', '<', $usageDate)
                    ->orderBy('usage_date', 'desc')
                    ->first();

                $dailyUsage = ['bw' => 0, 'color' => 0, 'total' => 0];
                $counters = ['bw', 'color', 'total'];

                // Hitung Selisih
                if ($yesterdayReading) {
                    foreach ($counters as $c) {
                        $currentCounter = $userData[$c];
                        // Ambil counter H-1 dari kolom DB yang sesuai (contoh: 'bw_counter')
                        $yesterdayCounter = $yesterdayReading->{$c . '_counter'};

                        if ($currentCounter >= $yesterdayCounter) {
                            $dailyUsage[$c] = $currentCounter - $yesterdayCounter;
                        } else {
                            // Log jika counter reset/error
                            Log::warning("Anomali counter '$c' user '$userName' reset. Total H: $currentCounter, Total H-1: $yesterdayCounter");
                            $dailyUsage[$c] = $currentCounter;
                        }
                    }
                } else {
                    // Jika tidak ada data sebelumnya (data pertama kali masuk)
                    foreach ($counters as $c) {
                        $dailyUsage[$c] = $userData[$c];
                    }
                }

                // Simpan Data Harian ke DB
                if ($existingReading) {
                    // UPDATE data yang sudah ada
                    $existingReading->update([
                        'bw_counter'    => $userData['bw'],
                        'color_counter' => $userData['color'],
                        'total_counter' => $userData['total'],
                        'limit'         => $userData['limit'],
                        'bw_daily'      => $dailyUsage['bw'],
                        'color_daily'   => $dailyUsage['color'],
                        'total_daily'   => $dailyUsage['total'],
                    ]);

                    $results[] = ['user' => $userName, 'status' => 'Updated', 'daily_total' => $existingReading->total_daily];
                } else {
                    // INSERT data baru
                    $reading = Copier::create([
                        'name'           => $userName,
                        'usage_date'     => $usageDate,
                        'bw_counter'     => $userData['bw'],
                        'color_counter'  => $userData['color'],
                        'total_counter'  => $userData['total'],
                        'limit'          => $userData['limit'],
                        'bw_daily'       => $dailyUsage['bw'],
                        'color_daily'    => $dailyUsage['color'],
                        'total_daily'    => $dailyUsage['total'],
                    ]);

                    $results[] = ['user' => $userName, 'status' => 'Inserted', 'daily_total' => $reading->total_daily];
                }
            }

            DB::commit(); // Commit semua perubahan

            return response()->json([
                'status' => 'success',
                'message' => 'Semua pembacaan user berhasil disimpan dan dihitung ke database.',
                'results' => $results
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses data ke database.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
