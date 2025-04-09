<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesAggregate;
use App\Models\ForecastSes;
use App\Models\ForecastDes;
use App\Models\ForecastTes;
use App\Models\ForecastEvaluation;
use App\Models\ForecastComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
//REMINDER!!!! berikan komentar karna ini bagian paling susah

class ForecastController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        return view('forecast.index', compact('products'));
    }

    public function ses()
    {
        $products = Product::orderBy('name')->get();
        return view('forecast.ses', compact('products'));
    }

    public function des()
    {
        $products = Product::orderBy('name')->get();
        return view('forecast.des', compact('products'));
    }

    public function tes()
    {
        $products = Product::orderBy('name')->get();
        return view('forecast.tes', compact('products'));
    }
//logika untuk menghitung dengan metode peramalan
    public function calculate(Request $request)
    {
        $request->validate([
            'method' => 'required|in:ses,des,tes',
            'product_id' => 'required|exists:products,id',
            'period' => 'required|integer|min:1',
            'alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'nullable|numeric|min:0|max:1',
            'gamma' => 'nullable|numeric|min:0|max:1',
            'forecast_periods' => 'required|integer|min:1|max:12',
        ]);

        $method = $request->method;
        $productId = $request->product_id;
        $period = $request->period;
        $alpha = $request->alpha;
        $beta = $request->beta ?? 0;
        $gamma = $request->gamma ?? 0;
        $forecastPeriods = $request->forecast_periods;

        // input data histori produk
        $historicalData = SalesAggregate::where('product_id', $productId)
            ->orderBy('year')
            ->orderBy('month')
            ->take($period)
            ->get();

        if ($historicalData->count() < $period) {
            return redirect()->back()->with('error', 'Data historis tidak cukup untuk periode yang dipilih.');
        }

        // persiapkan data
        $actualData = $historicalData->pluck('quantity')->toArray();
        $dates = [];
        foreach ($historicalData as $data) {
            $dates[] = $data->year . '-' . str_pad($data->month, 2, '0', STR_PAD_LEFT);
        }

        // perhitungan berdasarkan 3 metode exponential
        switch ($method) {
            case 'ses':
                $result = $this->calculateSES($actualData, $alpha, $forecastPeriods);
                break;
            case 'des':
                $result = $this->calculateDES($actualData, $alpha, $beta, $forecastPeriods);
                break;
            case 'tes':
                $result = $this->calculateTES($actualData, $alpha, $beta, $gamma, $forecastPeriods);
                break;
        }

        // Simpan hasil prakiraan ke database
        $this->saveForecastResults($method, $productId, $dates, $actualData, $result, $alpha, $beta, $gamma);

        // kalkulasi untuk evaluasi matrik
        $metrics = $this->calculateMetrics($actualData, $result['fitted']);

        // simpan hasil evaluasi matrik
        $this->saveEvaluationMetrics($method, $productId, $metrics);

        // membuat data untuk ditampilkan
        $product = Product::findOrFail($productId);
        $forecastDates = $this->generateForecastDates(end($dates), $forecastPeriods);
        $chartData = $this->prepareChartData($dates, $forecastDates, $actualData, $result);

        return view('forecast.result', compact(
            'method',
            'product',
            'period',
            'alpha',
            'beta',
            'gamma',
            'forecastPeriods',
            'chartData',
            'metrics',
            'result'
        ));
    }

    public function comparison()
    {
        $products = Product::orderBy('name')->get();
        $comparisons = ForecastComparison::with('product')->get();
        
        return view('forecast.comparison', compact('products', 'comparisons'));
    }
// fungsi khusus Single Exponential
    private function calculateSES($actualData, $alpha, $forecastPeriods)
    {
        $n = count($actualData);
        $fitted = [];
        $forecast = [];
        
        // inisiasi pertama untuk data penjualan atau aktual
        $fitted[0] = $actualData[0];
        
        // Hitung nilai yang dipasang SES
        for ($i = 1; $i < $n; $i++) {
            $fitted[$i] = $alpha * $actualData[$i - 1] + (1 - $alpha) * $fitted[$i - 1];
        }
        
        // hitung nilai peramalannya SES
        $lastFitted = end($fitted);
        for ($i = 0; $i < $forecastPeriods; $i++) {
            $forecast[$i] = $lastFitted;
        }
        
        return [
            'fitted' => $fitted,
            'forecast' => $forecast
        ];
    }
//fungsi khusus Double Exponential
    private function calculateDES($actualData, $alpha, $beta, $forecastPeriods)
    {
        $n = count($actualData);
        $level = [];
        $trend = [];
        $fitted = [];
        $forecast = [];
        
        // insiasi data
        $level[0] = $actualData[0];
        $trend[0] = $actualData[1] - $actualData[0];
        $fitted[0] = $actualData[0];
        
        // Hitung level, tren, dan nilai yang disesuaikan  DES
        for ($i = 1; $i < $n; $i++) {
            $level[$i] = $alpha * $actualData[$i] + (1 - $alpha) * ($level[$i - 1] + $trend[$i - 1]);
            $trend[$i] = $beta * ($level[$i] - $level[$i - 1]) + (1 - $beta) * $trend[$i - 1];
            $fitted[$i] = $level[$i - 1] + $trend[$i - 1];
        }
        
        // Hitung nilai perkiraan DES
        $lastLevel = end($level);
        $lastTrend = end($trend);
        for ($i = 1; $i <= $forecastPeriods; $i++) {
            $forecast[$i - 1] = $lastLevel + $i * $lastTrend;
        }
        
        return [
            'fitted' => $fitted,
            'forecast' => $forecast,
            'level' => $level,
            'trend' => $trend
        ];
    }
// fungsi untuk metode Triple Exponential
    private function calculateTES($actualData, $alpha, $beta, $gamma, $forecastPeriods)
    {
        $n = count($actualData);
        $level = [];
        $trend = [];
        $seasonal = [];
        $fitted = [];
        $forecast = [];
        
        // Tentukan panjang musim (dengan asumsi data bulanan, jadi musim = 12)
        $seasonLength = 12;
        
        // insiasi
        $level[0] = $actualData[0];
        $trend[0] = 0;
        
        // Inisiasiindeks musiman untuk DES
        for ($i = 0; $i < $seasonLength; $i++) {
            $seasonal[$i] = $i < $n ? $actualData[$i] / $level[0] : 1;
        }
        
        $fitted[0] = $level[0] * $seasonal[0];
        
        // Hitung level, tren, nilai musiman dan nilai yang disesuaikan
        for ($i = 1; $i < $n; $i++) {
            $s = $i % $seasonLength;
            $level[$i] = $alpha * ($actualData[$i] / $seasonal[$s]) + (1 - $alpha) * ($level[$i - 1] + $trend[$i - 1]);
            $trend[$i] = $beta * ($level[$i] - $level[$i - 1]) + (1 - $beta) * $trend[$i - 1];
            $seasonal[$s + $seasonLength] = $gamma * ($actualData[$i] / $level[$i]) + (1 - $gamma) * $seasonal[$s];
            $fitted[$i] = ($level[$i - 1] + $trend[$i - 1]) * $seasonal[$s];
        }
        
        // hitung nilai peramalan Triple Exponential 
        $lastLevel = end($level);
        $lastTrend = end($trend);
        for ($i = 1; $i <= $forecastPeriods; $i++) {
            $s = ($n + $i - 1) % $seasonLength;
            $forecast[$i - 1] = ($lastLevel + $i * $lastTrend) * $seasonal[$s + $seasonLength];
        }
        
        return [
            'fitted' => $fitted,
            'forecast' => $forecast,
            'level' => $level,
            'trend' => $trend,
            'seasonal' => $seasonal
        ];
    }
//fungsi perhitungan MSE MAE dan MAPE
    private function calculateMetrics($actual, $fitted)
    {
        $n = count($actual);
        $mse = 0;
        $mae = 0;
        $mape = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $error = $actual[$i] - $fitted[$i];
            $mse += pow($error, 2);
            $mae += abs($error);
            
            if ($actual[$i] != 0) {
                $mape += abs($error / $actual[$i]);
            }
        }
        
        $mse /= $n;
        $rmse = sqrt($mse);
        $mae /= $n;
        $mape = ($mape / $n) * 100;
        
        return [
            'mse' => $mse,
            'rmse' => $rmse,
            'mae' => $mae,
            'mape' => $mape
        ];
    }

    private function saveForecastResults($method, $productId, $dates, $actual, $result, $alpha, $beta, $gamma)
    {
        // fungsi Hapus perkiraan sebelumnya untuk produk dan metode TES
        switch ($method) {
            case 'ses':
                ForecastSes::where('product_id', $productId)->delete();
                break;
            case 'des':
                ForecastDes::where('product_id', $productId)->delete();
                break;
            case 'tes':
                ForecastTes::where('product_id', $productId)->delete();
                break;
        }
        
        // simpan nilanya
        $n = count($actual);
        for ($i = 0; $i < $n; $i++) {
            $date = explode('-', $dates[$i]);
            $year = $date[0];
            $month = intval($date[1]);
            
            switch ($method) {
                case 'ses':
                    ForecastSes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => $actual[$i],
                        'forecast' => $result['fitted'][$i],
                        'alpha' => $alpha,
                    ]);
                    break;
                case 'des':
                    ForecastDes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => $actual[$i],
                        'forecast' => $result['fitted'][$i],
                        'level' => $result['level'][$i],
                        'trend' => $result['trend'][$i],
                        'alpha' => $alpha,
                        'beta' => $beta,
                    ]);
                    break;
                case 'tes':
                    $s = $i % 12;
                    ForecastTes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => $actual[$i],
                        'forecast' => $result['fitted'][$i],
                        'level' => $result['level'][$i],
                        'trend' => $result['trend'][$i],
                        'seasonal' => $result['seasonal'][$s + 12],
                        'alpha' => $alpha,
                        'beta' => $beta,
                        'gamma' => $gamma,
                    ]);
                    break;
            }
        }
        
        // simpan nilai peramalan berdasarkan 3 metode exponential
        $forecastDates = $this->generateForecastDates(end($dates), count($result['forecast']));
        for ($i = 0; $i < count($result['forecast']); $i++) {
            $date = explode('-', $forecastDates[$i]);
            $year = $date[0];
            $month = intval($date[1]);
            
            switch ($method) {
                case 'ses':
                    ForecastSes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => null,
                        'forecast' => $result['forecast'][$i],
                        'alpha' => $alpha,
                    ]);
                    break;
                case 'des':
                    ForecastDes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => null,
                        'forecast' => $result['forecast'][$i],
                        'level' => end($result['level']),
                        'trend' => end($result['trend']),
                        'alpha' => $alpha,
                        'beta' => $beta,
                    ]);
                    break;
                case 'tes':
                    $s = ($n + $i) % 12;
                    ForecastTes::create([
                        'product_id' => $productId,
                        'year' => $year,
                        'month' => $month,
                        'actual' => null,
                        'forecast' => $result['forecast'][$i],
                        'level' => end($result['level']),
                        'trend' => end($result['trend']),
                        'seasonal' => $result['seasonal'][$s + 12],
                        'alpha' => $alpha,
                        'beta' => $beta,
                        'gamma' => $gamma,
                    ]);
                    break;
            }
        }
    }

    private function saveEvaluationMetrics($method, $productId, $metrics)
    {
        // Hapus perkiraan sebelumnya untuk produk dan metode
        ForecastEvaluation::where('product_id', $productId)
            ->where('method', $method)
            ->delete();
        
        // simpan hasil evaluasi
        ForecastEvaluation::create([
            'product_id' => $productId,
            'method' => $method,
            'mse' => $metrics['mse'],
            'rmse' => $metrics['rmse'],
            'mae' => $metrics['mae'],
            'mape' => $metrics['mape'],
            'date' => Carbon::now(),
        ]);
        
        // update tabel perbandingan
        $this->updateComparisonTable($productId);
    }

    private function updateComparisonTable($productId)
    {
        // Get evaluations for all methods for this product
        $evaluations = ForecastEvaluation::where('product_id', $productId)->get();
        
        if ($evaluations->count() > 0) {
            // untuk mencari nilai mape terbaik
            $bestMethod = $evaluations->sortBy('mape')->first()->method;
            
            // Hapus perkiraan sebelumnya untuk produk
            ForecastComparison::where('product_id', $productId)->delete();
            
            // simpan
            ForecastComparison::create([
                'product_id' => $productId,
                'best_method' => $bestMethod,
                'date' => Carbon::now(),
            ]);
        }
    }
// fungsi menghasilkan tanggal prakiraan
    private function generateForecastDates($lastDate, $periods)
    {
        $dates = [];
        $date = explode('-', $lastDate);
        $year = intval($date[0]);
        $month = intval($date[1]);
        
        for ($i = 0; $i < $periods; $i++) {
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
            $dates[] = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        
        return $dates;
    }
// buat grafiknya
    private function prepareChartData($dates, $forecastDates, $actual, $result)
    {
        $chartData = [
            'labels' => array_merge($dates, $forecastDates),
            'datasets' => [
                [
                    'label' => 'Data Aktual',
                    'data' => array_merge($actual, array_fill(0, count($forecastDates), null)),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                    'fill' => false
                ],
                [
                    'label' => 'Nilai Fitted',
                    'data' => array_merge($result['fitted'], array_fill(0, count($forecastDates), null)),
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                    'fill' => false
                ],
                [
                    'label' => 'Nilai Forecast',
                    'data' => array_merge(array_fill(0, count($dates), null), $result['forecast']),
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                    'fill' => false
                ]
            ]
        ];
        
        return $chartData;
    }
}
