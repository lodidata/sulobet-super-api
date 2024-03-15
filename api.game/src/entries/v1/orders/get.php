<?php

use Utils\Game\Action;
use Logic\Define\CacheKey;

/**
 * 获取某天已开奖的彩期列表
 * 以ID 来区分是否拉过的订单
 */
return new class extends Action
{

    public function run()
    {
        $this->verifyApiToken();
        $tid = $this->request->getParam('tid');
        $max_id = $this->request->getParam('max_id');
        if(!$max_id ||!$tid){
            $this->lang->set(0);
        }
        $lastId = 0;
        //获取表最大ID值
        $tableMaxId = \DB::table('orders')->max('id') ?: 0;
        //无数据更新
        if($tableMaxId == $max_id){
            return ['data' => [], 'total' => 0, 'lastId' => $max_id];
        }

        //从配置日期开始拉orders
        //$orders_day = $this->ci->get('settings')['app']['orders_day'] ?? '';

        $subsite_page_size = 10000;
        $query = \DB::table('orders')
            ->where('id', '>', $max_id)
            ->where('tid', $tid);

        //有设置日期并且大于配置日期才拉单
        /*if(isset($orders_day) && time() > strtotime($orders_day)){
            $query->where('date','>=',$orders_day);
        }*/

        $data = $query->limit($subsite_page_size)->get()->toArray();

        //无数据下次从最大ID开始
        if(empty($data)){
            $lastId = $tableMaxId;
        }else{
            $lastId = end($data)->id;
            if($lastId < ($max_id+$subsite_page_size)){
                $lastId = $tableMaxId;
            }
        }
        return $this->lang->set(0,[],$data, ['total' => count($data), 'lastId' => $lastId]);
    }

};