<?php
include 'auth.php';
require 'pay.class.php';

$arr['openid']='o979DwuJxPjaYHCTpATlGJ*****';
$arr['hbname']='test';
$arr['body'] = '给你发红包啦';
$arr['fee'] = 3;

$comm = new Common_util_pub();
$re = $comm->sendhongbaoto($arr);
echo "红包".$re['return_msg'].'请到手机端查看';
var_dump($re);
