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

class TmxService {

    public function __construct() {
    }


    public function parse($raw_xml) {
        $xml = new \SimpleXMLElement($raw_xml);
        $translation_units = $xml->body->tu;
        return $translation_units;
    }


    public function parse_split($raw_xml) {
        return $this->parse($raw_xml);
    }



    public function create($source_language, $destination_language, $source, $destination)
    {
        $entries = [];
        foreach ($source as $index => $item) {
            $entry = new TmxEntry();
            $entry->source_language = $source_language;
            $entry->destination_language = $destination_language;
            $entry->source_text = $item;
            $entry->destination_text = isset($destination[$index]) ? $destination[$index] : false;

            array_push($entries, $entry);
        }

        $dom = new \DOMDocument();
        $rootEl = $dom->createElement('tmx');
        $root = $dom->appendChild($rootEl);

        $head = $dom->createElement('head');
        $head->setAttribute('creationtool', 'Skuuper TMX creator');

        $body = $dom->createElement('body');
        foreach ($entries as $e) {
            $tu = $dom->createElement('tu');

            $tu->appendChild($this->_createTuvNode($dom, $e->source_language, $e->source_text));
            $tu->appendChild($this->_createTuvNode($dom, $e->destination_language, $e->destination_text));

            $body->appendChild($tu);
        }

        $root->appendChild($head);
        $root->appendChild($body);

        return $dom->saveXML();
    }



    private function _createTuvNode($dom, $language, $text) {
        $tuv = $dom->createElement('tuv');
        $tuv->setAttribute('xml:lang', $language);

        $seg = $dom->createElement('seg');
        $seg->nodeValue = $text;

        $tuv->appendChild($seg);
        return $tuv;
    }

}