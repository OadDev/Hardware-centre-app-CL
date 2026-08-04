<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OnboardingSlide;

class OnboardingController extends Controller
{
    public function index()
    {
        return response()->json([
            'slides' => OnboardingSlide::where('status', 'Active')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (OnboardingSlide $s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'subtitle' => $s->subtitle,
                    'image_url' => $s->image_url,
                ]),
        ]);
    }
}
