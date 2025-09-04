{{-- Modal Detail Frame --}}
<div class="modal fade" id="modal-frame-admin" tabindex="-1" role="dialog" aria-labelledby="modalFrameLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalFrameLabel">Detail Frame</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped datatable" id="table-frame-admin">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Frame</th>
              <th>Kode</th>
              <th>Stok</th>
              <th>Harga Jual</th>
              @if(auth()->user()->isSuperAdmin())
              <th>Harga Beli</th>
              @endif
              <th>Cabang</th>
            </tr>
          </thead>
          <tbody>
            @if(isset($detailFrame) && $detailFrame->count() > 0)
              @foreach($detailFrame as $i => $frame)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $frame->merk_frame ?? '-' }}</td>
                <td>{{ $frame->kode_frame ?? '-' }}</td>
                <td>{{ $frame->stok ?? 0 }}</td>
                <td>{{ number_format($frame->harga_jual_frame ?? 0) }}</td>
                @if(auth()->user()->isSuperAdmin())
                <td>{{ number_format($frame->harga_beli_frame ?? 0) }}</td>
                @endif
                <td>{{ $frame->branch->name ?? '-' }}</td>
              </tr>
              @endforeach
            @else
              <tr>
                <td colspan="{{ auth()->user()->isSuperAdmin() ? '7' : '6' }}" class="text-center">Tidak ada data frame</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal Detail Lensa --}}
<div class="modal fade" id="modal-lensa-admin" tabindex="-1" role="dialog" aria-labelledby="modalLensaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalLensaLabel">Detail Lensa</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped datatable" id="table-lensa-admin">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Lensa</th>
              <th>Kode</th>
              <th>Stok</th>
              <th>Harga Jual</th>
              @if(auth()->user()->isSuperAdmin())
              <th>Harga Beli</th>
              @endif
              <th>Cabang</th>
            </tr>
          </thead>
          <tbody>
            @if(isset($detailLensa) && $detailLensa->count() > 0)
              @foreach($detailLensa as $i => $lensa)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $lensa->merk_lensa ?? '-' }}</td>
                <td>{{ $lensa->kode_lensa ?? '-' }}</td>
                <td>{{ $lensa->stok ?? 0 }}</td>
                <td>{{ number_format($lensa->harga_jual_lensa ?? 0) }}</td>
                @if(auth()->user()->isSuperAdmin())
                <td>{{ number_format($lensa->harga_beli_lensa ?? 0) }}</td>
                @endif
                <td>{{ $lensa->branch->name ?? '-' }}</td>
              </tr>
              @endforeach
            @else
              <tr>
                <td colspan="{{ auth()->user()->isSuperAdmin() ? '7' : '6' }}" class="text-center">Tidak ada data lensa</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal Detail Pasien --}}
<div class="modal fade" id="modal-pasien-admin" tabindex="-1" role="dialog" aria-labelledby="modalPasienLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalPasienLabel">Detail Pasien</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped datatable" id="table-pasien-admin">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Pasien</th>
              <th>No. HP</th>
              <th>Alamat</th>
              <th>Jenis Layanan</th>
              <th>Dokter</th>
              <th>Tanggal Daftar</th>
            </tr>
          </thead>
          <tbody>
            @if(isset($detailPasien) && $detailPasien->count() > 0)
              @foreach($detailPasien as $i => $pasien)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $pasien->nama_pasien ?? '-' }}</td>
                <td>{{ $pasien->nohp ?? '-' }}</td>
                <td>{{ $pasien->alamat ?? '-' }}</td>
                <td>{{ $pasien->service_type ?? '-' }}</td>
                <td>{{ optional($pasien->prescriptions->last()?->dokter)->nama ?? $pasien->prescriptions->last()?->dokter_manual ?? '-' }}</td>
                <td>{{ $pasien->created_at ? $pasien->created_at->format('d-m-Y') : '-' }}</td>
              </tr>
              @endforeach
            @else
              <tr>
                <td colspan="7" class="text-center">Tidak ada data pasien</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal Detail Transaksi Aktif --}}
<div class="modal fade" id="modal-transaksi-aktif-admin" tabindex="-1" role="dialog" aria-labelledby="modalTransaksiAktifLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalTransaksiAktifLabel">Detail Transaksi Aktif Hari Ini</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped datatable" id="table-transaksi-aktif-admin">
          <thead>
            <tr>
              <th>No</th>
              <th>No. Nota</th>
              <th>Nama Pasien</th>
              <th>Jenis Layanan</th>
              <th>Dokter</th>
              <th>Total</th>
              <th>Status</th>
              <th>Status Pengerjaan</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            @if(isset($detailTransaksiAktif) && $detailTransaksiAktif->count() > 0)
              @foreach($detailTransaksiAktif as $i => $trx)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $trx->kode_penjualan ?? '-' }}</td>
                <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                <td>{{ $trx->pasien->service_type ?? '-' }}</td>
                <td>{{ optional($trx->dokter)->nama ?? $trx->dokter_manual ?? '-' }}</td>
                <td>Rp {{ number_format($trx->total ?? 0, 0, ',', '.') }}</td>
                <td>{{ $trx->status ?? '-' }}</td>
                <td>{{ $trx->status_pengerjaan ?? '-' }}</td>
                <td>{{ $trx->created_at ? $trx->created_at->format('d-m-Y H:i') : '-' }}</td>
              </tr>
              @endforeach
            @else
              <tr>
                <td colspan="9" class="text-center">Tidak ada transaksi aktif hari ini</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div> 