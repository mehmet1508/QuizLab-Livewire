<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionSampleDownloadController extends Controller
{
    /**
     * Örnek soru içe aktarma CSV'sini indir.
     */
    public function __invoke(Request $request): Response
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="soru_ornek.csv"',
        ];

        $content = "soru_metni,sik1,sik2,sik3,sik4,dogru_sik\n";
        $content .= "\"Türkiye'nin başkenti neresidir?\",İstanbul,Ankara,İzmir,Bursa,2\n";
        $content .= "\"PHP hangi tür bir programlama dilidir?\",Derlenen,Yorumlanan,Makine dili,,2\n";

        return response($content, 200, $headers);
    }
}
