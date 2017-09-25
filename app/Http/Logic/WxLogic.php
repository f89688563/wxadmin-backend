<?php
/**
 * author : panfeng
 * email : 89688563@qq.com
 * date : 2016-11-2
 * charset : UTF-8
 */

namespace App\Http\Logic;

class WxLogic extends BaseLogic
{
    var $centerUrl = 'http://c.nanzhusz.com/server.php/wx/gzh/token';
	//构造函数，获取Access Token
	public function __construct($appid = NULL, $appsecret = NULL)
	{
	    $this->appid = $appid ? $appid : get_config(APPSECRET)['value'];
	    $this->appsecret = $appsecret ? $appsecret : get_config(APPSECRET)['value'];
		
		$this->token_name = 'fw_access_token_'.$this->appid;
		$this->token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={appid}&secret={appsecret}';
		
		// 通过中转服务器获取token
		$this->access_token = $this->getTokenFromCenter();

// 		$this->access_token = $this->getAccessToken();
// 		if (!$this->validToken()) {
// 		    $this->access_token = $this->getAccessToken(1);
// 		}
	}
	
	private function getTokenFromCenter()
	{
		$res = $this->https_request($this->centerUrl);
		$res = json_decode($res, true);
		return $res['token'];
	}
	
	private function validToken()
	{
	    $res = $this->get_kf_list();
	    return isset($res['kf_list']);
	}
	
	// 开启会话
	public function create_session()
	{
		$url = "https://api.weixin.qq.com/customservice/kfsession/create?access_token=".$this->access_token;
		
		$data = [
			'kf_account'=>'kf2001@gh_1878f8a0e872',
			'openid'=>'oLfd9wNRgrE9KFouP8CfRUByZuBo'
		];
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}

	//获取未接入会话列表
	public function get_wait_session()
	{
		$url = "https://api.weixin.qq.com/customservice/kfsession/getwaitcase?access_token=".$this->access_token;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}

	//获取客户会话列表
	public function get_cust_session( $openid )
	{
		$url = "https://api.weixin.qq.com/customservice/kfsession/getsession?access_token=".$this->access_token."&openid=".$openid;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}

	//获取客服会话列表
	public function get_kf_session( $kfAccount )
	{
		$url = "https://api.weixin.qq.com/customservice/kfsession/getsessionlist?access_token=".$this->access_token."&kf_account=".$kfAccount;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}

	//获取关注者列表
	public function get_user_list( $next_openid = NULL )
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->access_token."&next_openid=".$next_openid;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}

	//获取用户基本信息
	public function get_user_info($openid)
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->access_token."&openid=".$openid."&lang=zh_CN";
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	//获取客服基本信息
	public function get_kf_list()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=".$this->access_token;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	//获取素材
	public function getMedia($type='image', $page=1, $perPage=10)
	{
	    $page = $page ? $page : 1;
	    $perPage = $perPage ? $perPage : 10;
	    $data = [
	        'type'     =>$type,
	        'offset'   => ($page-1)*$perPage,
	        'count'    => $perPage
	    ];
		$url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$this->access_token;
		$res = $this->https_request($url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($res, true);
	}
	
	//获取素材总数
	public function getMediaTotal()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=".$this->access_token;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	//创建菜单
	public function create_menu( $data=[] )
	{
		if (! $data) {
			$data = array(
				"button"=>array(
					array(
						"type"=>"view",
						"name"=>"屛碎险",
						"url"=>"http://sp.kuncloud.cn/htdocs/?action=auth/login"
					),
					array(
						"type"=>"view",
						"name"=>"帮助",
						"url"=>"http://baidu.com"
					),
					array(
						"name"=>"多菜单",
						"sub_button"=>array(
							array(
			                    "type"=>"scancode_waitmsg", 
			                    "name"=>"扫码带提示", 
			                    "key"=>"rselfmenu_0_0", 
			                ), 
			                array(
			                    "type"=>"scancode_push", 
			                    "name"=>"扫码推事件", 
			                    "key"=>"rselfmenu_0_1", 
			                )
		            	)
					),
				),
			);
		}
		// 参数2不转中文
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
		$res = $this->https_request( $url, $data );
		return json_decode( $res, true );
	}
	
	public function getMenu()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$this->access_token;
		$res = $this->https_request( $url );
		return json_decode( $res, true );
	}
	
	public function msg_2_kf($from, $data, $kf)
	{
	    
	}

	//企业客服发送消息-微信接收
	public function send_custom_message( $data )
	{
		$msg = array( 'touser' =>$data['Receiver']['Id'] );
		$msg['msgtype'] = $type = $data['MsgType'];
		
		$qyLogic = new QyLogic();
		$mediaId = $data['MediaId'];
		switch( $type )
		{
			case 'text':
				$msg['text'] = array('content'=> urlencode($data['Content']));
				break;
			case 'image':
				// 1.下载文件到本地
				$filename = 'Upload/'.$mediaId.'.png';
				$qyLogic->downMedia($filename, $mediaId);
				// 2.上传
				$uploadInfo = $this->uploadMedia($filename, $type);
				
				$msg['image'] = array('media_id'=> $uploadInfo['media_id']);
				break;
			case 'voice':
				$msg['voice'] = array('media_id'=> $mediaId);
				break;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
		return $this->https_request($url, urldecode(json_encode($msg)));
	}
	
	//生成参数二维码
	public function create_qrcode( $scene_type, $scene_id )
	{
		switch($scene_type)
		{
			case 'QR_LIMIT_SCENE': //永久
				$data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scene_id.'"}}}';
// 				$data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": "'.$scene_id.'"}}}';
				break;
			case 'QR_SCENE':       //临时
				$data = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
				break;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->access_token;
		$res = $this->https_request($url, $data);
		$result = json_decode($res, true);
		return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($result["ticket"]);
	}

	//创建分组
	public function create_group($name)
	{
		$data = '{"group": {"name": "'.$name.'"}}';
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=".$this->access_token;
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}

	//移动用户分组
	public function update_group($openid, $to_groupid)
	{
		$data = '{"openid":"'.$openid.'","to_groupid":'.$to_groupid.'}';
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=".$this->access_token;
		$res = $this->https_request($url, $data);
		return json_decode($res, true);
	}

	//上传多媒体文件
	public function uploadMedia($filename, $type)
	{
		// 解决php5.5以上传图片
// 		$data = array('media' => $this->curlFileCreate($filename));
		$data = array("media" => curl_file_create($filename));
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token."&type=".$type;
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
		$url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$mediaId;
		$res = $this->https_request($url);
		file_put_contents($filename, $res);
	}
	
	// 设备相关
	
	/**
	 * 发消息给设备
	 * @param unknown $data
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-30下午2:41:48
	 */
	public function deviceMsg($data)
	{
		$url = 'https://api.weixin.qq.com/device/transmsg?access_token='.$this->access_token;
		$res = $this->https_request($url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($res, true);
	}
	
	/**
	 * 获取设备绑定的openId
	 * @param unknown $deviceId
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-29上午11:12:05
	 */
	public function deviceOpenId($deviceType, $deviceId)
	{
		$url = 'https://api.weixin.qq.com/device/get_openid?access_token='.$this->access_token.'&device_type='.$deviceType.'&device_id='.$deviceId;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	/**
	 * 设备授权绑定信息
	 * @param unknown $deviceId
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-29上午11:36:48
	 */
	public function deviceInfo($deviceId)
	{
		$url = 'https://api.weixin.qq.com/device/get_stat?access_token='.$this->access_token.'&device_id='.$deviceId;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	/**
	 * 设备二维码
	 * @param unknown $productId
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-29上午11:32:38
	 */
	public function deviceQRCode($productId)
	{
		$url = 'https://api.weixin.qq.com/device/getqrcode?access_token='.$this->access_token.'&product_id='.$productId;
		$res = $this->https_request($url);
		return json_decode($res, true);
	}
	
	/**
	 * 验证二维码
	 * @param unknown $data
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-29上午11:37:41
	 */
	public function deviceVerifyQR($data)
	{
		$url = 'https://api.weixin.qq.com/device/verify_qrcode?access_token='.$this->access_token;
		$res = $this->https_request($url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($res, true);
	}

	/**
	 * 设置设备信息
	 * @param unknown $data
	 * @return mixed
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-30上午10:56:33
	 */
	public function deviceAuthorize($data)
	{
		$url = 'https://api.weixin.qq.com/device/authorize_device?access_token='.$this->access_token;
		$res = $this->https_request($url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($res, true);
	}
	
	public function errorCode($code = '')
	{
		$error = array(
				'40003'	=> 'openid不合法',
				'40013'	=> 'appid不合法',
				'41009'	=>	'缺少openid参数',
				'43001'	=>	'要求使用GET请求',
				'43002'	=>	'要求使用POST请求',
				'43003'	=>	'要求使用https',
				'43005'	=>	'要求是好友关系',
				'44002'	=>	'post的数据为空',
				'47001'	=>	'数据格式有误',
				'0'		=>	'成功',
				'-1'		=>	'系统错误',
				'100001'	=>	'查询请求不存在',
				'100002'	=>	'新增请求已经存在',
				'100003'	=>	'请求中的数据大小不合法',
				'100004'	=>	'二维码不合法',
				'100005'	=>	'不合法',
				'100006'	=>	'device id不合法',
				'100007'	=>	'设备状态不合法',
				'100008'	=>	'mac地址不合法',
				'100009'	=>	'protocol不合法',
				'100010'	=>	'key不合法',
				'100011'	=>	'close strategy不合法',
				'100012'	=>	'strategy不合法',
				'100013'	=>	'method不合法',
				'100014'	=>	'version不合法',
				'100015'	=>	'manufature mac position不合法',
				'100016'	=>	'serial number mac position不合法',
				'100017'	=>	'批量处理请求数量不合法',
				'100018'	=>	'optype不合法',
				'100019'	=>	'账号状态不合法',
				'100020'	=>	'账号设备授权配额已用完，需重新申请',
				'100021'	=>	'用户和设备的绑定关系不存在',
				'100022'	=>	'消息类型不合法',
				'100023'	=>	'消息内容不合法',
				'100024'	=>	'用户当前没有订阅wifi设备的状态',
				'100025'	=>	'设备属性未设置',
				'100026'	=>	'票据不合法',
		);
		return $code ? $error[$code] : $error;
	}
}