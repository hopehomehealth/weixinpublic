<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2017/11/21
 * Time: 16:08
 */

/**
 * Class Tools
 * @param Array $data
 * @param Int $pid
 * @param Int $count
 * $param Array $treeList
 */
/*
 * 购物车累
 * */

class Cart{
    private $cartInfo  =array();
    public function __construct()
    {
        session_start();
        $this->loadData();//获取session中的购物车信息
    }
    /*
     * 取得购物车里边已经存放的商品的信息
     * 该方法是该类里边第一个被执行的方法、
     * */
    private function loadData(){
        if(isset($_SESSION['cart'])){
            //判断session中有误购物车信息
            //取得购物车里边已经存放的商品信息  并且fan序列化
            $this->cartInfo = $_SESSION['cart'];
        }
    }
   /*
    * 商品添加到购物车中
    * */
    public  function add($goods){
        $pd_id = $goods['goods_id'];
        //对于重复购买的商品要判断（还要判断当前的购物车是否为空，几是否是第一次添加商品）
        if(!empty($this->cartInfo) && array_key_exists($pd_id,$this->cartInfo)){
            //数量家1
            $this->cartInfo[$pd_id]['goods_buy_number'] += 1;
            //2单间商品的总价增加  单件商品总价
            $this->cartInfo[$pd_id]['goods_total_price'] = $this->cartInfo[$pd_id]['price']*$this->cartInfo[$pd_id]['goods_buy_number'];
        }else{
            $this->cartInfo[$pd_id] = $goods;
        }
        $this->saveData();
    }


    /*
     * 将购物车中的商品信息存入购物车
     * */
    private function saveData(){
        $data = $this->cartInfo;
        $_SESSION['cart'] = $data;
        //setcookit('cart',$data,time()+3600);
    }

    /*
     * 删除购物车里边制定的商品
     *
     * */
    public function del($goods_id){
        if(array_key_exists($goods_id,$this->cartInfo)){
            unset($this->cartInfo[$goods_id]);
        }
        $this->saveData();
    }

    /*
     * 清空购物车
     * */

    public function delall(){
        unset($this->cartInfo);
        $this->saveData();
    }
    /*
     * 商品数量发生变化要执行的步骤
     * */
    public function changeNumber($pd_number,$pd_id){
        //1修改商品的数量
        $this->cartInfo[$pd_id]['goods_buy_number'] = $pd_number;
        $this->cartInfo[$pd_id]['goods_total_price'] = $pd_number*$this->cartInfo[$pd_id]['goods_price'];
        $this->saveData();
        //将刷新的数据重新存入cookie
    }
    /*
     * 获得购物车的商品
     * */
    private function getNumberPrice(){
        $number =0;//商品数量
        $price = 0;//商品总价钱
        foreach($this->cartInfo as $k => $v){
            $number += $v['goods_buy_number'];
            $price += $v['goods_total_price'];
        }
        $arr['number'] = $number;
        $arr['price'] = $price;
        return $arr;

    }

    public function getCartInfo(){
        return $this->cartInfo;
    }




















}