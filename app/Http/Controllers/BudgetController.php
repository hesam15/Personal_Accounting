<?php

namespace App\Http\Controllers;

use App\Enums\BudgetsPeriod;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $budgets = $user->budgets;

        if(count($budgets) === 0) {
            return response()->json([
                'message' => 'هیچ بودجه ای ثبت نشده است'
            ]);
        }

        return $budgets->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('budgets')->where('user_id', $user->id)],
                'amount' => 'required|integer|min:0',
                'period' => ['required', Rule::enum(BudgetsPeriod::class)]
            ]);

            $existsBudget = $user->budgets()->where('name', $request->name)->first();

            if($existsBudget) {
                return response()->json([
                    'message' => 'بودجه ای با این نام قبلا ایجاد شده است',
                    'budget' => $existsBudget
                ], 422);
            }

            $budget = null;

            $budget = DB::transaction(function() use ($user, $validated) {
                $budget = Budget::create([
                    'name' => $validated['name'],
                    'amount' => $validated['amount'],
                    'period' => $validated['period'],
                    'user_id' => $user->id
                ]);

                return $budget;
            });

            return response()->json([
                'message' => "بودجه '$budget->name' با موفقیت ذخیره سازی شد",
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ایجاد بودجه با ارور مواجه شد',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        return $budget->toResource()->additional([
            'transactions' => $budget->transactions->toResourceCollection()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('budgets')->where('user_id', $user->id)->ignore($budget->id)],
                'amount' => 'required|integer|min:0',
                'period' => ['required', Rule::enum(BudgetsPeriod::class)]
            ]);


            DB::transaction(function() use ($budget, $validated) {
                $budget->update([
                    'name' => $validated['name'],
                    'amount' => $validated['amount'],
                    'period' => $validated['period']
                ]);
            });

            return response()->json([
                'message' => "آپدیت بودجه '$budget->name' با موفقیت انجام شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'آپدیت بودجه با ارور مواجه شد',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $budgetName = $budget->name;

        try {
            $budget->delete();

            return response()->json([
                'message' => "بودجه '$budgetName' با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "حذف بودجه '$budgetName' با ارور مواجه شد",
                'error' => $e->getMessage()
            ]);
        }
    }
}
