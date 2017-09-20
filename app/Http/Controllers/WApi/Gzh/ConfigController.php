<?php

namespace App\Http\Controllers\WApi\Gzh;

use App\Http\Controllers\WApi\WApiController;

class ConfigController extends WApiController
{
    
    public function store()
    {
        $data = request()->input();
        $res = $this->model->fill($data)->save();
        if ($res)
        {
            $this->response();
        } else {
            $this->response(['errcode'=>-1, 'errmsg'=>'操作异常']);
        }
    }
    
    public function update($id)
    {
        $data = request()->input();
        $res = $this->model->where('id', $id)->update($data);
        if ($res)
        {
            $this->response();
        } else {
            $this->response(['errcode'=>-1, 'errmsg'=>'操作异常']);
        }
    }
}
