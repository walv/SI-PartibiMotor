@extends('layouts.app')

@section('title', 'Export & Import Data Penjualan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Export & Import Data Penjualan</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <!-- Export Section -->
                <div class="col-md-6">
                    <form action="{{ route('sales.export') }}" method="GET">
                        <div class="mb-3">
                            <label for="month">Bulan</label>
                            <select name="month" id="month" class="form-control" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="year">Tahun</label>
                            <select name="year" id="year" class="form-control" required>
                                @for ($i = date('Y'); $i >= 2000; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Laporan
                        </button>
                    </form>
                </div>
                </div>

                <!-- Import Section -->
                <div class="col-md-6">
                    <h6>Import Data Penjualan</h6>
                    <form action="{{ route('sales.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import Data
                        </button>
                    </form>
                    <a href="{{ route('sales.import.manual') }}" class="btn btn-info mt-2">
                        <i class="fas fa-keyboard"></i> Import Manual
                    </a>
                    <a href="{{ route('sales.import.template') }}" class="btn btn-secondary mt-2">
                        <i class="fas fa-download"></i> Unduh Template
                    </a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection