<?php
namespace App\Services;

use App\CollectedUrl;
use Illuminate\Support\Facades\Log;
use Weidner\Goutte\GoutteFacade;
use Exception;

class ScrapingService
{

    /**
     * 指定のURLページにアクセスし、ページ内のリンクを新規に取得する
     *
     * @param string $url
     *            URL文字列
     */
    public function get_links($url)
    {
        $ret = array();
        try {
            $goutte = GoutteFacade::request('GET', $url);
            $goutte->filter('a')->each(function ($a) use ($url, &$ret) {
                $href = $a->attr("href");
                if ($this->checker_href($href, $url)) {
                    array_push($ret, $href);
                }
            });
        } catch (Exception $e) {
            Log::error($e);
        }
        return $ret;
    }

    /**
     * 指定のURLをDBに保存する
     *
     * @param string $url
     *            URL文字列
     */
    public function save_new_url($url)
    {
        // 登録済みの場合には、処理せず
        $count = CollectedUrl::where('url', $url)->count();
        if ($count > 0) {
            return;
        }

        // データが取得できないものは、404エラーと判断し処理しない
        // $contents = @file_get_contents($url, NULL, NULL, 1, 1);
        // if (! $contents) {
        // return;
        // }

        // 登録
        $cu = new CollectedUrl();
        $cu->url = $url;
        $cu->exist = true;
        $cu->checked_at = null;
        $cu->save();
    }

    /**
     * 今回スクレイピング対象とするURLを取得する
     *
     * @param int $number
     *            取得する件数上限
     */
    public function get_target_urls($number)
    {
        // checked_at が nullのものが優先して取得される
        $urls_null = CollectedUrl::where('checked_at', null)->where('exist', true)
            ->take($number)
            ->get();
        $count = count($urls_null);
        if ($count >= $number) {
            return $urls_null;
        }

        // 上記が指定個数まで到達しなかった場合には、checked_at が 古い順に取得対象とする
        $urls_old = CollectedUrl::whereNotNull('checked_at')->where('exist', true)
            ->orderBy('checked_at', 'asc')
            ->take($number - $count)
            ->get();

        $ret = array();
        foreach ($urls_null as $url) {
            array_push($ret, $url);
        }
        foreach ($urls_old as $url) {
            array_push($ret, $url);
        }
        return $ret;
    }

    /**
     * 取得したAタグのhref要素が新規登録対象として適切かチェックする
     *
     * @param string $str
     *            Aタグのhref要素
     * @param string $url
     *            現在アクセスしているURL文字列
     */
    public function checker_href($str, $url)
    {
        // 要素がemptyの場合にはfalse
        if (empty($str)) {
            return false;
        }
        // 要素がmailtoの場合にはfalse
        if (preg_match('/^mailto/', $str)) {
            return false;
        }
        // 要素がページ内リンクの場合にはfalse(#から始まる文字列)
        if (preg_match('/^#/', $str)) {
            return false;
        }
        // 要素がgithub内リンクの場合にはfalse(http,httpsから始まらない文字列)
        if (! preg_match('/^http(s)*:\/\//', $str)) {
            return false;
        }
        // 要素がgithub内リンクの場合にはfalse(hostが自身と一致する文字列)
        $url_parsed = parse_url($url);
        $str_parsed = parse_url($str);
        if (! isset($url_parsed['host']) || ! isset($str_parsed['host'])) {
            return false;
        }
        if ($url_parsed['host'] == $str_parsed['host']) {
            return false;
        }

        return true;
    }
}
