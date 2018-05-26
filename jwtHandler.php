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
     $header = encrypt($header,$key);
     //token身份验证
     $payload = json_encode(array( "iss"=>'admin',"iat"=>$time,"exp" => $time+300,  'uid'=>$uid));    
     $$payload = encrypt($payload,$key); 
     //第一第二部分 
     $jwt_body = $header.'+'.$$payload; 

     //加密sign
     $signature = hash_hmac($alg, $jwt_body,$key);

     //第三部分
     $jwt =  $jwt_body.'+'.$signature;

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

     $token = explode('+',$jwt);

     //token 长度验证
     if( count($token) !=3){
         return false;
     }

     $key = md5($key);

     list($headerencr,$payloadencr,$sign) = $token;

     $header = json_decode(decrypt($headerencr,$key));
     $alg = $header['alg'];

     $jwt = $headerencr.'+'.$payloadencr;
     if(hash_hmac($jwt, $key, $alg) != $sign){
         return false;
     }

     $payload = json_decode(decrypt($payloadencr,$key));

     $time = $_SERVER['REQUEST_TIME'];

    if (isset($payload['iat']) && $payload['iat'] > $time)
        return false;

    if (isset($payload['exp']) && $payload['exp'] < $time)
        return false;

    return $payload;
 }

    
    /**
     * 加密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @param integer $expire 有效期（秒）     
     * @return string
     */
    function encrypt($str,$key,$expire=0){
        $expire = sprintf('%010d', $expire ? $expire + time():0);
        $r = md5($key);
        $c=0;
        $v = "";
        $str    =   $expire.$str;
		$len = strlen($str);
		$l = strlen($r);
        for ($i=0;$i<$len;$i++){
         if ($c== $l) $c=0;
         $v.= substr($r,$c,1) .
             (substr($str,$i,1) ^ substr($r,$c,1));
         $c++;
        }
        return ed($v,$key);
    }

    /**
     * 解密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
      function decrypt($str,$key)
       {
        $str = ed($str,$key);
        $v = "";
		$len = strlen($str);
        for ($i=0;$i<$len;$i++){
         $md5 = substr($str,$i,1);
         $i++;
         $v.= (substr($str,$i,1) ^ $md5);
        }
        $data   =    $v;
        $expire = substr($data,0,10);
        if($expire > 0 && $expire < time()) {
            return '';
        }
        $data   = substr($data,10);
        return $data;
    }

    /**
     * 字符串处理
     */
   function ed($str,$key) 
   {
      $r = md5($key);
      $c=0;
      $v = "";
	  $len = strlen($str);
	  $l = strlen($r);
      for ($i=0;$i<$len;$i++) {
         if ($c==$l) $c=0;
         $v.= substr($str,$i,1) ^ substr($r,$c,1);
         $c++;
      }
      return $v;
   }
