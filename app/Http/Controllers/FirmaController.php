<?php

namespace App\Http\Controllers;

use App\Http\Requests\FirmaRequest;
use App\Mail\BadanRegistered;
use App\Models\Firma;
use App\Services\FirmaService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FirmaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Firma::select([
                'firma.*',
                'provinces.name as nama_provinsi',
                'regencies.name as nama_kotkab',
                'firma_pemegang.nama as pemegang_utama'
            ])
            ->leftJoin('provinces', 'firma.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'firma.kotkab', 'regencies.id')
            ->leftJoin('firma_pemegang', function($join){
                $join->whereRaw('firma_pemegang.id = (
                    SELECT
                    MIN(firma_pemegang.id) pemegang_id
                    FROM firma_pemegang
                    WHERE firma_pemegang.firma_id = firma.id
                )');
            })
            ->orderBy('firma.id', 'desc')
            ->get();

        return ['result'=>true, 'data'=> $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FirmaRequest $request, FirmaService $firmaService)
    {        
        $request->validated();

        
        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');
        $validatedKTP = $firmaService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $firmaService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $newFirma = $firmaService->store($request);

        $bidangs = collect($request->input('bidangs'));
        $firmaService->addBidangs($newFirma->id, $bidangs);

        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_utama = collect($pemegang_utama);
        if ($pemegang_utama->count() > 0) {
            $pemegang_utama->transform(function ($item, $key) use ($ktps, $npwps) {
                $item['ktp'] = $ktps[$key];
                $item['npwp'] = isset($npwps[$key]) ? $npwps[$key] : null;
                return $item;
            });
            $firmaService->addPemegangTambahan($newFirma->id, $pemegang_utama, '1');
        }

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send(new BadanRegistered($newFirma));

        return [
            'result' => true,
            'data' => $newFirma->attributesToArray(),
            'formatted' => $newFirma->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, FirmaService $firmaService)
    {
        $data = $firmaService->find($id);

        return ['result' => true, 'data' => $data];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FirmaRequest $request, $id, FirmaService $firmaService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $firmaService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $firmaService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_utama = Helper::isJson($request->input('pemegang_utama'), true);
        $pemegang_utama = collect($pemegang_utama);

        $firma = $firmaService->update($id, $request);
        if ($firma) {
            $bidangs = collect($request->input('bidangs'));
            $firmaService->updateBidangs($firma->id, $bidangs);

            $firmaService->clearPemegangTambahan($firma->id, '1');
            $pemegang_utama->transform(function ($item, $key) use ($ktps, $npwps) {
                $item['ktp'] = $ktps[$key];
                $item['npwp'] = isset($npwps[$key]) ? $npwps[$key] : null;
                return $item;
            });
            $firmaService->addPemegangTambahan($firma->id, $pemegang_utama, '1');

            return ['result' => true, 'data' => $firma];
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
    public function destroy($id, FirmaService $firmaService)
    {
        $firmaService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
    }

    public function getFile($id, Request $request, FirmaService $firmaService)
    {
        $result = $firmaService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile($id, $file, Request $request, FirmaService $firmaService)
    {
        $result = $firmaService->downloadFile($request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
