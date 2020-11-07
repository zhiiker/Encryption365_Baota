<?php
namespace TrustOcean\Encryption365\Controller;

//_post()会返回所有POST参数，要获取POST参数中的username参数，请使用 _post('username')
//可通过_version()函数获取面板版本
//可通过_post('client_ip') 来获取访客IP

//常量说明：
//PLU_PATH 插件所在目录
//PLU_NAME 插件名称
//PLU_FUN  当前被访问的方法名称
use TrustOcean\Encryption365\Common\CertificateUtils;
use TrustOcean\Encryption365\Common\DatabaseUtils;
use TrustOcean\Encryption365\Common\Encryption365Service;
use TrustOcean\Encryption365\Common\TwigUtils;
use TrustOcean\Encryption365\Encryption365Exception;
use TrustOcean\Encryption365\Encryption365PageException;
use TrustOcean\Encryption365\Common\LogUtils;
use TrustOcean\Encryption365\Repository\SiteRep;

/**
 * Class MainController
 * @package TrustOcean\Encryption365\Controller
 */
class MainController{
    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * MainController constructor.
     */
    public function __construct()
    {
        // 启动Twig引擎
        $this->twig = TwigUtils::initTwig();
        // 检查和初始化数据库
        $this->checkAndInstallDatabase();
        // 检查并设置自动化任务
        DatabaseUtils::installCronJob();
    }

    /**
     * 检查是否已经设置了本地账户
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function checkInitAcc() {
        // 检查是否已经进行了初始化账户绑定
        $db = DatabaseUtils::initLocalDatabase();
        $checkAcc = $clientInfo = $db->query("select * from configuration where `setting` in ('client_id','access_token')")->fetchAll();
        if(empty($checkAcc)){
            die($this->twig->render('getAccountWelcome.html.twig'));
        }
    }

    /**
     * 注册新的账户页面
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getRegister() {
        return $this->twig->render('registerAccount.html.twig');
    }

    /**
     * 注册补充账户资料
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getRegisterTwo() {
        return $this->twig->render('registerAccountTwo.html.twig',['email'=>_post('email')]);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getSupport() {
        $this->checkInitAcc();
        return $this->twig->render('getSupport.html.twig');
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function siteList(){
        $this->checkInitAcc();
        return $this->twig->render('siteList.html.twig');
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getOrgTempList() {
        $this->checkInitAcc();
        return $this->twig->render('organizationTemplate.html.twig');
    }

    /**
     * 保存新的组织信息
     * @return array
     */
    public function createNewOrganization() {
        try{
            $db = DatabaseUtils::initLocalDatabase();
            $keys = [
                'organization_name',
                'organizationalUnitName',
                'registered_address_line1',
                'registered_no',
                'country',
                'state',
                'city',
                'postal_code',
                'organization_phone',
                'date_of_incorporation',
                'contact_name',
                'contact_title',
                'contact_phone',
                'contact_email'
            ];
            $newOrg = [];
            foreach ($keys as $name){
                $newOrg[$name] = _post($name);
            }

            $db->query('insert into organization_template ?', $newOrg);
            LogUtils::writeLog(LogUtils::STATUS_SUCCESS,LogUtils::TITLE_ORG_CREATE,'新的组织模板创建成功, 组织:'._post('organization_name'));
            return ['status'=>"success"];
        }    catch (\Exception $e){
            LogUtils::writeLog(LogUtils::STATUS_ERROR,LogUtils::TITLE_ORG_CREATE,'新的组织模板创建失败, 组织:'._post('organization_name'));
            return ['status'=>"error","message"=>'保存出错，该组织名称已存在或信息格式错误'];
        }
    }

    /**
     * @return array|string
     */
    public function getOrgTemplateList() {
        $start = _post('start')==""?0:_post('start');
        $draw = _post('draw');
        $length = _post('length')==""?6:(_post('length')>20?20:_post('length'));
        $search = _post('search[value]') != ""?_post('search[value]'):NULL;
        $db = DatabaseUtils::initLocalDatabase();
        try{
            if($search !== NULL){
                $sites = $db->query('SELECT * FROM organization_template where organization_template.organization_name LIKE ? order by organization_template.id desc limit ? offset ? ', "%$search%", $length,$start)->fetchAll();
                $recordsFiltered = ($db->query('select count(id) as total from organization_template where organization_template.organization_name LIKE ?', "%$search%")->fetch())['total'];
            }else{
                $sites = $db->query('SELECT * FROM organization_template order by organization_template.id desc limit ? offset ? ', $length,$start)->fetchAll();
                $recordsFiltered = ($db->query('select count(id) as total from organization_template')->fetch())['total'];
            }
            $recordsTotal = ($db->query('select count(id) as total from organization_template')->fetch())['total'];
        }catch(\Exception $exception){
            return $exception->getMessage();
        }

        $sites = json_decode(json_encode($sites), 1);

        return array(
            'data'=>$sites,
            'draw'=>$draw,
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=>$recordsFiltered
        );
    }

    /**
     * 更新组织模板信息
     * @return array
     */
    public function updateOrganizationDetails() {
        try{
            $db = DatabaseUtils::initLocalDatabase();
            $keys = [
                'organization_name',
                'organizationalUnitName',
                'registered_address_line1',
                'registered_no',
                'country',
                'state',
                'city',
                'postal_code',
                'organization_phone',
                'date_of_incorporation',
                'contact_name',
                'contact_title',
                'contact_phone',
                'contact_email'
            ];
            $newOrg = [];
            foreach ($keys as $name){
                $newOrg[$name] = _post($name);
            }

            $db->query('update organization_template set', $newOrg,'where id =?', _post('org_id'));
            LogUtils::writeLog(LogUtils::STATUS_SUCCESS,LogUtils::TITLE_ORG_UPDATE,'组织信息保存/更新成功, 组织:'._post('organization_name'));
            return ['status'=>"success"];
        }    catch (\Exception $e){
            LogUtils::writeLog(LogUtils::STATUS_ERROR,LogUtils::TITLE_ORG_UPDATE,'组织信息保存失败, 组织:'._post('organization_name'));
            return ['status'=>"error","message"=>'保存出错，信息格式可能出现错误'];
        }
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createOrganization() {
        return $this->twig->render('createOrganization.html.twig');
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function updateOrganization() {
        $db = DatabaseUtils::initLocalDatabase();
        $org = $db->query('select * from organization_template where id=?', _post('org_id'))->fetch();
        return $this->twig->render('updateOrganizationDetails.html.twig',['org'=>$org]);
    }

    /**
     * 切换自动续费状态
     * @return array
     */
    public function toggleAutoRenewal() {
        $site = SiteRep::getSiteInfo(_post('siteName'));
        $db = DatabaseUtils::initLocalDatabase();
        // 查询当前状态
        $order = $db->query("select * from certificate where site_id = ?", ($site['id']))->fetch();
        if(empty($order)){
            return  ['status'=>'error','message'=>'该站点未配置自动化证书'];
        }
        if($order['is_auto_renew'] === 0){
            $status = 1;
        }else{
            $status = 0;
        }
        $db->query('update certificate set', [
            'is_auto_renew'=>$status,
        ],'WHERE id=?', $order['id']);
        LogUtils::writeLog(LogUtils::STATUS_SUCCESS,'auto_renew_toggle','成功'.($status===1?'开启':'关闭').'站点'._post('siteName').'的自动续费', $order['id']);
        return ['status'=>'success','message'=>'自动续费已'.($status===1?'开启':'关闭')];
    }

    /**
     * 轮训检查证书是否已经签发
     * @return array
     */
    public function checkSSLOrderStatus() {
        $site = SiteRep::getSiteInfo(_post('siteName'));
        $db = DatabaseUtils::initLocalDatabase();
        // 查询当前状态
        $order = $db->query("select * from certificate where site_id = ?", ($site['id']))->fetch();
        if(empty($order) || $order['status'] !== "issued_active"){
            return  ['status'=>'error'];
        }
        if($order['status'] === "issued_active"){
            return  ['status'=>'success'];
        }
    }

    /**
     * 网站详情页面
     * @return mixed
     */
    public function siteSetting(){
        $site = SiteRep::getSiteInfo(_post('siteName'));
        // 获取 Encryption365 证书订单
        $db = DatabaseUtils::initLocalDatabase();
        $order = $db->query("select * from certificate where site_id = ?", ($site['id']))->fetch();
        if(isset($order->domains)){$order->domains = json_decode($order->domains);}
        return $this->twig->render('siteSetting.html.twig', ['site'=>$site,'ssl_order'=>$order]);
    }

    /**
     * 查询账单支付状态
     * @return array|mixed
     */
    public function getInvoiceStatus() {
        try{
            $pl = Encryption365Service::getInvoiceStatus(_post('invoiceid'));
            return $pl;
        }catch (Encryption365Exception $exception){
            return ["status"=>"error","message"=>"错误",$exception->getMessage()];
        }
    }

    /**
     * 取消充值账单
     * @return array
     */
    public function revokeInvoice() {
        try{
            $pl = Encryption365Service::revokeInvoice(_post('invoiceid'));
            return $pl;
        }catch (Encryption365Exception $exception){
            return ["status"=>"error","message"=>"错误",$exception->getMessage()];
        }
    }

    /**
     * 创建充值订单
     * @return array|mixed
     */
    public function generateAddFunds() {
        try{
            $pl = Encryption365Service::createAlipayInvoice(_post('amount'));
            return $pl;
        }catch (Encryption365Exception $exception){
            return ["status"=>"error","message"=>"错误",$exception->getMessage()];
        }
    }

    /**
     * 证书创建页面
     * @return string
     * @throws Encryption365PageException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function createSSLOrder() {
        $site = SiteRep::getSiteInfo(_post('siteName'));
        try{
            $products = Encryption365Service::getProducts();
        }catch (Encryption365Exception $exception){
            throw new Encryption365PageException("错误",$exception->getMessage());
        }

        $db = DatabaseUtils::initLocalDatabase();
        $orgs = $db->query('select `id`,`organization_name` from organization_template order by id desc')->fetchAll();
        return $this->twig->render('createSSLOrder.html.twig', ['site'=>$site, 'products'=>$products,'orgs'=>$orgs]);
    }

    /**
     * 尝试创建全新的SSL订单
     * @return array
     */
    public function createNewFullSSL() {
        try{
            $result = CertificateUtils::createNewFullSSLOrder(_post('siteName'), _post('pid'),_post('org_id'), _post('autoRenewal'));
            if($result === true){
                return ["status"=>"success","message"=>"证书订单创建成功, 完成签发后将会自动安装"];
            }else{
                return ["status"=>"error","message"=>"订单创建失败, 请您稍后再试"];
            }
        }catch(Encryption365Exception $exception){
            return ["status"=>"error","message"=>$exception->getMessage()];
        }
    }

    /**
     * 商业证书升级页面
     * @return string
     * @throws Encryption365PageException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function upgradeBusinessSSL() {
        $site = SiteRep::getSiteInfo(_post('siteName'));
        try{
            $products = Encryption365Service::getProducts();
        }catch (Encryption365Exception $exception){
            throw new Encryption365PageException("API 访问错误",$exception->getMessage());
        }
        $db = DatabaseUtils::initLocalDatabase();
        $orgs = $db->query('select `id`,`organization_name` from organization_template order by id desc')->fetchAll();
        return $this->twig->render('upgradeToBusinessLicense.html.twig', ['site'=>$site, 'products'=>$products,'orgs'=>$orgs]);
    }

    /**
     * 升级为商业版证书
     * @return array
     */
    public function upgradeToBusinessSSL() {
        try{
            $result = CertificateUtils::upgradeToBusinessSSL(_post('siteName'), _post('pid'),_post('org_id'), _post('autoRenewal'));
            if($result === true){
                return ["status"=>"success","message"=>"请求创建成功, 完成签发后将会自动安装"];
            }else{
                return ["status"=>"error","message"=>"订单创建失败, 请您稍后再试"];
            }
        }catch(Encryption365Exception $exception){
            return ["status"=>"error","message"=>$exception->getMessage()];
        }
    }

    /**
     * 重签证书订单
     * @return array
     */
    public function reissueSSLOrder() {
        try{
            $relt = CertificateUtils::reissueSSLOrder(_post('siteName'));
            if($relt===true){
                return ["status"=>"success","message"=>"重签名请求已提交"];
            }else{
                return ["status"=>"error","message"=>"重签名请求失败, 请稍后再试！"];
            }
        }catch (Encryption365Exception $exception){
            return ["status"=>"error","message"=>$exception->getMessage()];
        }
    }
    /**
     * 查看账户绑定和客户端信息页面
     * @return mixed
     */
    public function clientInfo(){
        $this->checkInitAcc();
        $db = DatabaseUtils::initLocalDatabase();
        $clientInfo = $db->query("select * from configuration where `setting` in ('acc_email','client_id','access_token','ip_address','client_status','registered_at')")->fetchAll();
        $info = [];
        foreach ($clientInfo as $setting){
            $info[$setting['setting']] = $setting['value'];
        }
        $info['version'] = Encryption365Service::getClientVersion();
        return $this->twig->render('clientInfo.html.twig',['client'=>$info]);
    }

    /**
     * 账户登陆页面
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getLogin() {
        return $this->twig->render('accountLogin.html.twig');
    }
    /**
     * 发送注册验证码
     * @return array|mixed
     */
    public function accountRegStep1() {
        return Encryption365Service::authcodeSend(_post('email'));
    }

    /**
     * 账户配置向导
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getAccountWelcome() {
        return $this->twig->render('getAccountWelcome.html.twig');
    }

    /**
     * 验证代码并完成注册
     * @return array|mixed
     */
    public function accountRegStep2() {
        LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_USER_REGISTER,"发送用户注册申请成功，账户名:"._post('username'));
        return Encryption365Service::accountRegister(
            _post('username'),
            _post('password'),
            _post('authcode'),
            _post('realName'),
            _post('idcardNumber'),
            _post('phoneNumber'),
            _post('country'),
            _post('companyname')
        );
    }

    /**
     * @return array
     */
    public function clientRegister(){
        $rlt = Encryption365Service::clientCreate(_post('username'), _post('password'));
        if($rlt['result'] !== "success"){
            LogUtils::writeLog(LogUtils::STATUS_ERROR, LogUtils::TITLE_CLIENT_REGISTER, "尝试注册客户端失败, 错误信息:".$rlt['message']);
            return $rlt;
        }else{
            LogUtils::writeLog(LogUtils::STATUS_SUCCESS, LogUtils::TITLE_CLIENT_REGISTER, "注册客户端成功, 获取到注册ID:".$rlt['client_id']);
            //TODO 保存到本地的信息
            $db = DatabaseUtils::initLocalDatabase();
            $db->query("INSERT INTO configuration (`setting`, `value`) values ('acc_email', '"._post('username')."'),('client_id', '".$rlt['client_id']."'),('access_token','".$rlt['access_token']."'),('ip_address','".$rlt['ip_address']."'),('client_status','".$rlt['status']."'),('registered_at','".$rlt['created_at']."')");
        }
        return ["status"=>"success","message"=>"登录成功, 客户端已经完成注册！"];
    }

    /**
     * 检查并创建本地插件数据库
     */
    public function checkAndInstallDatabase(){
        if(file_exists(__DIR__.'/../../databases/main.db')!==TRUE){
            try{
                $dbFile = new \SQLite3(__DIR__.'/../../databases/main.db');
                DatabaseUtils::installDatabase();
            }catch (\Exception $exception){
                return $exception->getMessage();
            }
        }
        return "success";
    }

    /**
     * 查看客户端日志
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getClientLogs() {
        return $this->twig->render('clientLogs.html.twig');
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function addFunds() {
        $details = Encryption365Service::getAccountDetails();
        if($details['result'] !== "success"){
            throw new Encryption365PageException($details['message']);
        }
        return $this->twig->render('addFunds.html.twig',['account'=>$details]);
    }

    /**
     * 获取列表 JQ TableLists
     * @return array|string
     * @throws Encryption365Exception
     */
    public function getClientLogList() {
        $start = _post('start')==""?0:_post('start');
        $length = _post('length')==""?6:(_post('length')>20?20:_post('length'));
        $search = _post('search[value]') != ""?_post('search[value]'):NULL;
        return LogUtils::getClientLogList(_post('draw'), $start, $length, $search);
    }

    /**
     * jQ Databases QuerAPI
     * 查询站点列表
     * @return mixed
     */
    public function getSiteList() {
        $start = _post('start')==""?0:_post('start');
        $length = _post('length')==""?6:(_post('length')>20?20:_post('length'));
        $search = _post('search[value]') != ""?_post('search[value]'):NULL;
        return SiteRep::getSiteList(_post('draw'), $start, $length, $search);
    }

}