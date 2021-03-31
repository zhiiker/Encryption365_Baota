import sys,os,shutil
import public,json
from shutil import copyfile


import Uninstall as UtiMod

panelPath = UtiMod.init_panel_path()

sys.path.append("class/")

__plugin_name = 'encryption365'
__plugin_path = panelPath + '/plugin/' + __plugin_name


def install():
    public.bt_print("开始执行安装流程")
    if not os.path.exists(__plugin_path): os.makedirs(__plugin_path)
    copyfile(__plugin_path+"/icon.png", panelPath+"/BTPanel/static/img/soft_ico/ico-encryption365.png")
    public.bt_print("检查并恢复备份的数据库文件...")
    backup_file = panelPath+'/data/plugin_encryption365_backup.db'
    new_database_file = panelPath+'/plugin/encryption365/databases/main.db'
    if os.path.isfile(backup_file) and not os.path.isfile(new_database_file):
        public.bt_print("正在恢复备份的数据库文件...")
        copyfile(backup_file, new_database_file)
        os.remove(backup_file)
    public.bt_print("安装完成咯!")


def update():
    install()


def uninstall():
    import src.Uninstall as uninstallFuncs
    public.bt_print("执行卸载任务")
    uninstallFuncs.uninstall()
    public.bt_print("删除文件夹")
    shutil.rmtree(__plugin_path)
    public.bt_print("卸载完成")


if __name__ == "__main__":
    opt = sys.argv[1]
    if opt == 'update':
        update()
    elif opt == 'uninstall':
        uninstall()
    else:
        install()
