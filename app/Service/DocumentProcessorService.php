<?php

namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Processor\IntrospectionProcessor;

class DocumentProcessorService {

    private $paragraph_separator;
    private $debug = 0;
    private $tmx;
    private $log;

    public function __construct($debug = 0)
    {
        $this->log = new Logger( __CLASS__ );
        $this->log->pushHandler(new BrowserConsoleHandler());
        $this->log->pushProcessor(new IntrospectionProcessor());

        $this->debug = $debug;
        $this->paragraph_separator = "\n";
        if ($this->debug > 0) {
            $this->paragraph_separator = "\n----\n";
        }

        $this->tmx = new TmxService();

    }


    public function process_array($input) {
        $input = str_replace("\r", "\n", str_replace("\r\n", "\n", $input));
        $input = $this->remove_between_brackets($input);
        $input = $this->sections_to_newlines($input);
        $input = $this->remove_double_newlines($input);
        $input = $this->remove_double_spaces($input);
        $input = $this->split_paragraph_titles($input);

        $paragraphs = array_map('trim', explode("\n\n", $input));
        if (count($paragraphs) <= 1) {
            $paragraphs = array_map('trim', explode("\n", $input));
        }
        //foreach ($paragraphs as $line) {
        //  print("DEBUG: ".str_replace("\n", "_____", $line));
        //}

        foreach ($paragraphs as &$paragraph) {
            $paragraph = $this->remove_newline_if_starts_with_character($paragraph);
            $paragraph = $this->cleanup_whitespace($paragraph);
        }

        $paragraphs = array_filter($paragraphs);
        return $paragraphs;
    }


    public function process($input) {
        $paragraphs = $this->process_array($input);
        $para_new = array();
        foreach ($paragraphs as &$para) {
            if (strpos($para, '。') !== false || strpos($para, '？') !== false || strpos($para, '！') !== false) {
              $para = str_replace("　", "", $para);
              // $para_new = array_merge($para_new, preg_split('/(?<=。)/', $para));
              $para_new = array_merge($para_new, preg_split('/(?<=[.?!。？！])[〞」》"』]?\s?(?=[A-ZА-Я-–〝\x{3400}-\x{4DB5}\x{4E00}-\x{9FCC}\x{FA0E}-\x{FA29}])/u', $para));
            } else
              $para_new = array_merge($para_new, preg_split('/(?<=[.?!])["“„»]?\s+(?=[A-ZА-Я-–])/u', $para));
        }
        $output = implode($this->paragraph_separator, $para_new);
        return $output;
    }


    private function remove_double_spaces($input) {
        //TODO: Check why double spaces are still there
        $k = 0;
        str_replace("\t", "", $input);
        while (strstr($input, '  ')) {
            $input = str_replace('  ', ' ', $input);
            $k++;
        }
        if ($k > 0) {
            $this->log->addDebug('Cleaned whitespaces in ' . $k . ' steps');
        }
        return $input;
    }


    private function remove_double_newlines($input) {
        $input = str_replace("\t", "\n\n", $input);
        $input = str_replace("\n \n", "\n\n", $input);
        $input = str_replace("\n\n", "\n", $input);
        return trim($input);
    }


    private function remove_between_brackets($input) {
        return preg_replace('/\[.*?\]/',"",$input);
    }


    private function sections_to_newlines($input) {
        return str_replace("^", "\n", $input);
    }

    private function split_paragraph_titles($input) {
        //TODO: Paragraph number and title should be on different lines
        # § ([0-9]*).
        $expression = "/\n§ ([0-9]*)./";
        $replacement = "\n$0\n";
        $input = preg_replace($expression, $replacement, $input);
        return $input;
    }



    private function remove_newline_if_starts_with_character($input) {

        # Expression to find newlines starting with number in parentheses: (\n\([0-9]\))
        # Expression to find newlines starting with number and parentheses: (\n[0-9]\))
        # Combination of those two expressions: (\n[0-9]\)|(\n\([0-9]\)))

        $expression = "/(\n[0-9]\)|(\n\([0-9]\)))/";

        $input = preg_replace($expression, "\n*$1", $input);
        $p = explode('*', $input);
        foreach ($p as &$item) {
            $item = str_replace("\n", " ", $item);
            $item = $this->remove_double_spaces($item);
            $item = $this->cleanup_whitespace($item);
        }
        $p = array_filter($p);
        $input = implode("\n", $p);

        return $input;
    }

    private function cleanup_whitespace($paragraph)
    {
        //TODO: Remove the line if it does not contain any alphanumeric characters
        $paragraph = trim($paragraph);
        if (mb_strlen($paragraph) <= 2) {
            return false;
        }
        return $paragraph;
    }



    public function build_tmx($request, $response) {
        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument();
        $writer->write('...');
        echo $writer->outputMemory();
    }

}