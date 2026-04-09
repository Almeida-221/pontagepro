<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdVideo;
use Illuminate\Http\Request;

class AdVideoApiController extends Controller
{
    /**
     * GET /api/ad-videos?app=securite|pointage
     * Retourne la vidéo publicitaire active pour l'application concernée.
     * Un seul résultat (la plus récente) pour ne pas surcharger l'écran.
     */
    public function index(Request $request)
    {
        $app = $request->query('app', 'securite');

        $video = AdVideo::activeFor($app)->latest()->first();

        if (!$video) {
            return response()->json(['video' => null]);
        }

        return response()->json(['video' => $video->toApiArray()]);
    }
}
