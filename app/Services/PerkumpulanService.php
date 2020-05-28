<?php

namespace App\Services;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Perkumpulan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerkumpulanService{

  private function fill($perkumpulan, $request): Perkumpulan
  {
    $perkumpulan->nama_perkumpulan = $request->input('nama_perkumpulan');
    $nama_alternatif = $request->input('nama_alternatif');
    $perkumpulan->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $perkumpulan->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $perkumpulan->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $perkumpulan->kategori_modal = $request->input('kategori_modal');
    $perkumpulan->biaya = $request->input('biaya');
    $perkumpulan->modal_dasar = $request->input('modal_dasar');

    $perkumpulan->alamat = $request->input('alamat');
    $perkumpulan->provinsi = $request->input('provinsi');
    $perkumpulan->kotkab = $request->input('kotkab');
    $perkumpulan->kecamatan = $request->input('kecamatan');
    $perkumpulan->kelurahan = $request->input('kelurahan');
    $perkumpulan->kodepos = $request->input('kodepos');
    $perkumpulan->rt = $request->input('rt');
    $perkumpulan->rw = $request->input('rw');
    $perkumpulan->kode_telpon = $request->input('kode_telpon');
    $perkumpulan->nomor_telpon = $request->input('nomor_telpon');
    $perkumpulan->nomor_handphone = $request->input('nomor_handphone');
    $perkumpulan->email = $request->input('email');
    $perkumpulan->kegiatan = $request->input('kegiatan');

    return $perkumpulan;
  }

  public function store(Request $request) : Perkumpulan{
    $perkumpulan = new Perkumpulan;
    $perkumpulan = $this->fill($perkumpulan, $request);

    $perkumpulan->save();

    return $perkumpulan;
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    
    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('perkumpulan/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('perkumpulan/npwp') : '';

      return [
        'perkumpulan_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'hp' => $item['hp'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('perkumpulan_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('perkumpulan_pemegang')
      ->where('perkumpulan_id', $id)
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
    $query = DB::table('perkumpulan_pemegang')
      ->where('perkumpulan_id', $id);
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
    $data = Perkumpulan::find($id);

    DB::table('perkumpulan_pemegang')
      ->where('perkumpulan_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }
  
  public function find($id)
  {
    $perkumpulan = null;
    $perkumpulan_pemegang = [];

    try {
      $perkumpulan = Perkumpulan::select([
        'perkumpulan.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'perkumpulan.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'perkumpulan.kotkab', 'regencies.id')
        ->leftJoin('districts', 'perkumpulan.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'perkumpulan.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $perkumpulan_pemegang = DB::table('perkumpulan_pemegang')
        ->select(['perkumpulan_pemegang.*'])
        ->where('perkumpulan_pemegang.perkumpulan_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['perkumpulan' => $perkumpulan, 'perkumpulan_pemegang' => $perkumpulan_pemegang];
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
    $perkumpulan = null;
    $perkumpulan_pemegang = [];
    try {
      $perkumpulan = Perkumpulan::findOrFail($id);
      $perkumpulan = $this->fill($perkumpulan, $request);
      $perkumpulan->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $perkumpulan;
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