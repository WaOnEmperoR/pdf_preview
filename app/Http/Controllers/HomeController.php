<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function createSign(){
        
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(300),
                new ImagickImageBackEnd()
            )
        );
        
        $qrpath = 'storage/tempsigned/test.png';
        $qr = $writer->writeFile($path, 'storage/tempsigned/test.png');

        $nama = "Rachmawan Atmaji Perdana";
        $nip = "w133eqeq";

        $qimage =imagecreatefrompng('storage/tempsigned/test.png');

        $template_image = imagecreatefrompng('storage/tempsigned/temp.png');

        $size = 33;
        // Allocate A Color For The Text
        $white = imagecolorallocate($template_image, 0, 0, 0);
        $shadow_color = imagecolorallocate($template_image, 0x99, 0x99, 0x99);
        $glow_color = imagecolorallocate($template_image, 255, 255, 255);

        $fontpath = "fonts/Arial.ttf";

        // Set Text to Be Printed On Image
        $text_nama = "";//"Telah ditandatangani secara elektronik oleh";//$nama;//"Irwan Rawal Husdi";
        $text_ttd = "Ditandatangani secara elektronik oleh";//$nama;//"Telah ditandatangani secara elektronik oleh";
        $text_jabatan =  $nama;//$jabatan;//"Kepala Pusat Manajemen Informasi";
        $text_tempat = $nip;//"Tanggal";//"di ".$lokasi;//"di Jakarta";
        $text_tanggal = "";//$waktu;//$waktu;//"2019-02-07 19:19+07:00";

        // set position
        $x_nama = 310;
        $y_nama = 65;
        $x_ttd = 310;
        $y_ttd = 115;
        $x_jabatan = 310;
        $y_jabatan = 165;
        $x_tempat = 310;
        $y_tempat = 215;
        $x_tanggal = 310;
        $y_tanggal = 265;
        $x_shadow = 3;
        $y_shadow = 3;


        // Print Text On Image
                     
        imagettftext($template_image, $size, 0, $x_nama, $y_nama, $white, $fontpath, $text_nama);       
        imagettftext($template_image, $size, 0, $x_ttd, $y_ttd, $white, $fontpath, $text_ttd);       
        imagettftext($template_image, $size, 0, $x_jabatan, $y_jabatan, $white, $fontpath, $text_jabatan);       
        imagettftext($template_image, $size , 0, $x_tempat, $y_tempat, $white, $fontpath, $text_tempat);
        imagettftext($template_image, $size , 0, $x_tanggal, $y_tanggal, $white, $fontpath, $text_tanggal);
        imagecopy($template_image, $qimage, 13, 10, 0, 0, imagesx($qimage), imagesy($qimage));

        
        // set transparent
        imagesavealpha($template_image, true);
        imagealphablending($template_image, false);
        $white = imagecolorallocatealpha($template_image, 255, 255, 255, 127);
        imagefill($template_image, 0, 0, $white);
        $res = 'storage/tempsigned/'. $nip.'.png';
        imagepng($template_image, $res);

        // Clear Memory
        imagedestroy($template_image);        
        
        // delete qrcode
        unlink($qrpath);
        return $res;

    }   
}
