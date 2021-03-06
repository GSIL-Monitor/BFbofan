<?php
namespace Index\Controller;
use Index\Model\ConcernsModel;
use Index\Model\FirendsModel;
use Index\Model\FriendsModel;
use Index\Model\NoteModel;
use Index\Model\NoteVIModel;
use Index\Model\ResumeModel;
use Index\Model\UserCountryModel;
use Index\Model\UserModel;
use Think\Controller;
use Think\Model;
use Think\Upload;

class UserController extends CommonController {

    protected $_checkAction = ['followList','personalCenter','acountSetting','resumeDetails','myPosts','myMessage','myFollowing','addressBook'
                                    ,'myGroup','feedback','virtualCurrencyRecharge','fansList','DeliveryRecord','ResumeTemplateList'
                                    ,'FollowFriend','RefuseAddFirend','AgreeAddFirend','AddFriend','myFans','ChangeConcernsName','AddGroup','SetGroup','ChangeConcernsGroupName','SetFriendAlias','DeleteFriend','mineResume','createResume','DeleteResume','DeliveryResume','ClearMessages','MessageDetails','DissolveGroup','QuitGroup'];//需要做登录验证的action

    public function _initialize()
    {
        parent::_initialize();
        if(in_array(ACTION_NAME,$this->_checkAction)){
            if (!$this->userid && !IS_AJAX){
                session('returnurl', __SELF__);
                $this->redirect(U('Index/Login/login'));
            }elseif(!$this->userid){
                $this->error('The user has not landed！');
            }
        }
    }

    public function personalCenter(){
        $id = I('get.id','trim');

        if(!is_numeric($id)){
            $this->error('Parameter error！');
        }

        $usermodel = new UserModel();
        $userone = $usermodel->findone('user_id = '.$id);

        if(!$userone){
            $this->error('The user does not exist！');
        }

        if ($userone['user_concerns']>10000){
            $userone['user_concerns'] = floor($userone['user_concerns']/10000).'m';
        }
        if ($userone['user_fans']>10000){
            $userone['user_fans'] = floor($userone['user_fans']/10000).'m';
        }

        $firendsmodel = new FirendsModel();
        $firendone = $firendsmodel->findone('firends_uid = '.$id.' and firends_aid = '.$this->userid.' and firends_type IN('. FriendsModel::TYPE_FRIEND.','. FriendsModel::TYPE_BLACKLISTED.','. FriendsModel::TYPE_BEDELETED .')');

        $concernsmodel = new ConcernsModel();
        $concernsone = $concernsmodel->findone('concerns_uid = '.$this->userid.' and concerns_cuid = '.$id . ' and concerns_status=1');

        //好友请求
        $fir_message = D('Message')->where('(message_sid='.$this->userid.' AND message_uid='.$id.') OR (message_sid='.$id.' AND message_uid='.$this->userid.')')->getField('message_id'); //对方申请成为我的好友或我申请成为对方的好友

        //查找当前用户的好友分组
        $concerns_groups = D('ConcernsGroup')->where('concerns_group_uid='.$this->userid)->select();
        $this->assign('concerns_groups',$concerns_groups);
        $isme = 0;
        if ($id == $this->userid){
            $isme = 1;
        }

        $notemodel = new NoteModel();
        //$alllist = $notemodel->join('u_user u on u_note.note_uid = u.user_id','INNER')->join('u_crowd c on u_note.note_cid = c.crowd_id','INNER')->field(false)->where('note_ishide = 1 and note_uid = '.$_GET['id'])->order('note_istop desc,note_iswally desc,note_createtime desc')->limit(0,20)->select();
        $alllist = $notemodel->jointwolist('note_ishide = 1 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);
        $allcount = $notemodel->jointwoone('note_ishide = 1 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];


        $postlist = $notemodel->jointwolist('note_ishide = 1 and note_type = 1 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);
        $postcount = $notemodel->jointwoone('note_ishide = 1 and note_type = 1 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        $questionlist = $notemodel->jointwolist('note_ishide = 1 and note_type = 2 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);
        $questioncount = $notemodel->jointwoone('note_ishide = 1 and note_type = 2 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        $resourcelist = $notemodel->jointwolist('note_ishide = 1 and note_type = 3 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc',0,20);
        $resourcecount = $notemodel->jointwoone('note_ishide = 1 and note_type = 3 and note_uid = '.$_GET['id'],'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        $wh=array(
            'concerns_cuid'=>$id,
            'concerns_status'=>1
        );

        //fans
        
         $following= D('Concerns')->alias('c')->join('u_user u ON u.user_id = c.concerns_cuid','LEFT')->where('c.concerns_uid='.$id .' AND c.concerns_status=1')->count();

    
          

        $ch=array(
            'concerns_cuid'=>$id,
            'concerns_status'=>1
        );
        //following
        $fans=m("u_concerns")->where($ch)->count();


        

        $this->assign(array(
            'userone' => $userone,
            'isme' => $isme,
            'firendone' => $firendone,
            'concernsone' => $concernsone,
            'fir_message' => $fir_message,
            'alllist' => $alllist,
            'allcount' => $allcount,
            'postlist' => $postlist,
            'postcount' => $postcount,
            'questionlist' => $questionlist,
            'questioncount' => $questioncount,
            'resourcelist' => $resourcelist,
            'resourcecount' => $resourcecount,
            'following'=>$following,
            'fans'=>$fans
        ));
        $this->display();

    }

    public function acountSetting(){

        $countrymodel = new UserCountryModel();
        $countrylist = $countrymodel->findlist('','user_country_sort desc,user_country_name');

        $second_mark=M("s_second_mark")->where(array('second_mark_fid'=>28))->select();


        $second_label=M("s_second_mark")->where(array('second_mark_fid'=>29))->select();


        $this->assign(array(
            'countrylist' =>$countrylist,
            'second_mark'=>$second_mark,
            'second_label'=>$second_label
        ));
        $this->display();
    }

    public function resumeDetails(){

        $goindex = 1;
        if (isset($_GET['gourl'])){
            $goindex = 0;
        }
        $resume_id = I('get.resume_id','0','intval');

        $resumemodel = new ResumeModel();
        $resumeone = $resumemodel->findone('resume_id = '.$resume_id . ' AND resume_uid = '.$this->userid);

        if(!$resumeone){
            $this->error('The resume does not exist!');
        }

        $this->assign(array(
            'goindex' => $goindex,
            'resumeone' => $resumeone,
        ));
        $this->display();
    }

    public function myPosts(){

        $notemodel = new NoteModel();

        $notecount = $notemodel->jointwoone('note_ishide = 1 and note_type = 1 and note_uid = '.$this->userid,'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        $questioncount = $notemodel->jointwoone('note_ishide = 1 and note_type = 2 and note_uid = '.$this->userid,'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        $resourcecount = $notemodel->jointwoone('note_ishide = 1 and note_type = 3 and note_uid = '.$this->userid,'u_user u on u_note.note_uid = u.user_id','u_crowd c on u_note.note_cid = c.crowd_id','note_istop desc,note_iswally desc,note_createtime desc','INNER','INNER','count(*) num')['num'];

        
        $this->assign(array(
            'notecount'=>$notecount,
            'questioncount'=>$questioncount,
            'resourcecount'=>$resourcecount,

        ));
        $this->assign('title','My Posts');
        $css = addCss('myPosts');
        $this->assign('CSS',$css);
        $this->display();
    }

    public function editPost(){

        if (!isset($_GET['nid'])||$_GET['nid']==''){

            Header("Location:".U('Index/User/myPosts'));
            exit();
        }

        $notemodel = new NoteModel();
        $noteone = $notemodel->findone('note_id = '.$_GET['nid']);
        $notevimodel = new NoteVIModel();
        $notevilist = $notevimodel->findlist('note_vi_nid = '.$_GET['nid'],'note_vi_sort desc');
        $this->assign(array(
            'nid' => $_GET['nid'],
            'noteone' => $noteone,
            'notevilist' => $notevilist,
        ));
        $this->display();

    }


    public function myMessage(){
        $where = 'm.message_uid='.$this->userid;
        $k = I('get.k','','trim');
        if($k){
            $where .= ' AND m.message_title LIKE "%'.$k.'%"';
        }
        $count      = D('Message')->alias('m')->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $messages = D('Message')->alias('m')->field('m.*,u.user_name,u.user_icon')->join('u_user u ON u.user_id = m.message_sid','LEFT')->where($where)->order('message_delivertime desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('messages',$messages);
        $this->assign('page',$show);

        $this->assign('title','My Message');
        $css = addCss('MyMessage');
        $this->assign('CSS',$css);
        $this->display();
    }

    public function myFollowing(){
        //My group
        //
        

        $groups = D('ConcernsGroup')->where('concerns_group_uid='.$this->userid )->select();

        $wh=array(
            'crowd_member_uid'=>$_GET['uid']
        );
        $groups =M("u_crowd_member")
                ->alias('a')
                ->join('u_crowd as b on a.crowd_member_cid=b.crowd_id')
                ->where($wh)
                ->select();


            if($this->userid == $_GET['uid']){

                $type=1;
            }        




            $id=$_GET['uid'];


       
        $this->assign('groups',$groups);

        $count      = D('Concerns')->where('concerns_uid='.$this->userid .' AND concerns_status=1')->count();

        $Page       = new \Think\Page($count,5);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $myFollows = D('Concerns')->alias('c')->field('c.*,u.user_name,u.user_icon,u.user_signature')->join('u_user u ON u.user_id = c.concerns_cuid','LEFT')->where('c.concerns_uid='.$id .' AND c.concerns_status=1')->limit($Page->firstRow.','.$Page->listRows)->select();
       


         $myFollowse= D('Concerns')->alias('c')->join('u_user u ON u.user_id = c.concerns_cuid','LEFT')->where('c.concerns_uid='.$this->userid .' AND c.concerns_status=1')->count();

        
       
        $this->assign('myFollows',$myFollows);
        $this->assign('page',$show);
        
        $this->assign('type',$type);

        $this->display();
    }

    public function addressBook(){
        $where = 'firends_uid='.$this->userid .' AND firends_type IN('.FriendsModel::TYPE_FRIEND.','. FriendsModel::TYPE_BLACKLISTED.','. FriendsModel::TYPE_BEDELETED.')';

        $k = I('get.k','','trim');
        if($k){
            $where .= ' AND (firends_mark LIKE "%'.$k.'%" OR u.user_name LIKE "%'.$k.'%")';
        }

        $count      = D('Friends')->alias('f')->join('u_user u ON u.user_id = f.firends_aid','LEFT')->where($where)->count();
        $Page       = new \Think\Page($count,2);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $myFirends = D('Friends')->alias('f')->field('f.*,u.user_name,u.user_icon,u.user_signature')->join('u_user u ON u.user_id = f.firends_aid','LEFT')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('myFirends',$myFirends);
        $this->assign('page',$show);

        $this->display();
    }

    public function myGroup(){
        $type = I('get.type',0,'intval');
        $k = I('get.k','','trim');
        if(empty($type)){
            $where = 'crowd_uid='.$this->userid;

            if($k){
                $where .= ' AND crowd_name LIKE "%'.$k.'%"';
            }

            $count      = D('Crowd')->alias('c')->join('u_crowd_member cm ON cm.crowd_member_cid=c.crowd_id')->where($where)->count();
            $Page       = new \Think\Page($count,5);
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $show       = $Page->show();

            if($count){
                $groups = D('Crowd')->alias('c')->field('c.*,count(cm.crowd_member_id) as total')->join('u_crowd_member cm ON cm.crowd_member_cid=c.crowd_id')->where($where)->group('c.crowd_id')->limit($Page->firstRow.','.$Page->listRows)->select();
            }else{
                $groups = [];
            }

            $this->assign('groups',$groups);
            $this->assign('page',$show);
        }else{
            $joinWhere = 'cm.crowd_member_uid='.$this->userid.' AND cm.crowd_member_status<>2';

            if($k){
                $joinWhere .= ' AND c.crowd_name LIKE "%'.$k.'%"';
            }

            $count      = D('Crowd')->alias('c')->join('u_crowd_member cm ON cm.crowd_member_cid=c.crowd_id')->where($joinWhere)->count();
            $Page       = new \Think\Page($count,5);
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $show       = $Page->show();
            if($count){
                $joingroups = D('Crowd')->alias('c')->join('u_crowd_member cm ON cm.crowd_member_cid=c.crowd_id')->field('c.*,count(cm.crowd_member_id) as total')->where($joinWhere)->group('c.crowd_id')->limit($Page->firstRow.','.$Page->listRows)->select();
            }else{
                $joingroups = [];
            }

            $this->assign('groups',$joingroups);
            $this->assign('page',$show);
        }

        $this->assign('title','My Group');
        $css = addCss('MyGroup');
        $this->assign('CSS',$css);
        $this->display();
    }

    public function feedback(){


        $this->display();
    }

    public function virtualCurrencyRecharge(){

        $css = addCss('VCR');
        $this->assign('title','Virtual Currency Recharge');
        $this->assign('CSS',$css);
        $this->display();
    }

    /**
     * 我的关注列表
     * @return mixed
     */
    public function followList(){


        $groups = D('ConcernsGroup')->alias('cg')->field('cg.*,count(c.concerns_id) total')->join('u_concerns c ON c.concerns_gid = cg.concerns_group_id AND c.concerns_status=1','LEFT')->where('cg.concerns_group_uid='.$_GET['uid'] )->group('cg.concerns_group_id')->select();



        $this->assign('groups',$groups);
        $this->assign('groups_num',count($groups));

        $count      = D('Concerns')->where('concerns_uid='.$_GET['uid'] .' AND concerns_status=1')->count();
        $this->assign('count',$count);
        $Page       = new \Think\Page($count,2);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $myFollows = D('Concerns')->alias('c')->field('c.*,u.user_name,u.user_icon,u.user_signature,cg.concerns_group_name')->join('u_user u ON u.user_id = c.concerns_cuid','LEFT')->join('u_concerns_group cg ON c.concerns_gid = cg.concerns_group_id','LEFT')->where('c.concerns_uid='.$_GET['uid'] .' AND c.concerns_status=1')->group('c.concerns_id')->limit($Page->firstRow.','.$Page->listRows)->select();

       
        $this->assign('myFollows',$myFollows);
        $this->assign('page',$show);


        //查找当前用户的好友分组
        $concerns_groups = D('ConcernsGroup')->where('concerns_group_uid='.$this->userid)->select();
        $this->assign('concerns_groups',$concerns_groups);

        $css = addCss(['FollowList','common']);
        $this->assign('title','Follow List');
        $this->assign('CSS',$css);

        $this->display();
    }

    //粉丝列表
    public function fansList(){

        if (!isset($_GET['uid'])||$_GET['uid']==''){
            Header("Location:".U('Index/Index/index'));
            exit();
        }

        $concernsmodel = new ConcernsModel();
        $concernscount = $concernsmodel->findone('concerns_status = 1 and concerns_cuid = '.$_GET['uid'],'count(*) num')['num'];

        $this->assign(array(
            'uid'=>$_GET['uid'],
            'concernscount' => $concernscount,
        ));
        $this->display();

    }

    //帮助
    public function helpCenter(){

        $this->display();
    }

    //告诉我们 
    public function contactUs(){

        $this->display();
    }

     //我的简历
     public function mineResume(){
         $count      = D('Resume')->alias('r')->join('u_user u ON u.user_id = r.resume_uid','LEFT')->where('resume_uid='.$this->userid)->count();
         $Page       = new \Think\Page($count,10);
         $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
         $show       = $Page->show();
         $myResumes = D('Resume')->alias('r')->field('r.*,u.user_name,u.user_icon,u.user_signature')->join('u_user u ON u.user_id = r.resume_uid','LEFT')->where('resume_uid='.$this->userid)->limit($Page->firstRow.','.$Page->listRows)->select();
         $this->assign('myResumes',$myResumes);
         $this->assign('page',$show);
         $this->display();
    }

     //我的简历
     public function createResume(){
        if(IS_POST){
            $rules = array(
                array('resume_tid',array(1,2,3),'The scope of the value is not correct！',1,'in'),
                array('resume_position','require','Please select the position information！'),
                array('resume_workyear','number','Please enter the number of years of work！'),
                array('resume_id','number','Parameter error！',2),
                array('resume_workyear','require','Please enter a graduate school！'),
                array('resume_degree','require','Please enter a degree！'),
                array('resume_specialty','require','Please enter a major！'),
            );
            $Resume = D("Resume"); // 实例化User对象
            if (!$Resume->create($_POST)){ // 登录验证数据
                // 验证没有通过 输出错误提示信息
                exit($Resume->getError());
            }else{
                // 验证通过 执行登录操作
            }
        }

        $this->display('resumeDetails');
    }
    /**
     * 我的投递记录
     * @return mixed
     */
    public function DeliveryRecord(){
        $count      = D('ResumeDelivery')->alias('rd')->join('u_resume r ON r.resume_id = rd.resume_id','LEFT')->where('rd.user_id='.$this->userid)->count();
        $Page       = new \Think\Page($count,10);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $myDelivery = D('ResumeDelivery')->alias('rd')->field('rd.*,r.resume_name,j.works_position,u.user_mail')->join('u_resume r ON r.resume_id = rd.resume_id','LEFT')->join('j_works j ON j.works_id = rd.works_id','LEFT')->join('u_user u ON u.user_id = rd.user_id','LEFT')->where('rd.user_id='.$this->userid)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('myDelivery',$myDelivery);
        $this->assign('page',$show);

        $css = addCss('DeliveryRecord');
        $this->assign('title','Resume Details');
        $this->assign('CSS',$css);
        $this->display();
    }

    /**
     * 简历模板
     * @return mixed
     */
    public function ResumeTemplateList(){
        $templates = D('ResumeModule')->
        $count      = D('ResumeModule')->count();
        $Page       = new \Think\Page($count,9);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();
        $templates = D('ResumeModule')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('templates',$templates);
        $this->assign('page',$show);


        $css = addCss('ResumeTemplateList');
        $this->assign('title','Resume Template List');
        $this->assign('CSS',$css);
        $this->display();
    }


    public function myFans(){


        $where = 'c.concerns_status = 1 and c.concerns_cuid = '.$this->userid;
        $k = I('get.k','','trim');

    
        if($k){
            $where .= ' AND u.user_name LIKE "%'.$k.'%"';
        }

        $count      = D('Concerns')->alias('c')->join('u_user u ON u.user_id = c.concerns_uid','LEFT')->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();


        $myFans = D('Concerns')->alias('c')->field('c.*,u.user_name,u.user_icon,u.user_signature,c1.concerns_status as concernsStatus')->join('u_user u ON u.user_id = c.concerns_uid','LEFT')->join('u_concerns c1 ON c1.concerns_uid = c.concerns_cuid AND c1.concerns_cuid=c.concerns_uid','LEFT')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
     
       

        $this->assign('myFans',$myFans);
        $this->assign('page',$show);

        $css = addCss(['FansList','common']);
        $this->assign('title','My Fans');
        $this->assign('CSS',$css);
        $this->display();

    }

    /**
     * 添加好友
     */
    public function AddFriend(){
        $uid = I('post.id/d');
        if(IS_AJAX){

            if(D('Message')->findone(['message_uid'=>$uid,'message_sid'=>$this->userid])){
                echo json_encode(['status'=>1,'msg'=>'Submit success, wait for the other party to review!']);
                exit;
            }

            $data = ['message_uid'=>$uid,'message_sid'=>$this->userid,'message_title'=>'User:'.$this->usercontent['user_name'].' applies to be your friend','message_content'=>'User:'.$this->username.' applies to be your friend','message_type'=>1];

            if(D('Message')->addone($data)){
                echo json_encode(['status'=>1,'msg'=>'Submit success, wait for the other party to review!']);
                exit;
            }else{
                echo json_encode(['status'=>0,'msg'=>'Submit failed!']);
                exit;
            }
        }else{
            $this->error('Illegal operations!');
        }
    }

    public function AgreeAddFirend(){
        $id = I('post.id',0,'intval');
        $uid = I('post.uid',0,'intval');
        if($id <= 0 || $uid <= 0){
            $this->error('Parameter error！');
        }

        if(D('Friends')->addone(['firends_uid'=>$this->userid,'firends_aid'=>$uid])){
            if(D('Message')->updataone('message_id='.$id,['message_isread'=>'1'])){//同意后状态改为已读
                die(json_encode(['status'=>1,'msg'=>'Submit success!']));
            }else{
                die(json_encode(['status'=>0,'msg'=>'Submit failed!']));
            }
        }else{
            die(json_encode(['status'=>0,'msg'=>'Submit failed!']));
        }
    }

    public function RefuseAddFirend(){
        $id = I('post.id',0,'intval');
        if($id <= 0 && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($id <= 0){
            $this->error('Parameter error！');
        }

        if(D('Message')->updataone('message_id='.$id,['message_isread'=>'2'])){//拒绝好友
            die(json_encode(['status'=>1,'msg'=>'Submit success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Submit failed!']));
        }
    }

    public function FollowFriend(){
        $id = I('post.id',0,'intval');
        // if($id <= 0 && IS_AJAX){
        //     die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        // }elseif($id <= 0){
        //     $this->error('Parameter error！');
        // }

        $concern = D('Concerns')->where('concerns_uid = '.$this->userid.' and concerns_cuid = '.$id)->getField('concerns_status');

        $Model = new Model();
        $Model->startTrans();
        if(is_null($concern)){
            $is_handle = D('Concerns')->add(['concerns_uid'=>$this->userid,'concerns_cuid'=>$id,'concerns_gid'=>0,'concerns_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $concern = abs($concern - 1);
            $is_handle = D('Concerns')->updataone('concerns_cuid='.$id.' AND concerns_uid='.$this->userid,['concerns_status'=>$concern]);
        }

        if($concern){


            //更新用户的关注数
            $is_handle1 = D('User')->execute('UPDATE `u_user` SET `user_concerns`=`user_concerns`+1 WHERE user_id='.$this->userid);
            //更新被关注用户的粉丝数
            $is_handle2 = D('User')->execute('UPDATE `u_user` SET `user_fans`=`user_fans`+1 WHERE user_id='.$id);
        }else{
                

        $wh=array(
            'user_id'=>$this->userid
        );

         $user=M("u_user")->where($wh)->find();

           


            if($user['user_concerns']!=0 ){
                    

                  //更新用户的关注数
                $is_handle1 = D('User')->execute('UPDATE `u_user` SET `user_concerns`=`user_concerns`-1 WHERE user_id='.$this->userid);
               

            }
             if($user['user_fans']!=0){

                    $is_handle2 = D('User')->execute('UPDATE `u_user` SET `user_fans`=`user_fans`-1 WHERE user_id='.$id);
             }
          
        }
         $is_handle2=2;
         $is_handle1=1;

        if($is_handle && $is_handle1 && $is_handle2){
            $Model->commit();
            die(json_encode(['status'=>1,'msg'=>'Submit success!']));
        }else{
            $Model->rollback();
            die(json_encode(['status'=>0,'msg'=>'Submit failed!']));
        }
    }

    public function ChangeConcernsName(){
        $concerns_id = I('post.concerns_id',0,'intval');
        $concerns_name = I('post.concerns_name','','trim');
        if(($concerns_id <= 0 || empty($concerns_name)) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($concerns_id <= 0 || empty($concerns_name)){
            $this->error('Parameter error！');
        }

        if(strlen($concerns_name)>80){
            $this->error('Concerns_name can\'t exceed 80 characters！');
        }

        if(D('Concerns')->updataone('concerns_id='.$concerns_id,['concerns_name'=>$concerns_name])){
            die(json_encode(['status'=>1,'msg'=>'ChangName success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'ChangName failed!']));
        }
    }

    public function ChangeConcernsGroupName(){
        $concerns_group_id = I('post.concerns_group_id',0,'intval');
        $concerns_group_name = I('post.concerns_group_name','','trim');
        if(($concerns_group_id <= 0 || empty($concerns_group_name)) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($concerns_group_id <= 0 || empty($concerns_group_name)){
            $this->error('Parameter error！');
        }

        if(strlen($concerns_group_name)>80){
            $this->error('concerns_group_name can\'t exceed 80 characters！');
        }

        if(D('ConcernsGroup')->where('concerns_group_id = '.$concerns_group_id)->setField(['concerns_group_name'=>$concerns_group_name])){
            die(json_encode(['status'=>1,'msg'=>'ChangName success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'ChangName failed!']));
        }
    }


    public function DeleteConcernsGroup(){



        $concerns_group_id = I('post.concerns_group_id',0,'intval');


        $wh=array(
            'crowd_member_uid'=>$this->userid,
            'a.crowd_member_cid'=>$concerns_group_id
        );
        $groups =M("u_crowd_member")
                ->alias('a')
                ->join('u_crowd as b on a.crowd_member_cid=b.crowd_id')
                ->where($wh)
                ->find();

      
    
        if($groups['crowd_member_status']==0){


            $wh=array(
                'crowd_member_id'=>$groups['crowd_member_id']
            );
            $delete=M("u_crowd_member")->where($wh)->delete();

              if($delete){
                    die(json_encode(['status'=>1,'msg'=>'Delete success!']));
                }else{
                    die(json_encode(['status'=>0,'msg'=>'Delete failed!']));
                }

        }else if($groups['crowd_member_status']>0){
             $wh=array(
                'crowd_id'=>$groups['crowd_member_id']
            );
            $delete=M("u_crowd_member")->where($wh)->delete();
            if($delete){
                    die(json_encode(['status'=>1,'msg'=>'Delete success!']));
                }else{
                    die(json_encode(['status'=>0,'msg'=>'Delete failed!']));
                }
        }     
         
        exit;

        if($concerns_group_id <= 0 && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($concerns_group_id <= 0){
            $this->error('Parameter error！');
        }

        if(D('ConcernsGroup')->where('concerns_group_id='.$concerns_group_id)->delete()){
            die(json_encode(['status'=>1,'msg'=>'Delete success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Delete failed!']));
        }
    }

    public function AddGroup(){
        $group_name = I('post.group_name','','trim');

        if(empty($group_name) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif(empty($group_name)){
            $this->error('Parameter error！');
        }

        if(strlen($group_name)>80){
            $this->error('concerns_group_name can\'t exceed 80 characters！');
        }

        if(D('ConcernsGroup')->add(['concerns_group_uid'=>$this->userid,'concerns_group_name'=>$group_name])){
            die(json_encode(['status'=>1,'msg'=>'Create group success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Create group failed!']));
        }
    }

    public function SetGroup(){
        $concerns_group_id = I('post.group_id',0,'intval');
        $concerns_group_uid = I('post.uid',0,'intval');
        if(($concerns_group_id <= 0 || $concerns_group_uid <= 0) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif(($concerns_group_id <= 0 || $concerns_group_uid <= 0)){
            $this->error('Parameter error！');
        }

        if(D('Concerns')->updataone('concerns_uid = '.$this->userid.' and concerns_cuid='.$concerns_group_uid,['concerns_gid'=>$concerns_group_id])){
            die(json_encode(['status'=>1,'msg'=>'ChangName success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'ChangName failed!']));
        }
    }

    public function SetFriendAlias(){
        $friends_id = I('post.friends_id',0,'intval');
        $firends_mark = I('post.firends_mark','','trim');
        if(($friends_id <= 0 || empty($firends_mark)) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif(($friends_id <= 0 || empty($firends_mark))){
            $this->error('Parameter error！');
        }

        if(strlen($firends_mark)>80){
            $this->error('firends_mark can\'t exceed 80 characters！');
        }

        if(D('Friends')->updataone('friends_id = '.$friends_id,['firends_mark'=>$firends_mark])){
            die(json_encode(['status'=>1,'msg'=>'Set Alias success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Set Alias failed!']));
        }
    }

    public function DeleteFriend(){
        $firends_uid = I('post.firends_uid',0,'intval');
        $firends_aid = I('post.firends_aid',0,'intval');
        if(($firends_uid <= 0 || $firends_aid <= 0) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif(($firends_uid <= 0 || $firends_aid <= 0)){
            $this->error('Parameter error！');
        }
        if($firends_uid != $this->userid){
            $this->error('Parameter error！');
        }

        $firends_type = D('Friends')->getFriendType(3,['firends_uid'=>$firends_uid,'firends_aid'=>$firends_aid]);
        if(D('Friends')->updataone('firends_uid = '.$firends_uid . ' AND firends_aid=' . $firends_aid,['firends_type'=>$firends_type])){
            die(json_encode(['status'=>1,'msg'=>'Delete success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Delete failed!']));
        }
    }

    public function DeleteResume(){
        $resume_id = I('post.resume_id',0,'intval');
        if($resume_id <= 0 && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($resume_id <= 0){
            $this->error('Parameter error！');
        }

        if(D('Resume')->where('resume_id='.$resume_id . ' AND resume_uid='.$this->userid)->delete()){
            die(json_encode(['status'=>1,'msg'=>'Delete success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Delete failed!']));
        }
    }

    public function DeliveryResume(){
        $interest = I('post.interest',0,'intval');
        $works_id = I('post.works_id',0,'intval');
        if(($interest <= 0 || $works_id <= 0) && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif(($interest <= 0 || $works_id <= 0)){
            $this->error('Parameter error！');
        }

        //查询该简历是否已经投递到职位下
        $myDelivery = D('ResumeDelivery')->where('resume_id='.$interest.' AND user_id='.$this->userid.' AND works_id='.$works_id)->find();
        if($myDelivery){
            die(json_encode(['status'=>0,'msg'=>'Please do not repeat delivery！']));
        }

        if(D('ResumeDelivery')->add(['resume_id'=>$interest,'user_id'=>$this->userid,'works_id'=>$works_id,'delivery_createtime'=>date('Y-m-d H:i:s',time())])){
            die(json_encode(['status'=>1,'msg'=>'Delivery success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Delivery failed!']));
        }
    }

    public function ClearMessages(){
        if(D('Message')->where('message_uid='.$this->userid)->delete()){
            die(json_encode(['status'=>1,'msg'=>'Clear Messages success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Clear Messages failed!']));
        }
    }

    public function MessageDetails(){
        $message_id = I('get.message_id',0,'intval');
        $myMessage = D('Message')->findone('message_id='.$message_id);
        $this->assign('myMessage',$myMessage);

        //更新阅读时间，和是否阅读
        $myMessage = D('Message')->updataone('message_id='.$message_id,['message_delivertime'=>date('Y-m-d',time()),'message_isread'=>1]);

        $this->assign('title','Message details');
        $css = addCss('MessageDetails');
        $this->assign('CSS',$css);
        $this->display();
    }

    public function DissolveGroup(){
        $crowd_id = I('post.crowd_id',0,'intval');
        if($crowd_id <= 0  && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($crowd_id <= 0){
            $this->error('Parameter error！');
        }
        $Model = new Model();
        $Model->startTrans();

        if(D('Crowd')->delete($crowd_id) && D('CrowdMember')->where('crowd_member_cid='.$crowd_id)->delete()){
            $Model->commit();
            die(json_encode(['status'=>1,'msg'=>'Dissolve Group success!']));
        }else{
            $Model->rollback();
            die(json_encode(['status'=>0,'msg'=>'Dissolve Group failed!']));
        }
    }

    public function QuitGroup(){
        $crowd_id = I('post.crowd_id',0,'intval');
        if($crowd_id <= 0  && IS_AJAX){
            die(json_encode(['status'=>0,'msg'=>'Parameter error！']));
        }elseif($crowd_id <= 0){
            $this->error('Parameter error！');
        }

        if(D('CrowdMember')->where('crowd_member_cid='.$crowd_id.' AND crowd_member_uid='.$this->userid)->delete()){
            die(json_encode(['status'=>1,'msg'=>'Quit Group success!']));
        }else{
            die(json_encode(['status'=>0,'msg'=>'Quit Group failed!']));
        }
    }


}