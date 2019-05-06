<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExistChecker extends Command
{
    protected $signature = 'scraping:404check';
    
    protected $description = 'カスタムコマンド : 404チェック';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 
    }
}
