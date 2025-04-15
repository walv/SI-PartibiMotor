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
    <div class="alert alert-warning mt-3">
        <h6><strong>Peringatan!</strong></h6>
        <p>Peramalan yang dilakukan menggunakan metode <strong>SES</strong> tidak selalu 100% akurat. </p>
        <p>Pastikan untuk menggunakan peramalan sebagai alat pendukung pengambilan keputusan, bukan satu-satunya acuan.</p>
    </div>

    <!-- Tombol untuk Memulai Peramalan -->
    <div class="text-center mt-4">
        <a href="{{ route('forecast.ses') }}" class="btn btn-primary btn-lg">Mulai Peramalan</a>
    </div>

    <!-- Petunjuk Tambahan -->
    <div class="mt-4">
        <h6><strong>Bagaimana Memilih Nilai Alpha?</strong></h6>
        <p><strong>Alpha (α)</strong> adalah parameter penghalusan yang menentukan seberapa banyak data terbaru mempengaruhi hasil peramalan. Jika α terlalu rendah, peramalan akan lebih stabil dan kurang responsif terhadap perubahan data terkini. Sebaliknya, jika α terlalu tinggi, peramalan akan sangat sensitif terhadap data terbaru dan bisa berfluktuasi tajam.</p>
        <p>Cobalah beberapa nilai α seperti <strong>0.2 hingga 0.5</strong> untuk melihat bagaimana nilai tersebut mempengaruhi hasil peramalan. Nilai yang tepat akan membantu mendapatkan hasil peramalan yang lebih akurat.</p>
    </div>
</div>
@endsection
