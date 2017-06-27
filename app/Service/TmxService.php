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
      $this->aligner_path = "thirdparty/aligner/";
      $this->aligner_bin = "align";
      if (PHP_OS == 'Darwin')
        $this->aligner_bin .= "_mac";
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

    public function spread(&$src, &$dst, $source, $destination) {
      $i = 0;
      $so = array();
      $do = array();
      if (sizeof($src) != sizeof($dst)) die("Different size of aligned array!");
      while ($i < sizeof($src)) {
        if ($i > 0 && $src[$i] == $src[$i - 1]) 
          $do[sizeof($do) - 1] .= ' '.$destination[$dst[$i]];
        elseif ($i > 0 && $dst[$i] == $dst[$i - 1])
          $so[sizeof($so) - 1] .= ' '.$source[$src[$i]];
        elseif ($dst[$i] == sizeof($destination))
          $so[sizeof($so) - 1] .= ' '.$source[$src[$i]];
        elseif ($src[$i] == sizeof($source))
          $do[sizeof($do) - 1] .= ' '.$destination[$dst[$i]];
        else {
          foreach (explode(',', $src[$i]) as $v)
            array_push($so, $source[$v]);
          foreach (explode(',', $dst[$i]) as $v)
            array_push($do, $destination[trim($v)]);
        }
        $i++;
      }
      if (strlen(end($so)) < 1) array_pop($so);
      if (strlen(end($do)) < 1) array_pop($do);
      $src = $so;
      $dst = $do;
    }

    public function align($source_language, $destination_language, &$source, &$destination, $aligner='aligner', $dic='')
    {
        $tempfile=tempnam(sys_get_temp_dir(),'');
        //print("Using temp dir: ".$tempfile."<br>\n");
        if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
        mkdir($tempfile);
        $st = $tempfile.'/'.$source_language.'.txt';
        $dt = $tempfile.'/'.$destination_language.'.txt';
        //foreach ($source as $k => &$line) if (strlen(trim($line)) < 1) unset($source[$k]);
        //foreach ($destination as $k => &$line) if (strlen(trim($line)) < 1) unset($destination[$k]);
        $this->write_file($st, $source);
        $this->write_file($dt, $destination);
        if (!is_dir($tempfile)) { die('Error creating temporary dir!'); }
        $out = array();
        $ret = -1;
        $new_src = array();
        $new_dst = array();
        if ($aligner == 'aligner') {	// HUNALIGN
            $this->aligner_bin = str_replace('align', 'hunalign', $this->aligner_bin);
            $dicfile = $this->aligner_path."data/";
            if ($dic === '') $dic .= $source_language.'-'.$destination_language.".dic"; 
            else
              $dicfile .= $dic;
            //print("Calling ".$this->aligner_path.$this->aligner_bin." ".$dicfile." ".$st." ".$dt);
            exec($this->aligner_path.$this->aligner_bin." ".$dicfile." ".$st." ".$dt, $out, $ret);
            
            $fh = fopen($tempfile.'/align.dat', 'w');
            foreach($out as $line)
              fprintf($fh, $line."\n");
            fclose($fh);
            
            if ($ret != 0) {
              print("Error calling hunalign!<br />\n");
              return;
            }
            $sp = -1;
            $dp = -1;
            array_push($source, "");
            array_push($destination, "");
            foreach ($out as $line) {
              $align = explode("\t", $line);
              //if ($sp > $align[0] || $dp > $align[1]) $this->write_file('err_'.time().".txt", array_merge($source, $destination));
              for ($i = $sp + 1; $i < $align[0]; $i++) {
                array_push($new_src, $i);
                array_push($new_dst, sizeof($destination));
              }
              for ($i = $dp + 1; $i < $align[1]; $i++) {
                array_push($new_dst, $i);
                array_push($new_src, sizeof($source));
              }
              //~ TODO: detect duplicate?
              array_push($new_src, $align[0]);
              array_push($new_dst, $align[1]);
              $dp = $align[1];
              $sp = $align[0];
            }
            for ($i = $sp + 1; $i < sizeof($source) - 1; $i++) {
                array_push($new_src, $i);
                array_push($new_dst, sizeof($destination));
            }
            for ($i = $dp + 1; $i < sizeof($destination) - 1; $i++) {
                array_push($new_dst, $i);
                array_push($new_src, sizeof($source));
            }
            if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
            $this->spread($new_src, $new_dst, $source, $destination);
        } else {	// CHAMPOLLION
            $this->aligner_path = str_replace('aligner', $aligner, $this->aligner_path);
            $this->aligner_bin = str_replace('_mac', '', $this->aligner_bin);
            $dicfile = $this->aligner_path."lib/";
            if ($dic === '') $dic .= $source_language[0].''.$destination_language[0]."dict.utf8.txt";  else $dicfile .= $dic;
            //print("Calling ".$this->aligner_path.$this->aligner_bin." ".$dicfile." ".$st." ".$dt);
            $op = $tempfile.'/align.txt';
            exec('cd '.$this->aligner_path.' && ./'.$this->aligner_bin." ".$st." ".$dt." ".$dicfile." ".$op, $out, $ret);
            if ($ret != 0) {              //die("Error calling aligner!<br />\n");  
              return;
            }
            $out = file($op);
            array_push($source, "");
            array_push($destination, "");
            foreach ($out as $line) {
              $align = explode(" <=> ", $line);
              $ss = '';
              $ds = '';
              foreach (explode(',', $align[0]) as $s)
                $ss .= ' '.$source[intval($s)];
              foreach (explode(',', $align[1]) as $s)
                $ds .= ' '.$destination[intval($s)];
              array_push($new_src, trim($ss));
              array_push($new_dst, trim($ds));
            }
        }
        print_r($source);
        $source = $new_src;
        $destination = $new_dst;
    }

    public function create($source_language, $destination_language, $source, $destination, $aligner='aligner', $dic='', $bAlign=true)
    {
        if ($bAlign)
          $this->align($source_language, $destination_language, $source, $destination, $aligner, $dic);
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