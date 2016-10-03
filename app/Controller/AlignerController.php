<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;
use App\Service\TmxService;
use App\Util\FileUtil;

class AlignerController extends BaseController {

    protected $tmx;
    protected $file;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->tmx = new TmxService();
        $this->file = FileUtil::getInstance();
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

        if ($this->debug > 0) {
            file_put_contents($this->dl_path . 'init_' . $filename . '.tmx', $contents);
        }

        file_put_contents($this->dl_path . $filename . '.tmx', $contents);

        $this->Session->set('filename', $filename );

        $data = [
            'file' => $filename
        ];

        return $this->view->render($response, 'aligner/align_vue.twig', $data);
    }


    /**
     * API call to provide chunks
     *
     * @param $request
     * @param $response
     */

    public function get_chunks($request, $response) {
        $file = 'generated.tmx';
        $file = 'sample.tmx';
        $raw = $this->file->read_file($file);
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
        $data = $request->getParsedBody();
        $source_language = 'et';
        $destination_language = 'en';
        $source = $data['language0'];
        $destination = $data['language1'];

        $tmx = $this->tmx->create($source_language, $destination_language, $source, $destination);

        $filename = 'generated';
        file_put_contents($this->dl_path . $filename . '.tmx', $tmx);

        return $response->withJson(['status' => 'ok']);
    }
}