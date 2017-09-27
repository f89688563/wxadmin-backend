<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;
use App\Http\Logic\WxLogic;

class TagsController extends WApiController
{
    public function __construct()
    {
        $this->logic = new WxLogic();
        parent::__construct();
    }
    
    public function index()
    {
        $res = $this->logic->getTags();
        $this->response($res);
    }
    
    public function store()
    {
        $name = request('name');
        $res = $this->logic->createTags($name);
        $this->response($res);
    }
    
    public function destroy($id)
    {
        $res = $this->logic->deleteTags($id);
        $this->response($res);
    }
    
    public function update($id)
    {
        $name = request('name');
        $res = $this->logic->updateTags($id, $name);
        $this->response($res);
    }
}
