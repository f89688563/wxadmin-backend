<?php
use App\ConfigModel;

// 获取系统配置
if (!function_exists('get_config'))
{
    function get_config($name='', $format=1)
    {
        $cache_name = 'wx_config';
        
        $config = cache($cache_name);
        
        if (!$config){
            $model = new ConfigModel();
            $config = $model->getConfig();
            cache([$cache_name => $config], 120);
        }
        
        $config = $name ? $config[$name] : $config;
        
        if ($format)
        {
            if ($name)
            {
                if ($config['type']==='array')
                {
                    $config['value'] = explode(';', $config['value']);
                }
            } else {
                foreach ($config as &$v)
                {
                    if ($v['type']==='array')
                    {
                        $v['value'] = explode(';', $v['value']);
                    }
                }
                unset($v);
            }
        }
        
        return $config;
    }
}