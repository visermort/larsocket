<?php

namespace App\Console\Commands;

use App\Servers\WorkermanServer;
use Illuminate\Console\Command;

class Workerman extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ws:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Web Socket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $workerMan = new WorkermanServer();
        $workerMan->run();
    }
}
