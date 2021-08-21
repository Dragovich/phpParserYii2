<?php

namespace app\commands;

use Symfony\Component\Console\Command\Command;

use Yii;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;

/**
 * ContactForm is the model behind the contact form.
 */
class ParseSchoolDescription extends Command {
    public static $descriptionPage = 'https://www.princetonreview.com/college/';

    /**
     * @param $infoAboutSchool array ['identity'] identifier of page from cite for this highschool
     *                         ['name'] name hightschool
     *                         ['city'] city where this hightschool work
     *                         ['state'] state where this highschool work
     *                         ['img_src'] url with preview page highschool
     * @return bool
     * @throws \yii\db\Exception
     */
    public function parseAllDescriptions($infoAboutSchool) {
        $browser = new HttpBrowser(HttpClient::create());

        foreach ($infoAboutSchool as $key => $school) {
            $crawler = $browser->request('GET', self::$descriptionPage . $school['identity']);
            $name = $crawler->filter('.school-headline > span')->text();
            $contact = $crawler->filter('.school-contacts > div')->first()->filter('div')->filter('.col-sm-9')->filter('.row');
            $siteTag = $crawler->filter('.school-headline-address > div > a');

            try {
                $site = $siteTag->attr('href');
            } catch (\Exception $e) {
                $site = '';
            }

            $address = '';
            $telephone = '';

            $contactData = $contact->each(function (Crawler $node) {
                $contactData = trim($node->filter('.col-xs-6')->last()->text());
                $blockData = trim($node->filter('.col-xs-6')->first()->text());
                if ($blockData === 'Address') {
                    return ['address' => $contactData];
                }
                if ($blockData === 'Phone') {
                    return ['telephone' => $contactData];
                }
            });

            foreach ($contactData as $key => $someInterestingData) {
                if (!is_null($someInterestingData)) {
                    foreach ($someInterestingData as $key2 => $value) {
                        if ($key2 == 'address') {
                            $address = $value;
                        }
                        if ($key2 = 'telephone') {
                            $telephone = $value;
                        }
                    }
                }
            }

            $description = [
                'identity' => $school['identity'],
                'name' => $name,
                'address' => $address,
                'telephone' => $telephone,
                'site' => $site,
            ];

            self::insertDBRows($description);
        }

        return true;
    }

    /**
     * @param $info
     * @return bool
     * @throws \yii\db\Exception
     */
    public function insertDBRows($info) {
//        foreach ($info as $key => $highSchool) {
        Yii::$app->db->createCommand()->insert('parser_schools_description',
            [
                'identity' => $info['identity'],
                'name' => $info['name'],
                'address' => $info['address'],
                'telephone' => $info['telephone'],
                'site' => $info['site']
            ])->execute();
//        }

        return true;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function removeDBRows() {
        Yii::$app->db->createCommand('DELETE FROM parser_schools_description')->execute();

        return true;
    }
}