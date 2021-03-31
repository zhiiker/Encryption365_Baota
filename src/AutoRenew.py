#!/usr/bin/python
# coding: utf-8
# -------------------------------------------------------------------
# Encryption365 AutoRenewal Client For 宝塔Linux面板
# -------------------------------------------------------------------
# Copyright (c) 2020-2099 环智中诚™ All rights reserved.
# -------------------------------------------------------------------
# Author: JasonLong <jasonlong@qiaokr.com>
# -------------------------------------------------------------------

# -------------------------------------------------------------------
# Encryption365 AutoRenewal Client
# -----------------------------------------------------------------

import re
import fcntl
import datetime
import binascii
import hashlib
import base64
import json
import copy
import time
import os
import sys
import requests
import json
import Uninstall as UtiMod

panelPath = UtiMod.init_panel_path()

if not 'class/' in sys.path:
    sys.path.insert(0, 'class/')
requests.DEFAULT_TYPE = 'curl'

try:
    import public
    from panelSite import panelSite
except:
    pass

try:
    import psutil
except:
    try:
        public.ExecShell("pip install psutil")
        import psutil
    except:
        public.ExecShell("pip install --upgrade pip")
        public.ExecShell("pip install psutil")
        import psutil

try:
    import OpenSSL
except:
    public.ExecShell("pip install pyopenssl")
    import OpenSSL

try:
    import dns.resolver
except:
    public.ExecShell("pip install dnspython")
    import dns.resolver
try:
    import sqlite3
except:
    public.ExecShell("pip install sqlite3")
    import sqlite3
try:
    import flask
except:
    public.ExecShell("pip install flask")
try:
    import flask_session
except:
    public.ExecShell("pip install flask_session")
try:
    import flask_sqlalchemy
except:
    public.ExecShell("pip install flask_sqlalchemy")


class encryption365:
    _apiServer = 'https://encrypt365.trustocean.com'

    def call_remote(self, uri, pdata):
        _apiServer = 'https://encrypt365.trustocean.com'
        credentials = self.get_client_credentials()
        pdata['client_id'] = credentials[0]
        pdata['access_token'] = credentials[1]
        post_result = requests.post(url=self._apiServer + uri, headers={"Content-Type": "application/json"},
                                    data=json.dumps(pdata))
        return post_result.content

    def get_cert_details(self, vendor_id):
        r = self.call_remote(uri='/cert/details', pdata={'trustocean_id': vendor_id})
        return json.loads(r)

    def get_client_credentials(self):
        db = get_database().cursor()
        c = db.execute('select `value` from configuration where `setting` like "client_id"')
        client_id = c.fetchall()[0][0]
        c = db.execute('select `value` from configuration where `setting` like "access_token"')
        access_token = c.fetchall()[0][0]
        return (client_id, access_token)


def get_database():
    conn = sqlite3.connect(panelPath+'/plugin/encryption365/databases/main.db')
    return conn


def get_baota_database():
    conn = sqlite3.connect(panelPath+'/data/default.db')
    return conn


# 保存新签发证书信息到本地
def save_active_cert(id, certData):
    db = get_database()
    c = db.cursor()
    valid_from = get_cert_valid_from(certData['cert_code'])
    valid_till = get_cert_valid_to(certData['cert_code'])
    sql = "update certificate set `cert_code`='" + certData['cert_code'] + '\n' + certData[
        'ca_code'] + "' , `status`='issued_active', `valid_from`='" + valid_from + "', `valid_till`='" + valid_till + "' where `id`=" + str(
        id)
    c.execute(sql)
    db.commit()
    write_log('success','cert_issued','证书#'+str(id)+'已经签发，保存成功!', id)


# 获取证书到期时间
def get_cert_valid_to(cret_data):
    x509 = OpenSSL.crypto.load_certificate(
        OpenSSL.crypto.FILETYPE_PEM, cret_data)
    cert_timeout = time.strftime("%Y-%m-%d %H:%M:%S",
                                 time.strptime(bytes.decode(x509.get_notAfter())[:-1], "%Y%m%d%H%M%S"))
    return cert_timeout


# 获取证书到期时间
def get_cert_valid_from(cret_data):
    x509 = OpenSSL.crypto.load_certificate(
        OpenSSL.crypto.FILETYPE_PEM, cret_data)
    cert_timeout = time.strftime("%Y-%m-%d %H:%M:%S",
                                 time.strptime(bytes.decode(x509.get_notBefore())[:-1], "%Y%m%d%H%M%S"))
    return cert_timeout


# 获取本地数据库中的证书
def get_local_cert(id):
    db = get_database().cursor()
    c = db.execute('select * from `certificate` where `id`=' + str(id))
    return c.fetchall()[0]


def get_site_info(siteId):
    # 查询站点信息
    db = get_baota_database()
    c = db.execute('select * from `sites` where `id`=' + str(siteId))
    return c.fetchall()[0]


# 调用宝塔面板API设置站点证书
def plug_cert_to_site(siteId, certId, certData):
    s = get_site_info(siteId)
    basePath = panelPath+'/vhost/cert/' + s[2]
    if not os.path.exists(basePath):
        os.makedirs(basePath, 384)
    lCert = get_local_cert(certId)
    cert_file = basePath + '/fullchain.pem'
    key_file = basePath + '/privkey.pem'
    public.writeFile(cert_file, certData['cert_code'] + certData['ca_code'])
    public.writeFile(key_file, lCert[13])
    get = {'siteName': s[1], 'key': lCert[13], 'csr': certData['cert_code'] + "\n" + certData['ca_code']}
    gets = build_object_json(get)
    print(gets.siteName)
    rt = panelSite().SetSSL(gets)
    if rt['status']:
        write_log('success', 'cert_installed', '证书#'+str(certId)+'安装成功！', certId)
    else:
        write_log('error', 'cert_install_error', '证书#'+str(certId)+'安装出错: '+status['msg'], certId)


# 创建通用对象
def build_object_json(data_s):
    obj = public.dict_obj()
    obj.siteName = data_s['siteName']
    obj.first_domain = data_s['siteName']
    obj.key = data_s['key']
    obj.csr = data_s['csr']
    return obj

# 获取网站域名
def get_site_domains(sitename):
    site_id = public.M('sites').where('name=?', (sitename,)).field('id').find()
    domains = public.M('domain').where('pid=?', (site_id['id'],)).field('name').select()
    domains = [d['name'] for d in domains]
    return domains


# 创建运行日志
def write_log(status, title, description, certificate_id=-1):
    db = get_database()
    c = db.cursor()
    created_at = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()))
    insert_query = 'insert into logs (`status`,`title`,`description`,`certificate_id`,`created_at`) values ("'+status+'","'+title+'","'+description+'",'+str(certificate_id)+',"'+created_at+'")'
    rt = c.execute(insert_query)
    db.commit()


# 删除超过过期的日志信息, 仅保留最近68天的日志
def delete_expired_logs():
    db = get_database()
    c = db.cursor()
    expire_at = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()-60*60*24*68))
    rm_sql = 'delete from logs where `created_at` < "'+expire_at+'"'
    rt = c.execute(rm_sql)
    db.commit()
    # write_log('success', 'log_deleted', '已成功删除'+expire_at+'之前的日志信息')


# 获取申请中的订单
def get_processing_orders():
    db = get_database().cursor()
    c = db.execute('select `id`,`vendor_id`,`site_id` from certificate where `status` like "enroll_caprocessing"')
    orders = c.fetchall()
    return orders


# 根据证书订单ID提交续费请求
def renew_site_ssl(cert_order_id):
    print("正在调用PHP API执行订单续费...#"+str(cert_order_id))
    try:
        result = os.popen("/usr/bin/php "+panelPath+"/plugin/encryption365/src/PythonUtils.php --fun=\"%s\" --cert_order_id=\"%s\"" %
                          ('renewSSLOrder',str(cert_order_id))).read()
        result = json.loads(result)
        print("续费订单提交成功， 站点#"+str(cert_order_id))
        write_log('success', 'renew_order_success', '成功创建证书#'+str(cert_order_id)+'的续费订单', cert_order_id)
        # print(result)
    except Exception as e:
        write_log('error', 'renew_order_error', '运行 AutoRenew::renew_site_ssl 创建续费订单时出现错误:'+str(e), cert_order_id)
        print("调用PHP API出错:")
        print(e)


# 从本地删除对应的证书订单
def remove_local_ssl_order(order_id):
    db = get_database()
    c = db.cursor()
    rm_sql = 'delete from certificate where id='+str(order_id)
    rt = c.execute(rm_sql)
    db.commit()
    write_log('success', 'cert_order_delete', '成功删除本地证书订单#'+str(order_id)+', 因为远端已经检测到该订单为 取消、退款、拒绝或其他不活跃状态', order_id)


# 获取即将过期/需要续费的订单
def get_expiring_orders():
    db = get_database().cursor()
    # 15天内过期就要开始尝试续费
    check_time = time.localtime(time.time() + 3600 * 24 * 15)
    check_sql = 'select `id`,`vendor_id`,`site_id`,`pid`,`period`,`domains` from certificate where `status` like "issued_active" and `valid_till` <= "' + time.strftime(
        "%Y-%m-%d %H:%M:%S", check_time) + '"'
    c = db.execute(check_sql)
    orders = c.fetchall()
    r_orders = []
    for order in orders:
        r_orders.append({
            'id': order[0],
            'vendor_id': order[1],
            'site_id': order[2],
            'pid': order[3],
            'period': order[4],
            'domains': order[5]
        })
    return r_orders


# TODO：Cron任务 处理本地等待签发状态的证书
def process_cert_issued():
    enc365 = encryption365()
    orders = get_processing_orders()
    for order in orders:
        details = enc365.get_cert_details(order[1])
        if details['cert_status'] == "cancelled" or details['cert_status'] == "revoked" or details['cert_status'] == "rejected" or details['cert_status'] == "expired":
            remove_local_ssl_order(order[0])
        if details['cert_status'] == "issued_active":
            save_active_cert(order[0], details)
            plug_cert_to_site(order[2], order[0], details)


# TODO: Cron任务 检查并续费证书
def process_cert_renewal():
    orders = get_expiring_orders()
    for order in orders:
        print('正在尝试生成续费订单#'+str(order['id']))
        renew_site_ssl(order['id'])


# 写此程序PID
def write_pid():
    if not os.path.exists(panelPath+'/plugin/encryption365/src/autorenewal.pid'):
        with open(panelPath+'/plugin/encryption365/src/autorenewal.pid', 'w') as fs:
            fs.write(str(os.getpid()))
    else:
        with open(panelPath+'/plugin/encryption365/src/autorenewal.pid', 'w') as f:
            f.write(str(os.getpid()))


# 读取本程序PID
def read_pid():
    if os.path.exists(panelPath+'/plugin/encryption365/src/autorenewal.pid'):
        with open(panelPath+'/plugin/encryption365/src/autorenewal.pid', 'r') as f:
            return f.read()
    else:
        return '0'


# 主程序入口
def main_process():
    # 检查证书签发并完成部署
    process_cert_issued()
    # 检查即将过期的证书并尝试下单续费
    process_cert_renewal()
    # 删除68天之前的日志信息
    delete_expired_logs()


if __name__ == '__main__':
    if read_pid() != False and int(read_pid()):
        pid = int(read_pid())
        if pid in psutil.pids():
            print("前序任务还未执行完成, 推出: " + str(pid))
        else:
            write_pid()
            print("新任务开始执行")
            main_process()
    else:
        write_pid()
        print("新任务开始执行")
        main_process()
