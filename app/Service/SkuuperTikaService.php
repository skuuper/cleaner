<?php

namespace App\Service;

/**
 * Class SkuuperTikaService
 *
 * Class to provide API integration with Skuuper Tika
 *
 * @package App\Service
 */


class SkuuperTikaService {

    public function __construct()
    {
        //TODO: Refator to use GuzzleHttp client
    }


    public function get_contents_stream($character_stream) {
        $uri = 'https://skuuper.com/tika-server/tika';
        $response = \Httpful\Request::put($uri)
            ->addHeader('Accept', 'text/plain')
            ->authenticateWith('tika-client', 'Tika!83Jprv5@0')
            ->body($character_stream)
            ->send();
        return $response->body;
    }


    public function get_contents($file) {
        $file_contents = file_get_contents($file);
        return $this->get_contents_stream($file_contents);
    }


    public function get_word_count($file) {

        $body = $this->get_contents($file);

        $word_count = (int)str_word_count($body);
        return $word_count;
    }

}