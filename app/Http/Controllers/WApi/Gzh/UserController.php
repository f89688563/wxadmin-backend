<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class UserController extends WApiController
{
    public $logic;
    public function __construct()
    {
        $this->logic = new WxLogic();
//         $this->model = new WxLogic();
        parent::__construct();
    }
    
    public function wxinfo($openid)
    {
        $info = $this->model->wxInfo($openid);
        $this->response($info);
    }
    
//     public function index()
//     {
//         echo '<pre>';
//         $extend = [];
//         $lists = $this->getLists($this->table, $extend, true);
//         if ($lists) {
//             array_filter($lists, function($item){
//                 $info = $this->logic->get_user_info($item['openid']);
//                 var_dump($info);
//             });
//         }
// //         $lists = $this->model->get_user_list();
// //         $lists = $this->model->get_kf_list();
//         dd($lists);
//     }
}
