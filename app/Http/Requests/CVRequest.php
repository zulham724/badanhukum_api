<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CVRequest extends FormRequest
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
            "nama_perusahaan" => "required",
            "nama_alternatif" => "sometimes|present|nullable|array|min:0",
            "direktur_utama" => "required",
            "komisaris_utama" => "required",
            "kategori_modal" => "required",
            "biaya" => "required|numeric",
            "modal_dasar" => "required|numeric|min:0",
            "modal_ditempatkan" => "required|numeric|min:0",
            "saham_direktur_utama" => "required|numeric",
            "saham_komisaris_utama" => "required|numeric",
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
            "bidangs" => "required|array|min:1", //json
            "pemegang_lain" => "present|nullable|json", //
            "pemegang_tambahan" => "present|nullable|json", //array|min:0
            "ktp" => 'required', //file|max:2048
            "npwp" => 'required'
        ];
    }
}
