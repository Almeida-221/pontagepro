<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'Marque comme expirés les abonnements actifs dont la date de fin est dépassée';

    public function handle(): int
    {
        $count = Subscription::where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info("$count abonnement(s) marqué(s) comme expirés.");

        return self::SUCCESS;
    }
}
