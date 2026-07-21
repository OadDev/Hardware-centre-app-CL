<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json(Banner::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $banner = Banner::create($data);

        return response()->json($banner, 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request);
        $banner->update($data);

        return response()->json($banner);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return response()->json(['message' => 'Banner removed']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'badge_label' => ['nullable', 'string', 'max:60'],
            'type' => ['required', Rule::in(['Homepage Banner', 'Offer Banner', 'Slider Image'])],
            'bg_color' => ['sometimes', 'string', 'max:20'],
            'sort_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);
    }
}
