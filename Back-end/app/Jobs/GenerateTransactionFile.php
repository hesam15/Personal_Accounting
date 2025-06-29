<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class GenerateTransactionFile implements ShouldQueue
{
    use Queueable;

    private $today;
    private $transactions;
    private $user;
    private $data = [];

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Collection $transactions)
    {
        $this->today = jdate()->getFirstDayOfMonth()->format('Y-m-d');

        $this->user = $user;

        $this->transactions = $transactions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info($this->transactions);

        foreach($this->transactions as $item) {
            $array = $item->toArray();
            unset($array['updated_at']);
            $this->data[] = $array;
        }

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        Storage::disk('report')->put($this->user->id."/$this->today/transactions.json", $json);
    }
}
