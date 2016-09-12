<?php

namespace App\Model;

use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Writer;

class TmxEntry implements XmlSerializable {

    public $language0;
    public $text0;
    public $language1;
    public $text1;

    public function xmlSerialize(Writer $writer)
    {
        //TODO: Create tu/tuv nodes
        $data = [];
        $writer->write($data);
    }
}