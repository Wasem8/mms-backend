<?php

namespace Modules\Community\Console;

use Illuminate\Console\Command;
use Modules\Community\Services\SermonService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CleanExpiredSermonsCommand extends Command
{
    protected $signature = 'sermons:purge-expired';
    protected $description = 'Automatically delete pending sermons once their delivery date is reached or passed.';

    public function handle(SermonService $sermonService)
    {
        $deletedCount = $sermonService->purgeExpiredPendingSermons();

        $this->info("System check completed. {$deletedCount} unapproved expired sermons have been deleted.");
    }
}
