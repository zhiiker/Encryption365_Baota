<?php
namespace TrustOcean\Encryption365\Common;
use TrustOcean\Encryption365\Encryption365Exception;

class Encryption365Service {

    /**
     * 获取客户端的登录凭证
     * @return array
     */
    protected static function getClientLoginDetails() {
        $db = DatabaseUtils::initLocalDatabase();
        $client_id =  ($db->query("select `value` from configuration where `setting` like 'client_id'")->fetch())['value'];
        $access_token =  ($db->query("select `value` from configuration where `setting` like 'access_token'")->fetch())['value'];
        return array(
            'client_id'=>$client_id,
            'access_token'=>$access_token
        );
    }
    /**
     * 登录并注册客户端
     * @param $username
     * @param $password
     * @return array|mixed
     */
    public static function clientCreate($username, $password) {
        return self::callAPI("/client/create", array(
            'username'=>$username,
            'password'=>$password,
            'servername'=>"example.com",
        ));
    }

    /**
     * 创建新的证书订单
     * @param $pid
     * @param $period
     * @param $csr_code
     * @param $domains
     * @param $org_id
     * @return array|mixed
     * @throws Encryption365Exception
     */
    public static function certCreate($pid, $period, $csr_code, $domains,$org_id, $renew=false, $old_vendor_id=-1) {
        $logicData = array(
            'pid'=>$pid,
            'period'=>$period,
            'csr_code'=>$csr_code,
            'domains'=>implode(',', $domains),
            'renew'=>$renew,
            'old_vendor_id'=>$old_vendor_id,
        );
        // 增加企业信息提交
        if($org_id !== 0 && $org_id != -1){
          $db = DatabaseUtils::initLocalDatabase();
          $org = $db->query('select * from organization_template where id = ?', ($org_id))->fetch();
          if(empty($org)){
              throw new Encryption365Exception("企业申请人信息不正确, 请您检查信息模板");
          }
          unset($org['id']);
          foreach ($org as $kyn => $value){
              $logicData[$kyn] = $value;
          }
        }

        return self::callAPI('/cert/create', array_merge(self::getClientLoginDetails(),$logicData));
    }

    /**
     * 重签SSL证书
     * @param $vendor_id
     * @param $csr_code
     * @param $domains
     * @return array|mixed
     */
    public static function certReissue($vendor_id, $csr_code, $domains) {
        return self::callAPI('/cert/reissue', array_merge(self::getClientLoginDetails(),array(
            'trustocean_id'=>$vendor_id,
            'csr_code'=>$csr_code,
            'domains'=>implode(',', $domains)
        )));
    }

    /**
     * 获取账户产品和价格
     * @return mixed
     * @throws Encryption365Exception
     */
    public static function getProducts() {
        $call = self::callAPI('/account/products', array_merge(self::getClientLoginDetails(), []));
        if($call['status']==="error"){
            throw new Encryption365Exception($call['message']);
        }else{
            return $call['products'];
        }
    }

    /**
     * 获取最新的版本更新信息
     * @return array|mixed
     */
    public static function checkUpdateVersion() {
        return self::callAPI('/client/version', array_merge(self::getClientLoginDetails(),array()));
    }

    /**
     * 发送注册验证码
     * @param $email
     * @return array|mixed
     */
    public static function authcodeSend($email) {
        return self::callAPI('/account/register/authcode', array_merge(self::getClientLoginDetails(),array(
            'username'=>$email
        )));
    }

    /**
     * 校验验证码并注册新的环智中诚账户
     * @param $email
     * @param $password
     * @param $authcode
     * @param $realName
     * @param $idcardNumber
     * @param $phoneNumber
     * @param $country
     * @param $companyName
     * @return array|mixed
     */
    public static function accountRegister($email,$password, $authcode, $realName,$idcardNumber, $phoneNumber,$country,$companyName) {
        return self::callAPI('/account/register', array_merge(self::getClientLoginDetails(),array(
            'username'=>$email,
            'password'=>$password,
            'authcode'=>$authcode,
            'realName'=>$realName,
            'idcardNumber'=>$idcardNumber,
            'phoneNumber'=>$phoneNumber,
            'country'=>$country,
            'companyname'=>$companyName,
        )));
    }
    /**
     * 查询证书的详细信息和签发状态
     * @param $vendor_id
     * @return array|mixed
     */
    public static function certDetails($vendor_id) {
        return self::callAPI('/cert/details', array_merge(self::getClientLoginDetails(),array(
            'trustocean_id'=>$vendor_id
        )));
    }

    /**
     * 重新执行域名验证
     * @param $vendor_id
     * @return array|mixed
     */
    public static function certReValidation($vendor_id) {
        return self::callAPI('/cert/challenge', array_merge(self::getClientLoginDetails(),array(
            'trustocean_id'=>$vendor_id
        )));
    }

    /**
     * 获取本地客户端版本
     * @return string
     */
    public static function getClientVersion() {
        $info = json_decode(file_get_contents(__DIR__.'/../../info.json'), 1);
        if (isset($info['versions'])){
            return $info['versions'];
        }else{
            return 'error-or-not-set';
        }
    }

    /**
     * 创建支付宝充值账单
     * @param $amount
     * @return array|mixed
     */
    public static function createAlipayInvoice($amount) {
        return self::callAPI('/payment/alipay/create', array_merge(self::getClientLoginDetails(),array(
            'amount'=>$amount
        )));
    }

    /**
     * 获取主账户信息
     * @return array|mixed
     */
    public static function getAccountDetails() {
        return self::callAPI('/account/details', array_merge(self::getClientLoginDetails(),[]));
    }

    /**
     * 获取支付状态
     * @return array|mixed
     */
    public static function getInvoiceStatus($invoiceid) {
        return self::callAPI('/invoice/status', array_merge(self::getClientLoginDetails(),array(
            'invoiceid'=>$invoiceid
        )));
    }

    /**
     * 取消充值账单
     * @param $invoiceid
     * @return array|mixed
     */
    public static function revokeInvoice($invoiceid) {
        return self::callAPI('/invoice/revoke', array_merge(self::getClientLoginDetails(),array(
            'invoiceid'=>$invoiceid
        )));
    }

    /**
     * CURL Handle
     * @param string $uri
     * @param array $params
     * @return array|mixed
     */
    private static function callAPI(string $uri, array $params) {
        $postVars = json_encode($params);
        // todo:: 检查设置的API版本
        $apiURL = 'https://encrypt365.trustocean.com'.$uri;
        $curlHandle = curl_init ();
        curl_setopt ($curlHandle, CURLOPT_URL, $apiURL);
        curl_setopt ($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt ($curlHandle, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ($curlHandle, CURLOPT_CAINFO, __DIR__.'/../Config/cacert.pem');
        curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, $postVars);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Encryption365-Client/'.self::getClientVersion().';BaotaPanel-LinuxVersion');
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postVars)));
        $callResult = curl_exec ($curlHandle);
        if (!curl_error ($curlHandle)) {
            curl_close ($curlHandle);
            $result = json_decode($callResult, 1);
            if(isset($result['status']) && $result['status'] === 'error'){
                return array(
                    "status"         =>  "error",
                    'message'  =>  $result['message'],
                );
            }else{
                return $result;
            }
        }else{
            return array(
                "status"         =>  "error",
                "message"       => "CURL ERROR: ".curl_error($curlHandle),
            );
        }
    }
}