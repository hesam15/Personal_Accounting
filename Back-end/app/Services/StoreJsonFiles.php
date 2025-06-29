<?php
namespace App\Services;

use App\Jobs\GenerateTransactionFile;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class StoreJsonFiles{
    public static function transactions() {
        $users = User::all();

        $firstDay = jdate()->getFirstDayOfMonth()->toCarbon()->format('Y-m-d H:i:s');
        $lastDay = jdate()->getEndDayOfMonth()->toCarbon()->format('Y-m-d H:i:s');

        foreach($users as $user) {
            $transactions = $user->transactions()->whereBetween('created_at', [$firstDay, $lastDay])->get();

            GenerateTransactionFile::dispatchIf($transactions->count(), $user, $transactions);
        }
    }
}