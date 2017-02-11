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
      $this->hunalign_path = "thirdparty/aligner/";
      $this->hunalign_bin = "hunalign";
      if (PHP_OS == 'Darwin')
        $this->hunalign_bin .= "_mac";
    }


    public function parse($raw_xml) {
        $xml = new \SimpleXMLElement($raw_xml);
        $translation_units = $xml->body->tu;
        return $translation_units;
    }


    public function parse_split($raw_xml) {
        return $this->parse($raw_xml);
    }

    function rrmdir($dir) { 
       if (is_dir($dir)) { 
         $objects = scandir($dir); 
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (is_dir($dir."/".$object))
               rrmdir($dir."/".$object);
             else
               unlink($dir."/".$object); 
          }
        }
        rmdir($dir); 
      } elseif (is_file($dir))
        unlink($dir);
    }

    function write_file($fp, $contents) {
      $myfile = fopen($fp, "w");
      fwrite($myfile, implode("\n", $contents));
      fclose($myfile);
    }

    public function align($source_language, $destination_language, &$source, &$destination)
    {
        $tempfile=tempnam(sys_get_temp_dir(),'');
        //print("Using temp dir: ".$tempfile."<br>\n");
        if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
        mkdir($tempfile);
        $st = $tempfile.'/'.$source_language.'.txt';
        $dt = $tempfile.'/'.$destination_language.'.txt';
        $this->write_file($st, $source);
        $this->write_file($dt, $destination);
        if (!is_dir($tempfile)) { die('Error creating temporary dir!'); }
        $out = array();
        $ret = -1;
        $dicfile = $this->hunalign_path."data/".$source_language.'-'.$destination_language.".dic";
        //print("Calling ".$this->hunalign_path.$this->hunalign_bin." ".$dicfile." ".$st." ".$dt);
        exec($this->hunalign_path.$this->hunalign_bin." ".$dicfile." ".$st." ".$dt, $out, $ret);
        if ($ret != 0) die("Error calling hunalign!<br />\n");
        //print_r($out);
        $sp = 0;
        $dp = 0;
        $new_src = array();
        $new_dst = array();
        foreach ($out as $line) {
          $align = explode("\t", $line);
          //print($align[0]." => ".$align[1]."<br />\n");
          for ($i = $sp + 1; $i < $align[0]; $i++) {
            array_push($new_src, $source[$i]);
            array_push($new_dst, "");
          }
          for ($i = $dp + 1; $i < $align[1]; $i++) {
            array_push($new_dst, $destination[$i]);
            array_push($new_src, "");
          }
          //~ TODO: detect duplicate?
          //~ TODO: log issues with reversed items
          array_push($new_src, $source[$align[0]]);
          array_push($new_dst, $destination[$align[1]]);
          $dp = $align[1];
          $sp = $align[0];
        }
        for ($i = $sp + 1; $i < sizeof($source); $i++) {
            array_push($new_src, $source[$i]);
            array_push($new_dst, "");
        }
        for ($i = $dp + 1; $i < sizeof($destination); $i++) {
            array_push($new_dst, $destination[$i]);
            array_push($new_src, "");
        }
        if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
        $source = $new_src;
        $destination = $new_dst;
    }

    public function create($source_language, $destination_language, $source, $destination)
    {
        $this->align($source_language, $destination_language, $source, $destination);
        $entries = [];
        foreach ($source as $index => $item) {
            $entry = new TmxEntry();
            $entry->source_language = $source_language;
            $entry->destination_language = $destination_language;
            $entry->source_text = $item;
            $entry->destination_text = isset($destination[$index]) ? $destination[$index] : false;

            array_push($entries, $entry);
        }
        for ($i = sizeof($source); $i < sizeof($destination); $i++)
        {
            $entry = new TmxEntry();
            $entry->source_language = $source_language;
            $entry->destination_language = $destination_language;
            $entry->source_text = false;
            $entry->destination_text = $destination[$i];
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



    private function _createTuvNode($dom, $language, $text = "") {
        $tuv = $dom->createElement('tuv');
        $tuv->setAttribute('xml:lang', $language);

        $text = (string)$text;

        $seg = $dom->createElement('seg');
        $seg->nodeValue = $text;

        $tuv->appendChild($seg);
        return $tuv;
    }


    /**
     * Extract the languages from raw TMX file
     *
     * @param $raw
     * @return array
     */

    public function get_languages($raw)
    {
        $xml = new \SimpleXMLElement($raw);
        $units = $xml->body->tu[0];

        $unit = $units->tuv[0]['lang'];

        $langs = $xml->xpath('body/tu/tuv[@xml:lang]/@xml:lang');
        $langs = array_map("strval", $langs);

        $ret = [$langs[0], $langs[1]];
        return $ret;
    }

}