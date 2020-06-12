短信接口
===================
支持阿里云，短信宝

### 阿里云 示例
```
use Xiaofan\Sms\Sms;

$config = [
    "key" => "AccessKeyId",
    "secret" => "AccessKeySecret",
    "sign" => "短信签名"
];
$sms_api = new Sms($config, 'Aliyun');
 
//单号码
$mobile = "1310000000";
//批量发送
$mobile = ["13123456789", "13012345678"];
           
$params = [
    "sms_id" => "短信模板id",
    "code" =>  "验证码",
    //多个模板参数
];

$res = $sms_api->sendSms($mobile, $params);
if (!$res) {
    echo $sms_api->getError();
}
```

### 短信宝 示例
```
use Xiaofan\Sms\Sms;

$config = [
    "account" => "AccessKeyId",
    "password" => "AccessKeySecret",
    "sign" => "短信签名"
];
$sms_api = new Sms($config, 'Smsbao');
 
//单号码
$mobile = "1310000000";
           
$params = '验证码';

$res = $sms_api->sendSms($mobile, $params);
if (!$res) {
    echo $sms_api->getError();
}
```


