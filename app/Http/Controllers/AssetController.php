<?php

namespace App\Http\Controllers;

use App\Enums\TransactionTypes;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function show(Asset $asset) {
        return $asset->toResource();
    }

    public function update(Asset $asset, Request $request) {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'amount' => 'required|integer',
                'type' => ['required', Rule::enum(TransactionTypes::class)]
            ]);

            $discription = $validated['type'] == 'incriment'
                ? 'واریز'
                : 'برداشت';

            DB::transaction(function() use ($asset, $validated, $discription, $user) {
                $asset->transactions()->create([
                    'asset' => abs($validated['amount'] - $asset->amount),
                    'type' => $validated['type'],
                    'description' => $discription,
                    'user_id' => $user->id
                ]);

                $asset->update([
                    'amount' => $validated['amount']
                ]);
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
