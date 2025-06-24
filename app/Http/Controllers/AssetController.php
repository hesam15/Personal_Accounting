<?php

namespace App\Http\Controllers;

use App\Enums\TransactionTypes;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use function App\Helpers\createTransaction;

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

            if($validated['type'] == 'decriment' && $asset->amount - $validated['amount'] < 0) {
                return response()->json([
                    'message' => 'موجودی کافی نمی باشد'
                ]);
            }

            DB::transaction(function() use ($asset, $validated) {
                $asset->update([
                    'amount' => $validated['amount'] + $asset->amount
                ]);
            });

            $request = Request::createFromGlobals([
                'asset' => $validated['amount'],
                'type' => $validated['type'],
                'description' => $discription,
                'user_id' => $user->id
            ]);

            createTransaction($asset ,$request, $user);


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
