<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\HsnCode;
use Illuminate\Http\Request;

class GstController extends Controller
{
    public function index()
    {
        return response()->json([
            'hsn_codes' => HsnCode::orderBy('code')->get(),
            'default_gst_rate' => (float) AppSetting::current()->default_gst_rate,
            'gst_inclusive' => (bool) AppSetting::current()->gst_inclusive,
            'invoice_prefix' => AppSetting::current()->invoice_prefix,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:255'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        return response()->json(HsnCode::create($data), 201);
    }

    public function update(Request $request, HsnCode $hsnCode)
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:20'],
            'description' => ['sometimes', 'string', 'max:255'],
            'gst_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $hsnCode->update($data);

        return response()->json($hsnCode);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'default_gst_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'gst_inclusive' => ['sometimes', 'boolean'],
            'invoice_prefix' => ['sometimes', 'string', 'max:20'],
        ]);

        AppSetting::current()->update($data);

        return response()->json(['message' => 'GST settings updated']);
    }
}
