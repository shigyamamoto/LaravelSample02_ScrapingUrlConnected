<?php
namespace App\Console\Commands;

use App\Services\ScrapingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Scraping extends Command
{

    protected $signature = 'scraping:some';

    protected $description = 'カスタムコマンド : スクレイピング';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 今回取得対象のURLを取得する
        $ss = new ScrapingService();
        $num = config('custom.scraping.scraping_number_at_once');
        $targets = $ss->get_target_urls($num);
        foreach ($targets as $target) {
            $urls = $ss->get_links($target->url);
            foreach ($urls as $url) {
                $ss->save_new_url($url);
            }

            // 更新後のターゲットは、checked_at を更新してあげる
            $target->checked_at = Carbon::now();
            $target->save();
        }
    }
}
