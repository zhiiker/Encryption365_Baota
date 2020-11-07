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
import json,os

result = os.popen("php ./PythonUtils.php --fun=\"%s\" --domain=\"%s\"" %
                          ('generateCsrKey','www.baidu.com')).read()
try:
    #解析执行结果
    result = json.loads(result)
    print(result)
except: pass