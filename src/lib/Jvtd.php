<?php


class Jvtd {

    private $config = [];
    public $error = '';

    const API_URL = "https://smshttp.jvtd.cn/jtdsms/smsSend.do";
    private $url = '';
    /**
     * 互亿无线短信接口
     * @param array $config <ul>
     * <li>string $appid  </li>
     * <li>string $appkey  </li>
     * </ul>
     */
    public function __construct(array $config) {
        $this->config = $config;
    }

    /**
     * 发送国际短信
     * @param string $mobile 
     * @param array $code
     * @return boolean
     */
    public function sendGjSms($mobile, $code) {
        return $this->sendSms($mobile, $code);
    }

    /**
     * 发送普通短信
     * @param string $mobile 
     * @param array $code
     * @return boolean
     */
    public function sendSms($mobile, $code) {
        if (empty($mobile)) {
            $this->error = "参数有误：手机号码不能为空";
            return false;
        }
        if (trim($code) == "") {
            $this->error = "参数有误：短信验证码不能为空";
            return false;
        }
        if (empty($this->config['account']) || empty($this->config['password'])) {
            $this->error = "参数有误：配置信息有误";
            return false;
        }
        if (is_array($mobile)) {
            $this->error = "不支持批量发送短信";
            return false;
        }
        if (!$this->url) {
            $this->url = self::API_URL;
        }
        $sign = isset($this->config['sign']) ? $this->config['sign'] : '思心';
        $params = [
            'uid' => $this->config['account'],
            'password' => strtoupper(md5($this->config['password'])),
            'mobile' => $mobile,
            'encode' => 'utf8',
            'content' => base64_encode("【{$sign}】您验证码是{$code}"),
            'encodeType' => 'base64',
            'cid' => '',// 唯一标识，选填，如果不填系统自动生成作为当前批次的唯一标识
            'extNumber' => '',// 扩展 选填
            'schtime' => '', // 定时时间，选填，格式2008-06-09 12:00:00
        ];
        try {
            $result = $this->httpPost($params);
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
        if ($result['response_body'] > 0) {
            return true;
        } else {
            $this->error = isset($result['error_msg']) && !empty($result['error_msg']) ? $result['error_msg'] : "错误码:{$result['response_body']}";
            return false;
        }
    }
    
    /*
     * php post提交数据
     */
    private  function httpPost($data){
        // 启动一个CURL会话
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$this->url);//接口地址
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 执行操作
        $response_body = curl_exec($curl);
        //捕抓异常
        $error_msg = "";
        if (curl_errno($curl)) {
            $error_msg = 'Errno' . curl_error($curl);
        }
        // 关闭CURL会话
        curl_close($curl);
        // 返回结果
        $response["response_body"] = $response_body;//请求接口返回的数据 大于0代表成功，否则根据返回值查找错误
        $response["error_msg"] = $error_msg;//curl post 提交发生的错误
        return $response;

    }
}
