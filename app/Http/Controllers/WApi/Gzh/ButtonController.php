<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class ButtonController extends WApiController
{
    var $logic;
    public function __construct()
    {
        $this->logic = new WxLogic();
        parent::__construct();
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
    
    public function store()
    {        
        $buttons = json_decode(request('buttons'), true);
        $tagId = request('tagId');
        $data = ['button'=>$buttons];
        if ($tagId) {
            $data['matchrule'] = ['tag_id'=>$tagId];
            $res = $this->logic->createConditionalMenu($data);
        } else {            
            $res = $this->logic->create_menu($data);
        }
        $this->response($res);
    }
    
    public function update($id=0)
    {
        $buttons = json_decode(request('buttons'), true);
        $data = ['button'=>$buttons];
        $tagId = request('tagId');
        if ($tagId) {
            $data['matchrule'] = ['tag_id'=>$tagId];
            $res = $this->logic->createConditionalMenu($data);
        } else {            
            $res = $this->logic->create_menu($data);
        }
        $res = $this->logic->create_menu($data);
        $this->response($res);
    }
}
