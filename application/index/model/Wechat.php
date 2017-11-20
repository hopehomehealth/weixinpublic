<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2017/11/19
 * Time: 12:00
 */
namespace app\index\model;
define('TOKEN','weixin');
class Wechat{

    private $appid;
    private $appsecret;
    private $access_token;
    private $token;
    private $api = array(
//        'getUserInfo'=>"https://api.weixin.qq.com/cgi-bin/user/info?access_token=&openid=OPENID&lang=zh_CN",
    );
    private $textTpl =
        "<xml>    
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";
    private $newsTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%s</ArticleCount>
        <Articles>%s</Articles>
        </xml>";
    private $item = "<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
        </item>";

    private function _sendTuwen($postSql){
        //组合新闻数组
        $newsList = array(
            array(
            'Title' => '2017中国公开赛：郑思维/黄雅琼获混双冠军',
            'Description' =>'郑思维/黄雅琼以2比0战胜马蒂亚斯/佩蒂森，获得冠军。',
            'PicUrl'=>'https://b.bdstatic.com/boxlib/20171120/2017112017521086656215786.jpg',
                'Url'=>'http://top	.china.com.cn/2017-11/20/content_41914128.htm',),
            array(
//                'Title' => '摘要：上港和申花的足协杯之争，这是一场可以咬碎后槽牙对决，前者本赛季豪言至少拿一冠，但中超和亚冠都已梦碎，足协杯成了救命稻草。后者从第四下滑到第11，胜少负多倍受质疑，一个足协杯换一个亚冠席位，也是申花自',
                'Title' => '摘要：上港和申花的足协杯之争',
                'Description' =>'上港和申花的足协杯之争，这是一场可以咬碎后槽牙对决，前者本赛季豪言至少拿一冠，但中超和亚冠都已梦碎，足协杯成了救命稻草。后者从第四下滑到第11，胜少负多倍受质疑，一个足协杯换一个亚冠席位，也是申花自我救赎的关键。',
                'PicUrl'=>'https://imgsa.baidu.com/news/q%3D100/sign=aeeb1ad773f0f736defe48013a54b382/f3d3572c11dfa9ec8f9c7abf69d0f703908fc1e3.jpg',
                'Url'=>'https://baijia.baidu.com/s?id=1584447810141110862',),
        );
//        $this->logger2("接受：\n".json_encode($newsList));
//        $this->logger2("接受：\n".'sddssdds');
        $items = '';
        //循环输出items模板
        foreach($newsList as $key=>$value){
            $items .= sprintf($this->item,$value['Title'],$value['Description'],$value['PicUrl'],$value['Url']);
        }
        $this->logger2("接受items：\n".$items);

        $contentStr = sprintf($this->newsTpl,$postSql->FromUserName,$postSql->ToUserName,time(),count($newsList),$items);
        $this->logger2("接受：\n".$contentStr);
        return $contentStr;
    }
    public function checkSignature()
    {
        if(!defined("TOKEN")){
            throw new Exception("TOKEN is not defined!");
        }
        $token = $this->token;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $tmpArr = array($token,$timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $signature == $tmpStr ){
//            return true;
            echo $_GET['echostr'];
        }else{
            return false;
        }
    }

    //构造方法
    public function __construct()
    {
       $this->appid = APPID;
       $this->appsecret = APPSECRET;
       $this->access_token = $this->getAccessToken();
       $this->token = TOKEN;
//       $this->_init();
    }

//    public function _init(){
//        if(!isset($_GET['echostr'])){
//            $this->receive();
//        }else{
//            $this->checkSignature();
//        }

    public function valid(){
        $echoStr = $_GET['echostr'];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }


//    }
    //自动回复文本
//    public function receiveEvent($postSql){
//        $xml="<xml>
//						<ToUserName><![CDATA[%s]]></ToUserName>
//						<FromUserName><![CDATA[%s]]></FromUserName>
//						<CreateTime>%s</CreateTime>
//						<MsgType><![CDATA[%s]]></MsgType>
//						<Content><![CDATA[%s]]></Content>
//					</xml>";
//
//
//        $result=sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),"text","欢迎听益达讲php开发微信公众账号");
//        $this->logger("自动回复：\n".$result);
//        return $result;
//    }

    public function logger($contents){
        $logSize = 100000;
        $log = "log.txt";
        if(file_exists($log) && filesize($log)>$logSize){
            unlink($log);
        }
        file_put_contents($log,date("H:i:s")." ".$contents."\n",FILE_APPEND);
    }

    public function responseMsgBak(){
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


    //处理所有的微信相关信息请求和xiangy8ing的处理
    public function responseMsg(){
        $postStr = file_get_contents("php://input");

        $postStr = trim($postStr);
        $this->logger2("接受：\n".$postStr);
        libxml_disable_entity_loader(true);
        $postSql = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
//        $this->logger2("接受：\n".$postSql);
//        $this->logger2("接受：\n".$postSql->MsgType);
        if(!empty($postSql)){
            $RX_TYPE = $postSql->msgType;
            switch(trim($postSql->MsgType)){
                case "text":
                    $result = $this->receiveText($postSql);
//                    $result = $this->_sendTuwen($postSql);
                    break;
                case "event":
//                    $result = $this->receiveEvent($postSql);
                    $result = $this->_doEvent($postSql);
                    break;
                case "image":
//                    $result = $this->receiveImage($postSql);
                    $result = $this->_doImage($postSql);
                    break;
                case "location":
//                    $result = $this->receiveLocation();
                    $result = $this->_doLocation($postSql);
                    break;
                case "voice":
                    $result = $this->receivVoice($postSql);
                    break;
                case "video":
                    $result = $this->receiveVideo($postSql);
                    break;
                case "link":
                    $result = $this->receiveLink($postSql);
                    break;
                default:
                    $result = "unknow msg type:".$RX_TYPE;
                    break;
            }
            $this->logger2("接受result：\n".$result);
            if(!empty($result)){
                echo $result;
            }else{
                $xml="<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
					</xml>";
                $result = sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),"text","没有这条消息");
                $this->logger2("自动回复：\n".$result);
            }
        }
    }
   /*
    * 处理事件类型
    * */
    private function _doEvent($postSql){
        //判断event值得类型，进行相应的操作
        switch($postSql->Event){
            case 'subscribe':
                $this->_doSubscribe($postSql);
                break;
            case "unsubscribe":
                $this->_doUnsubscribe($postSql);
                break;
            case "CLICK":
                $this->_doClick($postSql);
                break;
            default:
                break;
        }
    }
    /*
     * 自定义菜单点击事件
     * */
    private function _doClick($postSql){
        switch($postSql->EventKey){
            case 'news':
                $this->_sendTuwen($postSql);
                break;
            default:
                break;
        }
    }
    /*
     *
     * 取消关注事件
     * */
    private function _doUnsubscribe($postSql){
        //删除用户的一些信息获取绑定的相关操作

    }
    private function _doSubscribe($postSql){
        $contentStr = "欢迎关注我们，我们是php47期，请常关注我们";
        $resultStr = sprintf($this->textTpl,$postSql->FromUserName,$postSql->ToUserName,time(),'text',$contentStr);
        return $resultStr;
    }

    private function _doLocation($postSql){
        $contentStr = "您当前位置x为：".$postSql->Location_X.",Y为：".$postSql->Location_Y;
        $resultStr = sprintf($this->textTpl,$postSql->FromUserName,$postSql->ToUserName,time(),"text",$contentStr);
        return $resultStr;




    }

    private function _doImage($postSql){
        $PicUrl = $postSql -> PicUrl;
        //保存图片
        $resultStr = sprintf($this->textTpl,$postSql->FromUserName,$postSql->ToUserName,time(),"text",$PicUrl);
        return $resultStr;
    }

    public function receiveEvent(){

    }

    public function receiveText($postSql){
        $content = trim($postSql->Content);

            $xml = "
<xml>

<ToUserName><![CDATA[%s]]></ToUserName>

<FromUserName><![CDATA[%s]]></FromUserName>

<CreateTime>%s</CreateTime>

<MsgType><![CDATA[%s]]></MsgType>

<Content><![CDATA[%s]]></Content>

</xml>";
    if(!empty($content)){
        $msgType = "text";
        $contentStr = "welcome to wechat world!";
        $url = "http://api.qingyunke.com/api.php?key=free&appid=0&msg=".$content;
        $contents = $this->request($url);
        $contents = json_decode($contents);
        $contentStr = $contents->content;
        if($content == 'php47'){
            $contentStr = "我们是php47期成员";
        }
    }
            $result = sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),$postSql->MsgType,$contentStr);

        return $result;
    }



    public function receiveTextBak($postSql){
       $content = trim($postSql->Content);

       if(strstr($content,'你好')){
           $xml = "
<xml>

<ToUserName><![CDATA[%s]]></ToUserName>

<FromUserName><![CDATA[%s]]></FromUserName>

<CreateTime>%s</CreateTime>

<MsgType><![CDATA[%s]]></MsgType>

<Content><![CDATA[%s]]></Content>

</xml>";
           $result = sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),$postSql->MsgType,"hello");

       }else if(strstr($content,'单图文')){
             $result = $this->receiveTuwen($postSql);
       }else if(strstr($content,'多图文')){
            $result = $this->receiveImages($postSql);
       }else if(strstr($content,'图片')){
           $result = $this->receiveMedia($postSql);
       }
       return $result;
    }


    private function receiveTuwen($postSql){

        $xml="<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<ArticleCount>1</ArticleCount>
			<Articles>
			<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl><![CDATA[%s]]></PicUrl>
			<Url><![CDATA[%s]]></Url>
			</item>
			</Articles>
			</xml> ";

        $result=sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),"news","跟益达学微信开发，教益达泡妹子","它就是
	中国海南海花岛——缤纷水上乐园
	23项游乐
	打造雪山滑道区、激流河道区
	巨浪演绎区、阖家欢乐区
	极限滑道区五大主题游乐区","http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");

        return $result;
    }


    private function receiveImage($postSql){
        $xml="<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>
		</xml>";

        $result=sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),"image","82-482CHnSTGSJMvrdz9WXHxP_Vf31PKeWFo6pRdMBIkCIO3QCaSwP60L7IbuNxB");

        return $result;


    }

    private function   receiveImages($postSql){
        $content=array();
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");
        $content[]=array("Title"=>"跟益达学微信开发，教益达泡妹子","Description"=>"它就是
				中国海南海花岛——缤纷水上乐园
				23项游乐
				打造雪山滑道区、激流河道区
				巨浪演绎区、阖家欢乐区
				极限滑道区五大主题游乐区","PicUrl"=>"http://pic14.nipic.com/20110522/7411759_164157418126_2.jpg","http://www.maiziedu.com/");


        $str="<item>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				<PicUrl><![CDATA[%s]]></PicUrl>
				<Url><![CDATA[%s]]></Url>
				</item>";

        $news="";
        foreach ($content as $newArray) {
            $news.=sprintf($str,$newArray['Title'],$newArray['Description'],$newArray['PicUrl'],$newArray['Url']);
        }

        $xml="<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[%s]]></MsgType>
				<ArticleCount>%s</ArticleCount>
				<Articles>
					$news
				</Articles>
				</xml> ";

        $result=sprintf($xml,$postSql->FromUserName,$postSql->ToUserName,time(),"news",count($content));

        return $result;


    }


    public function logger2($content){
        header("Content-type: text/html; charset=utf-8");
        $fp = fopen('data.txt', 'a+');
//       fwrite($fp, '1');
//       fwrite($fp, '23');
        fwrite($fp,$content);
        fclose($fp);

    }



    //分装请求方法
    public function request($url,$https=true,$method='get',$data=null){
        //1.初始化url
        $ch = curl_init($url);
        //2.设置相关的参数
        //字符串不直接输出,进行一个变量的存储
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //判断是否为https请求
        if($https === true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //判断是否为post请求
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //3.发送请求
        $str = curl_exec($ch);
        //4.关闭连接
        curl_close($ch);
        //返回请求到的结果
        return $str;
    }
   /*
    * 获取accesstoken
    * */
    public function getAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
        //get方式获取值
        $content = $this->request($url);
        $content = json_decode($content);
//        var_dump($content);
        $access_token = $content->access_token;
//        file_put_contents('./accessToken',$access_token);
        return $access_token;
    }
    /*
     * 直接读取缓存中的access_token
     * */
    public function getAccessTokenCache(){
        //读取文件获取缓存数据
        $access_token = file_get_contents('./accessToken');
//        echo $access_token;
        return $access_token;
    }
    /*
    * 获取二维码的ticket票据
    * Time:2016年7月9日15:08:27
    * By:php47
    *
    */
    public function getTicket($tmp=0,$scene_id=123){
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->getAccessToken();
        //2组合post数据
        if($tmp == '1'){
            $data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';}
            else{
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';}
            $content = $this->request($url,true,'post',$data);
            $content = json_decode($content);
            echo $content->ticket;
    }
   //使用ticket换二维码
    public function getQRCode(){
        $ticket = 'gQH88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUmlBZnN6SnNjTDQxMDAwME0wM0UAAgTYFRFaAwQAAAAA';
//        $ticket = $this->getTicket();
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        $content = $this->request($url);
        file_put_contents('./qrcode.jpg',$content);
    }

    /*
     * 删除菜单操作
     *
      * */
     public function delMenu(){
         $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
         //2get请求方式，直接发送请求
         $content = $this->request($url);
         //w3处理返回值
         $content = json_decode($content);
         if($content->errmsg == 'ok'){
             echo '删除菜单成功';
         }else{
             echo '删除失败，错误代码为：'.$content->errcode;
         }
     }

     /*
      * 创建菜单操作
      * */
     public function createMenu(){
         $url ='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
         //2组合post数据
         $data = '{
    "button": [
        {
            "name": "扫码", 
            "sub_button": [
                {
                    "type": "scancode_waitmsg", 
                    "name": "扫码带提示", 
                    "key": "rselfmenu_0_0", 
                    "sub_button": []
                }, 
                {
                    "type": "scancode_push", 
                    "name": "扫码推事件", 
                    "key": "rselfmenu_0_1", 
                    "sub_button": []
                }
            ]
        }, 
        {
            "name": "发图", 
            "sub_button": [
                {
                    "type": "pic_sysphoto", 
                    "name": "系统拍照发图", 
                    "key": "rselfmenu_1_0", 
                   "sub_button": []
                 }, 
                {
                    "type": "pic_photo_or_album", 
                    "name": "拍照或者相册发图", 
                    "key": "rselfmenu_1_1", 
                    "sub_button": []
                }, 
                {
                    "type": "pic_weixin", 
                    "name": "微信相册发图", 
                    "key": "rselfmenu_1_2", 
                    "sub_button": []
                }
            ]
        }, 
        {
            "name": "发送位置", 
            "type": "location_select", 
            "key": "rselfmenu_2_0"
        },
        
    ]
}';
         $content = $this->request($url,true,'post',$data);
         $content = json_decode($content);
         var_dump($content);
         if($content->errmsg == 'ok'){
             echo "创建菜单成功";
         }else{
             echo '创建失败，错误代码为：'.$content->errcode;
         }
     }

     /*
      * 查询菜单操作
      * */
     public function showMenu(){
         $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->getAccessToken();
         //2get方式，直接发送请求
         $content = $this->request($url);
         echo "<pre>";
         print_r($content);
     }

/*获取用户列表*/
    public function getUserList(){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken();
        $content = $this->request($url);
        $content = json_decode($content);
        echo "<pre>";
        print_r($content);
        echo '用户关注数为：'.$content->total."<br/>";
        echo '本次拉去数量：'.$content->count."<br/>";
        $openIDList = $content->data->openid;
        foreach($openIDList as $key=> $value){
            echo $value."<br/>";
        }
    }

    /*获取用户列表*/
    public function getUserInfo(){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid=o7VwvxHoxazPE7au9IEes-nZmRLk&lang=zh_CN ';
        $content = $this->request($url);
//        echo "<pre>";
//        print_r($content);
        $content = json_decode($content);
        echo '昵称：'.$content->nickname."<br/>";
        echo '性别：'.$content->sex."<br/>";
        echo '省份：'.$content->province."<br/>";
        echo '头像：'.'<img = src="'.$content->headimgurl.'"/><br/>';

    }




























}