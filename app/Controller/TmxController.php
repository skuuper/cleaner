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
        $data = $request->getParsedBody();
        //print_r($data);
        $source_language = $data['source_language'];
        $destination_language = $data['destination_language'];
        $files = $request->getUploadedFiles();
        $bUseLF = isset($data['use_lf_aligner']) && ($data['use_lf_aligner'] == 'on' || $data['use_lf_aligner'] == 'true' || $data['use_lf_aligner'] == '1');
        $bUseLDC = isset($data['use_ldc_chunker']) && ($data['use_ldc_chunker'] == 'on' || $data['use_ldc_chunker'] == 'true' || $data['use_ldc_chunker'] == '1');
        //print($bUseLF);

        //TODO: Handle missing files with someting different than exceptions
        if (empty($files['source_text'])) {
            throw new Exception('Source text file not present');
        }
        if (empty($files['destination_text'])) {
            throw new Exception('Destination text file not present');
        }

        $source_raw = $files['source_text']->getStream()->getContents();
        $destination_raw = $files['destination_text']->getStream()->getContents();

        //print($destination_raw);
        $source_raw = $this->tika->get_contents_stream($source_raw);
        $destination_raw = $this->tika->get_contents_stream($destination_raw);
        //print($destination_raw);

        if ($bUseLDC)
          if ($source_language == 'zh')
            $source_raw = $this->processor->tokenize_ldc($source_raw);
          else
            $destination_raw = $this->processor->tokenize_ldc($destination_raw);

        $aligner = $data['aligner'];
        $dic = $data['dict'];

        $source = explode("\n", $this->processor->process($source_raw, $bUseLF, $source_language));
        $destination = explode("\n", $this->processor->process($destination_raw, $bUseLF, $destination_language));
        //print($destination_raw);

        $tmx = $this->tmx->create($source_language, $destination_language, $source, $destination, $aligner, $dic);

        date_default_timezone_set('UTC');
        $filename = str_replace("/", "_", str_replace(".", "_", $files['source_text']->getClientFilename())).'_'.date('Ymd_Hi');
        file_put_contents($this->dl_path . $filename . '.tmx', $tmx);

        $this->session->set('filename', $filename );

        $raw = $this->file->read_file($filename.'.tmx');
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
        $file = $args['filename'];

        $raw = $this->file->read_file($file);
        $contents = $this->tmx->parse($raw);

        $data = [
            'tmx' => $contents
        ];

        //TODO: Fallback to align.twig template
        return $this->view->render($response, 'tmx/align_vue.twig', $data);
    }
}