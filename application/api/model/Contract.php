<?php

namespace app\api\model;

use think\Model;

class Contract extends Model
{
    // 设置当前模型对应的完整数据表名称
    //protected $table = 'mk_contract';
    // 设置json类型字段
    protected $json = ['info'];
	protected $pk = 'id';
	protected $autoWriteTimestamp = true;  //开启自动写入时间  create_time和update_time两个字段的值，默认为整型（int）

    // 模型初始化
    protected static function init()
    {
        //TODO:初始化内容
    }	
}
