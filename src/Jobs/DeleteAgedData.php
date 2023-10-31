<?php

namespace Ragnarok\Consat\Jobs;

use Ragnarok\Consat\Facades\ConsatImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DeleteAgedData implements ShouldQueue
{
    use \Ragnarok\Sink\Traits\LogPrintf;
    use \Illuminate\Foundation\Bus\Dispatchable;
    use \Illuminate\Queue\InteractsWithQueue;
    use \Illuminate\Bus\Queueable;
    use \Illuminate\Queue\SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->logPrintfInit("[Consat DeleteAged]: ");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach (config('ragnarok_consat.max_age', []) as $csv => $age) {
            $model = ConsatImporter::getCsvModel($csv);
            if (!$model) {
                return;
            }
            $ageDate = today()->sub($age);
            $deleted = $model::whereDate('date', '<', $ageDate)->delete();
            $this->info("Deleted %d rows older than %s from %s", $deleted, $ageDate->format('Y-m-d'), $model);
        }
    }
}
