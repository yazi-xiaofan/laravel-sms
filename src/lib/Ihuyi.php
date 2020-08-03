<?php

/**
 * 互亿无线
 */
class Ihuyi {

    private $config = [];
    public $error = '';

    const API_URL = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
    const GJ_API_URL = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
    const VOICE_API_URL = "http://api.voice.ihuyi.com/webservice/voice.php?method=Submit";

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
        if (empty($mobile)) {
            $this->error = "参数有误：手机号码不能为空";
            return false;
        }
        if (trim($code) == "") {
            $this->error = "参数有误：短信验证码不能为空";
            return false;
        }
        if (empty($this->config['app_id']) || empty($this->config['app_key'])) {
            $this->error = "参数有误：配置信息有误";
            return false;
        }
        if (is_array($mobile)) {
            $this->error = "不支持批量发送短信";
            return false;
        }
        $post_data = "account={$this->config['app_id']}&password={$this->config['app_key']}&mobile={$mobile}&content=" . rawurlencode("您的验证码是：{$code}。请不要把验证码泄露给其他人。");
        try {
            $result = $this->xml_to_array($this->post($post_data, self::GJ_API_URL));
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
        if ($result['SubmitResult']['code']==2) {
            return true;
        } else {
            $this->error = isset($result['SubmitResult']['msg']) ? $result['SubmitResult']['msg'] : '发送失败';
            return false;
        }
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
        if (empty($this->config['app_id']) || empty($this->config['app_key'])) {
            $this->error = "参数有误：配置信息有误";
            return false;
        }
        if (is_array($mobile)) {
            $this->error = "不支持批量发送短信";
            return false;
        }
        if ($this->config['type'] == 'voice') {
            return $this->sendVoice($mobile, $code);
        }
        $sign = isset($this->config['sign']) ? $this->config['sign'] : '思心';
        $post_data = "account={$this->config['app_id']}&password={$this->config['app_key']}&mobile={$mobile}&content=" . rawurlencode("【{$sign}】您的验证码是：{$code}。请不要把验证码泄露给其他人。");
        try {
            $result = $this->xml_to_array($this->post($post_data, self::API_URL));
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
        if ($result['SubmitResult']['code']==2) {
            return true;
        } else {
            $this->error = isset($result['SubmitResult']['msg']) ? $result['SubmitResult']['msg'] : '发送失败';
            return false;
        }
    }

    /**
     * 发送普通短信
     * @param string $mobile 
     * @param array $code
     * @return boolean
     */
    public function sendVoice($mobile, $code) {
        if (empty($mobile)) {
            $this->error = "参数有误：手机号码不能为空";
            return false;
        }
        if (trim($code) == "") {
            $this->error = "参数有误：短信验证码不能为空";
            return false;
        }
        if (empty($this->config['app_id']) || empty($this->config['app_key'])) {
            $this->error = "参数有误：配置信息有误";
            return false;
        }
        if (is_array($mobile)) {
            $this->error = "不支持批量发送短信";
            return false;
        }
        $post_data = "account={$this->config['app_id']}&password={$this->config['app_key']}&mobile={$mobile}&content={$code}";
        try {
            $result = $this->xml_to_array($this->post($post_data, self::VOICE_API_URL));
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
        if ($result['SubmitResult']['code']==2) {
            return true;
        } else {
            $this->error = isset($result['SubmitResult']['msg']) ? $result['SubmitResult']['msg'] : '发送失败';
            return false;
        }
    }
    
    //请求数据到短信接口，检查环境是否 开启 curl init。
    private function post($curlPost, $url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    //将 xml数据转换为数组格式。
    private function xml_to_array($xml) {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    private function get_message($code) {
        $msg = [
            "100" => "发送成功",
            "101" => "验证失败",
            "102" => "手机号码格式不正确",
            "103" => "会员级别不够",
            "104" => "内容未审核",
            "105" => "内容过多",
            "106" => "账户余额不足",
            "107" => "Ip受限",
            "108" => "手机号码发送太频繁，请换号或隔天再发",
            "109" => "帐号被锁定",
            "110" => "手机号发送频率持续过高，黑名单屏蔽数日",
            "120" => "系统升级",
        ];

        return isset($msg[$code]) ? $msg[$code] : "短信发送失败";
    }

}
