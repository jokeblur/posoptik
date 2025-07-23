<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal-form" tabindex="-1" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="" method="post" class="form-horizontal">
        @csrf
        @method ('post')

    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-3" ></h1>
        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
      </div>
    <div class="modal-body">     
               <div class="form-group">
                  <label for="merk_frame" class="col-sm-2 control-label">Nama Frame</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" name="merk_frame"id="merk_frame" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="jenis_frame" class="col-sm-2 control-label">Jenis Frame</label>
                  <div class="col-sm-10">
                    <select name="jenis_frame" id="jenis_frame" class="form-control" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="BPJS I">BPJS I</option>
                        <option value="BPJS II">BPJS II</option>
                        <option value="BPJS III">BPJS III</option>
                        <option value="Umum">Umum</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="branch_id" class="col-sm-2 control-label">Cabang</label>
                  <div class="col-sm-10">
                    <select name="branch_id" class="form-control" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $key => $item)
                          <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="merk_frame" class="col-sm-2 control-label">Sales</label>
                  <div class="col-sm-10">
                  <select name="id_sales" class="form-control" required>
                        <option value="">-- Pilih Sales --</option>
                           @foreach($sales as $key => $item)
                          <option value="{{ $key }}">{{ $item }}</option>
                             @endforeach
                        </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="harga_beli_frame" class="col-sm-2 control-label">Harga Beli</label>

                  <div class="col-sm-10">
                    <input type="number" name="harga_beli_frame" class="form-control" id="harga_beli_frame" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="harga_jual_frame" class="col-sm-2 control-label">Harga Jual</label>

                  <div class="col-sm-10">
                    <input type="number" name="harga_jual_frame" class="form-control" id="harga_jual_frame" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="stok" class="col-sm-2 control-label">Stok</label>

                  <div class="col-sm-10">
                    <input type="number" name="stok" class="form-control" id="stok" >
                  </div>
                </div>
                
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button  class="btn btn-primary">Save changes</button>
      </div>
     
    
    </div>


        
      
     
    


    </form>
    </div>
  </div>
</div>
