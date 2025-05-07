<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database';
    protected $process_common;
    protected $process_transport;
    protected $process_order;
    protected $process_service;
    protected $process_delivery;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $today = date('d-m-Y');
        $day = date('d-m-Y', strtotime(date("d-m-Y") . " -6 day"));

        $files = array_filter(glob(storage_path('backups/').'backup-*'), 'is_dir');
        $database_folders = array_slice($files,0,-4);

        if(!empty($database_folders))
        {
            foreach ($database_folders as $database_folder) {
                $file = new Filesystem;
                $file->cleanDirectory($database_folder);
                rmdir($database_folder);
            }
           
        }

        if(!is_dir(storage_path('backups/backup-'.$today)))           
            mkdir(storage_path('backups/backup-'.$today),0777, true); 
        
        $this->process_common = new Process(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.common.username'),
            config('database.connections.common.password'),
            config('database.connections.common.database'),
            storage_path('backups/backup-'.$today.'/common.sql')
        ));
        $this->process_transport = new Process(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.transport.username'),
            config('database.connections.transport.password'),
            config('database.connections.transport.database'),
            storage_path('backups/backup-'.$today.'/transport.sql')
        ));
        $this->process_order = new Process(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.order.username'),
            config('database.connections.order.password'),
            config('database.connections.order.database'),
            storage_path('backups/backup-'.$today.'/order.sql')
        ));
        $this->process_service = new Process(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.service.username'),
            config('database.connections.service.password'),
            config('database.connections.service.database'),
            storage_path('backups/backup-'.$today.'/service.sql')
        ));
        $this->process_delivery = new Process(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.delivery.username'),
            config('database.connections.delivery.password'),
            config('database.connections.delivery.database'),
            storage_path('backups/backup-'.$today.'/delivery.sql')
        ));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->process_common->mustRun();
            $this->process_transport->mustRun();
            $this->process_order->mustRun();
            $this->process_service->mustRun();
            $this->process_delivery->mustRun();
           
        } catch (ProcessFailedException $exception) {
           Log::error('Backup Failed');
        }
    }
}
