<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class CoinruleController extends CommonController
{

	public function coinrule_base(){

		$coinrulelist = M("s_coinrule")->select();
		$this->assign(array(
					'coinrulelist'=>$coinrulelist
				   ));
		$this->display();
	}
	
	// 基础规则管理页面
	public function base()
	{
		$type = I('get.type');
                if(strlen($type) == 0) $type = 1;
                $model = M('s_coinrules')->where(['type' => $type]);
                $count = $model->count();
                $page = new \Think\Page($count, 10);
                $page->setConfig('prev', '上一页');
                $page->setConfig('next', '下一页');
                $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
                $this->assign('data', $model->where(['type' => (int)$type])->limit($PageModel->firstRow.','.$PageModel->listRows)->order('id desc')->select());
                $this->assign('page' ,$page->show());
                $this->assign('type' ,$type);

		$this->display();
	}

	// 签到积分管理页面
	public function signIn()
	{
		$type = I('get.type');
		if(strlen($type) == 0) $type = 7;
		$model = M('s_coinrules')->where(['type' => $type]);
		$count = $model->count();
		$page = new \Think\Page($count, 10);
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$this->assign('data', $model->where(['type' => (int)$type])->limit($PageModel->firstRow.','.$PageModel->listRows)->order('id desc')->select());
		$this->assign('page' ,$page->show());
		$this->assign('type' ,$type);
		$this->display();
	}

	//添加规则
	public function ajax_add()
	{	
		if(!IS_POST) {
                        $this->ajaxReturn(['code' => -1, 'msg' => 'Bad Method Request']);
                }
		$model = M('s_coinrules');
		$param = $_POST;
		$date =  date('Y-m-d H:i:s');
		if($model->data([
			'coin' => (int)$param['coin'],
			'max' => (int)$param['max'],
			'min' => (int)$param['min'],
			'type'=> (int)$param['type'],
			'created_at' => $date
		])->add()) {
			$this->ajaxReturn([
					'code' => 2,
					'msg' => 'ok'
					]);
		}
		$this->ajaxReturn(['code' => -2]);
	}

	//更新规则
	public function ajax_upd()
	{
		if(!IS_POST) {
                        $this->ajaxReturn(['code' => -1, 'msg' => 'Bad Method Request']);
                }
		$id = I('post.id');
		$params = I('post.');
		$date = date('Y-m-d H:i:s');
		if(isset($id)) {
			unset($params['id']);
			$params['updated_at'] = $date;
				$model = M('s_coinrules');
			if ($model->where('id='.(int)$id)->data($params)->save() > 0) {
				 $this->ajaxReturn([
                                                'code' => 2,
                                                'msg' => 'ok'
                                                ]);
			}
		}	
	
		$this->ajaxReturn(['code' => -2]);
	}

	// 删除规则
	public function ajax_del()
	{ 
		if(!IS_POST) {
			$this->ajaxReturn(['code' => -1, 'msg' => 'Bad Method Request']);
		} 
		$id = I('post.id');
		if(isset($id)) {
			if(M('s_coinrules')->where(['id' => (int)$id])->delete() > 0) 
			{	
				$this->ajaxReturn(['code' => 2]);
			}
		}
		$this->ajaxReturn(['code' => -2]);
	}

	public function ajax_coinrule_base_set(){

		$data0 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num0'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin0'],
			      );
		$data1 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num1'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin1'],
			      );
		$data2 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num2'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin2'],
			      );
		$data3 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num3'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin3'],
			      );
		$data4 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num4'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin4'],
			      );
		$data5 = array(
				's_coinrule_num' =>  $_POST['s_coinrule_num5'],
				's_coinrule_setcoin' =>  $_POST['s_coinrule_setcoin5'],
			      );
		M("s_coinrule")->startTrans();
		try {
			M("s_coinrule")->where('s_coinrule_id = 1')->save($data0);
			M("s_coinrule")->where('s_coinrule_id = 2')->save($data1);
			M("s_coinrule")->where('s_coinrule_id = 3')->save($data2);
			M("s_coinrule")->where('s_coinrule_id = 4')->save($data3);
			M("s_coinrule")->where('s_coinrule_id = 5')->save($data4);
			M("s_coinrule")->where('s_coinrule_id = 6')->save($data5);
			M("s_coinrule")->commit();
			die(json_encode(array('str' => 1, 'msg' =>'修改成功')));
		}catch (Exception $e){
			M("s_coinrule")->rollback();
			die(json_encode(array('str' => 2, 'msg' =>'修改失败')));
		}


	}


}
