<?php

namespace app\admin\model;

use comservice\Response;
use think\Db;
use think\Model;


class GoodsUsers extends Model
{
    // 表名
    protected $name = 'goods_users';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'is_show_text',
        'is_del_text'
    ];

    public static function offShelve($id,$uid)
    {
        Db::startTrans();
        $order_num = Orders::where('buy_goods_id',$id)->count();
        if($order_num>0){
            return Response::fail('不可下架');
        }
        $gu = self::where('id',$id)->where('uid',$uid)->find();
        if($gu['status']!=2){
            return Response::fail('不可下架');
        }
        $gu->status = 1;
        $gu->save();
        Goods::where('goods_user_id',$gu['id'])->delete();
        Db::commit();
        return Response::success('下架成功');
    }


    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'),'5' => __('Status 5'), '6' => __('Status 6')];
    }

    public function getIsShowList()
    {
        return ['0' => __('Is_show 0'), '1' => __('Is_show 1')];
    }

    public function getIsDelList()
    {
        return ['0' => __('Is_del 0'), '1' => __('Is_del 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsShowTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_show']) ? $data['is_show'] : '');
        $list = $this->getIsShowList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsDelTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_del']) ? $data['is_del'] : '');
        $list = $this->getIsDelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function users()
    {
        return $this->belongsTo('Users', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function goods()
    {
        return $this->belongsTo('Goods', 'goods_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
