<?php

namespace App\Http\Controllers;

use App\Models\SaveBox;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaveBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $saveBoxs = $user->saveBoxs;

        return $saveBoxs->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('save_boxes')->where('user_id', $user->id)],
                'amount' => 'required|integer|min:0'
            ]);

            $saveBox = DB::transaction(function() use ($validated, $user){
                $saveBox = SaveBox::create([
                    'name' => $validated['name'],
                    'amount' => $validated['amount'],
                    'user_id' => $user->id
                ]);
                
                return $saveBox;
            });

            return response()->json([
                'message' => "باکس ذخیره $saveBox->name با موفقیت ثبت شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'ذخیره باکس ذخیره با ارور مواجه شد، مجددا تلاش کنید',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SaveBox $saveBox)
    {
        return $saveBox->toResource()->additional([
            'transactions' => $saveBox->transactions->toResourceCollection()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaveBox $saveBox)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50', Rule::unique('save_boxs')->where('user_id', $user->id)->ignore($saveBox->id)],
                'amount' => 'required|integer|min:0'
            ]);

            DB::transaction(function() use ($validated, $saveBox){
                $saveBox->update([
                    'name' => $validated['name'],
                    'amount' => $validated['amount']
                ]);
            });

            return response()->json([
                'message' => "باکس ذخیره '$saveBox->name' با موفقیت آپدیت شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "حذف $saveBox->name با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaveBox $saveBox)
    {
        $saveBoxName = $saveBox->name;

        try {
            $saveBox->delete();

            return response()->json([
                'message' => "باکس ذخیره '$saveBoxName' با موفقیت حذف شد"
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "حذف باکس ذخیره '$saveBoxName' با ارور مواجه شد، مجددا تلاش کنید",
                'error' => $e->getMessage()
            ]);
        }
    }
}
