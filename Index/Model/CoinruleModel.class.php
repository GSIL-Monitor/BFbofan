<?php

namespace Index\Model;

use Think\Db;
use Think\Model;

class CoinruleModel extends Model{

    protected  $trueTableName = 's_coinrule';

    public function findone($where,$field=false){

        return $this->where($where)->field($field)->find();
    }

    public function updataone($where, $data)
    {
        return $this->where($where)->setField($data);
    }

    public function honorlist($where,$order,$limit1,$limit2,$field=false)
    {
        return $this->field($field)->where($where)->order($order)->limit($limit1,$limit2)->select();
    }

    public function findlist($where,$field=false){

        return $this->where($where)->field($field)->select();
    }


}