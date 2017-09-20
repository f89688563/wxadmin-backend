<?php
/**
 * author : panfeng
 * email : 89688563@qq.com
 * date : 2016-11-21
 * charset : UTF-8
 */

namespace App\Http\Logic;

class BaseLogic
{
	var $appid;
	var $appsecret;
	var $access_token;
	var $token_name;
	var $token_url;
	
	public function getAccessToken( $force = false )
	{
		$tokenname = $this->token_name;
		if ( ! $force ){
			$data = cache( $tokenname );
		}
		if ( empty( $data ) ){
			$data = $this->saveAccessToken();
		}
	
		return $data;
	}
	
	public function saveAccessToken()
	{
		$tokenname = $this->token_name;
		$url = str_replace(['{appid}','{appsecret}'], [$this->appid,$this->appsecret], $this->token_url);
		$res = $this->https_request($url);
		$result = json_decode($res, true);
		if ($result['access_token']){
			$data = $result['access_token'];
			cache([$tokenname => $data], $result['expires_in']);
		} else {
		    $data = 'error';
		}
		return $data;
	}
	
	public function curlFileCreate($filename, $mimetype = '', $postname = '')
	{
		return "@$filename;filename=".($postname ?: basename($filename)).($mimetype ? ";type=$mimetype" : '');
	}
	
	//https请求（支持GET和POST）
	protected function https_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_URL, $url);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
}