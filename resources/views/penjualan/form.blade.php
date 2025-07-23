@extends('layouts.master')
@section('title', 'Transaksi Penjualan')
@section('content')
<form action="{{ route('penjualan.store') }}" method="POST">
    @csrf
    <div>
        <label>Tanggal</label>
        <input type="date" name="tanggal" required>
    </div>
    <div>
        <label>Kode Penjualan</label>
        <input type="text" name="kode_penjualan" required>
    </div>
    <div>
        <label>Cabang</label>
        <select name="branch_id">
            <!-- Loop cabang -->
        </select>
    </div>
    <table>
        <thead>
            <tr>
                <th>Frame</th>
                <th>Lensa</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody id="detail-rows">
            <tr>
                <td>
                    <select name="details[0][frame_id]">
                        <option value="">-</option>
                        @foreach($frames as $frame)
                        <option value="{{ $frame->id_frame }}">{{ $frame->merk_frame }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="details[0][lensa_id]">
                        <option value="">-</option>
                        @foreach($lensas as $lensa)
                        <option value="{{ $lensa->id_lensa }}">{{ $lensa->merk_lensa }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="details[0][qty]" min="1" value="1"></td>
                <td><input type="number" name="details[0][harga]" min="0" value="0"></td>
                <td><input type="number" name="details[0][subtotal]" min="0" value="0" readonly></td>
            </tr>
        </tbody>
    </table>
    <button type="button" onclick="addRow()">Tambah Barang</button>
    <div>
        <label>Total</label>
        <input type="number" name="total" id="total" readonly>
    </div>
    <button type="submit">Simpan</button>
</form>
<script>
function addRow() {
    // JS untuk tambah baris detail
}
</script>
@endsection
