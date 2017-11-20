<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2017/11/19
 * Time: 18:24
 */

function checkSignature()

{
    $token = "weixin";
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];



    $tmpArr = array($timestamp, $nonce,$token);

    sort($tmpArr, SORT_STRING);

    $tmpStr = implode( $tmpArr );

    $tmpStr = sha1( $tmpStr );



    if( $signature == $tmpStr ){

        return true;

    }else{

        return false;

    }

}