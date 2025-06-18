<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $incomes = $user->incomes;

        if(count($incomes) === 0) {
            return response()->json([
                'message' => 'هیچ درآمدی ثبت نشده است'
            ]);
        }

        return $incomes->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('incomes')->where('user_id', $user->id)],
                'amount' => 'required|integer|min:4',
            ]);

            $income = DB::transaction(function() use ($validated, $user) {
                $income = Income::create([
                    'name' => $validated['name'],
                    'amount' => $validated['amount'],
                    'user_id' => $user->id
                ]);

                return $income;
            });

            return response()->json([
                'message' => "درآمد '$income->name' با موفقیت ثبت شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "ذخیره درآمد با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income)
    {
        return $income->toResource()->additional([
            'transactions' => $income->transactions->toResourceCollection()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('incomes')->where('user_id', $user->id)->ignore($income->id)],
                'amount' => 'required|integer|min:4',
            ]);

            DB::transaction(function () use ($validated, $income) {
                $income->update([
                    'name' => $validated['name'],
                    'amount' => $validated['amount']
                ]);
            });

            return response()->json([
                'message' => "آپدیت درآمد '$income->name' با موفقیت انجام شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'آپدیت درآمد با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        $incomeName = $income->name;

        try {
            DB::transaction(function() use ($income) {
                $income->delete();
            });

            return response()->json([
                'message' => "درآمد $incomeName با موفقیت انجام شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "حذف درآمد '$incomeName' با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }
}
