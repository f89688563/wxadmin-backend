<?php
/**
 * author : panfeng
 * email : 89688563@qq.com
 * date : 2016-11-15
 * charset : UTF-8
 */
namespace App\Http\Logic;

use Illuminate\Support\Facades\DB;
use App\MsgModel;

class MessageLogic
{
	var $wxLogic;
	var $qyLogic;
	var $config = [
	    'wx_app_id'        => '',
	    'wx_secret_key'    => ''
	];
	public function __construct($config=null)
	{
	    $config and $this->config = $config;
		$this->wxLogic = new WxLogic($this->config['wx_app_id'], $this->config['wx_secret_key']);
// 		$this->qyLogic = new QyLogic($config['qy_app_id'], $config['qy_secret_key']);
	}
	
	// 发送消息到微信客服
	public function msg_2_kf($from, $object)
	{
	    $kf = $this->isInservice($from);
	    if (!$kf)
	    {
	        // 随机分配客服
	        $kfList = $this->wxLogic->get_kf_list();
	        $kfList = $kfList['kf_list'];
	        $key = mt_rand(0, count($kfList)-1);
	        $kfInfo = $kfList[$key];
	        
	    }
	    
	    $msgType = $object['MsgType'];
	    $msg = [
	        'touser'   => $kf
	    ];
	    switch ($msgType)
	    {
	        case 'text':
	            $this->wxLogic->msg_2_kf($from, $content, $kf);
	            break;
	    }
	}
	
	/**
	 * 发送消息到企业号客服
	 * @param unknown $object
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-15下午2:44:32
	 */
	public function msg_2_qy($object)
	{
	    $this->qyLogic->msg_2_kf('', $object);
	    $this->saveMsg($object, 'pf');
	}
	
	public function msg_2_cus($xml)
	{
		$obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$res = json_decode(json_encode($obj), true);
		
		$agentType = $res['AgentType'];
		
		if (!$res['Item'][0])
		{
			$res['Item'] = array($res['Item']);
		}
		
		foreach ($res['Item'] as $v)
		{
			$kf = $v['FromUserName'];
			$msgType = $v['MsgType'];
			$content = $v['Content'];
			$toUser = $v['Receiver']['Id'];
			
			switch ($agentType)
			{
				case 'kf_external':
					$r = $this->wxLogic->send_custom_message($v);
// 					Log::write($r);
// 					file_put_contents('log.txt', $r."\r\n", FILE_APPEND);
					break;
				case 'kf_internal':
					break;
			}
			
			// 保存记录
			$this->saveMsg($v, $toUser);
			
			// 开启服务
			if ( ! $this->isInservice($toUser) )
			{
				$this->saveService($toUser, $kf);
			}
			// 关闭服务
			if ($content == '关闭')
			{
				$this->saveService($toUser, null);
			}
		}
		
		echo $res['PackageId'];
	}
	
	/**
	 * 保存消息数据
	 * @param unknown $msg
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-15下午3:36:19
	 */
	public function saveMsg($msg, $toUser)
	{
		$data['to'] = $toUser;
		$data['from'] = $msg['FromUserName'];
		$data['create_time'] = $msg['CreateTime'];
		$data['type'] = $msg['MsgType'];
		$data['msg_id'] = $msg['MsgId'];
		switch ($msg['MsgType'])
		{
			case 'text':
				$data['content'] = $msg['Content'];
				break;
			case 'image':
				$data['media_id'] = $msg['MediaId'];
				$data['img'] = $msg['PicUrl'];
				break;
		}
		
		$model = new MsgModel();
		$model->insert($data);
	}
	
	// 记录当前用户的客服
	public function saveService($user, $kf)
	{
	    $name = $this->serviceCacheName($user);
	    $data = [$name=>$kf];
	    cache($data, 86400*2);
	}
	
	private function serviceCacheName($openid)
	{
	    return "service_$openid";
	}
	
	/**
	 * 判断是否在服务中
	 * @param unknown $openid
	 * @author : panfeng <89688563@qq.com>
	 * time : 2016-11-15下午2:43:09
	 */
	public function isInservice($openid)
	{
	    $cache = cache($this->serviceCacheName($openid));
		return $cache;
	}
	
}