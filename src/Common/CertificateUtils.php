<?php
namespace TrustOcean\Encryption365\Common;
use blobfolio\domain\domain;
use TrustOcean\Encryption365\Encryption365Exception;
use TrustOcean\Encryption365\Repository\SiteRep;

class CertificateUtils {

    /**
     * 创建全新的SSL证书订单
     * @param $vhost_name
     * @param $pid
     * @param bool $autorenewal
     * @return bool
     * @throws Encryption365Exception
     */
    public static function createNewFullSSLOrder($vhost_name, $pid, $org_id=0, $autorenewal = false){
        $siteInfo = SiteRep::getSiteInfo($vhost_name);
        $db = DatabaseUtils::initLocalDatabase();
        //TODO 检查是否已经存在对应的证书了
        $check = $db->query('select * from certificate where site_id = ? limit 1', $siteInfo['id'])->fetch();
        if($check != NULL){
            throw new Encryption365Exception("此站点已经创建过证书");
        }
        //TODO 检查产品是否存在
        $products = Encryption365Service::getProducts();
        if(isset($products[$pid]) === FALSE){
            throw new Encryption365Exception("此产品不可订购, 请您选择其它产品");
        }
        //TODO 检查org_id
        if($products[$pid]['level'] !== "dv" && ($org_id===0 || $org_id == -1)){
            throw new Encryption365Exception("请选择申请人信息");
        }
        $product = $products[$pid];
        //TODO 还需要检查域名的合法性, 并且找出可以申请证书的域名, 以及中文域名的转换
        $domains = $siteInfo['valid_cert_domains'];
        if(empty($domains)){
            throw new Encryption365Exception("请先为站点添加有效的域名");
        }
        //TODO 创建CSR和KEY
        $newCsrAndKey = self::generateKeyPair($domains[0], FALSE); // 创建CSR，默认申请RSA证书

        die(json_encode($newCsrAndKey));

        //TODO 准备创建订单
        $orderRlt = Encryption365Service::certCreate($pid, $product['period'], $newCsrAndKey['csr_code'], $domains, $org_id);
        if($orderRlt['result'] !== "success"){
            LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_CERT_CREATED, "为站点 $vhost_name 创建新的证书订单失败了，错误信息：".$orderRlt['message']);
            throw new Encryption365Exception($orderRlt['message']);
        }
        //TODO 写入订单信息和状态到数据库
        $db->query("INSERT INTO certificate",[
            [
                'site_id'=>$siteInfo['id'],
                'type'=>$product['title'],
                'status'=>$orderRlt['cert_status'],
                'org_id'=>$org_id,
                'period'=>$product['period'],
                'domains'=>json_encode($domains),
                'is_auto_renew'=>$autorenewal===false?0:1,
                'is_business'=>$product['isFree']===false?1:0,
                'domain_count'=>count($domains),
                'pid'=>$pid,
                'dcv_info'=>json_encode($orderRlt['dcv_info']),
                'csr_code'=>$newCsrAndKey['csr_code'],
                'key_code'=>$newCsrAndKey['key_code'],
                'vendor_id'=>$orderRlt['trustocean_id'],
                'created_at'=>$orderRlt['created_at'],
                'total_fees'=>$orderRlt['total_fees']
            ]
        ]);
        //TODO 写入本地验证文件
        foreach ($orderRlt['dcv_info'] as $domain => $dcv){
            $filename = $dcv['http_filename'];
            $content = $dcv['http_filecontent'];
            $validationPath = str_replace("http://$domain/",'', str_replace($filename, '',$dcv['http_verifylink']));
            break;
        }
        NginxVhostUtils::writeValidationFile($siteInfo['vhost_info']['run_path'], $validationPath, $filename, $content);
        LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_CERT_CREATED, "为站点 $vhost_name 成功创建新的证书订单");
        return true;
    }

    /**
     * 升级为商业证书订单
     * @param $vhost_name
     * @param $pid
     * @param bool $autorenewal
     * @return bool
     * @throws Encryption365Exception
     */
    public static function upgradeToBusinessSSL($vhost_name, $pid, $org_id=0,$autorenewal = false){
        $siteInfo = SiteRep::getSiteInfo($vhost_name);

        //TODO 检查产品是否存在
        $products = Encryption365Service::getProducts();
        if(isset($products[$pid]) === FALSE){
            throw new Encryption365Exception("此产品不可订购, 请您选择其它产品");
        }
        //TODO 检查org_id
        if($products[$pid]['level'] !== "dv" && ($org_id===0 || $org_id == -1)){
            throw new Encryption365Exception("请选择申请人信息");
        }
        $product = $products[$pid];

        $db = DatabaseUtils::initLocalDatabase();
        //TODO 检查是否为商业证书
        if($product['isFree'] === true){
            throw new Encryption365Exception("请选择商业证书产品进行升级");
        }

        //TODO 还需要检查域名的合法性, 并且找出可以申请证书的域名, 以及中文域名的转换
        $domains = $siteInfo['valid_cert_domains'];
        if(empty($domains)){
            throw new Encryption365Exception("请先为站点添加有效的域名");
        }
        //TODO 创建CSR和KEY
        $newCsrAndKey = self::generateKeyPair($domains[0], FALSE); // 创建CSR，默认申请RSA证书
        //TODO 准备创建订单
        $orderRlt = Encryption365Service::certCreate($pid, $product['period'], $newCsrAndKey['csr_code'], $domains, $org_id);
        if($orderRlt['result'] !== "success"){
            LogUtils::writeLog(LogUtils::STATUS_ERROR, LogUtils::TITLE_CERT_UPGRADE,"证书升级商业证书时远端API返回失败信息, 站点:$vhost_name, 错误:".$orderRlt['message']);
            throw new Encryption365Exception($orderRlt['message']);
        }
        //TODO 更新订单信息和状态到数据库
        $db->query("UPDATE certificate SET",[
            'type'=>$product['title'],
            'status'=>$orderRlt['cert_status'],
            'period'=>$product['period'],
            'org_id'=>$org_id,
            'domains'=>json_encode($domains),
            'is_auto_renew'=>$autorenewal===false?0:1,
            'is_business'=>$product['isFree']===false?1:0,
            'domain_count'=>count($domains),
            'pid'=>$pid,
            'dcv_info'=>json_encode($orderRlt['dcv_info']),
            'csr_code'=>$newCsrAndKey['csr_code'],
            'key_code'=>$newCsrAndKey['key_code'],
            'vendor_id'=>$orderRlt['trustocean_id'],
            'created_at'=>$orderRlt['created_at'],
            'total_fees'=>$orderRlt['total_fees']
        ],'WHERE site_id = ?', $siteInfo['id']);
        //TODO 写入本地验证文件
        foreach ($orderRlt['dcv_info'] as $domain => $dcv){
            $filename = $dcv['http_filename'];
            $content = $dcv['http_filecontent'];
            $validationPath = str_replace("http://$domain/",'', str_replace($filename, '',$dcv['http_verifylink']));
            break;
        }
        NginxVhostUtils::writeValidationFile($siteInfo['vhost_info']['run_path'], $validationPath, $filename, $content);
        LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_CERT_UPGRADE,"证书升级商业证书创建成功, 站点:$vhost_name");
        return true;
    }

    /**
     * @param $cert_order_id
     * @return bool
     * @throws Encryption365Exception
     */
    public static function renewTheSiteSSL($cert_order_id){
        $db = DatabaseUtils::initLocalDatabase();
        $order = $db->query('select * from certificate where id=?', ($cert_order_id))->fetch();
        //TODO 还需要检查域名的合法性, 并且找出可以申请证书的域名, 以及中文域名的转换
        $domains = json_decode($order['domains']);
        if(empty($domains)){
            throw new Encryption365Exception("该站点没有域名, 无法续费L");
        }
        //TODO 创建CSR和KEY
        $newCsrAndKey = self::generateKeyPair($domains[0], FALSE); // 创建CSR，默认申请RSA证书
        //TODO 准备创建订单
        $orderRlt = Encryption365Service::certCreate($order['pid'], $order['period'], $newCsrAndKey['csr_code'], $domains, $order['org_id']>0?$order['org_id']:-1, true, $order['vendor_id']);
        if($orderRlt['result'] !== "success"){
            throw new Encryption365Exception($orderRlt['message']);
        }
        //TODO 更新订单信息和状态到数据库
        $db->query("UPDATE certificate SET",[
            'status'=>$orderRlt['cert_status'],
            'dcv_info'=>json_encode($orderRlt['dcv_info']),
            'csr_code'=>$newCsrAndKey['csr_code'],
            'key_code'=>$newCsrAndKey['key_code'],
            'vendor_id'=>$orderRlt['trustocean_id'],
            'created_at'=>$orderRlt['created_at'],
            'total_fees'=>$orderRlt['total_fees']
        ],'WHERE id = ?', $cert_order_id);
        //TODO 写入本地验证文件
        foreach ($orderRlt['dcv_info'] as $domain => $dcv){
            $filename = $dcv['http_filename'];
            $content = $dcv['http_filecontent'];
            $validationPath = str_replace("http://$domain/",'', str_replace($filename, '',$dcv['http_verifylink']));
            break;
        }
        // 查询站点信息
        $dbBaota = DatabaseUtils::initBaoTaSystemDatabase();
        $site = $dbBaota->query('select * from sites where id=?',($order['site_id']))->fetch();
        $siteInfo = NginxVhostUtils::getVhostConfigInfo($site['name']);
        // 写配置文件
        NginxVhostUtils::writeValidationFile($siteInfo['vhost_info']['run_path'], $validationPath, $filename, $content);
        return true;
    }

    /**
     * 重签 SSL 证书
     * @param $vhost_name
     * @return bool
     * @throws Encryption365Exception
     */
    public static function reissueSSLOrder($vhost_name) {
        //TODO 检查证书是是否存在
        $siteInfo = SiteRep::getSiteInfo($vhost_name);
        $db = DatabaseUtils::initLocalDatabase();
        //TODO 检查是否已经存在对应的证书了
        $check = $db->query('select * from certificate where `site_id` = ?',$siteInfo['id'])->fetch();
        if($check == NULL){
            throw new Encryption365Exception("未找到对应的SSL证书, 无法重签");
        }
        //TODO 检查证书状态是否为 issued_active
        if($check['status'] !== "issued_active"){
            throw new Encryption365Exception("证书状态不正确, 不可重签");
        }
        //TODO 获取站点信息、域名，检查域名合法性，并移除不正确的域名
        $domains = $siteInfo['valid_cert_domains'];
        //TODO 生成新的CSR和KEY
        $newCsrAndKey = self::generateKeyPair($domains[0], FALSE);
        //TODO 提交API重签
        $orderResult = Encryption365Service::certReissue($check['vendor_id'], $newCsrAndKey['csr_code'], $domains);
        if($orderResult['result'] !== "success"){
            throw new Encryption365Exception($orderResult['message']);
        }
        //TODO 保存新的证书订单信息
        $db->query("update certificate set", [
            'status'=>$orderResult['cert_status'],
            'dcv_info'=>json_encode($orderResult['dcv_info']),
            'csr_code'=>$newCsrAndKey['csr_code'],
            'key_code'=>$newCsrAndKey['key_code'],
            'domains'=>json_encode($domains),
            'domain_count'=>count($domains),
        ], 'WHERE id = ?', $check['id']);
        // 更新综合费用
        $db->query('update certificate set total_fees = total_fees + ? where id = ?',$orderResult['total_fees'], $check['id']);
        //TODO 写入本地验证文件
        foreach ($orderResult['dcv_info'] as $domain => $dcv){
            $filename = $dcv['http_filename'];
            $content = $dcv['http_filecontent'];
            $validationPath = str_replace("http://$domain/",'', str_replace($filename, '',$dcv['http_verifylink']));
            break;
        }
        NginxVhostUtils::writeValidationFile($siteInfo['vhost_info']['run_path'], $validationPath, $filename, $content);
        LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_CERT_REISSUE,'成功提交证书重签申请, 站点: '.$vhost_name, $check['id']);
        //TODO 返回状态
        return true;
    }

    /**
     * 创建证书申请信息和密钥对 CSR&KEY
     * @param $common_name
     * @param bool $is_ecc
     * @return array
     */
    public static function generateKeyPair($common_name, $is_ecc = FALSE){
        // 检查是否为IP地址
        $domainSp = new domain($common_name);
        if($domainSp->is_ip()){
            $common_name = "common-name-for-public-ip-address.com";
        }
        $subject = array(
            "commonName" => $common_name,
            "organizationName" => "Encryption365 SSL Security",
            "organizationalUnitName" => "Encryption365 SSL Security",
            "localityName" => "Xian",
            "stateOrProvinceName" => "Shaanxi",
            "countryName" => "CN",
        );
        try{
            // Generate a new private (and public) key pair
            if($is_ecc===FALSE){
                $private_key = openssl_pkey_new(array('private_key_type'=>OPENSSL_KEYTYPE_RSA,'private_key_bits'=>2048, 'config'=>__DIR__.'/../Config/openssl.cnf') );
                $csr_resource = openssl_csr_new($subject, $private_key, array('digest_alg'=>'sha256', 'config'=>__DIR__.'/../Config/openssl.cnf') );
            }else{
                $private_key = openssl_pkey_new( array('private_key_type'=>OPENSSL_KEYTYPE_EC,"curve_name" => 'prime256v1', 'config'=>__DIR__.'/../Config/openssl.cnf') );
                $csr_resource = openssl_csr_new($subject, $private_key, array('digest_alg'=>'sha384', 'config'=>__DIR__.'/../Config/openssl.cnf') );
            }
            openssl_csr_export($csr_resource, $csr_string);
            openssl_pkey_export($private_key, $private_key_string, null, array('config'=>__DIR__.'/../Config/openssl.cnf'));
        }catch (\Exception $exception){
            die($exception->getMessage());
        }

        return array(
            'csr_code'=>$csr_string,
            'key_code'=>$private_key_string
        );
    }
}