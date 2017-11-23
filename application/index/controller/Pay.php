<?php
namespace app\index\controller;
use think\Controller;

class Pay extends Controller{

    public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    public function index(){
//        echo 'aaaa';
        header("Content-type: text/html; charset=utf-8");
        $alipay_config=Config('alipay_config');
//        var_dump($alipay_config);die;
        $payment_type = "1"; //支付类型 //必填，不能修改
        $seller_email = Config('alipay.seller_email');//卖家支付宝帐户必填
        $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
//        $exter_invoke_ip = get_client_ip(); //客户端的IP地址
        $exter_invoke_ip = '127.0.0.1'; //客户端的IP地址
        $param = array(
            'service'=>'create_direct_pay_by_user',
            "partner" => trim($alipay_config['partner']),
            'payment_type'=>'1',
            'notify_url'=>'http://106.15.229.41/index.php/index/Pay/notify_url',
            'return_url'=>'http://106.15.229.41/index.php/index/Pay/return_url',
            "seller_email"    => $seller_email,
            'out_trade_no'=>'XS201612345672',
            'subject'=>'测试商品标题',
            "total_fee"    => '0.01',
            "body"            => 'sddss',
            "show_url"    => '',
            "anti_phishing_key"    => $anti_phishing_key,
            "exter_invoke_ip"    => $exter_invoke_ip,
            "payment_type"    => $payment_type,
            '_input_charset'=>'utf-8',
        );

        //构造请求参数
        $res = $this->buildRequestPara($param);
        $form = $this->buildRequestForm($res, 'get', '提交');
        echo $form;
    }


    public function notify_url(){

    }

    public function return_url(){

    }
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $button_name) {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

//        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower('utf-8'))."' method='".$method."'>";
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower('utf-8'))."' method='".$method."'  accept-charset='utf-8'>";
        while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";

        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim('MD5'));

        return $para_sort;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }


    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    function buildRequestMysign($para_sort) {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $mysign = "";
        switch (strtoupper(trim('MD5'))) {
            case "MD5" :
                $mysign = $this->md5Sign($prestr, 'k2zu8i7h9enbkafsvtfrgdcuy1n273qn');
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * 签名字符串
     * @param $prestr 需要签名的字符串
     * @param $key 私钥
     * return 签名结果
     */
    function md5Sign($prestr, $key) {
        $prestr = $prestr . $key;
        return md5($prestr);
    }
}