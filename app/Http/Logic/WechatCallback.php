<?php
/**
 * author： panfeng
 * email ：89688563@qq.com
 * date ：2016年5月29日
 * charset ： UTF-8
 */
 
namespace App\Http\Logic;

use App\UserModel;

class WechatCallback
{
    var $msgObj;
    
	// 验证签名
	public function valid($token) {
		$echoStr = $_GET ["echostr"];
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		$tmpArr = array (
			$token,
			$timestamp,
			$nonce 
		);
		sort ( $tmpArr );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		if ($tmpStr == $signature) {
			echo $echoStr;
			exit ();
		}
	}
	
	public function __construct($config=null) {
	    $this->msgObj = new MessageLogic($config);
	}
	
	// 响应消息
	public function responseMsg() {
// 		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		$postStr = file_get_contents("php://input");
		if (! empty ( $postStr )) {
			$this->logger ( "R " . $postStr );
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$postObj= json_decode(json_encode($postObj), true);
			$RX_TYPE = trim ( $postObj['MsgType'] );

			$config = '';
			$msgObj = $this->msgObj;
			$form = $postObj['FromUserName'];
			
			if ($msgObj->isInservice($postObj['FromUserName']))
			{
			    $msgObj->msg_2_kf($form, $postObj);
// 				$msgObj->msg_2_qy($postObj);
				$result = '';
			}
			else
			{
				// 消息类型分离
				switch ($RX_TYPE) {
					case "event" :
						$result = $this->receiveEvent ( $postObj );
						break;
					case "text" :
						$result = $this->receiveText ( $postObj );
						break;
					case "image" :
						$result = $this->receiveImage ( $postObj );
						break;
					case "location" :
						$result = $this->receiveLocation ( $postObj );
						break;
					case "voice" :
						$result = $this->receiveVoice ( $postObj );
						break;
					case "video" :
						$result = $this->receiveVideo ( $postObj );
						break;
					case "link" :
						$result = $this->receiveLink ( $postObj );
						break;
					case "device_event" :
						$result = $this->receiveDevice ( $postObj );
						break;
					default :
						$result = "unknown msg type: " . $RX_TYPE;
						break;
				}
			}
			$this->logger ( "T " . $result );
			echo $result;
		} else {
			echo "";
			exit ();
		}
	}
	
	// 接收设备消息
	private function receiveDevice($object) {
		switch ($object['Event']) {
			case 'bind':
				$this->deviceBind($object, 2);
				$content = '已绑定';
				break;
			case 'unbind':
				$this->deviceBind($object, 1);
				$content = '已取消绑定';
				break;
			case 'device_text':
			    $content = '设备信息';
				break;
			case 'subscribe_status':
			    $content = '绑定状态2';
				break;
			case 'unsubscribe_status':
			    $content = '绑定状态0';
				break;
		}
		
		$result = $this->transmitDevice($object, $content);
		return $result;
	}
	
	// 接收事件消息
	private function receiveEvent($object) {
		$content = "";
		switch ($object['Event']) {
			case "subscribe" :
			    $config = get_config(SUBSCRIBE_MSG)['value'];
				$content = $config ? $config : '感谢关注~~~';
				// 关注事件
				$this->subscribe($object);
				break;
			case "unsubscribe" :
				$content = "取消关注";
				$this->subscribe($object, 0);
				break;
			case "SCAN" :
				$content = "扫描场景 " . $object['EventKey'];
				break;
			case "CLICK" :
				switch ($object['EventKey']) {
					case "COMPANY" :
						$content = array ();
						$content [] = array (
							"Title" => "多图文1标题",
							"Description" => "",
							"PicUrl" => "http://discuz.comli.com/weixin/weather/icon/cartoon.jpg",
							"Url" => "http://m.cnblogs.com/?u=txw1958" 
						);
						break;
					default :
						$content = "点击菜单：" . $object['EventKey'];
						break;
				}
				break;
			case "LOCATION" :
				$content = "上传位置：纬度 " . $object['Latitude'] . ";经度 " . $object['Longitude'];
				break;
			case "VIEW" :
				$content = "跳转链接 " . $object['EventKey'];
				break;
			case "MASSSENDJOBFINISH" :
				$content = "消息ID：" . $object['MsgID'] . "，结果：" . $object['Status'] . "，粉丝数：" . $object['TotalCount'] . "，过滤：" . $object['FilterCount'] . "，发送成功：" . $object['SentCount'] . "，发送失败：" . $object['ErrorCount'];
				break;
			default :
				$content = "receive a new event: " . $object['Event'];
				break;
		}
		if (is_array ( $content )) {
			if (isset ( $content [0] )) {
				$result = $this->transmitNews ( $object, $content );
			} else if (isset ( $content ['MusicUrl'] )) {
				$result = $this->transmitMusic ( $object, $content );
			}
		} else {
			$result = $this->transmitText ( $object, $content );
		}
		
		return $result;
	}
	
	// 接收文本消息
	private function receiveText($object) {
		$keyword = trim ( $object['Content'] );
		// 多客服人工回复模式
		$config = get_config(KF_KEYWORDS, 1)['value'];
		if (in_array($keyword, $config)) {
			$result = $this->transmitService ( $object );
		} 		
		// 自动回复模式
		else 
		{
			if (strstr ( $keyword, "文本" )) {
				$content = "这是个文本消息";
			} else if (strstr ( $keyword, "单图文" )) {
				$content = array ();
				$content [] = array (
						"Title" => "单图文标题",
						"Description" => "单图文内容",
						"PicUrl" => "http://discuz.comli.com/weixin/weather/icon/cartoon.jpg",
						"Url" => "http://m.cnblogs.com/?u=txw1958" 
				);
			} else if (strstr ( $keyword, "图文" ) || strstr ( $keyword, "多图文" )) {
				$content = array ();
				$content [] = array (
						"Title" => "多图文1标题",
						"Description" => "",
						"PicUrl" => "http://discuz.comli.com/weixin/weather/icon/cartoon.jpg",
						"Url" => "http://m.cnblogs.com/?u=txw1958" 
				);
				$content [] = array (
						"Title" => "多图文2标题",
						"Description" => "",
						"PicUrl" => "http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg",
						"Url" => "http://m.cnblogs.com/?u=txw1958" 
				);
				$content [] = array (
						"Title" => "多图文3标题",
						"Description" => "",
						"PicUrl" => "http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg",
						"Url" => "http://m.cnblogs.com/?u=txw1958" 
				);
			} else if (strstr ( $keyword, "音乐" )) {
				$content = array ();
				$content = array (
						"Title" => "最炫民族风",
						"Description" => "歌手：凤凰传奇",
						"MusicUrl" => "http://121.199.4.61/music/zxmzf.mp3",
						"HQMusicUrl" => "http://121.199.4.61/music/zxmzf.mp3" 
				);
			} else {
				$content = date ( "Y-m-d H:i:s", time () ) . "\n技术支持 fourAM";
			}
			
			if (is_array ( $content )) {
				if (isset ( $content [0] ['PicUrl'] )) {
					$result = $this->transmitNews ( $object, $content );
				} else if (isset ( $content ['MusicUrl'] )) {
					$result = $this->transmitMusic ( $object, $content );
				}
			} else {
				$result = $this->transmitText ( $object, $content );
			}
		}
		
		return $result;
	}
	
	// 接收图片消息
	private function receiveImage($object) {
		$content = array (
				"MediaId" => $object['MediaId'] 
		);
		$result = $this->transmitImage ( $object, $content );
		return $result;
	}
	
	// 接收位置消息
	private function receiveLocation($object) {
		$content = "你发送的是位置，纬度为：" . $object['Location_X'] . "；经度为：" . $object['Location_Y'] . "；缩放级别为：" . $object['Scale'] . "；位置为：" . $object['Label'];
		$result = $this->transmitText ( $object, $content );
		return $result;
	}
	
	// 接收语音消息
	private function receiveVoice($object) {
		if (isset ( $object['Recognition'] ) && ! empty ( $object['Recognition'] )) {
			$content = "你刚才说的是：" . $object['Recognition'];
			$result = $this->transmitText ( $object, $content );
		} else {
			$content = array (
					"MediaId" => $object['MediaId'] 
			);
			$result = $this->transmitVoice ( $object, $content );
		}
		
		return $result;
	}
	
	// 接收视频消息
	private function receiveVideo($object) {
		$content = array (
				"MediaId" => $object['MediaId'],
				"ThumbMediaId" => $object['ThumbMediaId'],
				"Title" => "",
				"Description" => "" 
		);
		$result = $this->transmitVideo ( $object, $content );
		return $result;
	}
	
	// 接收链接消息
	private function receiveLink($object) {
		$content = "你发送的是链接，标题为：" . $object['Title'] . "；内容为：" . $object['Description'] . "；链接地址为：" . $object['Url'];
		$result = $this->transmitText ( $object, $content );
		return $result;
	}
	
	// 回复设备消息
	private function transmitDevice($object, $content) {
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%u</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<DeviceType><![CDATA[%s]]></DeviceType>
		<DeviceID><![CDATA[%s]]></DeviceID>
		<SessionID>%u</SessionID>
		<Content><![CDATA[%s]]></Content>
	    </xml>";
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time (), 'device_text', $object['DeviceType'], $object['DeviceID'], $object['SessionID'], $content );
		return $result;
	}
	
	// 回复文本消息
	private function transmitText($object, $content) {
		$xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time (), $content );
		return $result;
	}
	
	// 回复图片消息
	private function transmitImage($object, $imageArray) {
		$itemTpl = "<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>";
		
		$item_str = sprintf ( $itemTpl, $imageArray ['MediaId'] );
		
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		$item_str
		</xml>";
		
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time () );
		return $result;
	}
	
	// 回复语音消息
	private function transmitVoice($object, $voiceArray) {
		$itemTpl = "<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
		</Voice>";
		
		$item_str = sprintf ( $itemTpl, $voiceArray ['MediaId'] );
		
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";
		
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time () );
		return $result;
	}
	
	// 回复视频消息
	private function transmitVideo($object, $videoArray) {
		$itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			</Video>";
		
		$item_str = sprintf ( $itemTpl, $videoArray ['MediaId'], $videoArray ['ThumbMediaId'], $videoArray ['Title'], $videoArray ['Description'] );
		
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		$item_str
			</xml>";
		
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time () );
		return $result;
	}
	
	// 回复图文消息
	private function transmitNews($object, $newsArray) {
		if (! is_array ( $newsArray )) {
			return;
		}
		$itemTpl = "    <item>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>
		";
		$item_str = "";
		foreach ( $newsArray as $item ) {
			$item_str .= sprintf ( $itemTpl, $item ['Title'], $item ['Description'], $item ['PicUrl'], $item ['Url'] );
		}
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";
		
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time (), count ( $newsArray ) );
		return $result;
	}
	
	// 回复音乐消息
	private function transmitMusic($object, $musicArray) {
		$itemTpl = "<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		</Music>";
		
		$item_str = sprintf ( $itemTpl, $musicArray ['Title'], $musicArray ['Description'], $musicArray ['MusicUrl'], $musicArray ['HQMusicUrl'] );
		
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";
		
		$result = sprintf ( $xmlTpl, $object['FromUserName'], $object['ToUserName'], time () );
		return $result;
	}
	
	// 回复多客服消息
	private function transmitService($object) {
		// 消息发送到企业客服
		$msgObj = $this->msgObj;
		$from = $object['FromUserName'];
		$msgObj->msg_2_kf($from, $object);
// 		$msgObj->msg_2_qy($object);
		
		$result = $this->transmitText($object, '正在为您转接客服，请稍后...');
		return $result;
		/*
		$xmlTpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[transfer_customer_service]]></MsgType>
				</xml>";
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		
		// 提示信息
		$wxLogic = new WxLogic();
		$wxLogic->send_custom_message( (string)$object->FromUserName, 'text', '正在为您转接客服代表' );
		
		return $result;
		*/
	}
	
	/**
	 * 用户关注事件
	 * @param unknown $object
	 * @param number $subscribe
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-12-8下午4:58:31
	 */
	public function subscribe($object, $subscribe=1){
	    $model = new UserModel();
	    $model->subscribe($object['FromUserName'], $subscribe);
	}
	
	/**
	 * 设备绑定
	 * @param unknown $object
	 * @param number $bind
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-12-8下午5:07:32
	 */
	private function deviceBind($object, $bind=2) {
		$data = array(
			'id'=>$object['DeviceID'],
			'status'=>$bind,
			'openid'=>$object['FromUserName'],
			'bind_time'=>time()
		);
		$model = D('Device');
		if ($model->create($data) !== false) {
			$model->save();
		}
	}
	
	// 日志记录
	private function logger($log_content) {
		if (isset ( $_SERVER ['HTTP_APPNAME'] )) { // SAE
			sae_set_display_errors ( false );
			sae_debug ( $log_content );
			sae_set_display_errors ( true );
		} else if ($_SERVER ['REMOTE_ADDR'] != "127.0.0.1") { // LOCAL
			$max_size = 10000;
			$log_filename = "log/log.xml";
			if (file_exists ( $log_filename ) and (abs ( filesize ( $log_filename ) ) > $max_size)) {
				unlink ( $log_filename );
			}
			file_put_contents ( $log_filename, date ( 'H:i:s' ) . " " . $log_content . "\r\n", FILE_APPEND );
		}
	}
}
