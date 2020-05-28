<html>
  <header>
    <title>{{$badan->kategori}} {{$badan->nama}}</title>
  </header>

  <body>
    <header style="text-align:center;">{{config('app.name')}}</header>
    
    <h3 class="title m-b-md mb-5" style="text-align:center;">Pemesanan {{$badan->kategori}} {{$badan->nama}}</h3>
    
    <hr>

    <table cellpadding="5">
      <tr>
        <td style="width:30%"><b>Nama</b></td>
        <td style="width:3%">:</td>
        <td>{{$badan->nama}}</td>
      </tr>
      @if($badan->nama_alternatif_1)
      <tr>
        <td><b>Nama Alternatif 1</b></td>
        <td>:</td>
        <td>{{$badan->nama_alternatif_1}}</td>
      </tr>
      @endif
      @if($badan->nama_alternatif_2)
      <tr>
        <td><b>Nama Alternatif 2</b></td>
        <td>:</td>
        <td>{{$badan->nama_alternatif_2}}</td>
      </tr>
      @endif
      @if($badan->nama_alternatif_3)
      <tr>
        <td><b>Nama Alternatif 3</b></td>
        <td>:</td>
        <td>{{$badan->nama_alternatif_3}}</td>
      </tr>
      @endif

      @if(isset($badan->wilayah))
        <tr>
          <td><b>Wilayah</b></td>
          <td>:</td>
          <td>{{config('app.wilayah')[$badan->wilayah]['text']}}</td>
        </tr>
        @isset($badan->jenis)
        <tr>
          <td><b>Jenis</b></td>
          <td>:</td>
          <td>{{config('app.jenis')[$badan->jenis]}}</td>
        </tr>
          @if($badan->jenis != '1')
            @isset($badan->unit_simpan_pinjam)
            <tr>
              <td><b>Memiliki Unit Simpan Pinjam</b></td>
              <td>:</td>
              <td>{{($badan->unit_simpan_pinjam=='true' ? 'Ya' : 'Tidak')}}</td>
            </tr>
              @if($badan->unit_simpan_pinjam=='true')
                <tr>
                  <td><b>Alokasi</b></td>
                  <td>:</td>
                  <td>Rp. {{number_format($badan->alokasi)}}</td>
                </tr>
              @endif
            @endisset
          @endif
        @endisset
        
        <tr>
          <td>
          </td>
          <td>
          </td>
          <td>
            <div style="display:inline-block;margin-right:15px;">
              <div><strong>Wajib</strong></div>
              <div>Rp. {{number_format($badan->wajib)}}</div>
            </div>
              
            <div style="display:inline-block;margin-right:15px;">
              <div><strong>Pokok</strong></div>
              <div>Rp. {{number_format($badan->pokok)}}</div>
            </div>
              
            <div style="display:inline-block;">
              <div><strong>Sukarela</strong></div>
              <div>Rp. {{number_format($badan->sukarela)}}</div>
            </div>
          </td>
        </tr>
      @endif
      
      
      <tr>
        <td><b>Kategori Modal</b></td>
        <td>:</td>
        <td>{{config('app.kategori_modal')[$badan->kategori_modal]['text']}}</td>
      </tr>
      
      <tr>
        <td><b>Modal Dasar</b></td>
        <td>:</td>
        <td>Rp. {{number_format($badan->modal_dasar)}}</td>
      </tr>
      
      @isset($badan->modal_ditempatkan)
      <tr>
        <td><b>Modal Ditempatkan</b></td>
        <td>:</td>
        <td>Rp. {{number_format($badan->modal_ditempatkan)}}</td>
      </tr>
      @endisset
      
      <tr>
        <td><b>Alamat</b></td>
        <td>:</td>
        <td>{{$badan->alamat_formatted}}</td>
      </tr>
      <tr>
        <td><b>Kontak</b></td>
        <td>:</td>
        <td>
          <div>
            <div>{{$kontak['kode_telpon'].'-'.$kontak['telpon']}}</div>
            <div>{{$kontak['handphone']}}</div>
            <div>{{$kontak['email']}}</div>
          </div>
        </td>
      </tr>

      @isset($badan->bidang)
      <tr>
        <td><b>Kategori Bidang</b></td>
        <td>:</td>
        <td>{{ucwords($badan->bidang)}}</td>
      </tr>
      @endisset

      @if(!isset($badan->jenis) OR (isset($badan->jenis) AND $badan->jenis != '1'))
      <tr>
        <td><b>Bidang</b></td>
        <td>:</td>
        <td>
          @php
            $bidangs = $badan->bidangs;
            if(count($bidangs)>0){
              if (isset($bidangs[0]->kategori_bidang)) {
                foreach ($bidangs as $bidang){
                  echo '<div style="color:grey">'. $bidang->kategori_bidang .' - '. $bidang->kategori_nama.'</div>';
                  echo '<div>- '.$bidang->kode_bidang.' - '.$bidang->nama_bidang. '</div>';
                }
              } else {
                if(isset($bidangs['bidang'])) {
                  echo '<div>'.$bidangs['bidang']. '</div>';
                } else {
                  foreach ($bidangs as $bidang){
                    echo '<div>- '.$bidang->bidang. '</div>';
                  }
                }
              }
            } else {
              echo $bidangs;
            }
          @endphp
        </td>
      </tr>
      @endif

      {{-- PEMEGANG --}}
      @php
        $pemegangs = $badan->pemegangs
      @endphp
      <tr>
        <td><b>Pemegang</b></td>
        <td>:</td>
        <td>
          @foreach ($pemegangs as $pemegang)
            <div>{{ucwords($pemegang->kedudukan)}}</div>
            <div>- <strong>{{$pemegang->nama}}</strong> (
              @if(isset($pemegang->saham))
              {{number_format($pemegang->saham)}}
              @elseif(isset($pemegang->hp))
              {{$pemegang->hp}}
              @endif
            )</div>
          @endforeach
        </td>
      </tr>

      @isset($badan->jumlah_anggota)
      <tr>
        <td><b>Jumlah Anggota</b></td>
        <td>:</td>
        <td>{{number_format($badan->jumlah_anggota)}}</td>
      </tr>
      @endisset
      
      <tr>
        <td><b>Biaya</b></td>
        <td>:</td>
        <td>Rp. {{number_format($badan->biaya)}}</td>
      </tr>
    </table>

    <table border="1" cellspacing="0" cellpadding="10" style="width:100%;page-break-before:always;">
      <tr>
        <td colspan="2">Dokumen</td>
      </tr>

      @foreach ($pemegangs as $pemegang)
        <tr>
          <td colspan="2">{{$pemegang->nama}} ({{ucwords($pemegang->kedudukan)}})</td>
        </tr>
        <tr>
          <td style="word-wrap: break-word;width:50%;">
            @if($pemegang->ktp AND $pemegang->ktp != null AND pathinfo($pemegang->ktp, PATHINFO_EXTENSION) != 'pdf')
            <img style="max-width:300px;" src="{{get_file_base64($pemegang->ktp)}}" />
            @endif
          </td>
          <td style="word-wrap: break-word">
            @if($pemegang->npwp AND $pemegang->npwp != null AND pathinfo($pemegang->npwp, PATHINFO_EXTENSION) != 'pdf')
            <img style="max-width:300px;" src="{{get_file_base64($pemegang->npwp)}}" />
            @endif
          </td>
        </tr>
      @endforeach

    </table>
    
    {{-- <footer style="position:fixed; bottom:10px;"><small>{{config('app.name')}} copyright {{date('Y')}}</small></footer> --}}

    <style>
      table * {
        vertical-align: top;
      }
    </style>

  </body>
</html>
