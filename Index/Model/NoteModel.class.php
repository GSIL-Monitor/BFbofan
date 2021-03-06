<?php

namespace Index\Model;

use Think\Db;
use Think\Model;

class NoteModel extends Model{

    protected  $trueTableName = 'u_note';

    public function findone($where,$field=false){

        return $this->where($where)->field($field)->find();
    }
    public function joinone($where,$join1,$order,$jointype1='INNER',$field=false){

        return $this->join($join1,$jointype1)->field($field)->where($where)->order($order)->find();
    }

    public function updataone($where, $data)
    {
        return $this->where($where)->setField($data);
    }

    public function findlist($where,$order='',$field=false){

        return $this->where($where)->field($field)->order($order)->select();
    }

    public function limitlist($where,$limit1,$limit2,$order='',$field=false){

        return $this->where($where)->field($field)->order($order)->limit($limit1,$limit2)->select();
    }

    public function joinonelist($where,$join1,$order,$limit1,$limit2,$jointype1='INNER',$field=false){

        return $this->join($join1,$jointype1)->field($field)->where($where)->order($order)->limit($limit1,$limit2)->select();
    }

    public function jointwolist($where,$join1,$join2,$order,$limit1,$limit2,$jointype1='INNER',$jointype2='INNER',$field=false){

        return $this->join($join1,$jointype1)->join($join2,$jointype2)->field($field)->where($where)->order($order)->limit($limit1,$limit2)->select();
    }

    public function jointwoone($where,$join1,$join2,$order,$jointype1='INNER',$jointype2='INNER',$field=false){

        return $this->join($join1,$jointype1)->join($join2,$jointype2)->field($field)->where($where)->order($order)->find();
    }

    public function joinlist($where,$join1,$order='',$jointype1='INNER',$field=false){
        return $this->join($join1,$jointype1)->field($field)->where($where)->order($order)->select();
    }


}