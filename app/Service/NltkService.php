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

        $command = sprintf('python %s %s %s 2>&1', $this->api_file, $method, escapeshellarg($json));
        $result = shell_exec($command);

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