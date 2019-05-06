<?php
namespace App\Console\Commands;

use App\CollectedUrl;
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
        $num = config('custom.scraping.exist_checker_number_at_once');
        $urls = CollectedUrl::where('checked_at', null)->where('exist', false)
            ->where('active', true)
            ->take($num)
            ->get();

        $mh = curl_multi_init();
        $ch_list = array();
        foreach ($urls as $mykey => $url) {
            $ch_list[$mykey] = curl_init($url->url);
            curl_setopt($ch_list[$mykey], CURLOPT_HEADER, TRUE);
            curl_setopt($ch_list[$mykey], CURLOPT_NOBODY, TRUE);
            curl_setopt($ch_list[$mykey], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch_list[$mykey], CURLOPT_TIMEOUT, 2);
            curl_multi_add_handle($mh, $ch_list[$mykey]);
        }
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        foreach ($urls as $mykey => $url) {
            $httpCode = curl_getinfo($ch_list[$mykey], CURLINFO_HTTP_CODE);
            if ($httpCode != 404) {
                $url->exist = true;
                $url->save();
            } else {
                $url->active = false;
                $url->save();
            }
            curl_multi_remove_handle($mh, $ch_list[$mykey]);
            curl_close($ch_list[$mykey]);
        }
        curl_multi_close($mh);
    }
}
