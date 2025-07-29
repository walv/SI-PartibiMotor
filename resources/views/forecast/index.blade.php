@extends('layouts.app')

@section('title', 'Peramalan Penjualan')

@section('content')
<div class="container">
    <h5 class="text-center mt-5">Peramalan Penjualan dengan <strong>Single Exponential Smoothing (SES)</strong></h5>
    
    <!-- Penjelasan tentang SES -->
    <div class="alert alert-info mt-4">
        <h6><strong>Apa itu Single Exponential Smoothing (SES)?</strong></h6>
        <p>Single Exponential Smoothing (SES) adalah metode statistik yang digunakan untuk meramalkan nilai masa depan berdasarkan data historis. Metode ini memperhitungkan nilai terbaru lebih banyak daripada nilai-nilai sebelumnya, membuatnya lebih responsif terhadap perubahan data terbaru.</p>
        <p>Metode ini sangat berguna untuk peramalan penjualan barangg dipengaruhi oleh pola yang sudah ada sebelumnya guna menentukan stok dibulan berikutnya. Namun, perlu diingat bahwa hasil peramalan menggunakan SES dapat dipengaruhi oleh **parameter alpha (α)** yang dipilih, yang mengontrol seberapa besar pengaruh data terbaru terhadap peramalan.</p>
    </div>

    <!-- Peringatan tentang Ketidakakuratan Peramalan -->
 <div class="alert alert-info mt-4">
    Aplikasi ini menyediakan pilihan pencarian otomatis nilai alpha (α) terbaik untuk menghasilkan hasil peramalan paling optimal, atau Anda bisa mencoba input nilai sendiri. Hasil peramalan akan ditampilkan beserta grafik & evaluasi akurasi.
</div>

    <!-- Tombol untuk Memulai Peramalan -->
    <div class="text-center mt-4">
        <a href="{{ route('forecast.ses') }}" class="btn btn-primary btn-lg">Mulai Peramalan</a>
    </div>

</div>
</div>

@endsection
