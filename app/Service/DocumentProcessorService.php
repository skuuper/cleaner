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
        $this->ldc_path = "thirdparty/ldc-cn-seg/";
        $this->ldc_bin = "mansegment-utf8.pl";
        $this->lf_path = "thirdparty/sentence_splitter/";
        $this->lf_bin = 'split-sentences.perl';
    }

    function write_file($fp, $contents) {
      $myfile = fopen($fp, "w");
      fwrite($myfile, $contents);
      fclose($myfile);
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

    function run_pl($text, $cmd) {
        $tempfile=tempnam(sys_get_temp_dir(),'');
        if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
        mkdir($tempfile);
        $st = $tempfile.'/segmentation.txt';
        //foreach ($source as $k => &$line) if (strlen(trim($line)) < 1) unset($source[$k]);
        //foreach ($destination as $k => &$line) if (strlen(trim($line)) < 1) unset($destination[$k]);
        $this->write_file($st, $text);
        if (!is_dir($tempfile)) { die('Error creating temporary dir!'); }
        $out = array();
        $ret = -1;
        //print("Calling ".$this->hunalign_path.$this->hunalign_bin." ".$dicfile." ".$st." ".$dt);
        exec("perl ".$cmd." < ".$st, $out, $ret);
        if ($ret != 0) {
          //die("Error calling hunalign!<br />\n");
          print_r($out);
          return;
        }
        if (file_exists($tempfile)) { $this->rrmdir($tempfile); }
        //print_r($out);
        return implode("\n", $out);
    }

    public function tokenize_ldc($text) {
        $dicfile = $this->ldc_path."Mandarin.fre.utf8";
        $cmd = $this->ldc_path.$this->ldc_bin." ".$dicfile;
        return $this->run_pl($text, $cmd);
    }

    public function process_LF($text, $lang='en') {
        $cmd = $this->lf_path.$this->lf_bin.' -l '.$lang.' ';
        //print($cmd);
        return $this->run_pl($text, $cmd);
    }


    public function process_array($input) {
        $input = str_replace("\r", "\n", str_replace("\r\n", "\n", $input));
        $input = $this->remove_between_brackets($input);
        $input = $this->remove_double_newlines($input);
        $input = $this->sections_to_newlines($input);
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


    public function process($input, $bUseLF=false, $lang='en') {
        if ($bUseLF) {
          return $this->process_LF($input, $lang);
        }
        //print($input);
        $paragraphs = $this->process_array($input);
        $para_new = array();
        foreach ($paragraphs as &$para) {
            //print($para . "<br />");
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
        $ip = preg_replace('/(?<=[\.\?])(\s+)(?=[0-9])/', "\n\n", $input);
        //$input = preg_replace('(?<=[.\)?])(\s+)(?=[0-9]+)', '$1\\n$2', $input);
        return str_replace("^", "\n", $ip);
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