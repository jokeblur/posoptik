<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with('creator')->latest()->paginate(20);

        return view('voucher.index', compact('vouchers'));
    }

    public function create()
    {
        return view('voucher.form', ['voucher' => new Voucher()]);
    }

    public function store(Request $request)
    {
        $voucher = Voucher::create($this->validatedData($request));
        $voucher->created_by = auth()->id();
        $voucher->save();

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return view('voucher.form', compact('voucher'));
    }

    public function print(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'copies' => 'required|integer|min:1|max:50',
            'side' => 'nullable|in:front,back',
        ]);

        return view('voucher.print', [
            'voucher' => $voucher,
            'copies' => (int) $validated['copies'],
            'side' => $validated['side'] ?? 'front',
        ]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $voucher->update($this->validatedData($request, $voucher));

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        return $request->validate([
            'kode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vouchers', 'kode')->ignore($voucher ? $voucher->id : null),
            ],
            'nominal' => 'required|numeric|min:0',
            'syarat_ketentuan' => 'nullable|string|max:5000',
            'berlaku_mulai' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_mulai',
            'aktif' => 'nullable|boolean',
        ]) + [
            'aktif' => $request->boolean('aktif'),
        ];
    }
}
