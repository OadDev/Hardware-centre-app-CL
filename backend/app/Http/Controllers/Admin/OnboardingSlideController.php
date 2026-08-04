<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OnboardingSlideController extends Controller
{
    public function index()
    {
        return response()->json(OnboardingSlide::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = $data['sort_order'] ?? OnboardingSlide::max('sort_order') + 1;
        $slide = OnboardingSlide::create($data);

        return response()->json($slide, 201);
    }

    public function update(Request $request, OnboardingSlide $onboardingSlide)
    {
        $data = $this->validated($request);
        $onboardingSlide->update($data);

        return response()->json($onboardingSlide);
    }

    public function destroy(OnboardingSlide $onboardingSlide)
    {
        if ($onboardingSlide->image_path) {
            Storage::disk('uploads')->delete($onboardingSlide->image_path);
        }
        $onboardingSlide->delete();

        return response()->json(['message' => 'Onboarding slide removed']);
    }

    public function uploadImage(Request $request, OnboardingSlide $onboardingSlide)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($onboardingSlide->image_path) {
            Storage::disk('uploads')->delete($onboardingSlide->image_path);
        }

        $onboardingSlide->update([
            'image_path' => $request->file('image')->store('onboarding', 'uploads'),
        ]);

        return response()->json($onboardingSlide);
    }

    public function destroyImage(OnboardingSlide $onboardingSlide)
    {
        if ($onboardingSlide->image_path) {
            Storage::disk('uploads')->delete($onboardingSlide->image_path);
        }
        $onboardingSlide->update(['image_path' => null]);

        return response()->json($onboardingSlide);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);
    }
}
