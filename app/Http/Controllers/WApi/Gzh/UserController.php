<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class UserController extends WApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new WxLogic();
    }
    
    public function index()
    {
//         $lists = $this->model->get_user_list();
        $lists = $this->model->get_kf_list();
        dd($lists);
    }
}
