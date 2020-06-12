短信接口
===================
支持阿里云，短信宝

### 短信宝 示例
```
use Xiaofan\Sms\Sms;

$config = [
    "account" => "xxx",
    "password" => "xxx",
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


