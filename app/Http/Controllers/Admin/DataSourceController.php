<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrendSource;
use Illuminate\Http\Request;

class DataSourceController extends Controller
{
    /**
     * Menampilkan semua daftar sumber data marcom.
     */
    public function index()
    {
        $sources = TrendSource::orderBy('created_at', 'desc')->get();
        return view('admin.datasource.index', compact('sources'));
    }

    /**
     * Menyimpan sumber data marcom baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'platform'   => 'required|string|max:255',
            'source_url' => 'nullable|url|max:2048',
        ]);

        TrendSource::create($validated);

        return redirect()->back()->with('success', 'Sumber data tren marcom berhasil ditambahkan.');
    }

    /**
     * Memperbarui sumber data marcom yang tersimpan.
     */
    public function update(Request $request, $id)
    {
        $source = TrendSource::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'platform'   => 'required|string|max:255',
            'source_url' => 'nullable|url|max:2048',
        ]);

        $source->update($validated);

        return redirect()->back()->with('success', 'Sumber data tren marcom berhasil diperbarui.');
    }

    /**
     * Menghapus sumber data marcom dari sistem.
     */
    public function destroy($id)
    {
        $source = TrendSource::findOrFail($id);
        $source->delete();

        return redirect()->back()->with('success', 'Sumber data tren marcom berhasil dihapus.');
    }
}