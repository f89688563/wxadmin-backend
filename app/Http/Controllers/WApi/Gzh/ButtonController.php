<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class ButtonController extends WApiController
{
    var $logic;
    public function __construct()
    {
        parent::__construct();
        $this->logic = new WxLogic();
    }
    
    public function index()
    {
        $res = $this->logic->getMenu();
        
        $errcode = 0;
        $errmsg = '';
        if (isset($res['errcode']))
        {
            if ($res['errcode'] === 46003)
            {
                $button = [];
            } else {
                $errcode = -1;
                $errmsg = $res['errmsg'];
            }
        } else {
            $button = $res;
        }
        
        $data = ['errcode'=>$errcode, 'errmsg'=>$errmsg, 'button'=>$button];
        $this->response($data);
    }
    
    public function update($id)
    {
        $buttons = json_decode(request('buttons'), true);
        $data = ['button'=>$buttons];
        $res = $this->logic->create_menu($data);
        $this->response($res);
    }
}
