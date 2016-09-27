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
use App\Service\TmxService;
use App\Util\FileUtil;
use Slim\Http\UploadedFile;

class DocumentsController extends BaseController {

    private $tika;
    private $processor;
    private $file;

    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        parent::__construct($container);

        $this->file = FileUtil::getInstance();

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

        $filename = $this->file->get_file_name($file->getClientFilename());
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

        $this->file->download($file);
    }



    public function test($request, $response) {
        $nltk = new NltkService(1);
        $tmx = new TmxService();

        $raw_contents = $this->file->read_file('init_riigiteataja.txt');
        $contents = $this->processor->process($raw_contents);

        $language = $nltk->detect_lang($contents);
        debug($language);

        $out = $nltk->ner_text($contents);
        debug($out);

        $out = $nltk->chunk_text($contents);
        debug($out);

        $out = $nltk->prepare($contents);
        dd($out);

        $tmx->process($contents);
    }



    public function test_processing($request, $response) {
        $filename = 'riigiteataja.txt';

        $raw_contents = $this->file->read_file('init_riigiteataja.txt');
        $contents = $this->processor->process($raw_contents);

        $file = $file = strval(str_replace("\0", "", $this->dl_path . 'done_riigiteataja.txt'));
        file_put_contents($file, $contents);
        dd('Done processing');
    }
}