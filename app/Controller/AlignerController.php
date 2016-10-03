<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;
use App\Service\TmxService;
use App\Util\FileUtil;
use App\Service\SessionService;

class AlignerController extends BaseController {

    protected $tmx;
    protected $file;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->tmx = new TmxService();
        $this->file = FileUtil::getInstance();
        $this->session = new SessionService();
    }


    public function index($request, $response) {
        $this->view->render($response, 'aligner/index.twig');
    }


    /**
     * Process the uploaded file
     *
     */

    public function process($request, $response) {
        $files = $request->getUploadedFiles();
        if (empty($files['upload_file'])) {
            throw new Exception('Uploaded file not present');
        }

        /** @var UploadedFile $file */
        $file = $files['upload_file'];
        $contents = $file->getStream()->getContents();

        $filename = $this->file->get_file_name($file->getClientFilename());

        file_put_contents($this->dl_path . $filename . '.tmx', $contents);

        $this->session->set('filename', $filename );

        $data = [
            'file' => $filename
        ];

        return $this->view->render($response, 'aligner/align.twig', $data);
    }



    public function align($request, $response) {
        $filename = $this->session->get('filename');
        $data = [
            'file' => $filename
        ];
        return $this->view->render($response, 'aligner/align.twig', $data);
    }



    /**
     * API call to provide chunks
     *
     * @param $request
     * @param $response
     */

    public function get_chunks($request, $response) {
        $file = $this->session->get('filename');

        $raw = $this->file->read_file($file, "tmx");
        $tmx = $this->tmx->parse_split($raw);

        $contents = [];
        foreach ($tmx as $unit) {
            $contents['language_0'][] = (string)$unit->tuv[0]->seg;
            $contents['language_1'][] = (string)$unit->tuv[1]->seg;
        }
        return $response->withJson($contents);
    }


    /**
     * API call to save chunks to the TMX file.
     *
     * @param $request
     * @param $response
     * @return mixed
     */

    public function save_chunks($request, $response) {

        $file = $this->session->get('filename');
        $raw = $this->file->read_file($file, 'tmx');
        $languages = $this->tmx->get_languages($raw);

        $data = $request->getParsedBody();
        $source_language = $languages[0];
        $destination_language = $languages[1];
        $l0 = $data['language0'];
        $l1 = $data['language1'];

        foreach ($l0 as $item) {
            $source[] = $item['text'];
        }
        foreach ($l1 as $item) {
            $destination[] = $item['text'];
        }

        $tmx = $this->tmx->create($source_language, $destination_language, $source, $destination);

        $filename =  $this->session->get('filename');
        file_put_contents($this->dl_path . $filename . '.tmx', $tmx);

        return $response->withJson(['status' => 'ok']);
    }
}