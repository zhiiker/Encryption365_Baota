<?php
namespace TrustOcean\Encryption365\Common;
use Nette\Database\Connection;

class DatabaseUtils{
    /**
     * Boot Local Sqlite File
     * @return Connection
     */
    public static function initLocalDatabase() {
        $dbPath = realpath(__DIR__.'/../../databases/main.db');
        $database = new Connection("sqlite:$dbPath");
        return $database;
    }

    /**
     * Boot BaoTa System Sqlite File
     * @return Connection
     */
    public static function initBaoTaSystemDatabase() {
        $dbPath = realpath(__DIR__.'/../../../../data/default.db');
        $database = new Connection("sqlite:$dbPath");
        return $database;
    }

    /**
     * 初始化插件的数据库结构
     */
    public static function installDatabase() {
        $db = self::initLocalDatabase();
        // 存储配置信息表
        $db->query('create table if not exists configuration
(
	setting text not null
		constraint configuration_pk
			primary key,
	value text not null
);');
    // 存储证书信息的表
        $db->query('create table certificate
(
	id integer
		constraint certificate_pk
			primary key autoincrement,
	site_id integer not null,
	type text not null,
	status text not null,
	period char(15),
	domains text,
	is_auto_renew integer default 1 not null,
	is_business integer default 1 not null,
	domain_count integer default 1 not null,
	pid integer not null,
	dcv_info text,
	csr_code text,
	cert_code text,
	key_code text,
	valid_from timestamp,
	valid_till timestamp,
	vendor_id text,
	created_at timestamp default current_timestamp not null,
	total_fees decimal(10,2) default 0.00,
	org_id integer default 0
);

create unique index certificate_id_uindex
	on certificate (id);

create unique index certificate_site_id_uindex
	on certificate (site_id);

');
        // 保存日志信息的表
        $db->query('create table logs
(
	id integer
		constraint logs_pk
			primary key autoincrement,
	status text not null,
	title text not null,
	description text not null,
	certificate_id integer,
	created_at timestamp default current_timestamp
);

create unique index logs_id_uindex
	on logs (id);

');
        // 保存企业模板的数据库
        $db->query('create table organization_template
(
    id                       integer
        constraint organization_template_pk
            primary key autoincrement,
    organization_name        char(64)  not null
        constraint organization_template_pk_2
            unique,
    organizationalUnitName   char(64)  not null,
    registered_address_line1 char(128) default NULL not null,
    registered_no            char(25)  default NULL not null,
    country                  char(2)   not null,
    state                    char(128) not null,
    city                     char(128) not null,
    postal_code              char(40)  not null,
    organization_phone       char(15)  not null,
    date_of_incorporation    char(10)  not null,
    contact_name             char(64)  not null,
    contact_title            char(64)  not null,
    contact_phone            char(15)  not null,
    contact_email            char(255) not null
);');
    }

    /**
     * 设置自动化任务
     */
    public static function installCronJob() {
        $db = self::initBaoTaSystemDatabase();
        $echo = "5eeb48072b7a0fc713483bd5ade1d59d";
        $check = $db->query("select `id` from crontab where `echo` = ?", ($echo))->fetch();
        if(empty($check)){
            $pyEnv = self::findValidPythonExecutedPath();
            $db->query("INSERT INTO `crontab` ?", [
                'echo'=>$echo,
                'name'=>"Encryption365™ 证书自动化",
                'type'=>"minute-n",
                'where1'=>"1",
                'where_hour'=>"",
                'where_minute'=>"",
                'addtime'=>date('Y-m-d H:i:s', time()),
                'status'=>1,
                'save'=>"",
                'sName'=>"",
                'backupTo'=>"localhost",
                'sBody'=>"$pyEnv  /www/server/panel/plugin/encryption365/src/AutoRenew.py",
                'sType'=>'toShell',
                'urladdress'=>"",
            ]);
            // 写入宝塔任务
            $shellContent = "#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
export PYTHONIOENCODING=utf-8
$pyEnv /www/server/panel/plugin/encryption365/src/AutoRenew.py
echo \"----------------------------------------------------------------------------\"
endDate=`date +\"%Y-%m-%d %H:%M:%S\"`
echo \"★[\$endDate] Successful\"
echo \"----------------------------------------------------------------------------\"";
            if(!is_dir("/www/server/cron/")){mkdir("/www/server/cron/");}
            $baseShell = "/www/server/cron/".$echo;
            file_put_contents($baseShell,str_replace("\r\n","\n", $shellContent));
            // 写Cron WriteShell
            $rootCronPath = "/var/spool/cron";
            if(!is_dir($rootCronPath)){
                mkdir($rootCronPath, 472, true);
            }
            // Ubuntu系统可能路径不一致
            if(is_dir($rootCronPath.'/crontabs')){
                $rootCronPath .= '/crontabs';
            }
            $cronFile = $rootCronPath.'/root';
            $excuteLine = "*/1 * * * * /www/server/cron/$echo >> /www/server/cron/$echo.log 2>&1"."\n";
            file_put_contents($cronFile, $excuteLine, FILE_APPEND);
            // 设置权限
            chmod($baseShell, 755);
            self::reloadCrond();
            LogUtils::writeLog('success','cron_setup','设置定时任务成功PythonEnv: '.$pyEnv.'');
        }
    }

    /**
     * 查找Python可执行路径
     * @return mixed
     */
    private static function findValidPythonExecutedPath() {
        // 根据优先级定义可用的Python路径
        $initPath = [
            "/www/server/panel/pyenv/bin/python",
            "/usr/bin/python",
        ];
        foreach ($initPath as $pyenv){
            if(file_exists($pyenv) && is_executable($pyenv)){
                return $pyenv;
            }
        }
    }

    /**
     * 重载Cron任务
     */
    private static function reloadCrond() {
        if(is_file('/etc/init.d/crond')){
            exec('/etc/init.d/crond reload');
        }elseif (is_file("/etc/init.d/cron")) {
            exec('service cron restart');
        }else{
            exec('systemctl reload crond');
        }
    }
}