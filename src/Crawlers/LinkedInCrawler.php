<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler;

class LinkedInCrawler
{
    private $data;

    const KEYS = [
        'name',
        'followerCount',
        'staffCount',
        'specialities',
        'confirmedLocations',
        'description',
        'staffCountRange',
        'companyType',
        'localizedName',
        'foundedOn'
    ];

    public function __construct(Crawler $data)
    {
        $this->data = $data;
    }

    public function get()
    {
        $codes = $this->data->filter('code')->each(function (Crawler $node, $i) {
            if (strpos($node->text(), '"staffingCompany"')) {
                return $node->text();
            }
            return null;
        });

        $codes = $this->clearArray($codes);

        return $this->prepareData($codes);
    }

    private function prepareData($data)
    {

        $included = json_decode($data[0], true)['included'];

        $companyInfo = [];
        $companyId = null;
        foreach ($included as $array) {
            if (is_array($array)) {
//                dump($array);
                foreach ($array as $key => $item) {

                    if ($key == 'localizedName') $companyInfo[$key] = $item;

                    if ($key == 'staffingCompany') {
                        foreach ($array as $key2 => $item2) {
//                            dd($array);
                            if (in_array($key2, self::KEYS)) {
                                $companyInfo[$key2] = $item2;
                            }
                            if ($key2 == 'url') $companyId = preg_replace("/[^0-9]/", '', $array[$key2]);

                        }
                    }

                }

            }
        }


        foreach ($included as $array) {
            if (is_array($array)) {

                if (!empty($companyId)) {
                    foreach ($array as $key => $item) {

                        if ($key == 'followerCount' && $array['entityUrn'] == 'urn:li:fs_followingInfo:urn:li:company:' . $companyId) {
                            $companyInfo[$key] = $item;
                            break;
                        }

                    }
                }

            }
        }


        return $companyInfo;
    }

    private function clearArray(array $array)
    {
        return array_values(array_diff($array, array('', NULL, false)));
    }
}