<?php
//Boot Composer Loader
require __DIR__.'/vendor/autoload.php';
$pVersion = explode('.',PHP_VERSION);
$realVersion = $pVersion[0].'.'.$pVersion[1];
# Check PHP Version and Ext Requirements
$requirements = [];
if($realVersion < "7.1"){
    $requirements[] = [
        'title'=>'需要 PHP7.1 版本',
        'desc'=>'检测到您正在使用的PHP版本为 '.$realVersion.' ，请升级您的默认PHP版本至 PHP >= 7.1，因为运行此客户端需要您系统默认的PHP版本>=7.1, 通常情况下, 宝塔面板首个安装的PHP版本为系统默认版本。'
    ];
}
if(!extension_loaded('Sqlite3')){
    $requirements[] = [
        'title'=>'需要PHP开启 Sqlite3 扩展',
        'desc'=>'请为您的默认PHP版本开启 Sqlite3 扩展, 可以通过编辑您PHP对应的 .ini 配置文件开启。'
    ];
}
if(!extension_loaded('OpenSSL')){
    $requirements[] = [
        'title'=>'需要PHP开启 OpenSSL 扩展',
        'desc'=>'请为您的默认PHP版本开启 OpenSSL 扩展, 可以通过编辑您PHP对应的 .ini 配置文件开启。'
    ];
}
if(!extension_loaded('cURL')){
    $requirements[] = [
        'title'=>'需要PHP开启 cURL 扩展',
        'desc'=>'请为您的默认PHP版本开启 cURL 扩展, 可以通过编辑您PHP对应的 .ini 配置文件开启。'
    ];
}
if(!extension_loaded('Mbstring')){
    $requirements[] = [
        'title'=>'需要PHP开启 Mbstring 扩展',
        'desc'=>'请为您的默认PHP版本开启 Mbstring 扩展, 可以通过编辑您PHP对应的 .ini 配置文件开启。'
    ];
}
if(!empty($requirements)){
    $twig = \TrustOcean\Encryption365\Common\TwigUtils::initTwig();
    die($twig->render('PHPRequirements.html.twig',['errors'=>$requirements]));
}
# Check and Install Extension of PHP Ioncube Loader
//if(!extension_loaded('ionCube Loader')){
//    $twig = \TrustOcean\Encryption365\Common\TwigUtils::initTwig();
//    die($twig->render('ioncubeRequired.html.twig',['php_verison'=>$realVersion]));
//}
?>

<?php
//宝塔Linux面板插件demo for PHP
//@author 阿良<287962566@qq.com>

//必需面向对象编程，类名必需为bt_main
//允许面板访问的方法必需是public方法
//通过_get函数获取get参数,通过_post函数获取post参数
//可在public方法中直接return来返回数据到前端，也可以任意地方使用echo输出数据后exit();
//可在./php_version.json中指定兼容的PHP版本，如：["56","71","72","73"]，没有./php_version.json文件时则默认兼容所有PHP版本，面板将选择 已安装的最新版本执行插件
//允许使用模板，请在./templates目录中放入对应方法名的模板，如：test.html，请参考插件开发文档中的【使用模板】章节
//支持直接响应静态文件，请在./static目录中放入静态文件，请参考插件开发文档中的【插件静态文件】章节


class bt_main extends \TrustOcean\Encryption365\Controller\MainController {
	//不允许被面板访问的方法请不要设置为公有方法
    /**
     * @return string
     */
    private static function index(){
		//_post()会返回所有POST参数，要获取POST参数中的username参数，请使用 _post('username')
		//可通过_version()函数获取面板版本
		//可通过_post('client_ip') 来获取访客IP

		//常量说明：
		//PLU_PATH 插件所在目录
		//PLU_NAME 插件名称
		//PLU_FUN  当前被访问的方法名称
//		return array(
//			_get(),
//			_post(),
//			_version(),
//			PLU_FUN,
//			PLU_NAME,
//			PLU_PATH
//		);
        return "<p>此方法并未在 Controller 中进行定义, 为您展示默认内容</p>";
	}

	//获取phpinfo
	public function phpinfo(){
		return phpinfo();
	}
}


?>