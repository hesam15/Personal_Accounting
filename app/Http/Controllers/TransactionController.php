<?php

namespace App\Http\Controllers;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use App\Http\Requests\TransactionRequest;
use App\Models\DailyExpense;
use App\Models\Transaction;
use App\Traits\DailyExpensesHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use DailyExpensesHistory;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $transactions = $user->transaction;

        if(count($transactions) === 0) {
            return response()->json([
                'message' => 'هیچ تراکنش ای ثبت نشده است'
            ]);
        }

        return $transactions->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        try {
            $user = Auth::user();
            $model = ModelConsts::findModel($request->transationable_type)->find($request->transationable_id);

            $transaction = DB::transaction(function() use ($request, $user, $model) {
                $transaction = $model->transactions()->create([
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'description' => $request->description ?? null,
                    'user_id' => $user->id
                ]);
                
                return $transaction;
            });

            $this->setTotal($transaction);

            return response()->json([
                'message' => 'تراکنش با موفقیت ثبت شد'
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
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        try {
            $request->transationable_type = $this->setTotal($request);

            DB::transaction(function() use ($transaction, $request) {
                $transaction->update([
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'description' => $request->description
                ]);
            });

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
            $this->revert($transaction);

            $transaction->delete();

            return response()->json([
                'message' => "تراکنش مربوط به تاریخ $transaction->created_at با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'حذف تراکنش با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }
}
