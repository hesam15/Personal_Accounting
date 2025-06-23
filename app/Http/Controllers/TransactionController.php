<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;
use App\Traits\AllocateAsset;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;

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
            $response = null;

            if($transaction->type != $request->type || $transaction->asset != $request->asset) {
                $asset = abs($request->asset - $transaction->asset);

                $response = $request->type === 'incriment' 
                    ? $this->incriment($this->user->asset, $transaction->transationable()->first(), $asset)
                    : $this->decriment($this->user->asset, $transaction->transationable()->first(), $asset);
            }

            if($request->description != $transaction->description || $response && !in_array('error', $response)) {
                DB::transaction(function() use ($transaction, $request) {
                    $transaction->update([
                        'asset' => $request->asset,
                        'type' => $request->type,
                        'description' => $request->description
                    ]);
                });

                $response['message'] = 'آپدیت تراکنش با موفقیت انجام شد';
            }

            if(!$response) {
                return response()->json([
                    'message' => 'هیچ تغییری ایجاد نشد',
                ]);
            } 

            return response()->json([
                'message' => $response['message'],
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
}
