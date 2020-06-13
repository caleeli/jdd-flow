<?php

namespace JDD\Workflow\Console\Commands;

use Illuminate\Console\Command;
use JDD\Workflow\Providers\PackageServiceProvider;

class UpdatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     *
     * @var string
     */
    protected $signature = 'workflow:jdd-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the installed jdd workflow package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $this->info('Build asset: ' . $this->signature);
        $dir = getcwd();
        chdir(__DIR__ . '/../../../');
        file_exists('node_modules') ?: exec('npm install');
        exec('npm run build');
        chdir($dir);
        $this->call('vendor:publish', ['--provider' => PackageServiceProvider::class, '--force' => true, '--tag' => 'assets']);
    }
}
