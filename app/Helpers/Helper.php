<?php

use Illuminate\Support\Facades\Storage;

// if (!function_exists('get_file_base64')) {
//     function get_file_base64($file_path)
//     {
//         /* $arrContextOptions = array(
//             "ssl" => array(
//                 "verify_peer" => false,
//                 "verify_peer_name" => false,
//             ),
//         ); */
//         // $type = pathinfo($file_path, PATHINFO_EXTENSION);
//         $fileData = file_get_contents($file_path/* , false, stream_context_create($arrContextOptions) */);
//         // $fileData = Storage::get($file_path);
//         $fileBase64Data = base64_encode($fileData);
//         // $fileDataFull = 'data:image/' . $type . ';base64,' . $fileBase64Data;
//         $fileDataFull = 'data:text/html; charset=UTF-8;base64,' . $fileBase64Data;

//         return $fileDataFull;
//     }
// }

if (!function_exists('get_file_base64')) {
    function get_file_base64($file_path)
    {
        $fileData = Storage::get($file_path);
        $type = pathinfo($file_path, PATHINFO_EXTENSION);
        $fileBase64Data = base64_encode($fileData);
        // $fileDataFull = 'data:image/' . $type . ';base64,' . $fileBase64Data;
        $prefix = 'data:image/' . $type . ';base64,';
        $fileDataFull = $prefix. $fileBase64Data;

        return $fileDataFull;
    }
}
