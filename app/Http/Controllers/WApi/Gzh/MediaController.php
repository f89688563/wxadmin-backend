<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class MediaController extends WApiController
{
    public function __construct()
    {
        $this->model = new WxLogic();
        parent::__construct();
    }
    
    public function index()
    {
        $type = request('type', 'image');
        $page = request('page', 1);
        
        $res = $this->model->getMedia($type, $page);
        $this->response($res);
    }
    
}
