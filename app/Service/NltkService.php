<?php

namespace App\Service;

/**
 * Class NltkService
 * @package App\Service
 *
 * NLTK service wrapper for chunking. Base for TMX generation from user texts
 *
 * @see http://git.skuuper.com/skuuper/modules/blob/master/bindings/chunking.php
 */

class NltkService
{
    private $debug;
    private $api_file;

    public function __construct($debug = 0)
    {
        //TODO: Move API file location to external configuration
        $this->debug = $debug;
        $this->api_file = "/home/martin/work/modules/run_api.py";
    }


    private function call_py_api($method, $text)
    {
        $json = json_encode(array($text));
        $result = shell_exec('python ' . $this->api_file . '  '. $method . ' ' . escapeshellarg($json) . ' 2>&1');
        dd($result);
        $resultData = join("\n", json_decode($result, true)['result']);
        return $resultData;
    }

    public function chunk_text($text) {
        return $this->call_py_api('chunk', $text);
    }

    public function ner_text($text) {
        return $this->call_py_api('ner', $text);
    }

    public function detect_lang($text) {
        return $this->call_py_api('detectlang', $text);
    }

    public function prepare($text) {
        return $this->call_py_api('prepare', $text);
    }

}