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
# Encryption365 PHP Migrate API
# -----------------------------------------------------------------

import os
import sys
import requests
import getopt

sys.path.append(os.getcwd())
sys.path.append('/www/server/panel')
os.chdir('/www/server/panel')
if not 'class/' in sys.path:
    sys.path.insert(0, 'class/')
requests.DEFAULT_TYPE = 'curl'


# 获取Python的可执行路径
def get_python_execute_path(optionalData=""):
    print(sys.executable)


if __name__ == '__main__':
    try:
        ops, args = getopt.getopt(sys.argv[1:], "f:d:")
        data = ""
        function = ""
        for opt, arg in ops:
            if(opt == "-f"):
                function = arg
            elif(opt == '-d'):
                data = arg
        if function and callable(eval(function)):
            eval(str(function))(data)
        else:
            print("Not a Valid Callable method: "+function)
    except getopt.GetoptError:
        print('PhpUtils.py -f <FunctionName> -d <JsonEncodedDataString>')
        sys.exit(2)

