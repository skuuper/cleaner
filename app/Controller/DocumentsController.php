<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 26.08.16
 * Time: 14:59
 */

namespace App\Controller;

use App\Service\DocumentProcessorService;
use App\Service\NltkService;
use App\Service\SkuuperTikaService;
use App\Util\Transliterator;
use Slim\Http\UploadedFile;

class DocumentsController extends \App\Controller\BaseController {

    private $debug;
    private $tika;
    private $processor;
    private $dl_path = './downloads/';

    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        parent::__construct($container);

        $this->debug = $container->get('settings')['debug'];

        $this->tika = new SkuuperTikaService();
        $this->processor = new DocumentProcessorService($this->debug);
    }

    public function index($request, $response) {

        $data = [
            'file' => false
        ];
        return $this->view->render($response, 'documents/index.twig', $data);
    }


    public function process_file($request, $response) {
        $files = $request->getUploadedFiles();
        if (empty($files['upload_file'])) {
            throw new Exception('Uploaded file not present');
        }

        /** @var UploadedFile $file */
        $file = $files['upload_file'];
        $raw_contents = $file->getStream()->getContents();

        $filename = $this->get_file_name($file->getClientFilename());
        $contents = $this->tika->get_contents_stream($raw_contents);

        if ($this->debug > 0) {
            file_put_contents($this->dl_path . 'init_' . $filename . '.txt', $contents);
        }

        $contents = $this->processor->process($contents);

        file_put_contents($this->dl_path . $filename . '.txt', $contents);

        $data = [
            'file' => $filename
        ];

        return $this->view->render($response, 'documents/index.twig', $data);
    }


    public function process($request, $response) {
        $data = $request->getParsedBody();

        $url = $data['url_address'];
        $filename = $this->get_file_name($url);

        $raw_contents = file_get_contents($url);

        $contents = $this->tika->get_contents_stream($raw_contents);

        if ($this->debug > 0) {
            file_put_contents($this->dl_path . 'init_' . $filename . '.txt', $contents);
        }

        $contents = $this->processor->process($contents);

        file_put_contents($this->dl_path . $filename . '.txt', $contents);


        $data = [
            'file' => $filename,
            'url' => $url
        ];

        return $this->view->render($response, 'documents/index.twig', $data);
    }



    public function download($request, $response, $args) {
        $filename = $args['filename'];
        $file = $this->dl_path . $filename . '.txt';

        if (!file_exists($file)) {
            dd('Error: File does not exist - ' . $file);
            exit;
        }

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



    private function get_file_name($url) {
        $t = new Transliterator();
        $filename = basename(urldecode($url));
        $filename = strtolower($t->from_cyr($filename));
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace('.', '_', $filename);

        $filename =  pathinfo($filename, PATHINFO_FILENAME);
        return $filename;
    }



    public function test($request, $response) {
        $nltk = new NltkService(1);

        $this->dl_path = $_SERVER['DOCUMENT_ROOT'] . '/downloads/';
        $this->processor = new DocumentProcessorService(0);

        if (!file_exists($this->dl_path)) {
            die('Path does not exist: ' . $this->dl_path);
        }

        $filename = 'riigiteataja.txt ';
        $file = strval(str_replace("\0", "", $this->dl_path . 'init_' . $filename));

        if (file_exists($file)) {
            dd($file . ' does not exist');
            $raw_contents = file_get_contents($file);
            $contents = $this->processor->process($raw_contents);
        }



        $contents = "Peaminister Taavi Rõivase peaministrit ja ministrite asendamist puudutava korralduse järgi oleks välisministri asendaja haridus-ja teadusminister. Seega, kui Marina Kaljurand peaks võtma praeguses olukorras puhkuse või kui Rõivas läheks Kaljuranna avaldusega presidendi juurde, siis saaks Jürgen Ligist uue välisministri määramiseni välisministri kohuste täitja.
Teatavasti ütles välisminister Marina Kaljurand täna, et lahkub välisministri ametist ning kandideerib valimiskogus. Kaljurand rõhutas pressikonverentsil, et täna on tema viimane tööpäev.";


        $out = $nltk->detect_lang($contents);
        dd($out);
    }



    public function test_local($request, $response) {

        $this->dl_path = $_SERVER['DOCUMENT_ROOT'] . '/downloads/';
        $this->processor = new DocumentProcessorService(0);

        if (!file_exists($this->dl_path)) {
            dd('Path does not exist: ' . $this->dl_path);
        }

        $filename = 'riigiteataja.txt ';
        $file = strval(str_replace("\0", "", $this->dl_path . 'init_' . $filename));

        if (!file_exists($file)) {
            dd($file . ' does not exist');
        }

        $raw_contents = file_get_contents($file);
        $contents = $this->processor->process($raw_contents);

        $file = $file = strval(str_replace("\0", "", $this->dl_path . 'done_' . $filename));
        file_put_contents($file, $contents);
        dd('Done processing');
    }
}