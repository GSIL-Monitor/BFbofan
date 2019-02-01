<?php
namespace Index\Controller;
use Index\Model\AdvertisingModel;
use Index\Model\CoinruleModel;
use Index\Model\CrowdMemberModel;
use Index\Model\CrowdModel;
use Index\Model\CrowdTabModel;
use Index\Model\FirstMarkModel;
use Index\Model\ModuleModel;
use Index\Model\NewsModel;
use Index\Model\NoteCommentModel;
use Index\Model\NoteModel;
use Index\Model\NoteVIModel;
use Index\Model\QuestionModel;
use Index\Model\ResourceModel;
use Index\Model\SecondMarkModel;
use Index\Model\ThirdMarkModel;
use Index\Model\FourthMarkModel;
use Index\Model\UserModel;
use Think\Controller;
class RnterstController extends CommonController {
	public $modeleid = 2;

	/*
	   兴趣{:L('newworld_home')}
	 */
	public function interest(){

		$firstmodel = new FirstMarkModel();
		$secondmodel = new SecondMarkModel();
		$crodmodel = new CrowdModel();
		$firstlist = $firstmodel->findlist('first_mark_mid = '.$this->modeleid.' and first_mark_type = 1','firsth_mark_sort');
		$data = array();
		foreach ($firstlist as $firthkey =>$first){

			$data[$firthkey] = $first;
			$secondlist = $secondmodel->findlist('second_mark_fid = '.$first['first_mark_id'],'second_mark_sort');
			$data[$firthkey]['message'] = $secondlist;
		}

		$crodlist = $crodmodel->findlist('crowd_mid = '.$this->modeleid,'u_user u on u_crowd.crowd_uid = u.user_id','INNER','crowd_creattime desc','u.user_name,u_crowd.*');

		$crowdcount = $crodmodel->findone('crowd_mid = '.$this->modeleid,'','','count(*) num')['num'];

		$modulemodel = new ModuleModel();
		$moduleone = $modulemodel->findone('module_id = '.$this->modeleid);

		$now = date("Y-m-d H:i:s", time());
		$newsmodel = new NewsModel();
		$newslist = $newsmodel->findlist('news_static = 1 and news_for = 1 and news_mid = '.$this->modeleid.' and news_endtime > "'.$now.'"','news_sort desc');

		$advertisingmodel = new AdvertisingModel();
		$advertisinglist = $advertisingmodel->findlist('advertising_for = 1 and advertising_mid = '.$this->modeleid.' and advertising_finishtime > "'.$now.'"');

		session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
		$this->assign(array(
					'moduleone' => $moduleone,
					'newslist' => $newslist,
					'advertisinglist' => $advertisinglist,
					'data' =>$data,
					'crodlist'=>$crodlist,
					'crowdcount' => $crowdcount,
				   ));
		$this->display();
	}

	//新闻详情
	public function newsDetails(){

		$newsmodel = new NewsModel();
		$newsone = $newsmodel->findone('news_id = '.$_GET['nid']);

		$this->assign(array(
					'newsone' => $newsone,

				   ));
		$this->display();
	}

	/*
	   创建兴趣群
	 */
	public function createInterest(){

		if (!$this->userid){
			session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			Header("Location:".U('Index/Login/login'));
			exit();
		}

		$data = array();

		$firstmodel = new FirstMarkModel();
		$secondmodel = new SecondMarkModel();
		$firstlist = $firstmodel->findlist('first_mark_mid = '.$this->modeleid.' and first_mark_type = 1','firsth_mark_sort');
		foreach ($firstlist as $firthkey =>$first){

			$data[$firthkey] = $first;
			$secondlist = $secondmodel->findlist('second_mark_fid = '.$first['first_mark_id'],'second_mark_sort');

			$data[$firthkey]['message'] = $secondlist;

		}

		$this->assign(array(
					'data' => $data,

				   ));
		$this->display();
	}

	/*
	   兴趣群详情
	 */
	public function groupDetails(){

		if (!isset($_GET['cid'])){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}

		$nid=$_GET['nid'];
		if($_GET['nid']){


			$top=M("u_note")->where(array('note_top_status'=>1))->find();

			$wh=array(
					'note_id'=>$top['note_id'],
					'note_top_status'=>0
				 ); 

			$note=M("u_note")->save($wh);



			$wh=array(
					'note_id'=>$_GET['nid'],
					'note_top_status'=>1
				 );
			$notes=M("u_note")->save($wh);





		}


		$crowdmodel = new CrowdModel();
		$crowdone = $crowdmodel->findone('crowd_id = '.$_GET['cid'],'u_user u on u_crowd.crowd_uid = u.user_id','INNER','u_crowd.*,u.user_name');
		if (!$crowdone){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}

		if ($crowdone['crowd_mid']!=$this->modeleid){
			if ($crowdone['crowd_mid']==1){
				Header("Location:".U('Index/Index/index'));
				exit();
			}
			if ($crowdone['crowd_mid']==2){
				Header("Location:".U('Index/Rnterst/interest'));
				exit();
			}
			if ($crowdone['crowd_mid']==3){
				Header("Location:".U('Index/Academic/academic'));
				exit();
			}
			if ($crowdone['crowd_mid']==4){
				Header("Location:".U('Index/Jobs/work'));
				exit();
			}
			if ($crowdone['crowd_mid']==5){
				Header("Location:".U('Index/Life/life'));
				exit();
			}
		}

		$crowdmembermodel = new CrowdMemberModel();
		$join_in = 0;
		if ($this->userid){

			$memberone = $crowdmembermodel->findone('crowd_member_cid = '.$_GET['cid'].' and crowd_member_uid = '.$this->userid);

			if ($memberone){
				$join_in = 1;
			}
		}
		$adminlist = $crowdmembermodel->findlistlimit('crowd_member_cid = '.$_GET['cid'].' and crowd_member_status !=0','u_user u on u_crowd_member.crowd_member_uid = u.user_id',0,4,'INNER','crowd_member_status desc','u_crowd_member.*,u.user_icon,u.user_id');
		$memberlist = $crowdmembermodel->findlistlimit('crowd_member_cid = '.$_GET['cid'].' and crowd_member_status =0','u_user u on u_crowd_member.crowd_member_uid = u.user_id',0,8,'INNER','crowd_member_status desc,crowd_member_logintime desc','u_crowd_member.*,u.user_icon,u.user_id');

		$notemodel = new NoteModel();
		//$questionmodel = new QuestionModel();
		//$resourcemodel = new ResourcesrceModel();

		$type=$_GET['new'];


		if($type==1){

			$notelist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 1','u_user u on u_note.note_uid = u.user_id','note_top_status desc,note_counr_zan desc,note_createtime desc',0,20);

		}elseif($type==2) {


			$notelist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 1','u_user u on u_note.note_uid = u.user_id','note_top_status desc,note_createtime desc',0,20);

		}else if($nid){

			$notelist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 1','u_user u on u_note.note_uid = u.user_id','note_top_status desc,note_iswally desc,note_createtime desc',0,20);

		}else{
			$notelist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 1','u_user u on u_note.note_uid = u.user_id','note_top_status desc,note_iswally desc,note_createtime desc',0,20);

		}

		$i=0;
		foreach ($notelist as $key => $value) { 

			$i++;
			$notelist[$key]['num']=$i; 
			$zan=explode(',',$value['note_counr_zans']);

			$aa=in_array($this->userid,$zan);

			if($aa){
				$notelist[$key]['zan']=1;
			}else{
				$notelist[$key]['zan']=0;
			}

		}

		//dump($notelist);exit;


		$notecount = $notemodel->joinone('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 1','u_user u on u_note.note_uid = u.user_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','count(*) num')['num'];

		/*$questionlist = $questionmodel->joinonelist('question_cid = '.$_GET['cid'].' and question_ishide = 1','u_user u on u_question.question_uid = u.user_id','question_istop desc,question_iswally desc,question_createtime desc',0,20);

		  $questioncount = $questionmodel->joinone('question_cid = '.$_GET['cid'].' and question_ishide = 1','u_user u on u_question.question_uid = u.user_id','question_istop desc,question_iswally desc,question_createtime desc','INNER','count(*) num')['num'];*/
		$questionlist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 2','u_user u on u_note.note_uid = u.user_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);

		$questioncount = $notemodel->joinone('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 2','u_user u on u_note.note_uid = u.user_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','count(*) num')['num'];

		/*$resourcelist = $resourcemodel->joinonelist('resource_cid = '.$_GET['cid'].' and resource_ishide = 1','u_user u on u_resource.resource_uid = u.user_id','resource_istop desc,resource_iswally desc,resource_createtime desc',0,20);

		  $resourcecount = $resourcemodel->joinone('resource_cid = '.$_GET['cid'].' and resource_ishide = 1','u_user u on u_resource.resource_uid = u.user_id','resource_istop desc,resource_iswally desc,resource_createtime desc','INNER','count(*) num')['num'];*/
		$resourcelist = $notemodel->joinonelist('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 3','u_user u on u_note.note_uid = u.user_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);

		$resourcecount = $notemodel->joinone('note_cid = '.$_GET['cid'].' and note_ishide = 1 and note_type = 3','u_user u on u_note.note_uid = u.user_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','count(*) num')['num'];

		$now = date("Y-m-d H:i:s", time());

		$newsmodel = new NewsModel();
		$newslist = $newsmodel->findlist('news_static = 1 and news_for = 2 and news_mid = '.$this->modeleid.' and news_endtime > "'.$now.'" and news_crowdid = '.$_GET['cid'],'news_sort desc');
		if (!$newslist){
			$newslist = $newsmodel->findlist('news_static = 1 and news_for = 2 and news_mid = '.$this->modeleid.' and news_endtime > "'.$now.'" and news_crowdid = 0','news_sort desc');
		}

		$advertisingmodel = new AdvertisingModel();
		$advertisinglist = $advertisingmodel->findlist('advertising_for = 2 and advertising_mid = '.$this->modeleid.' and advertising_finishtime > "'.$now.'" and advertising_crowdid = '.$_GET['cid']);
		if (!$advertisinglist){
			$advertisinglist = $advertisingmodel->findlist('advertising_for = 2 and advertising_mid = '.$this->modeleid.' and advertising_finishtime > "'.$now.'" and advertising_crowdid = 0');
		}

		$user_id=$_SESSION['userid'];


		if($crowdone['crowd_uid']==$user_id){


			$type=1;
			$this->assign(
					array('type'=>$type)
				     );
		}


		$secondmarkmodel = new SecondMarkModel();
		$thirdmarkmodel = new ThirdMarkModel();
		$fourthmarkmodel = new FourthMarkModel();
		$secondmarklist = array();
		$thirdmarklist = array();
		$fourthmarklist = array();
		if(isset($crowdone['crowd_secondmarks'])&&$crowdone['crowd_secondmarks']!='') {

			$secondmarklist = $secondmarkmodel->findlist('second_mark_id in (' . $crowdone['crowd_secondmarks'] . ')', '', '*');
					}
					if(isset($crowdone['crowd_thirdmarks'])&&$crowdone['crowd_thirdmarks']!='') {
					$thirdmarklist = $thirdmarkmodel->findlist('third_mark_id in (' . $crowdone['crowd_thirdmarks'] . ')', '', '*');
						}
						if(isset($crowdone['crowd_fourthmarks'])&&$crowdone['crowd_fourthmarks']!='') {
						$fourthmarklist = $fourthmarkmodel->findlist('fourth_mark_id in (' . $crowdone['crowd_fourthmarks'] . ')', '', '*');
							}

							session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
							$this->assign(array(
									'newslist' => $newslist,
									'advertisinglist' => $advertisinglist,
									'crowone' => $crowdone,
									'join_in' => $join_in,
									'adminlist' => $adminlist,
									'memberlist' => $memberlist,
									'cid' => $_GET['cid'],
									'notelist' => $notelist,
									'notecount' => $notecount,
									'questionlist' => $questionlist,
									'questioncount' => $questioncount,
									'resourcelist' => $resourcelist,
									'resourcecount' => $resourcecount,
									'secondmarklist' => $secondmarklist,
									'thirdmarklist' => $thirdmarklist,
									'fourthmarklist' => $fourthmarklist,

									));

							$this->display();
	}

	//新闻详情
	public function crowdnews(){

		$newsmodel = new NewsModel();
		$newsone = $newsmodel->findone('news_id = '.$_GET['nid']);

		$crowdmodel = new CrowdModel();
		$crowdone = $crowdmodel->findone('crowd_id = '.$_GET['cid']);

		$this->assign(array(
					'newsone' => $newsone,
					'cid' => $_GET['cid'],
					'crowdone' => $crowdone,
				   ));
		$this->display();
	}

	/*
	   分享
	 */
	public function postSharing(){

		$this->display();
	}

	/*
	   视频分享
	 */
	public function videoFen(){

		$this->display();
	}

	/*
	   兴趣帖子详情
	 */
	public function postDetails(){
		if (!$this->userid){
			session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			Header("Location:".U('Index/Login/login'));
			exit();
		}

		if (!isset($_GET['cid'])||$_GET['cid']==''){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}
		if (!isset($_GET['nid'])||$_GET['nid']==''){
			Header("Location:".U('Index/Rnterst/groupDetails')."&cid=".$_GET['cid']);
			exit();
		}
		$crowdmodel = new CrowdModel();
		$crowdmembermodel = new CrowdMemberModel();
		$notemodel = new NoteModel();
		$notevimodel = new NoteVIModel();
		$notecommentmodel = new NoteCommentModel();
		$usermodel = new UserModel();

		$nid=$_GET['nid'];

		if($_GET['nid']){

			$wh=array(
					'note_id'=>$nid,
					'notes_time'=>time()
				 );
			$note=M("u_note")->save($wh); 
		}

		$crowdone = $crowdmodel->findone('crowd_id = '.$_GET['cid']);

		if ($crowdone['crowd_mid']!=$this->modeleid){
			if ($crowdone['crowd_mid']==1){
				Header("Location:".U('Index/Index/index'));
				exit();
			}
			if ($crowdone['crowd_mid']==2){
				Header("Location:".U('Index/Rnterst/postDetails').'&cid='.$_GET['cid'].'&nid='.$_GET['nid']);
				exit();
			}
			if ($crowdone['crowd_mid']==3){
				Header("Location:".U('Index/Academic/postDetails').'&cid='.$_GET['cid'].'&nid='.$_GET['nid']);
				exit();
			}
			if ($crowdone['crowd_mid']==4){
				Header("Location:".U('Index/Jobs/postDetails').'&cid='.$_GET['cid'].'&nid='.$_GET['nid']);
				exit();
			}
			if ($crowdone['crowd_mid']==5){
				Header("Location:".U('Index/Life/postDetails').'&cid='.$_GET['cid'].'&nid='.$_GET['nid']);
				exit();
			}
		}

		$crowdmemberone = $crowdmembermodel->findone('crowd_member_cid = '.$_GET['cid'].' and crowd_member_uid = '.$this->userid.' and crowd_member_status != -1');
		$noteone = $notemodel->findone('note_id = '.$_GET['nid']);
		if ($noteone['note_cid']!=$_GET['cid']){
			Header("Location:".U('Index/Rnterst/groupDetails')."&cid=".$_GET['cid']);
			exit();
		}
		$vilist = $notevimodel->findlist('note_vi_nid = '.$_GET['nid'],'note_vi_sort desc');
		$commentlist = $notecommentmodel->joinonelist('note_comment_nid = '.$_GET['nid'],'u_user u on u_note_comment.note_comment_uid = u.user_id','note_comment_isanswer desc,note_comment_zans desc,note_comment_createtime desc',0,10);
		foreach ($commentlist as $key => $comment){
			$uidlist = explode(',',$comment['note_comment_zaner']);
			if (in_array($this->userid,$uidlist)){

				$commentlist[$key]['iszan'] = 1;
			}else{

				$commentlist[$key]['iszan'] = 0;
			}
		}
		$commentcount = $notecommentmodel->joinone('note_comment_nid = '.$_GET['nid'],'u_user u on u_note_comment.note_comment_uid = u.user_id','note_comment_zans desc,note_comment_createtime desc','INNER','count(*) num')['num'];
		$noteuser = $usermodel->findone('user_id = '.$noteone['note_uid']);

		// 问答超时处理
		if($noteone['note_type'] == 2 && $noteone['note_haveanswer'] == 0 && (strtotime(date('Y-m-d H:i:s')) - strtotime($noteone['note_createtime'])) > 864000) {
			$coinTotal = $noteone['note_reward'];
			$CommentRow = $notecommentmodel->where(['note_comment_nid' => $nid])->field('note_comment_uid')->select();
			if(count($CommentRow)) {
				$CommentRow = array_unique($CommentRow);
				$coin = $coinTotal / count($CommentRow);
				foreach ($CommentRow as $resuid) {
					$usermodel->where(['user_id' => $resuid])->setInc('user_havecoin', $coin);
				}
			} else {
				//无人回答 退还
				$usermodel->where(['user_id' => $noteone['note_uid']])->setInc('user_havecoin', $coinTotal);
			}
			$notemodel->where(['note_id' => $nid])->data(['note_haveanswer' => 1])->save();
		}



		$isjoin = 0;
		$ishave = 0;
		if ($crowdmemberone){
			$isjoin = 1;
		}
		if ($noteone['note_uid']==$this->userid){
			$ishave = 1;
		}

		$isdown = 0;
		$downloadlist = explode(',',$noteone['note_downloadmember']);
		if (in_array($this->userid, $downloadlist)){
			$isdown=1;
		}

		//浏览量判定 null 则 自增1  记录
		$nowdata = [];
		if (M('u_note_viewlog')->where(['userid' => $this->userid, 'noteid' => $nid])->find() === null) {
			M('u_note_viewlog')->data([
					'userid' => $this->userid,
					'noteid' => $nid,
					'created_at' => date('Y-m-d H:i:s'),
					])->add();

			$note_browses = $noteone['note_browses'] + 1;
			$nowdata['note_browses'] = $note_browses;
			$notemodel->updataone('note_id = ' . $_GET['nid'], $nowdata);

			//  判断浏览量 奖励积分
			$ruleModel = M('s_coinrules')->where('type = 1 and min = '.(int)$note_browses)->find();
			//$ruleModel = false;
			// 存在规则则奖励
			if($ruleModel) {
				$usermodel->where(['user_id' => $noteone['note_uid']])->setInc('user_havecoin', $ruleModel['coin']);
			}
		}

		//


		$coinrulemodel = new CoinruleModel();
		$coinruleone = $coinrulemodel->findone('s_coinrule_id = 1','*');


		$user_id=$_SESSION['userid'];
		if($crowdone['crowd_uid']==$user_id){
			$type=1;
			$this->assign(
					array('type'=>$type)
				     );
		}
		if ($noteone['note_type']==1&&(($noteone['note_browses']+1)%$coinruleone['s_coinrule_num'])==0){

			$noteuserdata = array(
					'user_havecoin'=>$noteuser['user_havecoin']+$coinruleone['s_coinrule_setcoin'],
					);
			$usermodel->updataone('user_id = '.$noteone['note_uid'],$noteuserdata);

		}

		$this->assign(array(
					'crowdone' => $crowdone,
					'isjoin' => $isjoin,
					'crowdmemberone' => $crowdmemberone,
					'noteone' => $noteone,
					'cid' => $_GET['cid'],
					'noteuser' => $noteuser,
					'ishave' => $ishave,
					'vilist' => $vilist,
					'isdown' => $isdown,
					'commentlist' => $commentlist,
					'commentcount' => $commentcount

				   ));


		$this->display();
	}


	/*
	   兴趣帖子详情(文字视频)
	 */
	public function postVideoDetails(){

		$this->display();
	}

	/*
	   兴趣问答详情
	 */
	public function questionAnswerDetails(){

		$this->display();
	}

	/*
	   兴趣资源详情
	 */
	public function resourceDetails(){

		$this->display();
	}

	/*
	   发布
	 */
	public function groupDetailsRelease(){




		if (!isset($_GET['cid'])){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}

		if (!$this->userid){
			session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			Header("Location:".U('Index/Login/login'));
			exit();
		}

		$crowdmodel = new CrowdModel();
		$crowdone = $crowdmodel->findone('crowd_id = '.$_GET['cid'],'');

		if (!$crowdone){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}

		if ($crowdone['crowd_mid']!=$this->modeleid){
			if ($crowdone['crowd_mid']==1){
				Header("Location:".U('Index/Index/index'));
				exit();
			}
			if ($crowdone['crowd_mid']==2){
				Header("Location:".U('Index/Rnterst/interest'));
				exit();
			}
			if ($crowdone['crowd_mid']==3){
				Header("Location:".U('Index/Academic/academic'));
				exit();
			}
			if ($crowdone['crowd_mid']==4){
				Header("Location:".U('Index/Jobs/work'));
				exit();
			}
			if ($crowdone['crowd_mid']==5){
				Header("Location:".U('Index/Life/life'));
				exit();
			}
		}

		/*$crowdmembermodel = new CrowdMemberModel();
		  $memberone = $crowdmembermodel->findone('crowd_member_cid = '.$_GET['cid'].' and crowd_member_uid = '.$this->userid);
		  if (!$memberone){
		  Header("Location:".U('Index/Rnterst/groupDetails').'&cid = '.$_GET['cid']);
		  exit();
		  }*/

		$crowdtabmodel = new CrowdTabModel();
		$tablist = $crowdtabmodel->findlist('','crowd_tab_sort');
		$this->assign(array(
					'tablist' => $tablist,
					'cid' => $_GET['cid'],
					'crowdone' => $crowdone,
				   ));

		$this->display();
	}

	/*
	   更多成员
	 */
	public function moreMembers(){

		if (!isset($_GET['cid'])){
			Header("Location:".U('Index/Rnterst/interest'));
			exit();
		}
		if (!$this->userid){
			session('returnurl', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			Header("Location:".U('Index/Login/login'));
			exit();
		}
		$crowdmodel = new CrowdModel();
		$crowdmembermodel = new CrowdMemberModel();

		$crowdone = $crowdmodel->findone('crowd_id = '.$_GET['cid']);

		$list = $crowdmembermodel->findlistlimit('crowd_member_cid = '.$_GET['cid'],'u_user u on u_crowd_member.crowd_member_uid = u.user_id',0,10,'INNER','crowd_member_status desc,crowd_member_logintime desc','u_crowd_member.*,u.user_icon,u.user_name');
		$listcount =$crowdmembermodel->findonejoin('crowd_member_cid = '.$_GET['cid'],'u_user u on u_crowd_member.crowd_member_uid = u.user_id','INNER','crowd_member_status desc,crowd_member_logintime desc','count(*) num')['num'];

		$adminlist = array();
		$memberlist = array();
		foreach ($list as $l){
			if ($l['crowd_member_status']!=0){
				$adminlist[]=$l;
			}else if ($l['crowd_member_status']==0){
				$memberlist[]=$l;
			}
		}

		$this->assign(array(
					'crowdone'=>$crowdone,
					'cid' => $_GET['cid'],
					'adminlist' => $adminlist,
					'memberlist' => $memberlist,
					'listcount' => $listcount,

				   ));

		$this->display();
	}


}
