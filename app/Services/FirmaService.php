<?php

namespace App\Services;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Firma;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FirmaService
{

  private function fill($firma, $request): Firma
  {
    $firma->nama_firma = $request->input('nama_firma');
    $nama_alternatif = $request->input('nama_alternatif');
    $firma->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $firma->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $firma->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $firma->kategori_modal = $request->input('kategori_modal');
    $firma->biaya = $request->input('biaya');
    $firma->modal_dasar = $request->input('modal_dasar');
    $firma->modal_ditempatkan = $request->input('modal_ditempatkan');

    $firma->alamat = $request->input('alamat');
    $firma->provinsi = $request->input('provinsi');
    $firma->kotkab = $request->input('kotkab');
    $firma->kecamatan = $request->input('kecamatan');
    $firma->kelurahan = $request->input('kelurahan');
    $firma->kodepos = $request->input('kodepos');
    $firma->rt = $request->input('rt');
    $firma->rw = $request->input('rw');
    $firma->kode_telpon = $request->input('kode_telpon');
    $firma->nomor_telpon = $request->input('nomor_telpon');
    $firma->nomor_handphone = $request->input('nomor_handphone');
    $firma->email = $request->input('email');

    return $firma;
  }

  public function store(Request $request): Firma
  {
    $firma = new Firma;
    $firma = $this->fill($firma, $request);

    $firma->save();

    return $firma;
  }

  public function updateBidangs($id, Collection $bidangs)
  {
    DB::table('firma_bidang')->where('firma_id', $id)->delete();
    $this->addBidangs($id, $bidangs);
  }

  public function addBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'firma_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('firma_bidang')->insert($bidangs->toArray());
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {

    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('firma/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('firma/npwp') : '';

      return [
        'firma_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'saham' => $item['saham'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('firma_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('firma_pemegang')
      ->where('firma_id', $id)
      ->where('tipe', $tipe);

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();

    return $this->addPemegangTambahan($id, $pemegangs, $tipe);
  }

  public function clearPemegangTambahan($id, $tipe = '1')
  {
    $query = DB::table('firma_pemegang')
      ->where('firma_id', $id);
    if (!is_null($tipe)) {
      $query->where('tipe', $tipe);
    }

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();
  }

  public function delete($id)
  {
    $data = Firma::find($id);

    DB::table('firma_bidang')
      ->where('firma_id', $id)->delete();
    DB::table('firma_pemegang')
      ->where('firma_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }

  public function find($id)
  {
    $firma = null;
    $firma_bidang = [];
    $firma_pemegang = [];

    try {
      $firma = Firma::select([
        'firma.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'firma.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'firma.kotkab', 'regencies.id')
        ->leftJoin('districts', 'firma.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'firma.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $firma_bidang = DB::table('firma_bidang')
        ->select(['firma_bidang.*'])
        ->where('firma_bidang.firma_id', $id)
        ->get();

      $firma_pemegang = DB::table('firma_pemegang')
        ->select(['firma_pemegang.*'])
        ->where('firma_pemegang.firma_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['firma' => $firma, 'firma_bidang' => $firma_bidang, 'firma_pemegang' => $firma_pemegang];
  }

  public function getFileContent($id, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function downloadFile($path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function update($id, $request)
  {
    $firma = null;
    $firma_bidang = [];
    $firma_pemegang = [];
    try {
      $firma = Firma::findOrFail($id);
      $firma = $this->fill($firma, $request);
      $firma->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $firma;
  }

  public function validateKTP($requestKTP)
  {

    $allowed_types = ['pdf', 'jpg', 'png', 'jpeg', 'bmp'];

    foreach ($requestKTP as $key => $val) {
      if (!$val->isValid()) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' tidak valid'], 412);
      }
      if (!in_array($val->extension(), $allowed_types)) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' bukan format yang dibolehkan'], 412);
      }
      if (($val->getClientSize() / 1000) > 2048) {
        return response()->json(['message' => 'KTP ' . $val->getClientOriginalName() . ' lebih dari 2048 kb'], 412);
      }
    }

    return true;
  }
  public function validateNPWP($requestNPWP)
  {
    $allowed_types = ['pdf', 'jpg', 'png', 'jpeg', 'bmp'];

    foreach ($requestNPWP as $key => $val) {
      if (!$val->isValid()) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' tidak valid'], 412);
      }
      if (!in_array($val->extension(), $allowed_types)) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' bukan format yang dibolehkan'], 412);
      }
      if (($val->getClientSize() / 1000) > 2048) {
        return response()->json(['message' => 'NPWP ' . $val->getClientOriginalName() . ' lebih dari 2048 kb'], 412);
      }
    }

    return true;
  }
}
