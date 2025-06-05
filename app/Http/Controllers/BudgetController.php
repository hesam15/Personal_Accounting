<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $budgets = $user->budgets;

        return response()->json([
            'budgets' => $budgets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:1|max:50|unique:budgets',
                'amount' => 'required|integer|min:1000'
            ]);

            $budget = Budget::create([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => "بودجه $budget->name با موفقیت ذخیره سازی شد",
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
        return response()->json([
            'budget' => $budget
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:1|max:50|unique:budgets',
                'amount' => 'required|integer|min:1000'
            ]);

            $budget->update([
                'name' => $validated['name'],
                'amount' => $validated['amount']
            ]);

            return response()->json([
                'message' => "آپدیت بودجه $budget->name با موفقیت انجام شد"
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
        try {
            $budget->delete();

            return response()->json([
                'message' => "بودجه $budget->name با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'حذف بودجه با ارور مواجه شد',
                'error' => $e->getMessage()
            ]);
        }
    }
}
