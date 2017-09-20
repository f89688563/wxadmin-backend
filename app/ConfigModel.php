<?php

namespace App;

class ConfigModel extends BaseModel
{
    // 允许所有字段批量赋值
    protected $guarded = [];
    
    public function getConfig($name='')
    {
        if ($name)
        {
            $config = $this->where('name', $name)->first();
        } else {
            $config = $this->get();
        }
        $config = $config ? $config->toArray() : [];
        if ($config)
        {
            foreach ($config as $v)
            {
                $temp[$v['name']] = $v;
            }
            $config = $temp;
        }
        return $config;
    }
    
}
