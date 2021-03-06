<?php

namespace Index\Model;

use Think\Db;
use Think\Model;

class MessageModel extends Model{

    protected  $trueTableName = 'u_message';

    public function findone($where,$field=false){

        return $this->where($where)->field($field)->find();
    }

    public function updataone($where, $data)
    {
        return $this->where($where)->setField($data);
    }

    public function findlist($where,$order='',$field=false){

        return $this->where($where)->field($field)->order($order)->select();
    }

    public function addone(array $data){
        $data['message_sendtime'] = isset($data['message_sendtime']) ? $data['message_sendtime'] : date('Y-m-d H:i:s',time());
        $data['message_delivertime'] = isset($data['message_delivertime']) ? $data['message_delivertime'] : date('Y-m-d H:i:s',time());
        return $this->add($data);
    }

    public function joinfind($where,$join,$order='',$jointype='INNER',$field=false){

        return $this->join($join,$jointype)->where($where)->field($field)->order($order)->find();
    }

    public function joinlist($where,$join,$order='',$limit1,$limit2,$jointype='INNER',$field=false){

        return $this->join($join,$jointype)->where($where)->field($field)->order($order)->limit($limit1,$limit2)->select();
    }


}