<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Traits\TransactionTotal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvestmentController extends Controller
{
    use TransactionTotal;

    private $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = $this->user;

        $investments = $user->investments;

        if(count($investments) === 0) {
            return response()->json([
                'message' => 'هیچ سرمایه گذاری ثبت نشده است'
            ]);
        }

        return $investments->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $this->user;

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('investments')->where('user_id', $this->user->id)],
            ]);

            $investment = DB::transaction(function() use ($validated, $user) {
                $investment = Investment::create([
                    'name' => $validated['name'],
                    'user_id' => $user->id
                ]);

                return $investment;
            });

            return response()->json([
                'message' => "سرمایه گذاری '$investment->name' با موفقیت ثبت شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ذخیره سرمایه گذاری با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Investment $investment)
    {
        return $investment->toResource()->additional([
            'transactions' => $investment->transactions->toResourceCollection()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investment $investment)
    {
        try {
            $user = $this->user;

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('investments')->where('user_id', $user->id)->ignore($investment->id)],
            ]);

            DB::transaction(function() use ($validated, $investment) {
                $investment->update([
                    'name' => $validated['name'],
                ]);
            });

            return response()->json([
                'message' => "سرمایه گذاری '$investment->name' با موفقیت آپدیت شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "آپدیت سرمایه گذاری '$investment->name' با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investment $investment)
    {
        $investmentName = $investment->name;

        try {
            DB::transaction(function() use ($investment){
                $investment->delete();
            });

            return response()->json([
                'message' => "سرمایه گذاری '$investmentName' با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "حذف سرمایه گذاری '$investmentName' با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }
}
