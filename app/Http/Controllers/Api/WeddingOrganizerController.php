<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\WeddingOrganizerResource;
use App\Models\WeddingOrganizer;
use Illuminate\Http\Request;

class WeddingOrganizerController extends Controller
{
    public function index()
    {
        $weddingOrganizer = WeddingOrganizer::withCount('weddingPackages')->get();
        return WeddingOrganizerResource::collection($weddingOrganizer);
    }

    public function show(WeddingOrganizer $weddingOrganizer)
    {
        $weddingOrganizer->load([
            'weddingPackages.photos',
            'weddingPackages.weddingOrganizer' => function ($query) {
                $query->withCount('weddingPackages');
            }
        ])->loadCount('weddingPackages');
        return new WeddingOrganizerResource($weddingOrganizer);
    }
}
