@extends('layouts.app')

@section('title', 'Histori Peramalan')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Histori Peramalan {{ $method }} - {{ $product->name }}</h5>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Periode</th>
                            <th>Data Aktual</th>
                            <th>Hasil Peramalan</th>
                            @foreach($parameters as $param => $label)
                            <th>{{ $label }}</th>
                            @endforeach
                            <th>Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td>{{ $item->period }}</td>
                            <td>{{ $item->actual ? number_format($item->actual) : '-' }}</td>
                            <td>{{ number_format($item->forecast, 2) }}</td>
                            @foreach($parameters as $param => $label)
                            <td>
                                @if(isset($item->parameters[$param]))
                                {{ number_format($item->parameters[$param], 3) }}
                                @else
                                -
                                @endif
                            </td>
                            @endforeach
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($parameters) + 4 }}" class="text-center">
                                Tidak ada data historis
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
