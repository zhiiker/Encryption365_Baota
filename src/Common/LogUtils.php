<?php
namespace TrustOcean\Encryption365\Common;
class LogUtils {

    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";
    const TITLE_CERT_CREATED = "cert_created";
    const TITLE_CERT_UPGRADE = "cert_upgrade";
    const TITLE_CERT_REISSUE = "cert_reissue";
    const TITLE_USER_LOGIN = "user_login";
    const TITLE_USER_REGISTER = "user_register";
    const TITLE_CLIENT_REGISTER = "client_register";
    const TITLE_ORG_CREATE = "organization_created";
    const TITLE_ORG_UPDATE = "organization_updated";

    /**
     * 写客户端操作日志
     * @param $status
     * @param $title
     * @param $description
     * @param int $certificate_id
     */
    public static function writeLog($status, $title, $description, $certificate_id=-1) {
        $db = DatabaseUtils::initLocalDatabase();
        $db->query('insert into logs ?', [
            'status'=>$status,
            'title'=>$title,
            'description'=>$description,
            'certificate_id'=>$certificate_id,
            'created_at'=>date('Y-m-d H:i:s', time())
        ]);
    }

    /**
     * 获取列表
     * @param $draw
     * @param int $offset
     * @param int $limit
     * @param null $nameSearchValue
     * @return array|string
     * @throws \TrustOcean\Encryption365\Encryption365Exception
     */
    public static function getClientLogList($draw, $offset=0, $limit=6, $nameSearchValue=NULL) {
        $db = DatabaseUtils::initLocalDatabase();
        try{
            if($nameSearchValue !== NULL){
                $sites = $db->query('SELECT * FROM logs where logs.description LIKE ? order by logs.id desc limit ? offset ? ', "%$nameSearchValue%", $limit,$offset)->fetchAll();
                $recordsFiltered = ($db->query('select count(id) as total from logs where logs.description LIKE ?', "%$nameSearchValue%")->fetch())['total'];
            }else{
                $sites = $db->query('SELECT * FROM logs order by logs.id desc limit ? offset ? ', $limit,$offset)->fetchAll();
                $recordsFiltered = ($db->query('select count(id) as total from logs')->fetch())['total'];
            }
            $recordsTotal = ($db->query('select count(id) as total from logs')->fetch())['total'];
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
}