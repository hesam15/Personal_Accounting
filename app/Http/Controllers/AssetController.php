<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function show(Asset $asset) {
        return $asset->toResource();
    }

    public function update(Asset $asset, Request $request) {
        try {
            $validated = $request->validate([
                'amount' => 'required|integer'
            ]);

            DB::transaction(function() use ($asset, $validated) {
                $asset->update([
                    'amount' => $validated['amount']
                ]);

                $asset->
            });


            return response()->json([
                'message' => 'آپدیت موجودی با موفقیت انجام شد'
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'آپدیت موجودی با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
