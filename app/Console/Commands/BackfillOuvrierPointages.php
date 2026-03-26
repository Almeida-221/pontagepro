<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillOuvrierPointages extends Command
{
    protected $signature   = 'pointages:backfill';
    protected $description = 'Copy completed mobile attendances into ouvrier_pointages for web admin sync';

    public function handle(): int
    {
        DB::statement("
            INSERT INTO ouvrier_pointages (user_id, company_id, date, statut, initiated_by, created_at, updated_at)
            SELECT
                a.worker_id,
                a.company_id,
                a.date,
                'present',
                NULL,
                NOW(),
                NOW()
            FROM attendances a
            WHERE a.exit_time IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM ouvrier_pointages op
                  WHERE op.user_id = a.worker_id
                    AND op.date    = a.date
              )
        ");

        $count = DB::select("
            SELECT ROW_COUNT() as n
        ")[0]->n ?? 0;

        $this->info("Backfill terminé : {$count} pointage(s) ajouté(s).");
        return 0;
    }
}
