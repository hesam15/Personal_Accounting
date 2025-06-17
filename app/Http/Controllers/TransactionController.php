<?php

namespace App\Http\Controllers;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use App\Models\DailyExpense;
use App\Models\Transaction;
use App\Traits\DailyExpensesHistory;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $tableName = ModelConsts::getTableName($request->transationable_type);

            $validated = $request->validate([
                'amount' => 'required|integer|min:1000',
                'type' => ['required', Rule::enum(TransactionTypes::class)],
                'description' => 'nullable|string|max:50',
                'transationable_type' => ['required', Rule::in(ModelConsts::MODELS)],
                'transationable_id' => ['required', Rule::exists($tableName, 'id')]
            ]);

            $validated['transationable_type'] = $this->setTotal($validated);

            DB::transaction(function() use ($validated, $user) {
                Transaction::create([
                    'amount' => $validated['amount'],
                    'type' => $validated['type'],
                    'description' => $validated['description'] ?? null,
                    'transationable_type' => $validated['transationable_type'],
                    'transationable_id' => $validated['transationable_id'],
                    'user_id' => $user->id
                ]);
            });

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
    public function update(Request $request, Transaction $transaction)
    {
        try {
            $tableName = ModelConsts::getTableName($request->transationable_type);

            $validated = $request->validate([
                'amount' => 'required|integer|min:1000',
                'type' => ['required', Rule::enum(TransactionTypes::class)],
                'description' => 'nullable|string|max:50',
                'transationable_type' => ['required', Rule::in(ModelConsts::MODELS)],
                'transationable_id' => ['required', Rule::exists($tableName, 'id')]
            ]);

            $validated['transationable_type'] = $this->setTotal($validated);

            DB::transaction(function() use ($transaction, $validated) {
                $transaction->update([
                    'amount' => $validated['amount'],
                    'type' => $validated['type'],
                    'description' => $validated['description']
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
    public function destroy(DailyExpense $daily_expense)
    {
        try {
            $daily_expense_date = jdate($daily_expense->created_at)->format('Y/m/d');

            $daily_expense->delete();

            return response()->json([
                'message' => "تراکنش مربوط به تاریخ $daily_expense_date با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'حذف تراکنش با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }
}
