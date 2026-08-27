<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Copier;
use Illuminate\Http\Request;

class CopierController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'mtd');
        $value = $request->input('value');

        switch ($range) {
            case 'day':
                $startDate = $value ? Carbon::parse($value)->startOfDay() : Carbon::yesterday();
                $endDate   = $value ? Carbon::parse($value)->endOfDay() : Carbon::yesterday();
                break;

            case 'week':
                if (!empty($value)) {
                    [$year, $week] = explode('-W', $value);
                } else {
                    $year = now()->year;
                    $week = now()->weekOfYear;
                }
                $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
                break;

            case 'month':
                $month = $value ?? now()->format('Y-m');
                $startDate = Carbon::parse($month)->startOfMonth();
                $endDate   = Carbon::parse($month)->endOfMonth();
                break;

            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
        }

        if ($range === 'mtd') {
            $data = Copier::select('name', 'limit', 'bw_counter as bw', 'color_counter as color', 'total_counter as total')
                ->whereDate('usage_date', '<=', Carbon::today())
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('copiers')
                        ->groupBy('name');
                })
                ->get();
        } else {
            $data = Copier::select('name', 'limit')
                ->selectRaw('SUM(bw_daily) as bw')
                ->selectRaw('SUM(color_daily) as color')
                ->selectRaw('SUM(total_daily) as total')
                ->whereBetween('usage_date', [$startDate, $endDate])
                ->groupBy('name', 'limit')
                ->get();
        }

        $date = Copier::where('usage_date', '<=', Carbon::now()->format('Y-m-d'))
            ->max('updated_at');

        return view('copier.index', compact(['data', 'date', 'range']));
    }
}
