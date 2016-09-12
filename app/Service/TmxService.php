<?php

namespace App\Service;

/**
 * TMX consumer/producer service
 *
 * @see http://www.ttt.org/oscarstandards/tmx/tmx14-20020710.htm
 *
 * General structure of TMX document
 *  <!DOCTYPE tmx SYSTEM "tmx14.dtd">
 *  tmx version="1.4"
 *      header
 *      body
 *          tu
 *              tuv xml:lang
 *
 * Class TmxService
 * @package App\Service
 */

use App\Model\TmxEntry;
use Sabre\Xml\Service;

class TmxService {

    private $xml;

    public function __construct()
    {
        $this->xml = new Service();
    }

    public function process($data = []) {
        
        $this->xml->namespaceMap = [
            'http://example.org' => 'xml'
        ];

        foreach($data as $unit) {

            $entry = new TmxEntry();
            $entry->language0 = 'en';
            $entry->language1 = 'et';
            $entry->text0 = 'Tere';
            $entry->text1 = 'Hello';

            $this->xml->write('body', [
                'tu' => $entry
            ]);
        }


    }
}