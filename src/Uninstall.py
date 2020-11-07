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
# Encryption365 Uninstall.py
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

sys.path.append(os.getcwd())
sys.path.append('/www/server/panel')
os.chdir('/www/server/panel')
if not 'class/' in sys.path:
    sys.path.insert(0, 'class/')
requests.DEFAULT_TYPE = 'curl'

try:
    import public
    from crontab import crontab
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


def get_baota_database():
    conn = sqlite3.connect('/www/server/panel/data/default.db')
    return conn
def get_setup_path():
    db = get_baota_database().cursor()
    c = db.execute('select `id` from crontab where `echo` = "5eeb48072b7a0fc713483bd5ade1d59d"')
    cron_id = c.fetchall()[0][0]

def uninstall():
    # 备份数据库文件至目录 ${setup_path}/panel/data/plugin_encryption365_backup.db
    # 防止证书数据丢失, 待下次安装/升级插件时自动导入旧的数据库文件
    public.ExecShell('cp -f /www/server/panel/plugin/encryption365/databases/main.db /www/server/panel/data/plugin_encryption365_backup.db')
    print('已完成数据库备份')
    # 调用Baota API删除已创建的CronTab
    db = get_baota_database().cursor()
    c = db.execute('select `id` from crontab where `echo` = "5eeb48072b7a0fc713483bd5ade1d59d"')
    cron_id = c.fetchall()[0][0]
    gets = public.dict_obj()
    gets.id = cron_id
    crontab().DelCrontab(gets)
    print('已删除 Encryption365 定时任务')


if __name__ == '__main__':
    uninstall()