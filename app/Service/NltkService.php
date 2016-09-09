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

    public function __construct($debug = 0)
    {
        $this->debug = $debug;
    }


    private function call_py_api($method, $text)
    {
        $json = json_encode(array($text));
        $result = shell_exec('python ../run_api.py ' . $method . ' ' . escapeshellarg($json) . ' 2>&1');
        $resultData = join("\n", json_decode($result, true)['result']);
        return $resultData;
    }

    public function chunk_text($text)
    {
        return call_py_api('chunk', $text);
    }

    public function ner_text($text)
    {
        return call_py_api('ner', $text);
    }

    public function detect_lang($text)
    {
        return call_py_api('detectlang', $text);
    }

    public function prepare($text)
    {
        return call_py_api('prepare', $text);
    }

}