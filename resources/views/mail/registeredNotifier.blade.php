@extends('mail.layouts.app')

@section('title'){{$subject ?? ''}}@endsection

@section('content')
<div>
  <table>
    <tr>
      <td colspan="2">{{config('app.name')}}</td>
    </tr>
    <tr>
      <td colspan="2"> 
        <div class="title m-b-md" style="text-align:center;">
          {{$badan->kategori}} berhasil disimpan
        </div>
      </td>
    </tr>
    <tr>
      <td valign="top">
        <p>
          Halo {{$badan->kategori}} {{$badan->nama}}, 
          Terimakasih telah melakukan pemesanan pembuatan {{$badan->kategori}} di {{config('app.name')}}. Silahkan melakukan pembayaran dengan nominal Rp. {{number_format($badan->biaya)}} ke Rekening kami {{env('REKENING_BANK')}} {{env('REKENING_NOMOR')}} a.n {{env('REKENING_NAMA')}}
          <br>
          <br>
          Dan konfirmasi ke nomor : {{env('NOMOR_KONFIRMASI')}} jika telah melakukan pembayaran.
          <br>
          <br>
          Ringkasan data {{$badan->kategori}} {{$badan->nama}} :
          <ul style="text-align:left;">
            <li><b>Nama</b> : {{$badan->nama}}</li>
            <li><b>Alamat</b> : {{$badan->alamat_formatted}}</li>
            <li>
              <div><b>Kontak</b></div>
              <ul>
                <li>{{$kontak['kode_telpon'].'-'.$kontak['telpon']}}</li>
                <li>{{$kontak['handphone']}}</li>
                <li>{{$kontak['email']}}</li>
              </ul>
            </li>
            <li><b>Pimpinan</b> : {{$badan->pemimpin}}</li>
            <li><b>Modal Dasar</b> : Rp. {{number_format($badan->modal_dasar)}}</li>
          </ul>
        </p>
      </td>
    </tr>
    <tr>
      <td colspan="2">Data lengkap telah kami lampirkan dalam bentuk PDF</td>
    </tr>
    <tr>
      <td colspan="2"><small>{{config('app.name')}} copyright {{date('Y')}}</small></td>
    </tr>
  </table>
</div>
@endsection