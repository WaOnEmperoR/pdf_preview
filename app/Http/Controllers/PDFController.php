<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Storage;

class PDFController extends Controller
{
    public function index(){
        return view('mytest');
    }

    public function signPDF(Request $request)
    {
        $file_to_upload = $request->file('file-to-upload');
        
        $file_basename = basename($file_to_upload->getClientOriginalName(), '.'.$file_to_upload->getClientOriginalExtension());

        // confusion between lly and ury in signing engine
        // it also cannot handle floating point, so it must be rounded to integer
        $llx = round($request->input('llx_trans'));
        $lly = round($request->input('ury_trans'));
        $urx = round($request->input('urx_trans'));
        $ury = round($request->input('lly_trans'));
        $passphrase = $request->input('passphrase');
        $page = $request->input('true_page');
        $token = $request->input('token');

        $response = Curl::to(config('global.kliwon_url').'signPDF')
            ->withHeader('Authorization: Bearer '.config('global.jwt_kliwon_sisumaker'))
            ->withFile('pdf', $file_to_upload->getRealPath(), $file_to_upload->getClientMimeType(), $file_basename)
            ->withFile('imageSign', 'image/hiclipart.com.png', 'image/png', 'myImage.png')
            ->withData( 
                    array( 
                        'username' => config('global.username'),
                        'urx' => $urx,
                        'ury' => $ury,
                        'llx' => $llx,
                        'lly' => $lly,
                        'passphrase' => $passphrase,
                        'token' => $token,
                        'page' => $page,
                        'idkeystore' => config('global.id_keystore'),
                        'reason' => 'Tanda Tangan Coba',
                        'location' => 'Tangerang Selatan' 
                    ) 
                )
            ->post();

        $decoded = json_decode($response);

        $data_base64 = base64_decode($decoded->data);
        Storage::disk('local')->put($file_basename."_SIGNED.".$file_to_upload->getClientOriginalExtension(), $data_base64);
    
        echo('Success');                
    }
}
