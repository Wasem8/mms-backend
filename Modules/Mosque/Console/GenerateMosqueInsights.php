<?php

namespace Modules\Mosque\Console;

use Illuminate\Console\Command;
use Modules\Mosque\Services\MosqueReportingEtlService;

class GenerateMosqueInsights extends Command
{
    protected $signature = 'etl:generate-mosque-insights {--days=1 : Lookback window in days. Defaults to yesterday when run nightly.}';

    protected $description = 'Generate daily mosque performance insights for the reporting data mart.';

    public function handle(MosqueReportingEtlService $etl): int
    {
        $days = max(1, (int) $this->option('days'));

        $this->info("Starting ETL for the last {$days} day(s)...");

        $summary = $etl->generateDailyMosqueInsights($days);

        $this->info("ETL complete.");
        $this->info("Days processed: {$summary['days_processed']}");
        $this->info("Fact rows upserted: {$summary['fact_rows']}");
        $this->info("Dim mosque rows: {$summary['mosque_dimensions']}");

        return self::SUCCESS;
    }
}
