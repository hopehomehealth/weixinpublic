<?php
namespace app\index\controller;
use think\Controller;

class Wechat extends Controller
{

    private $_wechatModel;
    public function __construct()
    {
        $this->_wechatModel = new \app\index\model\Wechat();
//        $this->init();
    }

    public function valid(){
        if(isset($_GET['echostr'])){
            $this->_wechatModel->valid();
        }
        else{
            $this->_wechatModel->responseMsg();
//            $this->responseMsg();
        }
    }

    public function responseMsg(){
        $postStr = file_get_contents("php://input");
        $postStr = trim($postStr);
        if(!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[%s]]></MsgType>
 <Content><![CDATA[%s]]></Content>
 </xml>";
//            $textTpl = "<xml>
//                    <ToUserName><![CDATA[%s]]></ToUserName>
//                    <FromUserName><![CDATA[%s]]></FromUserName>
//                    <CreateTime>%s</CreateTime>
//                    <MsgType><![CDATA[%s]]></MsgType>
//                    <Content><![CDATA[%s]]></Content>
//                    <FuncFlag>0</FuncFlag>
//                    </xml>";
            if(!empty($keyword)){
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
//                $resultStr = sprintf($textTpl,$fromUsername,$toUsername,$time,$msgType,$contentStr);
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;

            }else{
                echo "Input something";
            }

        }else{
            echo "";
            exit();
        }
    }

   public function receiveText($postSql){

   }

    public function responseMsgBack(){
        echo 'aaaaaaaaaaaaa';
        $postStr = file_get_contents("php://input");
//        $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
//        $this->logger("接收：".$postStr);
        $this->logger2("接收poststr：".$postStr);
//        $this->logger2("接收postobj：".$postObj);
        $postStr = trim($postStr);
        if(!empty($postStr)){
            libxml_disable_entity_loader(true);
//            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
//            echo $postObj;
            $this->logger2("接收postobj：".$postObj);
            $RX_TYPE = trim($postObj->MsgType);
            switch($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->handleText($postObj);
                    break;
                case "event":
                    $resultStr = $this->handleEvent($postObj);
                    break;
                default:
                    $resultStr = "Unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $resultStr;
        }else{
            echo "";
            exit();
        }
    }

    public function handleText($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        if(!empty( $keyword ))
        {
            $msgType = "text";

            if($keyword=="你好"){
                $contentStr = "hello";
            }elseif($keyword=="苏州"){
                $contentStr = "上有天堂，下有苏杭";
            }else{
                $contentStr = "感谢您关注【卓锦苏州】 微信号：zhuojinsz";
            }
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }

    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢您关注【卓锦苏州】"."\n"."微信号：zhuojinsz"."\n"."卓越锦绣，名城苏州，我们为您提供苏州本地生活指南，苏州相关信息查询，做最好的苏州微信平台。"."\n"."目前平台功能如下："."\n"."【1】 查天气，如输入：苏州天气"."\n"."【2】 查公交，如输入：苏州公交178"."\n"."【3】 翻译，如输入：翻译I love you"."\n"."【4】 苏州信息查询，如输入：苏州观前街"."\n"."更多内容，敬请期待...";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }

    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    public function xmlto(){
        $xml = "
<xml>
 <ToUserName><![CDATA[toUser]]></ToUserName>
 <FromUserName><![CDATA[fromUser]]></FromUserName>
 <CreateTime>1348831860</CreateTime>
 <MsgType><![CDATA[text]]></MsgType>
 <Content><![CDATA[this is a test]]></Content>
 <MsgId>1234567890123456</MsgId>
 </xml>";
$obj = simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
var_dump($obj);

    }
   public function logger2($content){
       header("Content-type: text/html; charset=utf-8");
       $fp = fopen('data.txt', 'a+');
//       fwrite($fp, '1');
//       fwrite($fp, '23');
       fwrite($fp,$content);
       fclose($fp);

   }

    //写日志
    public function logger($content){
        $logSize=100000;

        $log="log.txt";

        if(file_exists($log) && filesize($log)  > $logSize){
            unlink($log);
        }

        file_put_contents($log,date('H:i:s')." ".$content."\n",FILE_APPEND);

    }
    public function aaa(){
        echo 'aaaaaaaa';
    }
    //获取accessToken
    public function getAccessToken(){
//        echo 'sdfsdfsfaaaaaaaaaaaa';
        $accessToken = $this->_wechatModel->getAccessToken();
//        echo $accessToken;
        return $accessToken;
    }

    public function getTicket(){
        $tmp = 0;
        $scene_id=666;
        $ticket = $this->_wechatModel->getTicket($tmp,$scene_id);
        echo $ticket;
        return $ticket;
    }

    public function getQRCode(){
        $this->_wechatModel->getQRCode();
    }


    public function delMenu(){
        $this->_wechatModel->delMenu();
    }

    public function checkSignature(){
        $this->_wechatModel->checkSignature();
    }


    public function createMenu(){
        $this->_wechatModel->createMenu();
    }


    public function receiveEvent(){
        $this->_wechatModel->receiveEvent($postSql);
    }

//
//    public function receive(){
//        $this->_wechatModel->receive();
//    }




    public function showMenu(){
        $this->_wechatModel->showMenu();
    }


    public function getUserList(){
        $this->_wechatModel->getUserList();
    }


    public function getUserInfo(){
        $this->_wechatModel->getUserInfo();
    }
}
