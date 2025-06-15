<?php

namespace App\Http\Controllers;

use App\Models\DailyExpense;
use App\Traits\DailyExpensesHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyExpenseController extends Controller
{
    use DailyExpensesHistory;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $expenses = $user->dailyExpenses;

        if(count($expenses) === 0) {
            return response()->json([
                'message' => 'هیچ مخارج روزانه ای ثبت نشده است'
            ]);
        }

        return $expenses->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $today = Carbon::today();
            $expenses = $user->dailyExpenses()->whereDate('created_at', $today)->first();

            if($expenses) {
                return response()->json([
                   'message' => 'مخارج امروز قبلا ثبت شده است',
                   'expenses' => $expenses 
                ]);
            }

            $validated = $request->validate([
                'expenses' => 'required',
            ]);

            $expenses = json_decode($validated['expenses'], true);
            $this->setTotal($expenses, $user);
            $total = array_sum(array_map('intval', array_column($expenses, 'amount')));

            DB::transaction(function() use ($validated, $user, $total) {
                DailyExpense::create([
                    'expenses' => $validated['expenses'],
                    'total' => $total,
                    'user_id' => $user->id
                ]);
            });

            return response()->json([
                'message' => 'مخارج روزانه با موفقیت ثبت شد'
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ذخیره مخارج روزانه با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyExpense $daily_expense)
    {           
        return $daily_expense->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyExpense $daily_expense)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'expenses' => 'required|json',
            ]);

            $expenses = json_decode($validated['expenses'], true);
            $this->setTotal($expenses, $user);
            $total = array_sum(array_map('intval', array_column($expenses, 'amount')));

            DB::transaction(function() use ($daily_expense, $validated, $total) {
                $daily_expense->update([
                    'expenses' => $validated['expenses'],
                    'total' => $total
                ]);
            });

            return response()->json([
                'message' => 'آپدیت مخارج روزانه با موفقیت انجام شد',
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'آپدیت مخارج روزانه با ارور مواجه شد، مجددا تلاش کنید',
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
                'message' => "مخارج روزانه مربوط به تاریخ $daily_expense_date با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'حذف مخارج روزانه با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }
}
