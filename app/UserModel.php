<?php
namespace App;

use App\Http\Logic\WxLogic;

class UserModel extends BaseModel
{
    // 屏蔽字段批量赋值
    protected $guarded = ['id', 'deleted_at'];
    
    public function wxinfo($openid)
    {
        $wxLogic = new WxLogic();
        $info = $wxLogic->get_user_info($openid);
        return $info;
    }
    
    public function subscribe($openid, $subscribe=1)
    {
        $where = [
            ['openid', '=', $openid]
        ];
        $info = $this->withTrashed()->where($where)->first();
        if ($info) {
            if ($info->trashed())
            {
                $subscribe and $info->restore();
            } else {
                !$subscribe and $info->delete();
            }
        } else{
            $info = array(
                'openid'=>$openid
            );
            $this->fill($info)->save();
        }
    }
}
