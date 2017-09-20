<?php
/**
 * author : panfeng
 * email : 89688563@qq.com
 * date : 2016-11-18
 * charset : UTF-8
 */
namespace App\Http\Logic;

class AppLogic extends BaseLogic {
	//构造函数，获取Access Token
	public function __construct($appid = NULL, $appsecret = NULL)
	{
		$this->appid = $appid ? $appid : 'wx554a396c5dde5f3d' ;
		$this->appsecret = $appsecret ? $appsecret : 'e537ceed49e064a991657d624a7b44c9';
		
		$this->token_name = 'app_token_'.$this->appid;
		$this->token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={appid}&secret={appsecret}';
		
		$this->access_token = $this->getAccessToken();
		if ( 'error' == $this->access_token) {
			$this->access_token = $this->getAccessToken(1);
		}
	}
	
	public function sendCusMsg($openid, $content) {
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->access_token;
		$data = array(
			"touser" => $openid,
			"msgtype" => "text",
			"text" => ["content"=>$content]
		);
		$res = $this->https_request($url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($res, true);
	}
	
	public function getOpenid($code) {
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->appsecret.'&js_code='.$code.'&grant_type=authorization_code';
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	public function sendModelMsg()
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token;
		$data = [
			
		];
	}
}