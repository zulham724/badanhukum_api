<?php

namespace App\Http\Controllers;

use App\Http\Requests\CVRequest;
use App\Mail\BadanRegistered;
use App\Models\Cv;
use App\Services\CVService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CVController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Cv::select([
            'cv.*',
            'provinces.name as nama_provinsi',
            'regencies.name as nama_kotkab',
            'direktur.nama as nama_direktur',
        ])
            ->leftJoin('provinces', 'cv.provinsi', 'provinces.id')
            ->leftJoin('regencies', 'cv.kotkab', 'regencies.id')
            ->leftJoin('cv_pemegang as direktur', function ($join) {
                $join->on('cv.id', 'direktur.cv_id')
                    ->where('direktur.tipe', '1')
                    ->where('direktur.kedudukan', 'direktur');
            })
            ->orderBy('cv.id', 'desc')
            ->get();

        return ['result' => true, 'data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CVRequest $request, CVService $cvService)
    {

        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $cvService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $cvService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_lain = Helper::isJson($request->input('pemegang_lain'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_lain = collect($pemegang_lain);

        $newCV = $cvService->store($request);

        $bidangs = collect($request->input('bidangs'));
        $cvService->addBidangs($newCV->id, $bidangs);

        $pemegangs = collect([
            [
                "nama" => $request->input('direktur_utama'),
                "kedudukan" => 'direktur',
                "saham" => $request->input('saham_direktur_utama'),
                "ktp" => $ktps[0],
                "npwp" => $npwps[0],
            ],
            [
                "nama" => $request->input('komisaris_utama'),
                "kedudukan" => 'komisaris',
                "saham" => $request->input('saham_komisaris_utama'),
                "ktp" => $ktps[1],
                "npwp" => isset($npwps[1]) ? $npwps[1] : null,
            ]
        ]);
        $cvService->addPemegangTambahan($newCV->id, $pemegangs, '1');

        if ($pemegang_tambahan->count() > 0) {
            $index = 1;

            $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $cvService->addPemegangTambahan($newCV->id, $pemegang_tambahan, '2');
        }

        if ($pemegang_lain->count() > 0) {
            $pemegang_lain->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                $index = $index + 1;
                $item['ktp'] = $ktps[$index];
                $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                return $item;
            });
            $cvService->addPemegangTambahan($newCV->id, $pemegang_lain, '3');
        }

        Mail::to($request->input('email'))
            ->cc(config('mail.from.address'))
            ->send(new BadanRegistered($newCV));

        return [
            'result' => true,
            'data' => $newCV->attributesToArray(),
            'formatted' => $newCV->summarized
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, CVService $cvService)
    {
        $data = $cvService->find($id);

        return ['result' => true, 'data' => $data];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CVRequest $request, $id, CVService $cvService)
    {
        $request->validated();

        $ktps = $request->file('ktp');
        $npwps = $request->file('npwp');

        $validatedKTP = $cvService->validateKTP($ktps);
        if (!is_bool($validatedKTP)) {
            return $validatedKTP;
        }
        $validatedNPWP = $cvService->validateKTP($npwps);
        if (!is_bool($validatedNPWP)) {
            return $validatedNPWP;
        }

        $pemegang_tambahan = Helper::isJson($request->input('pemegang_tambahan'), true);
        $pemegang_lain = Helper::isJson($request->input('pemegang_lain'), true);
        $pemegang_tambahan = collect($pemegang_tambahan);
        $pemegang_lain = collect($pemegang_lain);

        $cv = $cvService->update($id, $request);
        if ($cv) {
            $bidangs = collect($request->input('bidangs'));
            $cvService->updateBidangs($cv->id, $bidangs);

            $pemegangs = collect([
                [
                    "nama" => $request->input('direktur_utama'),
                    "kedudukan" => 'direktur',
                    "saham" => $request->input('saham_direktur_utama'),
                    "ktp" => isset($ktps[0]) ? $ktps[0] : null,
                    "npwp" => isset($npwps[0]) ? $npwps[0] : null,
                ],
                [
                    "nama" => $request->input('komisaris_utama'),
                    "kedudukan" => 'komisaris',
                    "saham" => $request->input('saham_komisaris_utama'),
                    "ktp" => isset($ktps[1]) ? $ktps[1] : null,
                    "npwp" => isset($npwps[1]) ? $npwps[1] : null,
                ]
            ]);
            $cvService->updatePemegangTambahan($cv->id, $pemegangs, '1');

            $cvService->clearPemegangTambahan($cv->id, '2');
            if ($pemegang_tambahan->count() > 0) {
                $index = 1;

                $pemegang_tambahan->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $cvService->addPemegangTambahan($cv->id, $pemegang_tambahan, '2');
            }

            $cvService->clearPemegangTambahan($cv->id, '3');
            if ($pemegang_lain->count() > 0) {
                $pemegang_lain->transform(function ($item, $key) use (&$index, $ktps, $npwps) {
                    $index = $index + 1;
                    $item['ktp'] = $ktps[$index];
                    $item['npwp'] = isset($npwps[$index]) ? $npwps[$index] : null;
                    return $item;
                });
                $cvService->addPemegangTambahan($cv->id, $pemegang_lain, '3');
            }

            return ['result' => true, 'data' => $cv];
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
    public function destroy($id, CVService $cvService)
    {
        $cvService->delete($id);

        return ['result' => true, 'messege' => "Sukses menghapus data"];
    }

    public function getFile($id, Request $request, CVService $cvService)
    {
        $result = $cvService->getFileContent($id, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }

    public function downloadFile($id, $file, Request $request, CVService $cvService)
    {
        $result = $cvService->downloadFile($id, $file, $request->input('path'));
        if ($result === false) {
            return response()->json(['message' => 'File tidak ditemukan'], 422);
        } else {
            return $result;
        }
    }
}
