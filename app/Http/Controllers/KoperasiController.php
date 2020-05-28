<?php

namespace App\Http\Controllers;

use App\Http\Requests\KoperasiRequest;
use App\Mail\BadanRegistered;
use App\Models\Koperasi;
use App\Services\KoperasiService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class KoperasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Koperasi::select([
            'koperasi.*',
            'provinces.name as nama_provinsi',
            'regencies.name as nama_kotkab',
            'ketua.nama as nama_ketua'
        ])
            ->leftJoin('provinces', 'koperasi.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'koperasi.kotkab', 'regencies.id')
            ->leftJoin('koperasi_pemegang as ketua', function ($join) {
                $join->on('koperasi.id', 'ketua.koperasi_id')
                    ->where('ketua.tipe', '1')
                    ->where('ketua.kedudukan', 'ketua');
            })
            ->orderBy('koperasi.id', 'desc')
            ->get();

        return ['result' => true, 'data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KoperasiRequest $request, KoperasiService $koperasiService)
    {

        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $koperasiService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $koperasiService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_utama = collect($pemegang_utama);

        $newKoperasi = $koperasiService->store($request);

        $bidangs = collect($request->input('bidangs'));
        $koperasiService->addBidangs($newKoperasi->id, $bidangs);

        $pemegang_utama->transform(function ($item, $key) use ($ktps, $npwps) {
            $item['ktp'] = $ktps[$key];
            $item['npwp'] = isset($npwps[$key]) ? $npwps[$key] : null;
            return $item;
        });
        $koperasiService->addPemegangTambahan($newKoperasi->id, $pemegang_utama, '1');

        if ($pemegang_tambahan->count() > 0) {
            $index = 5;

            $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $koperasiService->addPemegangTambahan($newKoperasi->id, $pemegang_tambahan, '2');
        }
        
        $ktp_anggota_path = Storage::path($newKoperasi->ktp_anggota);
        $daftar_hadir_path = Storage::path($newKoperasi->daftar_hadir);
        $rekapitulasi_path = Storage::path($newKoperasi->rekapitulasi);
        $berita_pendirian_path = Storage::path($newKoperasi->berita_pendirian);


        $mailable = new BadanRegistered($newKoperasi);
        $mailable->attach($ktp_anggota_path, ['as' => 'ktp_anggota.pdf'])
            ->attach($daftar_hadir_path, ['as' => 'daftar_hadir.pdf'])
            ->attach($rekapitulasi_path, ['as' => 'rekapitulasi.pdf'])
            ->attach($berita_pendirian_path, ['as' => 'berita_pendirian.pdf']);

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send($mailable);

        return [
            'result' => true,
            'data' => $newKoperasi->attributesToArray(),
            'formatted' => $newKoperasi->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, KoperasiService $koperasiService)
    {
        $data = $koperasiService->find($id);

        return ['result' => true, 'data' => $data];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(KoperasiRequest $request, $id, KoperasiService $koperasiService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $koperasiService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $koperasiService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_utama = collect($pemegang_utama);

        $koperasi = $koperasiService->update($id, $request);
        if ($koperasi) {
            $bidangs = collect($request->input('bidangs'));
            $koperasiService->updateBidangs($koperasi->id, $bidangs);

            $koperasiService->clearPemegangTambahan($koperasi->id, '1');
            $index = 0;
            $pemegang_utama->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $key;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $koperasiService->addPemegangTambahan($koperasi->id, $pemegang_utama, '1');

            $koperasiService->clearPemegangTambahan($koperasi->id, '2');
            if ($pemegang_tambahan->count() > 0) {

                $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $koperasiService->addPemegangTambahan($koperasi->id, $pemegang_tambahan, '2');
            }

            return ['result' => true, 'data' => $koperasi];
        } else {
            return ['result' => false, 'message' => 'Data tidak ditemukan'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, KoperasiService $koperasiService)
    {
        $koperasiService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
        //
    }

    public function getFile($id, Request $request, KoperasiService $koperasiService)
    {
        $result = $koperasiService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile(/* $id, $file,  */Request $request, KoperasiService $koperasiService)
    {
        $result = $koperasiService->downloadFile($request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
