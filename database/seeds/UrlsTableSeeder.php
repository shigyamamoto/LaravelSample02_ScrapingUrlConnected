<?php
use App\CollectedUrl;
use Illuminate\Database\Seeder;

class UrlsTableSeeder extends Seeder
{

    public function run()
    {
        $url = new CollectedUrl();
        $url->url = 'https://github.com/shigyamamoto/LaravelSample02_ScrapingUrlConnected';
        $url->exist = true;
        $url->checked_at = null;
        $url->save();
    }
}
