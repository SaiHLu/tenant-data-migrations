<?php

namespace App\Console\Commands;

use App\Imports\NewCxLogImport;
use App\Models\NewCxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CxLogMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cx-log-migration {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = DB::table("tenants")->get();

        $filePath = $this->option('file');

        if(!file_exists($filePath)) throw new \Exception("File doesn't exist");

        foreach ($tenants as $tenant) {
            Config::set('database.connections.pgsql', [
                ...Config::get('database.connections.pgsql'),
                'database' => $tenant->db_name
            ]);

            $connection = DB::reconnect('pgsql');

            Excel::import(new NewCxLogImport($connection), $filePath);
        }

        $this->info("Imported");
    }
}
