<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    // create时不对created_at和updated_at做操作
//     public $timestamps = false;
    // 指定主键为非递增或非数字
//     public $incrementing = false;
    /**
     * 模型的日期字段保存格式。
     *
     * @var string
     */
    protected $dateFormat = 'U';
    protected $hidden = ['deleted_at'];
    
    
    public function __construct()
    {
        parent::__construct();
        if (!$this->table)
        {
            $this->table = $this->getTableName();
        }
    }
    
    
    
    private function getTableName()
    {
        $className = get_class($this);
        $table = substr($className, strripos($className, '\\') + 1);
        $table = str_replace('Model', '', $table);
        $temp = preg_split('/(?=[A-Z])/', $table);
        $arr = array_map(function($item){
            return lcfirst($item);
        }, $temp);
            
        return $this->table = trim(implode('_', $arr), '_');
    }
    
}
