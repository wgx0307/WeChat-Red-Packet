
<?php
header("Content-type: text/html; charset=utf-8");

class Common_util_pub
{
    public function sendhongbaoto($arr){
        $data['mch_id'] = '1234******'; //商户id
        $data['mch_billno'] = '1234'.date("YmdHis",time()).mt_rand(1111,9999);  //商户订单号 唯一性
        $data['nonce_str'] = uniqid();  //随机字符串
        $data['re_openid'] = $arr['openid']; //红包接受者id
        $data['wxappid'] = 'wx998482038f3d9***';
        $data['total_amount'] = $arr['fee']*100; //付款金额
        $data['total_num'] = 3;  //发送红包个数   最低为三个
        $data['nick_name'] = $arr['hbname']; //提供方名称
        $data['send_name'] = $arr['hbname'];//红包发送者名称
        $data['client_ip'] = $_SERVER['REMOTE_ADDR'];  //调用接口的ip地址
        $data['act_name'] = '测试'; //活动名称
        $data['remark'] = '备注'; //备注信息
        $data['amt_type'] = 'ALL_RAND'; //金额随机
        $data['wishing'] = $arr['body'];//红包祝福语
        if(!$data['re_openid']){
            $rearr['return_msg'] = '缺少用户的openid';
            return $rearr;
        }
        $data['sign'] =  self::getSign($data); //生产签名
        $xml = self::arrayToXml($data); //静态调用本页面的方法
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack";
        $re = self::wxHttpsRequestPem($xml,$url); //发送http请求
        $rearr = self::xmlToArray($re);
        return $rearr;

    }


    /**
     *作用：array转xml
     */
    public  function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array       
        $array_data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $array_data;
    }
    /**
     * 发送http请求方法
     */
    public function wxHttpsRequestPem( $vars,$url, $second=30){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        //证书密钥
        curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/cert/apiclient_key.pem');
        //CA证书目录
        curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).'/cert/rootca.pem');

        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }    else    {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }

    }



    /**
     *  作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        $String = $String."&key=".KEY; // 商户后台设置的key
        $String = md5($String);
        $result_ = strtoupper($String);
        return $result_;
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

}


?>
