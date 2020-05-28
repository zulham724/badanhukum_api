<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function index()
    {
        //
    }

    public function getProvinces()
    {
        $locations = DB::table('provinces')->get();
        
        return ['result' => true, 'data' => $locations];
    }

    public function getRegencies(Request $request)
    {
        $query = DB::table('regencies');
        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        $regencies = $query->select(['id','name'])->get();

        return ['result' => true, 'data' => $regencies];
    }

    public function getDistricts(Request $request)
    {
        $query = DB::table('districts');
        if ($request->has('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }
        $subdistricts = $query->select('id', 'name')->get();
        
        return ['result' => true, 'data' => $subdistricts];
    }

    public function getUrbans(Request $request)
    {
        $query = DB::table('urbans');
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        $urbans = $query->select(['id', 'name'])->get();

        return ['result' => true, 'data' => $urbans];
    }


}
