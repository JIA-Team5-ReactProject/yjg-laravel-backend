<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $stub = File::get(base_path('stubs/service.stub'));
        $filePath = app_path('Services/' . $name . '.php');
        File::put($filePath, str_replace('{{ class }}', $name, $stub));
        $this->info('Service created successfully.');
    }
}
