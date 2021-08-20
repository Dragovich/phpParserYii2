<?php

/* @var $this yii\web\View */

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use yii\helpers\Html;
use Symfony\Component\DomCrawler\Crawler;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;

$page = 'https://www.princetonreview.com/college-search?ceid=cp-1022984&page=';
$highSchool = [];

$browser = new HttpBrowser(HttpClient::create());

for ($i = 1; $i <= 2; $i++) {
    $pageRequest = $page . $i;
    $crawler = $browser->request('GET', $pageRequest);
    $_COOKIE['page'] = $i;
    $highSchoolFromPage = $crawler->filter('.row .vertical-padding')->each(function (Crawler $node) {
        try {
            $img_src = $node->filter('img');

            $name = $node->filter('.margin-top-none')->text('');
            $location = $node->filter('.location')->text('');

            if ($location !== '') {
                $location = explode(', ', $location);
                $city = $location[0];
                $state = $location[1];
            } else {
                $city = '';
                $state = '';
            }

            $link = $node->filter('.margin-top-none > a')->attr('href');
            $identity = str_replace('/college/', '', $link);
            $identity = substr($identity, 0, strpos($identity, "?"));

            try {
                $img_src = $img_src->attr('src');
            } catch (Exception $e) {
                $img_src = '';
            }

            return [
                'identity' => $identity,
                'name' => $name,
                'city' => $city,
                'state' => $state,
                'img_src' => $img_src,
            ];
        } catch (Exception $e) {
            return [];
        }
    });
    $highSchool = array_merge($highSchool, $highSchoolFromPage);
}
echo '<pre>';
var_dump($highSchool);
var_dump(count($highSchool));
echo '</pre>';


?>

