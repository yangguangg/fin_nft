<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class GoodsUsers extends Backend
{
    
    /**
     * GoodsUsers模型对象
     * @var \app\admin\model\GoodsUsers
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\GoodsUsers;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("isShowList", $this->model->getIsShowList());
        $this->view->assign("isDelList", $this->model->getIsDelList());
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->where(['goods_users.is_del'=>0])
                    ->with(['users','goods'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','goods_number','price','status','is_show','create_time','order']);
                $row->visible(['users']);
				$row->getRelation('users')->visible(['phone']);
				$row->visible(['goods']);
				$row->getRelation('goods')->visible(['name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function del($ids = "")
    {
        $result = $this->model->where(['id'=>['in',$ids]])->update(['is_del'=>1]);
        if($result) return json(['code'=>1,'msg'=>'删除成功']);
        return json(['code'=>0,'msg'=>'删除失败']);
    }
    public function add()
    {
        if(request()->isPost()){
            $data = input('post.');
            $data = $data['row'];
            $phone = $data['phone'];
            $user = (new \app\admin\model\Users())->where(['phone'=>$phone,'is_del'=>0])->find();
            if(empty($user)) return json(['code'=>0,'msg'=>'会员手机号错误']);
            $goods['goods_number'] = uniqueNum();
            $goods['uid'] = $user['id'];
            $goods['goods_id'] = $data['goods_id'];
            $goods['price'] = $data['price'];
            $goods['order'] = $data['order'];
            $goods['status'] = $data['status'];
            $goods['create_time'] = date('Y-m-d H:i:s');
            $result = $this->model->insertGetId($goods);
            if($result) return json(['code'=>1,'msg'=>'添加成功']);
            return json(['code'=>0,'msg'=>'添加失败']);
        }
        return $this->fetch();
    }

}
