<?php
/**
 * 非正式JWT token验证类
 * author erick
 * date 2018/05/26
 */

 /**
 * jwt加密函数 header+payload+sign
 * @param string $uid 用户名ID字符串
 * @param string $key 加密key
 * @param string $alg 加密算法     
 * @return string
  */
 function jwt_encode(string $uid,string $key ,string $alg = 'SHA256'):string
 {
     if( empty($uid) ) return $uid;

     if( empty($key) ) return $key;

     $time = time(0);

     //加密key
     $key = md5($key);

     //头部
     $header = json_encode(array('typ'=>'JWT','alg'=>$alg));
     $header = base64Url($header);
     //token身份验证
     $payload = json_encode(array( "iss"=>'admin',"iat"=>$time,"exp" => $time+300,  'uid'=>$uid));    
     $$payload = base64Url($payload); 
     //第一第二部分 
     $jwt_body = $header.'.'.$$payload; 

     //加密sign
     $signature = hash_hmac($alg, $jwt_body,$key);

     //第三部分
     $jwt =  $jwt_body.'.'.$signature;

     return  $jwt;
 }

 /**
   * header+payload+sign
   * jwt 解密函数
   * @param string $jwt 解密字符串
   * @param string $key 加密key   
   * @return string|bool
  */
 function jwt_decode(string $jwt, string $key)
 {
    if( empty($key) ) return false;

     $token = explode('.',$jwt);

     //token 长度验证
     if( count($token) !=3){
         return false;
     }

     $key = md5($key);

     list($headerencr,$payloadencr,$sign) = $token;

     $header = json_decode(base64Url($headerencr));
     $alg = $header['alg'];

     $jwt = $headerencr.'.'.$payloadencr;
     if(hash_hmac($jwt, $key, $alg) != $sign){
         return false;
     }

     $payload = json_decode(base64Url($payloadencr));

     $time = $_SERVER['REQUEST_TIME'];

    if (isset($payload['iat']) && $payload['iat'] > $time)
        return false;

    if (isset($payload['exp']) && $payload['exp'] < $time)
        return false;

    return $payload;
 }

 function base64Url(string $input )
 {
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
 }
