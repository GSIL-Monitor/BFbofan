<?php
namespace Index\Controller;

use Index\Model\UserModel;
use Think\Controller;

class SigninController extends CommonController {
    public $modeleid = 1;
	/*
	{:L('newworld_home')}
	 */
    

    //签到页面
    public function sign(){

	if (!$_SESSION['userid']){
            session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
            Header("Location:".U('Index/Login/login'));
            exit();
        }       		

	    $day=date("Y-m-d");

	    $date = date("Y-m-d",strtotime(" -1day",strtotime($day)));		




	   
    	$map['date'] = array("LIKE", '%' . $date . '%','user_id='.$_SESSION['userid']);
		     
		$signs=M("u_sign")->where($map)->find();	
		
		//统计累计签到	
		
		$month=date("Y-m");

		$wh=array(
			'month'=> ["LIKE", "%" . $month . "%"],
			'user_id'=>$_SESSION['userid']	
		);
		$count=M("u_sign")->where($wh)->count();


		
		//判断有没有连续签到
		if(empty($signs)){
			$days=date("Y-m-d");
			$map['date'] = array("LIKE", '%' . $days . '%','user_id='.$_SESSION['userid']);
     
			$signs=M("u_sign")->where($map)->find();	

			if($signs){

				$sign=M("u_user")->where(array('user_id'=>$_SESSION['userid']))->save(array('user_signday'=>1));
			
			}else{
			
				$sign=M("u_user")->where(array('user_id'=>$_SESSION['userid']))->save(array('user_signday'=>0));
			}

			
				
		}
			

		$user=M("u_user")->where(array('user_id'=>$_SESSION['userid']))->find();


		$date_time=date("Y-m-d");
 		$map['date'] = array("LIKE", '%' . $date_time . '%','user_id='.$_SESSION['userid']);
     
		$signs=M("u_sign")->where($map)->find();
		if($signs){
			$this->assign("signs",$signs);
		}
		
		$this->assign('user',$user);
		$this->assign("count",$count);
		$this->display();
   
    }


 
 	public function sign_in(){
		$date = I('post.');
        $user_id = $_SESSION['userid'];
        $date = date('Y-m-d');
        $dateArr = explode('-', $date);
        $day = $dateArr[2];
        $ym = $dateArr[0] . '-' . $dateArr[1];
        $coin = 0;
        $usermodel = new UserModel();
        $signInLog = M('u_sign')->where([
            'user_id' => $user_id,
            'date' => ["LIKE", "%" . date('Y-m') . "%"],
        ])->select();
        $signToday = M('u_sign')->where([
            'user_id' => $user_id,
            'date' => ["LIKE", "%" . date('Y-m-d') . "%"],
        ])->find();
        // 昨日是否签到
        if ((strtotime($date) - strtotime($signInLog[$signInCount - 1]['date'])) / 86400) {
			$usermodel->updataone(['user_id' => $user_id], ['user_signday' => 0]);
			$signInLen = 0;
        } else {
			$signInLen = $usermodel->findone(['user_id' => $user_id], 'user_signday')['user_signday']; //连续签到长度
		}
        $signInCount = count($signInLog); //签到累积
        // 今日是否签到
        if ($signToday !== null) {
            $this->ajaxReturn('2');
        }
        // 连续签到积分计算
	$coin += M('s_coinrules')->where([
		'type' => 7,
		'min' => ['egt', $signInLen],
		'max' => ['elt', $signInLen]
	])->find()['coin'];
	// 累计签到	
	if($ruleModel = M('s_coinrules')->where([
		'type' => 8,
		'min'  => $signInCount]
	])->find()) {
		$coin += $ruleModel['coin'];
	}
        
	if (M('u_sign')->add([
            'user_id' => $user_id,
            'date' => date('Y-m-d H:i:s'),
            'jifen' => $coin,
            'month' => $ym,
            'day' => $day,
        ])) {
            M("u_user")->where(array('user_id' => $user_id))->setInc('user_havecoin', $coin);
            M("u_user")->where(array('user_id' => $user_id))->setInc('user_signday', 1);
            $this->ajaxReturn('1');
        }

 	}


 	public function sign_day(){

 		$wh=array(
 			'user_id'=>$_SESSION['userid'],
			'date' => ["LIKE", '%'.date('Y-m').'%']
 		);
 		$signday=M("u_sign")->where($wh)->select();

 		if($signday){

 			$this->ajaxReturn($signday);
 		
 		}
 	
 	}

 	//查询上个月的记录
 	public function sign_up(){


 		$date=I('post.');
 		$month=$date['date'];
 		$date = date("Y-m",strtotime(" -1month",strtotime($month)));		

 		$wh=array(
 			'user_id'=>$_SESSION['userid'],
 			'month'  =>["LIKE", "%".$date."%"],
 		);
 		$signday=M("u_sign")->where($wh)->select();
 		
 		if($signday){
 			$this->ajaxReturn($signday);
 		}else{
 			$this->ajaxReturn([]);
 		}

 	}
	//查询下个月的记录
 	public function lower(){ 	
 		$date=I('post.');

 		$month=$date['date'];


 		$date = date("Y-m",strtotime(" +1month",strtotime($month)));		


 		
 		$wh=array(
 			'user_id'=>$_SESSION['userid'],
 			'month'=> ["LIKE", "%".$date."%"],
 		);
 		$signday=M("u_sign")->where($wh)->select();

 	
 		if($signday){

 			$this->ajaxReturn($signday);
 		
 		}else{
 			$this->ajaxReturn($signday);
 		}

 	}
	
	public function log() {
		if (!$_SESSION['userid']) {
            		session('returnurl', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
            		Header("Location:" . U('Index/Login/login'));
            		exit();
       	 	}
		$model = M('u_sign')->where(['user_id' => $_SESSION['userid']]);
		$this->assign("logcount", $model->count());		
		$this->display();		
	}

	public function log_data() {
		if (IS_POST) {

            if (!isset($_POST['limit1']) || $this->post('limit1') == 0) {

                die(json_encode(array('str' => 3, 'msg' => '请选择正确的页码')));
            } else if (!isset($_POST['limit2']) || $this->post('limit2') == 0) {

                die(json_encode(array('str' => 4, 'msg' => '请选择正确的页码')));
            } else {
                $limit1 = ($this->post('limit1') - 1) * $this->post('limit2');
                $res = M('u_sign')->where(['user_id' => $_SESSION['userid']])->order('id desc')->limit($limit1 ,$this->post('limit2'))->select();
		if ($res) {

                    die(json_encode(array('str' => 1, 'msg' => $res)));
                } else {

                    die(json_encode(array('str' => 2, 'msg' => '你暂无记录')));
                }

            }
        	} else {

            	die(json_encode(array('str' => 0, 'msg' => '存在非法字符')));
        	}
	}
}
