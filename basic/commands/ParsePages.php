<?php

namespace app\commands;

use Yii;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;
use Exception;
use yii\console\Controller;

/**
 * ContactForm is the model behind the contact form.
 */
class ParsePages extends Controller {
    private static $parseUrl = 'https://www.princetonreview.com/college-search?ceid=cp-1022984&page=';

    private function getNumberOfPages() {
//        $numberOfPages = 1;

        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', self::$parseUrl . '1');

        $numberOfPages = $crawler->filter('#filtersForm')->children()->last()->filter('div')->last()->filter('div')->each(function (Crawler $node) {
            $paginator = explode(' ', $node->text());
            return $paginator[3];
        });

        if (is_array($numberOfPages)) {
            $numberOfPages = $numberOfPages[0];
        }

        return $numberOfPages;
    }

    private function parseAllPages() {
        $numberOfPages = self::getNumberOfPages();

        $highSchool = [];

        for ($i = 1; $i <= $numberOfPages; $i++) {
            $pageRequest = self::$parseUrl . $i;
            $browser = new HttpBrowser(HttpClient::create());
            $crawler = $browser->request('GET', $pageRequest);
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

        return $highSchool;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function updateDBRows() {
        $data = self::parseAllPages();

        foreach ($data as $key => $highSchool) {
            Yii::$app->db->createCommand()->insert('parser_schools',
                [
                    'identity' => $highSchool['identity'],
                    'name' => $highSchool['name'],
                    'city' => $highSchool['city'],
                    'state' => $highSchool['state'],
                    'img_src' => $highSchool['img_src']
                ])->execute();
        }

        return true;
    }
}
