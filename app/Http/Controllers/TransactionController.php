<?php

namespace App\Http\Controllers;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use App\Models\Transaction;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;
use App\Traits\AllocateAsset;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;
use App\Models\Asset;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use AllocateAsset;

    private $user;

    public function __construct() {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = $this->user->transactions;

        if(count($transactions) === 0) {
            return response()->json([
                'message' => 'هیچ تراکنشی ثبت نشده است'
            ]);
        }

        return $transactions->toResourceCollection();
    }

    public function dateIndex() {
        $date = Jalalian::fromFormat('Y/m/d', request()->query('date'))->toCarbon();

        $transactions = Transaction::whereDate('created_at',$date->toDateString())->get();

        return $transactions->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionStoreRequest $request)
    {
        try {
            $response = $this->allocate($request, $this->user);

            return response()->json([
                'message' => $response['message']
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ذخیره تراکنش با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {               
        return $transaction->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionUpdateRequest $request, Transaction $transaction)
    {
        try {
            $transactionLastData = [
                'asset' => $transaction->asset,
                'type' => $transaction->type
            ];

            DB::transaction(function() use ($transaction, $request) {
                $transaction->update([
                    'asset' => $request->asset,
                    'type' => $request->type,
                    'description' => $request->description
                ]);
            });

            if($transactionLastData['type'] != $request->type || $transactionLastData['asset'] != $request->asset) {
                $asset = abs($transaction->asset - $transactionLastData['asset']);

                $transaction->type == 'incriment' 
                    ? $this->incriment($this->user->asset, $transaction->transationable()->first(), $asset) 
                    : $this->decriment($this->user->asset, $transaction->transationable()->first(), $asset);
            }

            return response()->json([
                'message' => 'آپدیت تراکنش با موفقیت انجام شد',
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'آپدیت تراکنش با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        try {
            $this->decriment($this->user->asset, $transaction->transationable()->first(), $transaction->asset);

            $transaction->delete();

            return response()->json([
                'message' => "تراکنش با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'حذف تراکنش با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function allocateAsset(TransactionRequest $request) {
        try {
            $response = AllocateAsset::allocate($request, $this->user);

            return response()->json($response);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'تخصیص موجودی با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }
}
