<?php
namespace TrustOcean\Encryption365;
//Boot Composer Loader
use TrustOcean\Encryption365\Common\CertificateUtils;
require __DIR__.'/../vendor/autoload.php';
/**
 * 辅助类, 提供暴露给Python调用的函数入口
 * Class PythonUtils
 * @package TrustOcean\Encryption365
 */
class PythonUtils {
    /**
     * 尝试进行站点的证书续费
     */
    public static function renewSSLOrder() {
        $ops = getopt("",[
            "cert_order_id:",
        ]);
        try{
            $result = CertificateUtils::renewTheSiteSSL($ops['cert_order_id']);
            if($result !== true){
                throw new Encryption365Exception("尝试续费时可能出现了错误, 该站点续费失败");
            }else{
                // 续费成功了
                echo json_encode(['status'=>"success"]);
            }
        }catch(Encryption365Exception $exception){
            echo json_encode([
                'status'=>'error',
                'message'=>$exception->getMessage()
            ]);
        }

    }

    /**
     * 根据域名创建新的CSR和KEY
     */
    public static function generateCsrKey() {
        $ops = getopt("",[
            "domain:",
        ]);
        $result = CertificateUtils::generateKeyPair($ops['domain'], FALSE);
        echo json_encode($result);
    }
}

// Main Processing
try{
    $utils = new PythonUtils();
    $params = getopt('',[
        'fun:'
    ]);
    $function = $params['fun'];
    if(is_callable([$utils, $function], true, $callable_name)){
        call_user_func($callable_name);
    }else{
        echo json_encode(['status'=>'error','message'=>'function not found!']);
    }
}catch(\Exception $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
