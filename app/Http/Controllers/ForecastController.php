<?php

namespace App\Http\Controllers;
//hard part. komentar untuk bagian sulit
use App\Models\SalesAggregate;
use App\Models\Product;
use App\Models\ForecastSes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ForecastController extends Controller
{
    // Method untuk halaman utama forecasting
    public function index()
    {
        $products = Product::with('category')->get();
        return view('forecast.index', compact('products'));
    }

    // Method untuk halaman SES
    public function ses()
    {
        $products = Product::with('salesAggregates')->get();
        return view('forecast.ses', compact('products'));
    }

    // Method utama untuk kalkulasi semua jenis forecasting
    public function calculate(Request $request)
{
    try {
        // Validasi input
        $validated = $this->validateRequest($request);
        
        // Ambil data historis
        $historicalData = $this->getHistoricalData($validated);
        
        // Proses peramalan
        $result = $this->processForecast($validated, $historicalData);

        // Siapkan data grafik
        $chartData = $this->prepareChartData($historicalData, $result, $validated); // Pastikan $chartData di sini
        
        // Simpan hasil peramalan
        $this->saveForecastResults($validated['method'], $validated['product_id'], $result);
        // dd($chartData); 
        // Kirim data ke view
        return view('forecast.result', [
            'method' => strtoupper($validated['method']),
            'product' => Product::find($validated['product_id']),
            'parameters' => $result['parameters'],
            'result' => $result,
            'chartData' => $chartData,  // Pastikan chartData ada
            'metrics' => $this->calculateMetrics($result)
        ]);
    } catch (\Exception $e) {
        // Tangani error
        return redirect()->back()->withErrors(['system_error' => $e->getMessage()])->withInput();
    }
}

    
    // Menambahkan fungsi perbandingan di ForecastController

    // ========================
    // CORE BUSINESS LOGIC
    // ========================
    public function validateRequest(Request $request)
    {
        return $request->validate([
            'method'           => 'required|in:ses',
            'product_id'       => 'required|exists:products,id',
            'alpha'            => 'required|numeric|min:0.01|max:0.99',
            'start_period'     => 'required_if:method,ses|date', // Start period hanya untuk SES
            'period'           => 'required_if:method,des,tes|integer|min:3|max:36', // Periode untuk DES/TES wajib diisi
            'forecast_periods' => 'required|integer|min:1|max:24',
        ]);
    }

    private function getHistoricalData(array $validated)
    {
        $productId = $validated['product_id'];
        // Ambil data penjualan mulai dari periode awal (start_period) yang dipilih
        $startDate = Carbon::parse($validated['start_period']);
        $data = SalesAggregate::where('product_id', $productId)
            ->where('period', '>=', $startDate)
            ->orderBy('period')
            ->get();
        if ($data->isEmpty()) {
            throw new \Exception('Tidak ada data penjualan dari periode awal yang dipilih.');
        }
        return $data;
    }
private function processForecast(array $validated, $historicalData)
{

    return $this->calculateSES(
        $historicalData->pluck('total_sales')->toArray(),
        $validated['alpha'],
        $validated['forecast_periods']
    );
}


private function calculateSES(array $actualData, float $alpha, int $forecastPeriods)
{
    $n = count($actualData);
    $fitted = [];
    // Inisialisasi: anggap ramalan pertama sama dengan data aktual pertama
    $fitted[0] = $actualData[0]; 

    // Perhitungan nilai fitted untuk setiap periode actual berikutnya
    for ($i = 1; $i < $n; $i++) {
        $fitted[$i] = $alpha * $actualData[$i-1] + (1 - $alpha) * $fitted[$i-1];
    }

    // Perhitungan forecast untuk periode mendatang (out-of-sample)
    $lastValue = $alpha * $actualData[$n-1] + (1 - $alpha) * $fitted[$n-1];
    $forecast = array_fill(0, $forecastPeriods, $lastValue);

    return [
        'method'    => 'SES',
        'actual'    => $actualData,
        'fitted'    => $fitted,
        'forecast'  => $forecast,
        'parameters'=> ['alpha' => $alpha]
    ];
}




    // ========================
    // DATA PROCESSING UTILITIES
    // ========================

    private function prepareChartData($historicalData, array $result)
    {
        // Siapkan label bulan/tahun untuk data historis
        $labelsHist = $historicalData->pluck('period')->map(
            fn($date) => Carbon::parse($date)->format('M Y')
        )->toArray();

        // Siapkan label untuk periode forecast (bulan setelah data terakhir)
        $forecastCount = count($result['forecast']);
        $forecastLabels = [];
        $lastDate = Carbon::parse(end($labelsHist));
        for ($i = 1; $i <= $forecastCount; $i++) {
            $forecastLabels[] = $lastDate->addMonth()->format('M Y');
        }

        // Gabungkan label historis dan forecast
        $labels = array_merge($labelsHist, $forecastLabels);

        // Siapkan dua deret data: aktual (dengan bagian forecast diberi null), dan hasil peramalan (gabungan fitted + forecast)
        $actualSeries   = array_merge($result['actual'], array_fill(0, $forecastCount, null));
        $forecastSeries = array_merge($result['fitted'], $result['forecast']);

        return [
            'labels'   => $labels,
            'actual'   => $actualSeries,
            'forecast' => $forecastSeries
        ];
    }
    
    





private function saveForecastResults(string $method, int $productId, array $result)
{
    // Tentukan model yang sesuai berdasarkan metode peramalan
    $model = match($method) {
        'ses' => ForecastSes::class,
    };

    DB::transaction(function () use ($model, $productId, $result) {
        // Hapus data lama untuk produk tertentu (agar tidak terjadi duplikasi)
        $model::where('product_id', $productId)->delete();

        // Menyimpan hasil peramalan yang sudah difit (fitted)
        foreach ($result['fitted'] as $index => $value) {
            Log::info('Saving fitted data:', [
                'product_id' => $productId,
                'period' => Carbon::now()->addMonths($index + 1)->format('Y-m-01'),
                'actual' => $result['actual'][$index] ?? null,
                'forecast' => $value,
                'forecast_value' => $value,
                'alpha' => $result['parameters']['alpha'],
            ]);

            $model::create([
                'product_id' => $productId,
                'period' => Carbon::now()->addMonths($index + 1)->format('Y-m-01'),  // Menyimpan periode peramalan
                'actual' => $result['actual'][$index] ?? null,
                'forecast' => $value,
                'forecast_value' => $value,  // Menyimpan forecast_value untuk fitted
                'alpha' => $result['parameters']['alpha'],  // Menyimpan alpha
                
            ]);
        }

        // Menyimpan hasil peramalan masa depan (forecast)
        foreach ($result['forecast'] as $index => $value) {
            Log::info('Saving forecast data:', [
                'product_id' => $productId,
                'period' => Carbon::now()->addMonths(count($result['fitted']) + $index + 1)->format('Y-m-01'),
                'forecast' => $value,
                'forecast_value' => $value,
                'alpha' => $result['parameters']['alpha'],
               
            ]);

            $model::create([
                'product_id' => $productId,
                'period' => Carbon::now()->addMonths(count($result['fitted']) + $index + 1)->format('Y-m-01'),
                'forecast' => $value,
                'forecast_value' => $value,  // Menyimpan forecast_value untuk forecast
                'alpha' => $result['parameters']['alpha'], // Menyimpan alpha
                
            ]);
        }
    });
}


private function buildResponse(array $validated, array $result, array $chartData)
{
    $product = Product::find($validated['product_id']);
    if (!$product) {
        return redirect()->back()->withErrors(['Produk tidak ditemukan.']);
    }

    // Menghitung metrik untuk peramalan
    $metrics = $this->calculateMetrics($result);

    // Kembalikan view hasil peramalan SES dengan data lengkap
    return view('forecast.result', [
        'method'     => 'SES',
        'product'    => $product,
        'parameters' => $result['parameters'],
        'result'     => $result,
        'chartData'  => $chartData,
        'metrics'    => $metrics // Menambahkan metrik ke view
    ]);
}

private function calculateMetrics(array $result)
{
    $actual = $result['actual'];
    $fitted = $result['fitted'];
    if (empty($actual) || empty($fitted)) {
        throw new \Exception("Data aktual atau fitted tidak tersedia untuk evaluasi.");
    }
    // Hitung MSE, MAE, dan MAPE
    $mse = $mae = $mape = 0.0;
    $count = 0;
    for ($i = 1; $i < count($fitted); $i++) {
        if (!isset($actual[$i]) || $actual[$i] == 0) continue;  // hindari pembagian nol
        $error = $actual[$i] - $fitted[$i];
        $mse   += pow($error, 2);
        $mae   += abs($error);
        $mape  += abs($error / $actual[$i]);
        $count++;
    }
    // Rata-rata (Mean) untuk setiap metrik
    $mse_mean = $count > 0 ? $mse / $count : 0;
    return [
        'mse'  => $mse_mean,
        'mae'  => $count > 0 ? $mae / $count : 0,
        'mape' => $count > 0 ? ($mape / $count) * 100 : 0,
        'rmse' => $count > 0 ? sqrt($mse_mean) : 0,
    ];
}

    // ========================
    // ADDITIONAL SUPPORT METHODS
    // ========================

    public function history(Request $request)
{
    try {
        // Validasi input
        $validated = $request->validate([
            'method' => 'required|in:ses,des,tes',
            'product_id' => 'required|exists:products,id',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        // Tentukan model berdasarkan metode
        $model = match($validated['method']) {
            'ses' => ForecastSes::class,
        };

        // Query data historis
        $query = $model::where('product_id', $validated['product_id'])
            ->orderBy('period', 'desc');

        // Pagination
        $perPage = $validated['per_page'] ?? 24;
        $data = $query->paginate($perPage)
            ->appends($request->query());

        // Ambil nama produk untuk ditampilkan
        $product = Product::findOrFail($validated['product_id']);

        return view('forecast.history', [
            'data' => $data,
            'method' => strtoupper($validated['method']),
            'product' => $product,
            'parameters' => $this->getMethodParameters($validated['method'])
        ]);

    } catch (ValidationException $e) {
        return redirect()->back()->withErrors($e->errors());
    } catch (\Exception $e) {
        Log::error('History Error: ' . $e->getMessage());
        return redirect()->back()
            ->withErrors(['error' => 'Gagal memuat histori peramalan']);
    }
}

private function getMethodParameters(string $method)
{
    return match($method) {
        'ses' => ['alpha' => 'Parameter Alpha (α)'],
        default => []
    };
}


    public function export(Request $request)
    {
        // Method untuk export data
        // ... (kode lengkap)
    }

    public function apiData(Request $request)
    {
        // Method untuk API
        // ... (kode lengkap)
    }
}
