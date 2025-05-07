<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Clearlog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:clearlog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear log files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        exec('rm ' . storage_path('logs/*.log'));

        $this->comment('Logs have been cleared!');
    }
}
