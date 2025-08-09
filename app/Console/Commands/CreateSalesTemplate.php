<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesTemplateExport;

class CreateSalesTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create template Excel for sales import';

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
     * @return int
     */
    public function handle()
    {
        $filename = 'template_sales_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        Excel::store(new SalesTemplateExport, $filename, 'public');
        
        $this->info("Template Excel berhasil dibuat: storage/app/public/{$filename}");
        
        return 0;
    }
}
