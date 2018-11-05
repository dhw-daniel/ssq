<?php
/**
 * Created by ecitlm.
 * User: ecitlm
 * Date: 2017/9/23
 * Time: 00:18
 */


namespace app\api\controller;
require '../extend/rpc/jsonRPCClient.php';
use think\Controller;
use rpc\jsonRPCClient;
class Contract extends Controller{
    private $_developerId = '2091829019505852963';
    private $_pem = '-----BEGIN RSA PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJSJyoRxQ6pJsbewfHLCURlVB/RH5oaf5bascN2yWOb3gj/InnOstLFNMZ/pCNou5OUUSG/L7n84JpUZm2CwFfiRgoO2hkpVsgF+SLSZ85swYwREQ/voobFXet2q1ZHsjZrVw+ec2uaeVxIDRXRmGZRONOUnpnouOgBWOMs0NNvlAgMBAAECgYBj6ceajOF6AvYS3BjpzIFmq8ac71xGA/othRKqXVPlkGlBZD4JCwvEE2uk58h3koGPAbSz1pYHZwq00jOstuUfdtGycRUQ/Xcuocl0t9OIlTpdp2YU3hWr9JU215JRwlwINS27vpHECIux6RbCU2LyFpAoaVT/4iPiXBZZCzRxwQJBAMQiAE7e6LyiR+E2WdEdYgj98zF4uhhwj8LIjFmdIGZ1K01D13Qn6cmCoErnw5Ca48nXyfD/Z/UEu2BQI9yzD5ECQQDB4LTR42eBrjcgFonolV2i4sJOOIqv6wO0/VO+9W6TWsSx6XQAOcOZbedCfKt/DALuJY9xOkzyAcwJbzTU6sUVAkEAw4pOmlOc3+w/E6b3VwgfZG2jV7BQgOtAOOdvHi0MT3oDqO25UaI1cGUeYG++x13VOrg8KlzJDTwhf/2GM5QGMQJAfQO7NPfwn1NKInvGE151EXoslqmo7ASb0FHldWXnFkdaO+pwLVESClYu39Vp9DM3lH5Nv1I7mXWFLrQxmfWEfQJAZZW4RV+kBWUbiDi9u2F4d8FJE4bedjUWnFbeZTUSuE6FMloDUEJqYoDG6ARo+AwVXhiNDQgFlExpRvB8Sz+wqQ==
-----END RSA PRIVATE KEY-----';
	private $_host = 'https://openapi.bestsign.info/openapi/v2';
	
    private static $_instances;
    private $_default_user_agent = '';
    private $_response_headers = '';
	
    const DEFAULT_CONNECT_TIMEOUT = 60; //默认连接超时
    const DEFAULT_READ_TIMEOUT = 6000; //默认读取超时
    const MAX_REDIRECT_COUNT = 10;

    public function setDefaultUserAgent($user_agent) {
        $this->_default_user_agent = $user_agent;
        return $this;
    }
    
    public function get($url, array $headers = array(), $auto_redirect = true, $cookie_file = null) 
    {
        return $this->_request($url, "GET", null, null, $headers, $auto_redirect, $cookie_file);
    }
    
    public function post($url, $post_data = null, $post_files = null, array $headers = array(), $cookie_file = null)
    {
        return $this->_request($url, "POST", $post_data, $post_files, $headers, $cookie_file);
    }
    
    private function _headerCallback($ch, $data)
    {
        $this->_response_headers .= $data;
        return strlen($data);
    }
    
    private function _request($url, $method = "GET", $post_data = null, $post_files = null, array $headers = array(), $auto_redirect = true, $cookie_file = null)
    {
        //$url = 'http://localhost/ssq/test.php';
        if (strcasecmp($method, "POST") == 0) {
            $method = 'POST';
        }
        else {
            $method = 'GET';
        }

        if (!empty($post_files) && !is_array($post_files))
        {
            $post_files = array();
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_READ_TIMEOUT);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        
        if (!empty($cookie_file))
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);     
        }
        
        // set location
        if ($auto_redirect)
        {
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, self::MAX_REDIRECT_COUNT);
        }
        
        // set callback
        $this->_response_headers = '';
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, '_headerCallback'));
        
        // set https
        if (0 == strcasecmp('https://', substr($url, 0, 8)))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    
        }
        
        // set headers
        if (!is_array($headers))
        {
            $headers = array();
        }
        if (!empty($this->_default_user_agent))
        {
            $has_user_agent = false;
            foreach ($headers as $line)
            {
                $row = explode(':', $line);
                $name = trim($row[0]);
                if (strcasecmp($name, 'User-Agent') == 0)
                {
                    $has_user_agent = true;
                    break;
                }
            }
            if (!$has_user_agent)
            {
                $headers[] = "User-Agent: " . $this->_default_user_agent;
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // set post
        if ($method == 'POST')
        {

            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($post_data) || !empty($post_files))
            {
                $post = array();
                if (!empty($post_files)) {
                    foreach ($post_files as $name => $file_path) {
                        if (is_file($file_path)) {
                            $post[$name] = "@{$file_path}";    
                        }
                    }
                    if (!is_array($post_data)) {
                        $tmp_post_data_list = implode('&', $post_data);
                        $post_data = array();
                        foreach ($tmp_post_data_list as $line) {
                            $item = explode('=', $line);
                            $name = $item[0];
                            $value = isset($item[1]) ? rawurldecode($item[1]) : '';
                            $post[$name] = $value;
                        }
                    }
                }
                else {
                    $post = $post_data;
                }
                
                if (!empty($post)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                }
            }
        }
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        $errno = 0;
        $errmsg = '';
        $errno = curl_errno($ch);
            $errmsg = curl_error($ch);
        
        if (false === $response) {
            $errno = curl_errno($ch);
            $errmsg = curl_error($ch);
        }
        curl_close($ch);
        
        if ($errno != 0) {
            throw new \Exception("Http Request Wrong: {$errno} - {$errmsg}");
        }
        
        $result = array(
            'http_code' => $http_code,
            'errno' => $errno,
            'errmsg' => $errmsg,
            'headers' => $this->_response_headers,
            'response' => $response,
        );
        
        return $result;
    }	
    /**
     * @param $path：接口名
     * @param $url_params: get请求需要放进参数中的参数
     * @param $rtick：随机生成，标识当前请求
     * @param $post_md5：post请求时，body的md5值
     * @return string
     */
    private function _genSignData($path, $url_params, $rtick, $post_md5)
    {
        $request_path = parse_url($this->_host . $path)['path'];

        $url_params['developerId'] = $this -> _developerId;
        $url_params['rtick'] = $rtick;
        $url_params['signType'] = 'rsa';

        ksort($url_params);

        $sign_data = '';
        foreach ($url_params as $key => $value)
        {
            $sign_data = $sign_data . $key . '=' . $value;
        }
        $sign_data = $sign_data . $request_path;

        if (null != $post_md5)
        {
            $sign_data = $sign_data . $post_md5;
        }
        return $sign_data;
    }

    private function _getRequestUrl($path, $url_params, $sign, $rtick)
    {
        $url = $this->_host .$path . '?';

        //url
        $url_params['sign'] = $sign;
        $url_params['developerId'] = $this -> _developerId;
        $url_params['rtick'] = $rtick;
        $url_params['signType'] = 'rsa';

        foreach ($url_params as $key => $value)
        {
            $value = urlencode($value);
            $url = $url . $key . '=' . $value . '&';
        }

        $url = substr($url, 0, -1);
        return $url;
    }

    private function _formatPem($rsa_pem, $pem_type = '')
    {
        //如果是文件, 返回内容
        if (is_file($rsa_pem))
        {
            return file_get_contents($rsa_pem);
        }

        //如果是完整的证书文件内容, 直接返回
        $rsa_pem = trim($rsa_pem);
        $lines = explode("\n", $rsa_pem);
        if (count($lines) > 1)
        {
            return $rsa_pem;
        }

        //只有证书内容, 需要格式化成证书格式
        $pem = '';
        for ($i = 0; $i < strlen($rsa_pem); $i++)
        {
            $ch = substr($rsa_pem, $i, 1);
            $pem .= $ch;
            if (($i + 1) % 64 == 0)
            {
                $pem .= "\n";
            }
        }
        $pem = trim($pem);
        if (0 == strcasecmp('RSA', $pem_type))
        {
            $pem = "-----BEGIN RSA PRIVATE KEY-----\n{$pem}\n-----END RSA PRIVATE KEY-----\n";
        }
        else
        {
            $pem = "-----BEGIN PRIVATE KEY-----\n{$pem}\n-----END PRIVATE KEY-----\n";
        }
        return $pem;
    }
    /**
     * 获取签名串
     * @param $args
     * @return
     */
    public function getRsaSign()
    {
        $pkeyid = openssl_pkey_get_private($this->_pem);
        if (!$pkeyid)
        {
            throw new \Exception("openssl_pkey_get_private wrong!", -1);
        }

        if (func_num_args() == 0) {
            throw new \Exception('no args');
        }
        $sign_data = func_get_args();
        $sign_data = trim(implode("\n", $sign_data));

        openssl_sign($sign_data, $sign, $this->_pem);
        openssl_free_key($pkeyid);
        return base64_encode($sign);
    }
    //执行请求
    public function execute($method, $url, $request_body = null, array $header_data = array(), $auto_redirect = true, $cookie_file = null)
    {
        $response = $this->request($method, $url, $request_body, $header_data, $auto_redirect, $cookie_file);

        $http_code = $response['http_code'];
        if ($http_code != 200)
        {
            throw new \Exception("Request err, code: " . $http_code . "\nmsg: " . $response['response'] );
        }

        return $response['response'];
    }

    public function request($method, $url, $post_data = null, array $header_data = array(), $auto_redirect = true, $cookie_file = null)
    {
        $headers = array();
        $headers[] = 'Content-Type: application/json; charset=UTF-8';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'Connection: keep-alive';

        foreach ($header_data as $name => $value)
        {
            $line = $name . ': ' . rawurlencode($value);
            $headers[] = $line;
        }

        if (strcasecmp('POST', $method) == 0)
        {
            $ret = $this->post($url, $post_data, null, $headers, $auto_redirect, $cookie_file);
        }
        else
        {
            $ret = $this->get($url, $headers, $auto_redirect, $cookie_file);
        }
        return $ret;
    }	
    //********************************************************************************
    // 接口
    //********************************************************************************
    public function regBaseUser($account, $mail, $mobile, $name, $userType, $credential=null, $applyCert='1')
    {

        $path = "/user/reg/";

        //post data
        $post_data['email'] = $mail;
        $post_data['mobile'] = $mobile;
        $post_data['name'] = $name;
        $post_data['userType'] = $userType;
        $post_data['account'] = $account;
        $post_data['credential'] = $credential;
        $post_data['applyCert'] = $applyCert;

		$response = $this->basePara($path, $post_data);
        return $response;
    }
	
	//基础jsonArr数据封装
    /**
     * @param $post_data: 请求的参数
     * @param $data_para：要处理的参数键值
     * @return string
     */	
    public function getjsonArr($post_data = array(), $data_param = '')
    {
		
		$post_data[$data_param] = '['.json_encode($post_data[$data_param]).']';		
        //$response = json_encode($post_data);
		$content = '';
		foreach($post_data as $k=>$v){
			if($k==$data_param){
				$content.='"'.$k.'":'.$v.',';
			}else{
				$content.='"'.$k.'":"'.$v.'",';
			}
		}
		$content = rtrim($content, ',');
		$response = '{'.$content.'}';
        return $response;
    }
	//基础数据封装
    /**
     * @param $path：接口名
     * @param $post_data: 请求的参数
     * @param $method： 请求方式POST或GET
	 * @param $jsonStr  是否是json格式
     * @return string
     */	
    public function basePara($path = '', $post_data = array(), $method = '', $jsonStr = false)
    {

        $path = $path;
		
		$url_params = $post_data;
		if(!$jsonStr){
			$post_data = json_encode($post_data);
		}
        //rtick
        $rtick = time().rand(1000, 9999);

        //header data
        $header_data = array();
		if(!$method){
			$method = 'POST';
			//sign data
			$sign_data = $this->_genSignData($path, null, $rtick, md5($post_data));

			//sign
			$sign = $this->getRsaSign($sign_data);

			$params['developerId'] = $this -> _developerId;
			$params['rtick'] = $rtick;
			$params['signType'] = 'rsa';
			$params['sign'] =$sign;

			//url
			$url = $this->_getRequestUrl($path, null, $sign, $rtick);
			//var_dump($url);
			//var_dump($post_data);die;
			$response = $this->execute($method, $url, $post_data, $header_data, true);
		}else{
			//sign
			$sign_data = $this->_genSignData($path, $url_params, $rtick, null);
			$sign = $this->getRsaSign($sign_data);

			$url = $this->_getRequestUrl($path, $url_params, $sign, $rtick);

			//content
			$response = $this->execute('GET', $url, null, $header_data, true);			
		}
        //content
        
        return $response;
    }
	//下载签名/公章
    public function downloadSignatureImage($account, $image_name)
    {
        $path = "/signatureImage/user/download/";

        $url_params['account'] = $account;
        $url_params['imageName'] = $image_name;

        //rtick
        $rtick = time() . rand(1000, 9999);

        //sign
        $sign_data = $this->_genSignData($path, $url_params, $rtick, null);
        $sign = $this->getRsaSign($sign_data);

        $url = $this->_getRequestUrl($path, $url_params, $sign, $rtick);

        //header data
        $header_data = array();

        //content
        $response = $this->execute('GET', $url, null, $header_data, true);

        return $response;
    }
	/**
	* 获取PDF的页数
	*/
	public function getPageTotal($path){
		// 打开文件
		if (!$fp = @fopen($path,"r")) {
		  $error = "打开文件{$path}失败";
		  return false;
		}
		else {
		  $max=0;
		  while(!feof($fp)) {
			$line = fgets($fp,255);
			if (preg_match('/\/Count [0-9]+/', $line, $matches)){
			  preg_match('/[0-9]+/',$matches[0], $matches2);
			  if ($max<$matches2[0]) $max=$matches2[0];
			}
		  }
		  fclose($fp);
		  // 返回页数
		  return $max;
		}
	}
	/**
	* 几个月后的时间戳
	*/
	public function getMonthTimes($month){
      return strtotime("+".$month." months");
	}

    /**
     * curlPost请求
     */
    public function curlPost($url,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data); //data为json串时  需要开启头部设置
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //设置头部信息
        //$headers = array('Content-Type:application/json; charset=utf-8','Content-Length: '.strlen($data));//data为二维数组，需要注释头部注释 为json串时开启头部配置
        //curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        //执行请求
        $output = curl_exec($ch);
        return $output;
    }
	//****************************************************************************************************
	// demo functions
	//****************************************************************************************************
	//注册个人用户
	function regUser()
	{

		$mail = time() . rand(1000, 9999)."@test.com";
		$identity = '612722199103133571';
		$account = $identity;
		$mobile = "18111555206";
		$name = "test_name1";
		$user_type = "1";
		
		$credential['identity'] = $identity;
		$credential['identityType'] = '0';
		$credential['contactMobile'] = '';
		$credential['contactMail'] = '506682730@qq.com';
		$credential['province']= '';
		$credential['city'] = '';
		$credential['address'] = '';

		$applyCert = '1';

		$response = $this->regBaseUser($account, $mail, $mobile, $name, $user_type, $credential, $applyCert);
		var_dump($response);
	}
	//注册企业用户
	function regUserWithCredential()
	{
		var_dump("Test regUser with credential...");

		$mail = "296274300@qq.com";
		$account = "510231196811155237";
		$mobile = "13668169319";
		$name = "成都中国旅行社有限公司";
		$user_type = "2";

		$credential['regCode'] = '91510100734794268U';
		$credential['orgCode'] = '91510100734794268U';
		$credential['taxCode'] = '91510100734794268U';
		$credential['legalPerson'] = '彭忠';
		$credential['legalPersonIdentity'] = '510231196811155237';
		$credential['legalPersonIdentityType'] = '0';
		$credential['legalPersonMobile'] = '13668169319';
		$credential['contactMobile'] = '13668169319';
		$credential['contactMail'] = '296274300@qq.com';
		$credential['province']= '';
		$credential['city'] = '';
		$credential['address'] = '';

		$applyCert = '1';

		$response = $this->regBaseUser($account, $mail, $mobile, $name, $user_type, $credential, $applyCert);
		return $response;
	}		
	//异步查询证书状态
	function checkTaskStatus()
	{
        $path = "/user/async/applyCert/status/";
        //post data
        $post_data['account'] = '510231196811155237';
		$post_data['taskId'] = '153905567901000001';
		
		$response = $this->basePara($path, $post_data);
        return $response;
	}
	
	//查询证书编号
	function getCert()
	{
        $path = "/user/getCert/";
        //post data
        $post_data['account'] = '15389804737539@test.com';
        
		$response = $this->basePara($path, $post_data, 'POST');
        return $response;
	}
	//查询个人用户证件信息
	function getPersonalCredential()
	{
        $path = "/user/getPersonalCredential/";
        //post data
        $post_data['account'] = '15389804737539@test.com';
		$post_data['account'] = '612722199103133571';
		$response = $this->basePara($path, $post_data);
        return $response;
	}	
	//查询企业用户证件信息
	function getEnterpriseCredential()
	{
        $path = "/user/getEnterpriseCredential/";
        //post data
        $post_data['account'] = '15389804737539@test.com';
        
		$response = $this->basePara($path, $post_data);
        return $response;
	}
	//获取证书详细信息
	function getCertInfo()
	{
        $path = "/user/cert/info/";
        //post data
        $post_data['account'] = '15389804737539@test.com';
		$post_data['certId'] = 'BJCA-33-20181008143433648-81724';
        
		$response = $this->basePara($path, $post_data);
        return $response;
	}
	//上传企业公章
	function qyUpSignImage()
	{
		$img_base64 = '';
		$app_img_file = 'zl.jpg';   //绝对路径
		$img_info = getimagesize($app_img_file);//取得图片的大小，类型等
		$fp = fopen($app_img_file, "r");     //图片是否可读权限
		if ($fp) {
			$file_content = chunk_split(base64_encode(fread($fp, filesize($app_img_file))));//base64编码
			switch ($img_info[2]) {  //判读图片类型
				case 1:
					$img_type = "gif";
					break;
				case 2:
					$img_type = "jpg";
					break;
				case 3:
					$img_type = "png";
					break;
			}
		}
		$img_base64 = 'data:image/' . $img_type .';base64,' . $file_content;//合成图片的base64编码
		//var_dump($img_base64);
	    fclose($fp);		
        $path = "/signatureImage/user/upload/";
        //post data
        $post_data['account'] = '510231196811155237';
		$post_data['imageData'] = $file_content;
		$post_data['imageName'] = $post_data['account'];
        
		$response = $this->basePara($path, $post_data);
        return $response;
	}
	//下载企业公章
	function qyDlSignImage()
	{		
        $path = "/signatureImage/user/download/";
        //post data
        $post_data['account'] = '510231196811155237';
		$post_data['imageName'] = $post_data['account'];
		//$response = $this->downloadSignatureImage($post_data['account'],$post_data['imageName']);  //原测试案例
		$response = $this->basePara($path, $post_data, "GET");
		$fp = fopen ( $post_data['account'].'.png', 'w+' );//新建png文件
		if($fp){
			fwrite ( $fp, $response );  //二进制流写入文件
			fclose ( $fp );  
		}
        return $response;
	}
	//上传doc ，docx文件并转化为pdf
	function upFiletoPdf()
	{	// https://stackoverflow.com/questions/8986067/how-to-get-the-number-of-pages-in-a-word-document-on-linux  得到页数案例
		$url = "http://fileok.mankk.cn/line//05899923793617697.doc";
		//$url = "http://fileok.mankk.cn/line//05899950157579504.docx";
		$response = file_get_contents($url);
		$fp = fopen ( 'aa.doc', 'w+' );//新建png文件
		if($fp){
			fwrite ( $fp, $response );  //二进制流写入文件
			fclose ( $fp );  
		}
        return $response;
	}
		//上传doc ，docx, pdf文件
	function upFile()
	{	
		//$filename = "cc.docx";
		$filename = "aa.doc";
		$filepath = "./".$filename;
		//$filepath = "http://101.201.70.35/test.pdf";   //远程文件
        $md5file = md5_file($filepath); //得到文件的md5
		$ftype = substr($filepath,strripos($filepath,".")+1);
		//var_dump($md5file);var_dump($ftype);die;
		$fp = fopen($filepath, "r");     //文件是否可读权限
		if ($fp) {
			//$file_content = chunk_split(base64_encode(fread($fp, filesize($filepath))));//base64编码
			
			$file_content = chunk_split(base64_encode(file_get_contents($filepath)));   //远程文件得到base64编码
		}
		fclose ( $fp );
		$path = "/storage/upload/";
        //post data
        $post_data['account'] = '510231196811155237';
		$post_data['fdata'] = $file_content;
		$post_data['fmd5'] = $md5file;
		$post_data['ftype'] = $ftype;
		$post_data['fname'] = $filename;
		$post_data['fpages'] = 100;  //此处的页码数只要大于实际页码数就没问题
        
		$response = $this->basePara($path, $post_data);
		//fid: 7887457438124855003  account: 510231196811155237  
		//fid: 1675922615677631156  account: 510231196811155237 
        return $response;		

	}
	//doc ，docx文件并转化为pdf
	function toPdf()
	{	
		$path = "/storage/convert/";
        //post data
        $post_data['account'] = '510231196811155237';
		//$post_data['fid'] = "7887457438124855003";
		$post_data['fid'] = "4832731977038900811";
		$post_data['ftype'] = "PDF";
		$response = $this->basePara($path, $post_data);
        return $response;		

	}
	//下载pdf
	function dolPdf()
	{	
		$path = "/storage/download/";
        //post data
		$post_data['fid'] = "675819628827218391";
		$post_data['fid'] = "6663789385475722304";
		$post_data['fid'] = "8082542010827255142";
		//$post_data['fid'] = "4832731977038900811";  //doc 文档
		$file_type = ".pdf";
		//$file_type = ".doc";
		$response = $this->basePara($path, $post_data, "GET");
		$fp = fopen ( $post_data['fid'].$file_type, 'w+' );//新建文件
		if($fp){
			fwrite ( $fp, $response );  //二进制流写入文件
			fclose ( $fp );  
		}
        return $response;		

	}
	//得到pdf的页码
	function ss()
	{
	    return json_encode($_POST);
		$filename = "5996267343751410328.pdf";
		$filepath = "./".$filename;
		$filepath = "http://101.201.70.35/test.pdf";
		$response = $this->getPageTotal($filepath);
        return $response;		

	}
	//创建单文件合同
	function createContract()
	{	
		$path = "/contract/create/";
        //post data
        $post_data['account'] = '510231196811155237';
		$post_data['fid'] = "4761274319626032401";
		$post_data['expireTime'] = $this->getMonthTimes(1).'';  //1个月后的时间戳
		$post_data['title'] = "测试合同";
		$post_data['description'] = "";
		$post_data['hotStoragePeriod'] = "31536000";
		$response = $this->basePara($path, $post_data);
		// contractId  153922895201000001   153923893801000001
        return $response;	

	}
	//得到单文件合同预览网址
	function getContractView()
	{	
		$path = "/contract/getPreviewURL/";
        //post data
        $post_data['contractId'] = '153924058501000001';
		$post_data['account'] = '510231196811155237';
		$post_data['dpi'] = '160';
		$post_data['expireTime'] = '0';  //1个月后的时间戳
		$response = $this->basePara($path, $post_data);
		// url  153922895201000001
        //return $response;	
		$arr = json_decode($response,true);
		var_dump($arr);

	}
	//自动签署单文件合同(旅行社)
	function signContract()
	{	
		$path = "/storage/contract/sign/cert/";
        //post data
		$fid = "4761274319626032401";  //合同对应的合同文件
		$filepath = './'.$fid.'.pdf';
		$pagenum = $this->getPageTotal($filepath);
		$arr = array();
	    $arr['pageNum'] = '1';
		$arr['x'] = '0.4';
		$arr['y'] = '0.5';
		$arr['rptPageNums'] = '0';
        $post_data['contractId'] = '153924058501000001';
		$post_data['signer'] = '510231196811155237';
		$post_data['signatureImageName'] = $post_data['signer'];
		$post_data['signaturePositions'] = $arr; 
		$jsonStr = $this->getJsonArr($post_data,'signaturePositions');  //提前处理为jsonArr格式
		//var_dump($jsonStr);die;
		$response = $this->basePara($path, $jsonStr, '', true);
		// url  153922895201000001
        //return $response;	
		$arrs = json_decode($response,true);
		var_dump($arrs);

	}
	
	//手动签署单文件合同(游客)
	function sendContract()
	{	
		$path = "/contract/send/";
        //post data
		$fid = "4761274319626032401";  //合同对应的合同文件
		$filepath = './'.$fid.'.pdf';
		$pagenum = $this->getPageTotal($filepath);
	    $arr['pageNum'] = $pagenum;
		$arr['x'] = '0.7';
		$arr['y'] = '0.5';
        $post_data['contractId'] = '153924058501000001';
		$post_data['signer'] = '612722199103133571';
		$post_data['dpi'] = '120';
		$post_data['isAllowChangeSignaturePosition'] = '1';
		$post_data['vcodeMobile'] = '';      		 //手写签名收验证码手机号，可不填即不收取验证码
		$post_data['isDrawSignatureImage'] = '1';    //1点击签名图片能触发手写面板 2强制必须手绘签名
		$post_data['sid'] = '';    					 //平台流水号
		$post_data['pushUrl'] = '';    				 //平台接收回调地址，不填选默认
		$post_data['signaturePositions'] = $arr; 
		$jsonStr = $this->getJsonArr($post_data,'signaturePositions');  //提前处理为jsonArr格式
		//var_dump($jsonStr);die;
		$response = $this->basePara($path, $jsonStr, '', true);
		$arrs = json_decode($response,true);
		var_dump($arrs);

	}
	//得到合同签署者状态
    function getSignerStatus()
    {
        $path = "/contract/getSignerStatus/";

        $url_params['contractId'] = '153924058501000001';
		$response = $this->basePara($path, $url_params);
        return $response;
    }
	//撤销单文件合同
    function cancelContract()
    {
        $path = "/contract/cancel/";

        $url_params['contractId'] = '153924058501000001';
		$response = $this->basePara($path, $url_params);
        return $response;
    }
	//锁定并结束单文件合同
    function lockContract()
    {
        $path = "/storage/contract/lock/";

        $url_params['contractId'] = '153924058501000001';
		$response = $this->basePara($path, $url_params);
        return $response;
    }	
	//下载单文件合同
    function downloadContract()
    {
        $path = "/storage/contract/download/";

        $url_params['contractId'] = '153924058501000001';
		$response = $this->basePara($path, $url_params, 'GET');
		file_put_contents("ceshi.pdf",$response);
        return $response;
    }
	//创建合同目录
    function createCatalog()
    {
		$path = "/catalog/create/";
        //post data
        $post_data['senderAccount'] = '510231196811155237';
		$post_data['expireTime'] = $this->getMonthTimes(1).'';  //1个月后的时间戳
		$post_data['catalogName'] = "测试多文件合同目录";
		$post_data['description'] = "";
		$response = $this->basePara($path, $post_data);
        return $response;	
    }
	//合同目录添加合同文件
    function addContract()
    {
		$path = "/catalog/uploadContract/";
        //post data
        $post_data['senderAccount'] = '510231196811155237';
		$post_data['catalogName'] = "测试多文件合同目录";   //合同目录唯一标识
		$post_data['fid'] = "8082542010827255142";       //6663789385475722304   8082542010827255142
		$post_data['title'] = "测试3333";
		$response = $this->basePara($path, $post_data);
        return $response;	
    }
	//得到合同目录的合同列表
    function getContracts()
    {
		$path = "/catalog/getContracts/";
        //post data
		$post_data['catalogName'] = "测试多文件合同目录";   //合同目录唯一标识
		$response = $this->basePara($path, $post_data);
        return $response;	
    }
	//自动签署多文件合同(旅行社)
	function signCatalog()
	{	
		$orderPath = "/catalog/getContracts/";
        //post data
		$order_post_data['catalogName'] = "测试多文件合同目录";   //合同目录唯一标识
		$res = $this->basePara($orderPath, $order_post_data);
		$arrs = json_decode($res,true);
		$path = "/contract/sign/cert/";
        //post data

	    $arr['pageNum'] = '1';
		$arr['x'] = '0.4';
		$arr['y'] = '0.5';
		$arr['rptPageNums'] = '0';
		foreach($arrs['data']['contracts'] as $k=>$v){
			$post_data['contractId'] = $v['contractId'];
			$post_data['signerAccount'] = '510231196811155237';
			$post_data['signatureImageName'] = $post_data['signerAccount'];
			$post_data['signaturePositions'] = $arr; 
			$jsonStr = $this->getJsonArr($post_data,'signaturePositions');  //提前处理为jsonArr格式
			$response = $this->basePara($path, $jsonStr, '', true);
			$arrss[] = json_decode($response,true);			
		}
		var_dump($arrss);
	}
	
	//手动签署多文件合同(游客)
	function sendCatalog()
	{	
		$path = "/catalog/send/";
        //post data
		$fid = "6663789385475722304";  //合同对应的合同文件
		$filepath = './'.$fid.'.pdf';
		$pagenum = $this->getPageTotal($filepath);
	    $arr['pageNum'] = $pagenum;
		$arr['pageNum'] = '1';
		$arr['x'] = '0.7';
		$arr['y'] = '0.5';
        $post_data['catalogName'] = '测试多文件合同目录';
		$post_data['signerAccount'] = '612722199103133571';
		$post_data['vcodeMobile'] = '';      		 //手写签名收验证码手机号，可不填即不收取验证码
		$post_data['isDrawSignatureImage'] = '1';    //1点击签名图片能触发手写面板 2强制必须手绘签名
		$post_data['contractParams']['测试22222222222']['signaturePositions'] = '-';
		$post_data['contractParams']['测试3333']['signaturePositions'] = '|';
		$temp = '['.json_encode($arr).']';
		$str = json_encode($post_data);
		$str = str_replace('"-"',$temp,$str);
		$str = str_replace('"|"',$temp,$str);
		$jsonStr = $str;  //提前处理为jsonArr格式
		//var_dump($jsonStr);die;
		$response = $this->basePara($path, $jsonStr, '', true);
		$arrs = json_decode($response,true);
		var_dump($arrs);

	}
	//得到多文件合同预览网址
	function getCatalogView()
	{	
		$path = "/catalog/getPreviewURL/";
        //post data
        $post_data['catalogName'] = '测试多文件合同目录';
		$post_data['signerAccount'] = '510231196811155237';
		$post_data['dpi'] = '160';
		$post_data['expireTime'] = '0';  //1个月后的时间戳
		$response = $this->basePara($path, $post_data);
		// url  153922895201000001
        //return $response;	
		$arr = json_decode($response,true);
		var_dump($arr);

	}
	//结束多文件合同
	function lockCatalog()
	{	
		$path = "/catalog/lock/";
        //post data
        $post_data['catalogName'] = '测试多文件合同目录';
		$response = $this->basePara($path, $post_data);
		// url  153922895201000001
        //return $response;	
		$arr = json_decode($response,true);
		var_dump($arr);

	}

    //生成自由合同
    function createFreeContract()
    {
        //注册合同游客用户

        //上传合同文件

        //创建合同

        //自动签署

    }
    //生成订单合同
    function createOrderContract()
    {
        //注册合同游客用户

        //上传合同文件

        //创建合同目录

        //向合同目录添加文件

        //自动签署

    }

    //测试rpc生成合同请求
    function ceshiCreate()
    {
        $host = 'http://sdata.mankkk.cn';
        $url = $host.'/Home/ordersService';
        $client = new jsonRPCClient("$url");
        $where = array();
        $pram['session']=$_SESSION;
        $pram['post']=$_POST;
        $pram['get']=$_GET;
        $pram['where']=$where;
        $pram['mode']='sendCreatTwo';
        $result = $client->contract($pram);
        var_dump($result);die;
        //$this->ajaxReturn($result['data']);
        //return $result['data'];
    }
}