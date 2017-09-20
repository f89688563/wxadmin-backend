<?php
/**
 * author : panfeng
 * email : 89688563@qq.com
 * date : 2016-11-14
 * charset : UTF-8
 */

namespace App\Http\Logic;

class QyLogic extends BaseLogic
{
	var $aesKey = 'h69zNkx3KsBpBwbkfczimT2oH3BuBJ7poj6Gw5rWIOP';
	
	//构造函数，获取Access Token
	public function __construct($corpid = '', $corpsecret = '')
	{
		$this->appid = $corpid ? $corpid : 'wx1c40df09d2c90926';
		$this->appsecret = $corpsecret ? $corpsecret : '9coiKMwz8nbnM21CQPtaD3YVVxeOdeyto_9_e1bGO8IzwCZ9nrQPHUSDqEuAaWkF';
		
		$this->token_name = 'qydsit2016'.$this->appid;
		$this->token_url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={appid}&corpsecret={appsecret}';

		$this->access_token = $this->getAccessToken();
		if ( 'error' == $this->access_token) {
		    $this->access_token = $this->getAccessToken(1);
		}
	}
	
	public function send_msg()
	{
		$data = [
			'touser'=>'pf',
			'msgtype'=>'text',
			'agentid'=>0,
			'text'=>[
				'content'=>'asd'
			]
		];
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		
		$url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$this->access_token;
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}
	
	public function msg_2_kf($kfid, $msg)
	{
		$url = 'https://qyapi.weixin.qq.com/cgi-bin/kf/send?access_token='.$this->access_token;
		$data = [
			'sender'=>[
				'type'=>'openid',
				'id'=>$msg['FromUserName']
			],
			'receiver'=>[
				'type'=>'kf',
				'id'=>'pf'
			],
			'msgtype'=>$msg['MsgType']
		];
		
		$wxLogic = new WxLogic();
		$type = $msg['MsgType'];
		$mediaId = $msg['MediaId'];
		switch ($type)
		{
			case 'text':
				$data['text']['content'] = $msg['Content'];
				break;
			case 'image':
				// 1.下载文件到本地
				$filename = 'Upload/'.$mediaId.'.png';
				$wxLogic->downMedia($filename, $mediaId);
				// 2.上传
				$uploadInfo = $this->uploadMedia($filename, $type);
				
				$data['image']['media_id'] = $uploadInfo['media_id'];
				break;
			case 'voice':
				$data['voice']['media_id'] = $mediaId;
				break;
		}
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}
	
	/**
	 * 下载媒体文件
	 * @param unknown $accountType
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-21下午4:59:10
	 */
	public function downMedia($filename, $mediaId)
	{
		$url = 'https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$mediaId;
		$res = $this->https_request($url);
		file_put_contents($filename, $res);
	}
	
	//上传多媒体文件
	public function uploadMedia($filename, $type)
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token."&type=".$type;
		// 解决php5.5以上传图片
// 		$data = array("media" => $this->curlFileCreate($filename));
		$data = array("media" => curl_file_create($filename));
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}
	
	public function get_kf_list()
	{
		// type = internal 内部客服 external 外部客服 不填 获取所有;
		$url = 'https://qyapi.weixin.qq.com/cgi-bin/kf/list?access_token='.$this->access_token.'&type=external';
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
}