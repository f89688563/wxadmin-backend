<?php

namespace App\Http\Controllers\Ext;

use App\Http\Controllers\Controller;
use App\Http\Logic\WechatCallback;
use App\Http\Logic\WxLogic;
use App\Http\Logic\MessageLogic;

class WxController extends Controller
{
    public function index()
    {
        
//         $logic = new WxLogic();
        $logic = new MessageLogic();
        $lists = $logic->msg_2_kf(1, 2);
        dd($lists);
        
        header('Content-type:text');
        $config = '';
        $wechatObj = new WechatCallback($config);
        if (!isset($_GET['echostr'])) {
            $wechatObj->responseMsg();
        } else {
            $token = 'nz_gzh_token';
            $wechatObj->valid($token);
        }
    }
}
