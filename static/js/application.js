//插件唯一识别ID
let plugin_id = "encryption365";

//定义窗口尺寸
$('.layui-layer-page').css({ 'width': '900px' });

//左测菜单切换效果
$(".bt-w-menu p").click(function () {
    $(this).addClass('bgw').siblings().removeClass('bgw')
});

/**
 * 插件交互对象
 * 您的所有JS代码可以写在里面
 * 若不习惯JS的面向对象编程，可删除此对象，使用传统函数化的方式编写
 * */
var encryption365 = {
    //构造概览内容
    // get_index: function () {
    //     $('.plugin_body').html("<h1 style='text-align:center;margin-top:30%;'>这是一个示例插件!</h1>");
    // },
    gVars:{
        reg: {},
        siteSetting:{},
        payment:{},
    },
    /**
     * 获取PHPINFO
     */
    phpinfo : function (p) {
        if (p == undefined) p = 1;
        request_plugin('encryption365', 'phpinfo', { p: p, callback: 'demo.phpinfo' }, function (rdata) {
            $('.plugin_body').html("<pre>"+rdata+"</pre>");
        });
    },
    // 页面主内容导航
    page: function(page, params = {}, loadingText=false) {
        if(loadingText !== false){
            var loads = bt.load(loadingText+'...');
        }
        request_plugin(plugin_id, page, params, function(response){
            if(loadingText !== false){
                loads.close();
            }
            if(undefined !== response.status && response.status === false){
                layer.msg(response.msg, { icon: 2 });
            }else if(response !== ""){
                $('.plugin_body').html(response);
            }else{
                layer.msg("程序启动失败", { icon: 2 });
                $('.plugin_body').html('<p style="color: darkred;margin-top: 15px;margin-bottom: 10px;">抱歉！Encryption365™ 程序启动失败, 请检查您的最新版 PHP 环境是否设置正确。或查看我们的产品社区获取帮助。</p><a href="https://board.trustocean.com" target="_blank" class="btn btn-xs btn-primary">获取帮助</a>');
            }
        });
    },
    createNewFullSSL: function(siteName, pid, level, org_id, autoRenewal) {
        // 检查Org
        if(level !== "dv" && org_id === "-1"){
            layer.msg('请选择此证书的申请人信息',{icon:7});
            return false;
        }
        var loads = bt.load('正在提交签名请求...');
        request_plugin('encryption365', 'createNewFullSSL', { siteName: siteName, pid:pid, org_id:org_id, autoRenewal: autoRenewal}, function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg(response.message, {icon:1});
                encryption365.page('siteSetting', {siteName: siteName});
            }
        });
    },
    upgradeToBusinessSSL: function(siteName, pid, level, org_id, autoRenewal) {
        // 检查Org
        if(level !== "dv" && org_id === "-1"){
            layer.msg('请选择此证书的申请人信息',{icon:7});
            return false;
        }
        var loads = bt.load('正在提交签名请求...');
        request_plugin('encryption365', 'upgradeToBusinessSSL', { siteName: siteName, pid:pid, org_id:org_id, autoRenewal: autoRenewal}, function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg(response.message, {icon:1});
                encryption365.page('siteSetting', {siteName: siteName});
            }
        });
    },
    setSSL: function(siteName, certCode, keycode) {
        var loads = bt.load('正在尝试安装...');
        request_baotaAjax('site', 'SetSSL', { type:1, siteName: siteName, key:keycode, csr:certCode}, function (response) {
            loads.close();
            if(response.status !== true){
                layer.msg(response.msg, {icon:2});
            }else{
                layer.msg(response.msg, {icon:1});
                encryption365.page('siteSetting', {siteName: siteName});
            }
        });
    },
    reissueSSLOrder:function(siteName) {
        var loads = bt.load('正在提交签名请求...');
        request_plugin('encryption365', 'reissueSSLOrder', { siteName: siteName}, function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg(response.message, {icon:6});
                encryption365.page('siteSetting', {siteName: siteName});
            }
        });
    },
    removeSSLOrder: function(siteName) {
        layer.confirm("确认删除此订单？取消订单后您可以重新为此站点配置申请新的SSL证书",{title:"删除证书订单",icon:0},function(t){
            request_plugin('encryption365', 'removeSSLOrder', { siteName: siteName }, function (response) {
                if(response.status !== "success"){
                    layer.msg(response.message, {icon:2});
                    return false;
                }else{
                    layer.msg(response.message, {icon:6});
                    encryption365.page('siteSetting',{siteName: siteName});
                }
            });
        });
    },
    toggleAutoRenewal: function(siteName) {
        request_plugin('encryption365', 'toggleAutoRenewal', { siteName: siteName }, function (response) {
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
                return false;
            }else{
                layer.msg(response.message, {icon:6});
            }
        });
    },
    checkSSLOrderStatus: function(siteName, refJob) {
        request_plugin('encryption365', 'checkSSLOrderStatus', { siteName: siteName }, function (response) {
            if(response.status === "success"){
                layer.msg("证书已成功签发", {icon:1});
                // clearInterval(refJob); // 清除定时刷新任务
                encryption365.page('siteSetting',{siteName: siteName});
            }else{
                layer.msg("还未签发, 请检查域名验证信息是否设置正确", {icon: 5});
            }
        });
    },
    createOrganization: function(frm) {
        var loads = bt.load('正在保存...');
        request_plugin('encryption365', 'createNewOrganization', $(frm).serialize(), function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg("新建成功", {icon:1});
                // 执行一些其他操作
                encryption365.page('getOrgTempList');
            }
        });
        return false;
    },
    tryToAddFunds: function(form) {
        var loads = bt.load('创建充值账单...');
        request_plugin('encryption365', 'generateAddFunds', $(form).serialize(), function (response) {
            loads.close();
            if(response.result !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                encryption365.showQrAlipayWindow(response.invoiceid, response.link);
            }
        });
        return false;
    },
    showQrAlipayWindow: function(invoiceid, link){
        layer.open({
            type: 1,
            title: '支付宝扫码支付',
            area: "300px !important;height: 355px !important",
            closeBtn: 2,
            content:`<div class='bt-form pd20 pb70' style='width: 100% !important;'>
<div style="width: 300px; background: antiquewhite; padding: 10px; margin-top: -20px; margin-left: -20px; margin-bottom: 10px;">
订单号: ENCRYPTION365`+invoiceid+`<br/>
产品描述：环智中诚™账户余额充值
</div>
<div class="logo-alipay" style="height: 30px; margin: 0 auto; width: 80px;margin-bottom: 10px;">
</div>
<div id="alipayQrCode" style="height: 180px; width: 180px; margin: 0 auto;"></div>
<div style="text-align: center; margin-bottom: 15px;">
<img src="/encryption365/static/img/ajax-loader.gif" style=""> 等待支付...
</div>
<script>new QRCode(document.getElementById('alipayQrCode'), {
    text:'`+link+`',
    width:175,
    height:175,
    correctLevel: QRCode.CorrectLevel.H
});</script>
                            </div>`,
            success:function(layerDiv, index){
                $('#layui-layer'+index+' .layui-layer-close').click(function(){
                    // 取消了支付
                    window.clearInterval(encryption365.gVars.payment.checkAlipay);
                    // 需要增加处理一些逻辑
                    var loads = bt.load('正在取消充值订单...');
                    request_plugin('encryption365', 'revokeInvoice', {invoiceid: invoiceid}, function (response) {
                        loads.close();
                        if(response.status === "success"){
                            layer.msg("充值订单已取消!", {icon:1});
                        }else{
                            layer.msg("充值订单取消失败!", {icon:5});
                        }
                        layer.close(index);
                    });
                });
                // 复制待用
                encryption365.gVars.payment.layer = layer;
                encryption365.gVars.payment.layer_index = index;
                // 定时检查订单状态
                encryption365.gVars.payment.checkAlipay = setInterval(function(layer){
                    request_plugin('encryption365', 'getInvoiceStatus', {invoiceid: invoiceid}, function (response) {
                        if(response.result === "success" && response.invoice_status ==="Paid"){
                            window.clearInterval(encryption365.gVars.payment.checkAlipay);
                            encryption365.gVars.payment.layer.close(encryption365.gVars.payment.layer_index);
                            encryption365.gVars.payment.layer.msg("充值成功!", {icon:1});
                            encryption365.page('addFunds',{},'账户查询中');
                        }
                    });
                }, 1000);
            }
        });
    },
    updateOrganizationDetails: function(frm) {
        var loads = bt.load('正在保存...');
        request_plugin('encryption365', 'updateOrganizationDetails', $(frm).serialize(), function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg("保存成功", {icon:1});
                // 执行一些其他操作
                encryption365.page('getOrgTempList');
            }
        });
        return false;
    },
    accountRegStep1: function(pars) {
        encryption365.gVars.reg.email = $('input[name=email]').val();
        var loads = bt.load('正在请求验证码...');
        request_plugin('encryption365', 'accountRegStep1', pars, function (response) {
            loads.close();
            if(response.result !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg("验证码发送成功", {icon:1});
                // 执行一些其他操作
                encryption365.page('getRegisterTwo',{email: encryption365.gVars.reg.email});
            }
        });
    },
    accountRegStep2: function(pars) {
        var loads = bt.load('账户注册中...');
        request_plugin('encryption365', 'accountRegStep2', pars, function (response) {
            loads.close();
            if(response.result !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg("注册成功", {icon:1});
                // 开始登陆并绑定账户
                encryption365.clientRegister(encryption365.gVars.reg.email, $('input[name=password]').val());
            }
        });
    },
    clientRegister: function(u,p) {
        var loads = bt.load('正在登陆并绑定客户端...');
        request_plugin('encryption365', 'clientRegister', { username: u, password: p }, function (response) {
            loads.close();
            if(response.status !== "success"){
                layer.msg(response.message, {icon:2});
            }else{
                layer.msg("客户端绑定成功", {icon:1});
                // 执行一些其他操作
                encryption365.page('siteList');
            }
        });
    },
    openPath: function(a) {
        setCookie("Path", a);
        window.open("/files",'_blank');
    },
    localCartTotal: function(productElem) {
        let pel = $(productElem);
        let level = $(pel).data('level');
        let price = {
            fqdn: parseFloat(pel.data('price-fqdn')).toFixed(2),
            wildcard: parseFloat(pel.data('price-wildcard')).toFixed(2),
            ipv4: parseFloat(pel.data('price-ipv4')).toFixed(2)
        };
        let domainCount = {
            fqdn: $('span[data-fqdn]').data('fqdn'),
            wildcard: $('span[data-wildcard]').data('wildcard'),
            ipv4: $('span[data-ipv4]').data('ipv4'),
        };
        // 判断展示企业选择器
        let orgSelection = $('#orgSelection');
        if(level !== "dv"){
            $(orgSelection).show();
        }else{
            $(orgSelection).hide();
        }
        // 展示域名单价
        $('span[data-domain-price-fqdn]').html(parseMoneyFormatNum(price.fqdn,2));
        $('span[data-domain-price-wildcard]').html(parseMoneyFormatNum(price.wildcard,2));
        $('span[data-domain-price-ipv4]').html(parseMoneyFormatNum(price.ipv4,2));
        // 计算总金额
        let cartTotal = 0.00;
        for (let key in price){
            if(price[key] >= 0){
                var nicePrice = price[key]*domainCount[key];
                cartTotal += nicePrice;
                // 展示域名费用小计
                $('span[data-domain-pricet-'+key+']').html(parseMoneyFormatNum(nicePrice, 2));
            }
        }
        $('span[data-toCartTotal]').html(parseMoneyFormatNum(cartTotal, 2));
        // 展示订购周期
        $('span[data-toCartPeriod]').html(pel.data('period-text'));
    }

};

/**
 * 转换金额格式
 * @param number
 * @param n
 * @returns {string}
 */
function parseMoneyFormatNum(number,n){
    if(n !== 0 ){
        n = (n > 0 && n <= 20) ? n : 2;
    }
    number = parseFloat((number + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
    var sub_val = number.split(".")[0].split("").reverse();
    var sub_xs = number.split(".")[1];
    var show_html = "";
    for (i = 0; i < sub_val.length; i++){
        show_html += sub_val[i] + ((i + 1) % 3 === 0 && (i + 1) !== sub_val.length ? "," : "");
    }
    if(n === 0 ){
        return show_html.split("").reverse().join("");
    }else{
        return show_html.split("").reverse().join("") + "." + sub_xs;
    }
}

/**
 * 发送请求到插件
 * 注意：除非你知道如何自己构造正确访问插件的ajax，否则建议您使用此方法与后端进行通信
 * @param plugin_name    插件名称 如：demo
 * @param function_name  要访问的方法名，如：get_logs
 * @param args           传到插件方法中的参数 请传入数组，示例：{p:1,rows:10,callback:"demo.get_logs"}
 * @param callback       请传入处理函数，响应内容将传入到第一个参数中
 */
function request_plugin(plugin_name, function_name, args, callback, timeout) {
    if (!timeout) timeout = 10000;
    $.ajax({
        type:'POST',
        url: '/plugin?action=a&s=' + function_name + '&name=' + plugin_name,
        data: args,
        timeout:timeout,
        success: function(rdata) {
            if (!callback) {
                layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                return;
            }
            return callback(rdata);
        },
        error: function(ex) {
            if (!callback) {
                layer.msg('请求过程发现错误!', { icon: 2 });
                return;
            }
            return callback(ex);
        }
    });
}

/**
 * 发送 Ajax 请求到宝塔面板
 * @param layer
 * @param action
 * @param args
 * @param callback
 * @param timeout
 */
function request_baotaAjax(layer, action, args, callback, timeout) {
    if (!timeout) timeout = 10000;
    $.ajax({
        type:'POST',
        url: '/'+layer+'?action='+action,
        data: args,
        timeout:timeout,
        success: function(rdata) {
            if (!callback) {
                layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                return;
            }
            return callback(rdata);
        },
        error: function(ex) {
            if (!callback) {
                layer.msg('请求过程发现错误!', { icon: 2 });
                return;
            }
            return callback(ex);
        }
    });
}