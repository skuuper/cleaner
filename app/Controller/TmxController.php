<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 13.09.16
 * Time: 13:56
 */

namespace App\Controller;

use App\Service\SkuuperTikaService;
use App\Service\TmxService;
use App\Util\FileUtil;
use Exception;
use Interop\Container\ContainerInterface;
use App\Service\DocumentProcessorService;
use App\Service\SessionService;

class TmxController extends BaseController {

    private $tmx;
    private $file;
    private $tika;
    private $processor;

    /**
     * TmxController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->tmx = new TmxService();
        $this->file = FileUtil::getInstance();
        $this->tika = new SkuuperTikaService();
        $this->processor = new DocumentProcessorService($this->debug);
        $this->session = new SessionService();
    }




    public function index($request, $response) {
        $data = [
            'languages' => $this->getLanguages()
        ];
        return $this->view->render($response, 'tmx/index.twig', $data);
    }


    /**
     * @param $request
     * @param $response
     * @throws Exception
     */
    public function process($request, $response) {
        $files = $request->getUploadedFiles();

        //TODO: Handle missing files with someting different than exceptions
        if (empty($files['source_text'])) {
            throw new Exception('Source text file not present');
        }
        if (empty($files['destination_text'])) {
            throw new Exception('Destination text file not present');
        }

        $source_raw = $files['source_text']->getStream()->getContents();
        $destination_raw = $files['destination_text']->getStream()->getContents();

        
        
        $source_raw = $this->tika->get_contents_stream($source_raw);
        $destination_raw = $this->tika->get_contents_stream($destination_raw);

        $source = explode("\n", $this->processor->process($source_raw));
        $destination = explode("\n", $this->processor->process($destination_raw));



        $data = $request->getParsedBody();
        $source_language = $data['source_language'];
        $destination_language = $data['destination_language'];

        $tmx = $this->tmx->create($source_language, $destination_language, $source, $destination);

        $filename = 'generated';
        file_put_contents($this->dl_path . $filename . '.tmx', $tmx);

        $this->session->set('filename', $filename );

        $raw = $this->file->read_file('generated.tmx');
        $contents = $this->tmx->parse($raw);

        $data = [
            'file' => $filename,
            'languages' => $this->getLanguages(),
            'req' => $data,
            'tmx' => $contents
        ];

        return $this->view->render($response, 'tmx/preview.twig', $data);
    }



    public function download($request, $response, $args) {
        $filename = $args['filename'];
        $file = $this->dl_path . $filename . '.tmx';

        if (!file_exists($file)) {
            dd('Error: File does not exist - ' . $file);
            exit;
        }

        $this->file->download($file);
    }
    


    public function align($request, $response) {

        $file = 'generated.tmx';
        # $file = 'sample.tmx';


        $raw = $this->file->read_file($file);
        $contents = $this->tmx->parse($raw);

        $data = [
            'tmx' => $contents
        ];

        //TODO: Fallback to align.twig template
        return $this->view->render($response, 'tmx/align_vue.twig', $data);
    }
}