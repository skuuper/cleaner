<?php
/**
 * Created by PhpStorm.
 * User: oelg
 * Date: 28.05.17
 * Time: 15:27
 */

namespace App\Controller;

use Exception;
use Interop\Container\ContainerInterface;
use App\Service\SessionService;

class DicController extends BaseController {
    /**
     * TmxController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->session = new SessionService();
    }



    public function index($request, $response) {
        $data = $_GET;
        $srclang = strtolower(isset($data['srclang']) ? $data['srclang'] : '');
        $dstlang = strtolower(isset($data['dstlang']) ? $data['dstlang'] : '');
        //print("_".$srclang." is source");
        $out = array();
        foreach ( scandir( 'thirdparty/aligner/data' ) as $key => $value ) {
          if (substr($value, -4) != '.dic') continue;
          if (strlen($srclang) > 0 && substr($value, 0, 2) != $srclang) continue;
          if (strlen($dstlang) > 0 && substr($value, 3, 2) != $dstlang) continue;
          $out[] = $value;
        }
        $srclang = str_replace('zh', 'cn', $srclang);
        $dstlang = str_replace('zh', 'cn', $dstlang);
        foreach ( scandir( 'thirdparty/aligner_ch/lib' ) as $key => $value ) {
          if (substr($value, -9) != '.utf8.txt') continue;
          if (strlen($srclang) > 0 && $value[0] != $srclang[0]) continue;
          if (strlen($dstlang) > 0 && $value[1] != $dstlang[0]) continue;
          $out[] = $value;
        }
        return json_encode($out);
    }


}