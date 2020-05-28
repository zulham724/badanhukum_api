<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YayasanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "nama_yayasan" => "required",
            "nama_alternatif" => "sometimes|present|nullable|array|min:0",
            "kategori_modal" => "required",
            "biaya" => "required|numeric",
            "modal_dasar" => "required|numeric|min:0",

            "alamat" => "required",
            "provinsi" => "required|numeric",
            "kotkab" => "required|numeric",
            "kecamatan" => "required|numeric",
            "kelurahan" => 'required|numeric',
            "kodepos" => "required|numeric",
            "rt" => "required|numeric",
            "rw" => "required|numeric",
            "kode_telpon" => 'present|nullable|numeric',
            "nomor_telpon" => 'present|nullable|numeric',
            "nomor_handphone" => "required|numeric",
            "email" => "required|email",

            "bidang" => "required",
            "bidangs" => "required|array|min:0", //json

            "pemegang_utama" => "required|json", //array|min:0
            "pemegang_tambahan" => "present|nullable|json", //array|min:0
            "ktp" => 'required', //file|max:2048
            "npwp" => 'required'
        ];
    }
}
