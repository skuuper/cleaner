<?php

namespace App\Model;

use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Writer;

class TmxEntry implements XmlSerializable {

    public $source_language;
    public $source_text;
    public $destination_language;
    public $destination_text;

    public function xmlSerialize(Writer $writer)
    {
        //TODO: Create tu/tuv nodes
        $data = [];
        $writer->write($data);
    }
}