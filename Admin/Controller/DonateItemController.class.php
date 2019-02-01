<?php

namespace Admin\Controller;

class DonateItemController extends CommonController {
	//页面
	public function index() {
		$model = M('s_donateitem');
		$count = $model->count();
		$PageModel = new \Think\Page($count, $this->pagenum);
		$PageModel->setConfig('prev', '上一页');
		$PageModel->setConfig('next', '下一页');
		$PageModel->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$this->assign('data' ,$model->limit($PageModel->firstRow.','.$PageModel->listRows)->order('id desc')->select());
		$this->assign('page' ,$PageModel->show());
		$this->display();
	}

	//创建套餐
	public function add() {
		if(!IS_POST) {
			$this->ajaxReturn([
					'code' => -1,
					'msg' => 'Bad Method Request'
					]);
		}
		if(!$this->verifyAddForm($_POST)) {
			$this->ajaxReturn([
					'code' => 0,
					'msg' => 'Missing Important Param'
					]);
		}
		$model = M('s_donateitem');
		$param = $_POST;
		$date =  date('Y-m-d H:i:s');
		$param['created_at'] = $date;
		$param['updated_at'] = $date;
		if($model->data($param)->add()) {
			$this->ajaxReturn([
					'code' => 2,
					'msg' => 'ok'
					]);
		}
		$this->ajaxReturn(['code' => -2]);
	} 

	//检验新建表单
	public function verifyAddForm(&$params) {
		if (isset($params['title']) && isset($params['price']) && isset($params['coin'])) {
			return true;
		}
		return false;
	}

	//删除套餐
	public function del() {
		if(!IS_POST) {
			$this->ajaxReturn([
					'code' => -1,
					'msg' => 'Bad Method Request'
					]);
		}
		$id = I('post.id');
		if(isset($id)) {
			$model = M('s_donateitem');
			if($model->count() <= 4) {			
				$this->ajaxReturn(['code' => -2]);
			}
			if($model->where(['Id' => (int)$id])->delete() > 0) {
				$this->ajaxReturn([
						'code' => 2,
						'msg' => 'ok'
						]);
			}
		}
		$this->ajaxReturn(['code' => -2]);
	}
	
	//更新套餐
	public function upd() {
		if(!IS_POST) {
                        $this->ajaxReturn([
                                        'code' => -1,
                                        'msg' => 'Bad Method Request'
                                        ]);
                }
		$id = I('post.id');
		$params = I('post.');
		$date = date('Y-m-d H:i:s');

		if(isset($id)) {
			unset($params['id']);
			$params['updated_at'] = $date;
				$model = M('s_donateitem');
			if ($model->where('id='.(int)$id)->data($params)->save() > 0) {
				 $this->ajaxReturn([
                                                'code' => 2,
                                                'msg' => 'ok'
                                                ]);

			}
		}	
	
		$this->ajaxReturn(['code' => -2]);
	}

}
