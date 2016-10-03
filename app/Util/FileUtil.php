<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 13.09.16
 * Time: 18:31
 */

namespace App\Util;

class FileUtil {

    private static $_instance = null;
    protected $dl_path = './downloads/';

    private function __construct() {

    }


    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new FileUtil();
        }
        return self::$_instance;
    }


    /**
     * Get the base name from URL or full path and clean it up.
     * If the filename is in cyrillic, then transliterate it to latin
     *
     * @param $url
     * @return mixed
     */

    public function get_file_name($url) {
        $t = new Transliterator();
        $filename = basename(urldecode($url));
        $filename = strtolower($t->from_cyr($filename));
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace('.', '_', $filename);

        $filename =  pathinfo($filename, PATHINFO_FILENAME);
        return $filename;
    }


    /**
     * Read the file contents from a given path
     *
     * @param $filename
     * @return mixed
     */


    public function read_file($filename, $extension = '')  {

        if (!file_exists($this->dl_path)) {
            dd('Path does not exist: ' . $this->dl_path);
        }

        if ('' != $extension) {
            $filename .= '.' . $extension;
        }

        $file = strval(str_replace("\0", "", $this->dl_path . $filename));

        if (!file_exists($file)) {
            dd($file . ' does not exist');
        }

        $raw_contents = file_get_contents($file);
        return $raw_contents;
    }


    /**
     * Download the file as attachment
     *
     * @param $file
     */

    public function download($file) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}