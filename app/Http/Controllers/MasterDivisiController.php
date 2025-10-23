<?php

namespace App\Http\Controllers;

use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDivisiController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $divisi = MasterDivisi::query()
            ->when($q, fn($s) => $s->where('nama_divisi', 'like', "%{$q}%"))
            ->orderBy('nama_divisi')
            ->paginate(10)
            ->withQueryString();

        return view('master.divisi.index', compact('divisi', 'q'));
    }

    // form create tidak dipakai (form ada di index) -> boleh dihapus
    public function create() {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_divisi' => [
                'required',
                'string',
                'max:150',
                Rule::unique('master_divisi', 'nama_divisi')->whereNull('deleted_at'),
            ],
            'isactive' => ['nullable', 'boolean'],
        ]);

        $validated['isactive'] = (bool) ($validated['isactive'] ?? false);

        MasterDivisi::create($validated);

        return redirect()->route('divisi.index')->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function edit(MasterDivisi $divisi)
    {
        return view('master.divisi.edit', compact('divisi'));
    }

    public function update(Request $request, MasterDivisi $divisi)
    {
        $validated = $request->validate([
            'nama_divisi' => [
                'required',
                'string',
                'max:150',
                Rule::unique('master_divisi', 'nama_divisi')
                    ->ignore($divisi->getKey(), $divisi->getKeyName())
                    ->whereNull('deleted_at'),
            ],
            'isactive' => ['nullable', 'boolean'],
        ]);

        $validated['isactive'] = (bool) ($validated['isactive'] ?? false);

        $divisi->update($validated);

        return redirect()->route('divisi.index')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(MasterDivisi $divisi)
    {
        $divisi->delete();

        return redirect()->route('divisi.index')->with('success', 'Divisi berhasil dihapus (soft delete).');
    }

    // Toggle aktif/nonaktif
    public function toggle($id)
    {
        $item = MasterDivisi::findOrFail($id);
        $item->isactive = ! $item->isactive;
        $item->save();

        return back()->with('success', 'Status aktif berhasil diubah.');
    }
}
