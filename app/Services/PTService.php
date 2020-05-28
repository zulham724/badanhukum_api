<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Pt;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PTService
{

  private function fill($pt, $request): Pt
  {
    $pt->nama_perusahaan = $request->input('nama_perusahaan');
    $nama_alternatif = $request->input('nama_alternatif');
    $pt->nama_alternatif_1 = isset($nama_alternatif[0]) ? $nama_alternatif[0] : '';
    $pt->nama_alternatif_2 = isset($nama_alternatif[1]) ? $nama_alternatif[1] : '';
    $pt->nama_alternatif_3 = isset($nama_alternatif[2]) ? $nama_alternatif[2] : '';

    $pt->kategori_modal = $request->input('kategori_modal');
    $pt->biaya = $request->input('biaya');
    $pt->modal_dasar = $request->input('modal_dasar');
    $pt->modal_ditempatkan = $request->input('modal_ditempatkan');

    $pt->alamat = $request->input('alamat');
    $pt->provinsi = $request->input('provinsi');
    $pt->kotkab = $request->input('kotkab');
    $pt->kecamatan = $request->input('kecamatan');
    $pt->kelurahan = $request->input('kelurahan');
    $pt->kodepos = $request->input('kodepos');
    $pt->rt = $request->input('rt');
    $pt->rw = $request->input('rw');
    $pt->kode_telpon = $request->input('kode_telpon');
    $pt->nomor_telpon = $request->input('nomor_telpon');
    $pt->nomor_handphone = $request->input('nomor_handphone');
    $pt->email = $request->input('email');

    return $pt;
  }

  public function store(Request $request): Pt
  {
    $pt = new Pt;
    $pt = $this->fill($pt, $request);

    $pt->save();

    return $pt;
  }

  public function addBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'pt_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('pt_bidang')->insert($bidangs->toArray());
  }

  public function updateBidangs($id, Collection $bidangs)
  {
    $bidangs->transform(function ($item, $key) use ($id) {
      return [
        'pt_id' => $id,
        'bidang' => $item,
      ];
    });

    DB::table('pt_bidang')->where('pt_id', $id)->delete();
    DB::table('pt_bidang')->insert($bidangs->toArray());
  }

  public function addPemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {

    $pemegangs->transform(function ($item, $key) use ($id, $tipe) {
      $ktp_path = $item['ktp']->store('pt/ktp');
      $npwp_path = isset($item['npwp']) ? $item['npwp']->store('pt/npwp') : '';

      return [
        'pt_id' => $id,
        'tipe' => $tipe,
        'nama' => $item['nama'],
        'kedudukan' => strtolower($item['kedudukan']),
        'saham' => $item['saham'],
        'ktp' => $ktp_path,
        'npwp' => $npwp_path,
      ];
    });

    DB::table('pt_pemegang')->insert($pemegangs->toArray());
  }

  public function updatePemegangTambahan($id, Collection $pemegangs, $tipe = '1')
  {
    $query = DB::table('pt_pemegang')
      ->where('pt_id', $id)
      ->where('tipe', $tipe);

    $current_pemegangs = $query->get();

    $current_pemegangs->each(function ($item, $key) {
      Storage::exists($item->ktp) and Storage::delete($item->ktp);
      Storage::exists($item->npwp) and Storage::delete($item->npwp);
    });

    $query->delete();

    return $this->addPemegangTambahan($id, $pemegangs, $tipe);
  }

  public function clearPemegangTambahan($id, $tipe = null)
  {
    $query = DB::table('pt_pemegang')
      ->where('pt_id', $id);
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
    $data = Pt::find($id);

    DB::table('pt_bidang')
      ->where('pt_id', $id)->delete();
    DB::table('pt_pemegang')
      ->where('pt_id', $id)->delete();
    $this->clearPemegangTambahan($id);
    $data->delete();
  }

  public function find($id)
  {
    $pt = null;
    $pt_bidang = [];
    $pt_pemegang = [];

    try {
      $pt = Pt::select([
        'pt.*',
        'provinces.name as nama_provinsi',
        'regencies.name as nama_kotkab',
        'districts.name as nama_kecamatan',
        'urbans.name as nama_kelurahan',
      ])
        ->leftJoin('provinces', 'pt.provinsi', 'provinces.id')
        ->leftJoin('regencies', 'pt.kotkab', 'regencies.id')
        ->leftJoin('districts', 'pt.kecamatan', 'districts.id')
        ->leftJoin('urbans', 'pt.kelurahan', 'urbans.id')
        ->findOrFail($id);

      $pt_bidang = DB::table('pt_bidang')
        ->select(['pt_bidang.*'])
        ->where('pt_bidang.pt_id', $id)
        ->get();

      $pt_pemegang = DB::table('pt_pemegang')
        ->select(['pt_pemegang.*'])
        ->where('pt_pemegang.pt_id', $id)
        ->orderBy('tipe')
        ->get();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return ['pt' => $pt, 'pt_bidang' => $pt_bidang, 'pt_pemegang' => $pt_pemegang];
  }

  public function getFileContent($id, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function downloadFile($id, $file, $path)
  {
    if (Storage::exists($path)) {
      return Storage::download($path);
    } else {
      return false;
    }
  }

  public function update($id, $request)
  {
    $pt = null;
    $pt_bidang = [];
    $pt_pemegang = [];
    try {
      $pt = Pt::findOrFail($id);
      $pt = $this->fill($pt, $request);
      $pt->save();
    } catch (ModelNotFoundException $e) {
      return null;
    } catch (Exception $e) {
      return null;
    };

    return $pt;
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
