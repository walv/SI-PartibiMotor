<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('name')->paginate(10); // Ambil data jasa dengan pagination
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create'); // Tampilkan form tambah jasa
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        Service::create($request->only('name', 'harga'));

        return redirect()->route('services.index')->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service')); // Tampilkan form edit jasa
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        $service->update($request->only('name', 'harga'));

        return redirect()->route('services.index')->with('success', 'Jasa berhasil diperbarui.');
    }
    public function destroy(Service $service)
{
    $service->delete(); // Hapus jasa dari database

    return redirect()->route('services.index')->with('success', 'Jasa berhasil dihapus.');
}
}