<?php

namespace App\Http\Controllers;

use App\Models\SalesAggregate;
use App\Models\Product;
use App\Models\ForecastSes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ForecastController extends Controller
{
    /**
     * Menampilkan halaman utama forecasting dengan daftar produk
     */
    public function index()
    {
        $products = Product::with('category')->get();
        return view('forecast.index', compact('products'));
    }

    /**
     * Menampilkan form peramalan SES (Single Exponential Smoothing)
     */
    public function ses(Request $request)
    {
        $products = Product::with('salesAggregates')->get();
        $selectedProductId = $request->input('product_id');
        $availablePeriods = [];

        if ($selectedProductId) {
            $availablePeriods = SalesAggregate::where('product_id', $selectedProductId)
                ->select('period')
                ->orderBy('period', 'asc')
                ->distinct()
                ->pluck('period')
                ->toArray();
        }

        return view('forecast.ses', compact('products', 'availablePeriods', 'selectedProductId'));
    }

    /**
     * Menghitung forecast berdasarkan input user
     */
    public function calculate(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            $historicalData = $this->getHistoricalData($validated);
            
            // Cek jumlah data historis
            $dataCount = $historicalData->count();
            $notification = null;
            if ($dataCount < 12) {
                $notification = 'Data produk kurang dari 12 bulan. Hasil forecast mungkin kurang akurat.';
            }

            // Hitung alpha (manual/otomatis)
            if ($validated['alpha_mode'] === 'manual') {
                $alpha = $validated['alpha_manual'];
            } else {
                $alpha = $this->calculateBestAlpha($historicalData->pluck('total_sales')->toArray());
            }

            $periods = $historicalData->pluck('period')->toArray();

            // Hitung forecast menggunakan SES
            $result = $this->calculateSES(
                $historicalData->pluck('total_sales')->toArray(),
                $alpha,
                $validated['forecast_periods']
            );

            $result['periods'] = $periods;
            $result['formatted_periods'] = $this->formatPeriods($periods);

            // Siapkan data untuk chart dan simpan hasil
            $chartData = $this->prepareChartData($historicalData, $result);
            $this->saveForecastResults($validated['method'], $validated['product_id'], $result, $historicalData->last()->period);

            return view('forecast.result', [
                'method' => strtoupper($validated['method']),
                'product' => Product::find($validated['product_id']),
                'parameters' => ['alpha' => $alpha],
                'result' => $result,
                'chartData' => $chartData,
                'metrics' => $this->calculateMetrics($result),
                'notification' => $notification
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['system_error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Validasi input dari user
     */
    public function validateRequest(Request $request)
    {
        return $request->validate([
            'method' => 'required|in:ses',
            'product_id' => 'required|exists:products,id',
            'alpha_mode' => 'required|in:auto,manual',
            'alpha_manual' => 'required_if:alpha_mode,manual|numeric|min:0.01|max:0.99',
            'start_period' => 'required_if:method,ses|date_format:Y-m',
            'end_period' => 'required_if:method,ses|date_format:Y-m',
            'forecast_periods' => 'required|integer|min:1|max:24',
        ]);
    }

    /**
     * Mengambil data historis penjualan produk
     */
    private function getHistoricalData(array $validated)
    {
        $data = SalesAggregate::where('product_id', $validated['product_id'])
            ->where('period', '>=', $validated['start_period'])
            ->where('period', '<=', $validated['end_period'])
            ->orderBy('period')
            ->get();

        if ($data->isEmpty()) {
            throw new \Exception('Tidak ada data penjualan dari periode yang dipilih.');
        }

        // Minimal 3 data untuk forecasting
        if ($data->count() < 3) {
            throw new \Exception('Peramalan membutuhkan minimal 3 data historis.');
        }

        return $data;
    }

    /**
     * Menghitung forecast menggunakan metode SES (Single Exponential Smoothing)
     */
    private function calculateSES(array $actualData, float $alpha, int $forecastPeriods)
    {
        $n = count($actualData);
        $fitted = [];

        // Hitung fitted values mulai dari data kedua
        for ($i = 1; $i < $n; $i++) {
            $fitted[$i] = $alpha * $actualData[$i - 1] + (1 - $alpha) * ($fitted[$i - 1] ?? $actualData[$i - 1]);
        }

        // Hitung forecast untuk periode berikutnya
        $lastValue = $alpha * $actualData[$n - 1] + (1 - $alpha) * ($fitted[$n - 1] ?? $actualData[$n - 1]);
        $forecast = array_fill(0, $forecastPeriods, $lastValue);

        return [
            'method' => 'SES',
            'actual' => $actualData,
            'fitted' => $fitted,
            'forecast' => $forecast,
            'parameters' => ['alpha' => $alpha],
        ];
    }

    /**
     * Menghitung alpha terbaik secara otomatis
     */
    private function calculateBestAlpha(array $actualData)
    {
        $bestAlpha = 0.01;
        $lowestError = INF;

        // Cari alpha dengan MSE terendah (0.01 - 0.99)
        for ($alpha = 0.01; $alpha < 1; $alpha += 0.01) {
            $forecast = $this->calculateSES($actualData, $alpha, 0);
            $mse = $this->calculateMSE($actualData, $forecast['fitted']);
            if ($mse < $lowestError) {
                $lowestError = $mse;
                $bestAlpha = $alpha;
            }
        }

        return round($bestAlpha, 2);
    }

    /**
     * Menghitung Mean Squared Error (MSE)
     */
    private function calculateMSE(array $actual, array $forecast)
    {
        $sum = 0;
        $count = 0;

        for ($i = 1; $i < count($actual); $i++) {
            if (!isset($actual[$i]) || !isset($forecast[$i])) continue;
            $error = $actual[$i] - $forecast[$i];
            $sum += pow($error, 2);
            $count++;
        }

        return $count > 0 ? $sum / $count : 0;
    }

    /**
     * Menyiapkan data untuk ditampilkan dalam chart
     */
    private function prepareChartData($historicalData, array $result)
    {
        // Format label periode historis
        $labelsHist = $historicalData->pluck('period')->map(
            fn($date) => Carbon::parse($date . '-01')->format('M Y')
        )->toArray();

        // Buat label untuk periode forecast
        $forecastCount = count($result['forecast']);
        $forecastLabels = [];
        $lastDate = Carbon::parse(end($labelsHist));

        for ($i = 1; $i <= $forecastCount; $i++) {
            $forecastLabels[] = $lastDate->copy()->addMonths($i)->format('M Y');
        }

        // Gabungkan label historis dan forecast
        $labels = array_merge($labelsHist, $forecastLabels);

        // Gabungkan data aktual dan forecast
        $actualSeries = array_merge($result['actual'], array_fill(0, $forecastCount, null));
        $forecastSeries = array_merge($result['fitted'] ?? [], $result['forecast']);

        return [
            'labels' => $labels,
            'actual' => $actualSeries,
            'forecast' => $forecastSeries,
        ];
    }

    /**
     * Menyimpan hasil forecast ke database
     */
    private function saveForecastResults(string $method, int $productId, array $result, string $lastPeriod)
    {
        $model = match($method) {
            'ses' => ForecastSes::class,
        };

        DB::transaction(function () use ($model, $productId, $result, $lastPeriod) {
            // Hapus forecast lama untuk produk ini
            $model::where('product_id', $productId)->delete();

            $lastDate = Carbon::parse($lastPeriod);

            // Simpan fitted values
            foreach ($result['fitted'] as $index => $value) {
                if (!isset($value)) continue;
                
                $model::create([
                    'product_id'  => $productId,
                    'period'      => $lastDate->copy()->addMonths($index)->format('Y-m-01'),
                    'actual'      => $result['actual'][$index] ?? null,
                    'forecast'    => $value,
                    'alpha'       => $result['parameters']['alpha'],
                ]);
            }

            // Simpan forecast values
            $fittedCount = count(array_filter($result['fitted']));
            foreach ($result['forecast'] as $index => $value) {
                $model::create([
                    'product_id'  => $productId,
                    'period'      => $lastDate->copy()->addMonths($fittedCount + $index + 1)->format('Y-m-01'),
                    'forecast'    => $value,
                    'alpha'       => $result['parameters']['alpha'],
                ]);
            }
        });
    }

    /**
     * Menghitung metrik evaluasi forecast (MSE, MAE, MAPE, RMSE)
     */
    private function calculateMetrics(array $result)
    {
        $actual = $result['actual'];
        $fitted = $result['fitted'] ?? [];

        if (empty($actual) || empty($fitted)) {
            throw new \Exception("Data aktual atau fitted tidak tersedia untuk evaluasi.");
        }

        $mse = $mae = $mape = 0.0;
        $count = 0;

        // Hitung error untuk setiap periode
        for ($i = 1; $i < count($fitted); $i++) {
            if (!isset($actual[$i]) || !isset($fitted[$i])) continue;
            if ($actual[$i] == 0) continue;

            $error = $actual[$i] - $fitted[$i];
            $mse += pow($error, 2);
            $mae += abs($error);
            $mape += abs($error / $actual[$i]);
            $count++;
        }

        $mse_mean = $count > 0 ? $mse / $count : 0;

        return [
            'mse'  => $mse_mean,
            'mae'  => $count > 0 ? $mae / $count : 0,
            'mape' => $count > 0 ? ($mape / $count) * 100 : 0,
            'rmse' => $count > 0 ? sqrt($mse_mean) : 0,
        ];
    }

    /**
     * Format periode untuk ditampilkan (Bulan Tahun)
     */
    private function formatPeriods(array $periods)
    {
        return array_map(function ($period) {
            return Carbon::parse($period . '-01')->format('M Y');
        }, $periods);
    }
   public function printPDF($productId)
{
    $product = Product::findOrFail($productId);
    
    $latestForecast = ForecastSes::where('product_id', $productId)
        ->orderBy('created_at', 'desc')
        ->firstOrFail();

    $forecastData = ForecastSes::where('product_id', $productId)
        ->where('created_at', $latestForecast->created_at)
        ->get()
        ->groupBy(function($item) {
            return $item->actual ? 'fitted' : 'forecast';
        });

    // Pastikan ada data fitted sebelum generate chart
    if (!isset($forecastData['fitted'])) {
        abort(404, 'Data historis tidak ditemukan');
    }

    // Hitung metrik evaluasi
    $metrics = $this->calculateMetricsForPrint($forecastData);

    // Generate chart image
    $chartImage = $this->generateChartImage($forecastData);
    
    // Konversi ke base64 untuk ditampilkan di HTML
    $chartBase64 = 'data:image/png;base64,' . base64_encode($chartImage);

    $pdf = Pdf::loadView('forecast.print', [
        'product' => $product,
        'fittedData' => $forecastData['fitted'] ?? [],
        'forecastData' => $forecastData['forecast'] ?? [],
        'alpha' => $latestForecast->alpha,
        'chartImage' => $chartBase64,
        'createdAt' => $latestForecast->created_at,
        'metrics' => $metrics // Tambahkan metrik ke view
    ]);

    return $pdf->download('forecast_report_' . $product->name . '.pdf');
}

private function calculateMetricsForPrint($forecastData)
{
    $actual = [];
    $fitted = [];

    foreach ($forecastData['fitted'] as $item) {
        if ($item->actual !== null && $item->forecast !== null) {
            $actual[] = $item->actual;
            $fitted[] = $item->forecast;
        }
    }

    if (empty($actual) || empty($fitted)) {
        return [
            'mse' => 0,
            'mae' => 0,
            'mape' => 0,
            'rmse' => 0
        ];
    }

    $mse = $mae = $mape = 0.0;
    $count = count($actual);

    for ($i = 0; $i < $count; $i++) {
        $error = $actual[$i] - $fitted[$i];
        $mse += pow($error, 2);
        $mae += abs($error);
        if ($actual[$i] != 0) {
            $mape += abs($error / $actual[$i]);
        }
    }

    $mse_mean = $mse / $count;
    $mape_mean = ($mape / $count) * 100;

    return [
        'mse' => number_format($mse_mean, 2),
        'mae' => number_format($mae / $count, 2),
        'mape' => number_format($mape_mean, 2),
        'rmse' => number_format(sqrt($mse_mean), 2)
    ];
}
private function generateChartImage($forecastData)
{
    $labels = [];
    $actual = [];
    $forecast = [];

    foreach ($forecastData['fitted'] ?? [] as $item) {
        $labels[] = Carbon::parse($item->period)->format('M Y');
        $actual[] = $item->actual;
        $forecast[] = $item->forecast;
    }

    foreach ($forecastData['forecast'] ?? [] as $item) {
        $labels[] = Carbon::parse($item->period)->format('M Y');
        $actual[] = null;
        $forecast[] = $item->forecast;
    }

    $chartConfig = [
        'type' => 'line',
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Aktual',
                    'data' => $actual,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)'
                ],
                [
                    'label' => 'Forecast',
                    'data' => $forecast,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'borderDash' => [5, 5]
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Grafik Peramalan Penjualan'
                ]
            ]
        ]
    ];

    $chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
    
    // Gunakan cURL untuk lebih reliable
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chartUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $image = curl_exec($ch);
    curl_close($ch);

    if (!$image) {
        throw new \Exception('Gagal menghasilkan grafik');
    }

    return $image;
}


}