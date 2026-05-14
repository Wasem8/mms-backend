<?php

namespace Modules\Donation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Donation\Services\CampaignService;

class ExpireEndedCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature   = 'campaigns:expire';
    protected $description = 'Mark campaigns whose end_date has passed as completed';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(CampaignService $service): int
    {
        $count = $service->expireEndedCampaigns();

        $this->info("Expired {$count} campaign(s).");

        return self::SUCCESS;
    }
    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
