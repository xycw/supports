<?php if (!defined('THINK_PATH')) exit(); echo R('Common/Html/html_start');?> 
<header id="header"><?php echo R('Common/Layout/menu');?></header>
<div class="container" id="content">
<h1>未下单客户</h1>
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-default">
            <div class="panel-heading">等待发送邮件的客户</div>
            <div class="panel-body">
                <form method="post" action="<?php echo U('Marketing/Email/no_order_customers')?>" id="search-form">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>网站</label>
                                                        <select id="" name="site_id[]" ondblclick="" onchange="" multiple="multiple" class="" size="" ><?php  foreach($options_site_name as $key=>$val) { if(!empty($site_id_select) && ($site_id_select == $key || in_array($key,$site_id_select))) { ?><option selected="selected" value="<?php echo $key ?>"><?php echo $val ?></option><?php }else { ?><option value="<?php echo $key ?>"><?php echo $val ?></option><?php } } ?></select>
                        </div>
                    </div>
                    <div class="form-inline mt5">
                        <div class="form-group">
                            <label>注册时间</label>
                            <input class="form-control" type="number" name="register_time_start" value="<?php echo I('register_time_start', '') ?>" placeholder="起始时间" style="width:65px;">
                                小时前至
                                <input class="form-control" type="number" name="register_time_end" value="<?php echo I('register_time_end', '') ?>" placeholder="结束时间" style="width:65px;">
                                小时前之间
                            <button type="submit" class="btn btn-default">查询</button>
                        </div>
                    </div>
                </form>
                <table class="table table-border mt5">
                    <tr>
                        <th><input type="checkbox" name="check_all" checked="true"> 序号</th>
                        <th>网站</th>
                        <th>客户邮箱</th>
                        <th>注册时间</th>
                        <th>邮件发送次数</th>
                    </tr>
                    <?php foreach ($list as $k=>$customer){?>
                    <tr>
                        <td><input type="checkbox" name="customers_email[]" value="<?php echo $customer['customers_email_address'];?>" checked="true"><input type="hidden" value="<?php echo $customer['site_id'];?>"><input type="hidden" value="<?php echo $customer['customers_id'];?>"> <?php echo $k+1;?></td>
                        <td>#<?php echo $customer['site_id'].'-'.$customer['site_name'];?></td>
                        <td><?php echo $customer['customers_email_address'];?></td>
                        <td><?php echo $customer['customers_info_date_account_created'];?></td>
                        <td id="number-<?php echo $customer['site_id'].'-'.$customer['customers_id'];?>"><?php echo $customer['send_mail_number'];?></td>
                    </tr>
                    <?php }?>
                </table>

                <div class="page-nav">
                    <div class="page-nav-info">总数:<?php echo $count?></div>
                    <?php
 W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$count, 'name'=>'Marketing/Email/no_order_customers', $page_data)); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel panel-default">
            <div class="panel-heading">选择邮件模板 <a href="<?php echo U('Marketing/Email/template',array('source'=>'no_order'))?>">模板管理</a></div>
            <div class="panel-body">
                                <select id="" name="email_template" onchange="" ondblclick="" class="form-control" ><?php  foreach($options_template as $key=>$val) { ?><option value="<?php echo $key ?>"><?php echo $val ?></option><?php } ?></select>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">使用网站默认的邮箱发送邮件</div>
            <div class="panel-body">
                <input type="checkbox" name="default_mail_account" value="1" checked="checked"> 不勾选则随机使用系统已有的邮箱账号中的一个 <a href="<?php echo U('Marketing/Email/smtp_account',array('source'=>'no_order'))?>">账号管理</a>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">可用的邮箱接口</div>
            <div class="panel-body">
                <select multiple="multiple" name="mail_api" class="form-control" style="height:200px;">
                    <?php
 foreach ($send_mail_api as $k=>$api){ echo '<option value="'.$api.'"'.($k==0 ? ' selected="selected"' : '').'>'.$api.'</option>'; } ?>
                </select>
            </div>
        </div>

        <button type="button" id="start_send" class="btn btn-default btn-block">发送</button>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.css">
<script src="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.js"></script>
<script>
$(function() {
    $('select[name="site_id[]"]').multipleSelect();
    $('input[name="register_time_start"],input[name="register_time_end"]').blur(function(){
        if($(this).val() < 0) $(this).val(0);
    });
    $('#search-form').submit(function(){
        if($('input[name="register_time_start"]').val() === ''){
            alert('请输入起始时间');
            return false;
        }
        if($('input[name="register_time_end"]').val() === ''){
            alert('请输入结束时间');
            return false;
        }
        if($('input[name="register_time_start"]').val() < $('input[name="register_time_end"]').val()){
            alert('起始时间不能小于结束时间');
            return false;
        }
        return true;
    });
})
var ajax_task = (function(){
    var _num_max_runing  = 5;//最大同时ajax的数量
    var _queue_runing  = new Array();//运行下载的队列
    var _queue_waiting = new Array();//等待下载的队列

    function _task(url, post_data, fun_beforeSend, fun_success, fun_error){
        this.url = url;
        this.isfinish = false;

        this.run = function(){
            var _this = this;
            $.ajax({
                url : this.url,
                dataType : 'json',
                async : true,
                timeout : 60000,//请求时间
                type : "post",
                data : post_data,
                beforeSend : function(){
                    fun_beforeSend();
                },
                success : function(data){
                    _this.isfinish = true;
                    fun_success(data);
                },
                error : function(jqXHR, textStatus, errorThrown){
                    _this.isfinish = true;
                    fun_error();
                }
            });
        }
    }

    var _loading = new (function(){
        var _status = false;
        return {
            start:function(){
                if(_status==false){
                    _status = true;
                    layer.load(1);
                }
            },
            end:function(){
                if(_status==true){
                    _status = false;
                    layer.closeAll('loading');
                }
            },
            listen_end:function(condition){
                if(eval(condition)){
                    _loading.end();
                }else{
                    setTimeout("ajax_task._loading().listen_end("+condition+")", 1200);
                }
            }
        }
    })();

    return {
        _loading:function(){
            return _loading;
        },
        idle:function(){
            return (_queue_waiting.length==0 && _queue_runing.length==0);
        },        
        add : function(url, fun_beforeSend, fun_success, fun_error){
            var task = new _task(url, fun_beforeSend, fun_success, fun_error);
            _queue_waiting.push(task);
        },
        run : function(){
            _loading.start();
            if(_queue_runing.length>0){
                //将已完成的任务从队列移除
                for(var i in _queue_runing){
                    if(_queue_runing[i].isfinish){
                        _queue_runing.splice(i,1);
                    }
                }
            }
            if(_queue_waiting.length>0){
                if(_queue_runing.length<_num_max_runing){
                    do{
                        var task = _queue_waiting.shift();
                        task.run();
                        _queue_runing.push(task);
                    }while(_queue_runing.length<_num_max_runing && _queue_waiting.length>0);
                }
            }

            if(_queue_runing.length>0 || _queue_waiting.length>0){
                setTimeout("ajax_task.run()", 1000);
            }
            _loading.listen_end('ajax_task.idle()');
        }
    };

})();

var mail_send = (function(){
    var _site_ids, _customers_ids, _mail_api, _default_mail_account, _customers_emails, _email_template_id;

    var _start = function(){
        if(_customers_emails.length==0) return;

        while(_customers_emails.length>0 && _mail_api.length>0){
            var site_id = _site_ids.shift();
            var customers_id = _customers_ids.shift();
            var email_address = _customers_emails.shift();
            var default_mail_account   = _default_mail_account;
            var mail_api = _mail_api.shift();
            var email_template = _email_template_id;

            (function(site_id, customers_id, email_address, default_mail_account, mail_api, email_template){
                 var post_data = {
                    'url' : mail_api,
                    'site_id' : site_id,
                    'customers_id' : customers_id,
                    'default_mail_account' : default_mail_account,
                    'to_address' : email_address,
                    'email_template_id' : email_template,
                };
                var fun_beforeSend = function() {layer.msg('正在发送邮件至'+email_address+'...');}
                var fun_success = function(data){
                    if(data.status==1){
                        layer.msg('发送邮件到'+email_address+'成功！');
                        var number_td = $('#number-'+site_id+'-'+customers_id);
                        number_td.html(parseInt(number_td.text()) + 1);
                    }else{
                        layer.msg('发送邮件到'+email_address+'失败！('+data.error+')');
                    }
                    _mail_api.push(mail_api);
                }
                var fun_error = function(){
                    layer.msg('发送邮件到'+email_address+'失败！');
                    _mail_api.push(mail_api);
                }

                ajax_task.add("<?php echo U('Marketing/Email/send_customers')?>", post_data, fun_beforeSend, fun_success, fun_error);
                ajax_task.run();
            })(site_id, customers_id, email_address, default_mail_account, mail_api, email_template);
            ajax_task.run();
        }

        setTimeout(function(){_start()}, 1000);
    }

    return {
        setup:function(site_ids, customers_ids, mail_api, default_mail_account, customers_emails, email_template_id){
            _site_ids = site_ids;
            _customers_ids = customers_ids;
            _mail_api = mail_api;
            _default_mail_account = default_mail_account;
            _customers_emails = customers_emails;
            _email_template_id = email_template_id;
        },

        start:function(){
            _start();
        }
    }
})();

$('input[name="check_all"]').click(function(){
    var checked = $(this).is(':checked');
    $('input[name="customers_email[]"]').prop("checked", checked);
});

$('#start_send').click(function(){
    var site_ids = new Array();
    var customers_ids = new Array();
    var mail_api = new Array();
    var default_mail_account = 0; 
    var customers_emails = new Array();

    $('option:selected','select[name="mail_api"]').each(function(){
        mail_api.push($(this).val());
    });
    if(mail_api.length==0){
        alert('请选择发送邮件的接口');
        return false;
    }

    if($('input[name="default_mail_account"]').is(':checked')) default_mail_account = 1;

    $('input[name="customers_email[]"]:checked').each(function(){
        site_ids.push($(this).next('input').val());
        customers_ids.push($(this).next('input').next('input').val());
        customers_emails.push($(this).val());
    });
    if(customers_emails.length==0){
        alert('请选择客户');
        return false;
    }

    var email_template_id = $('select[name="email_template"]').val();

    mail_send.setup(site_ids, customers_ids, mail_api, default_mail_account, customers_emails, email_template_id);
    mail_send.start();
});
</script>
</div>
<footer id="footer"><?php echo R('Common/Layout/footer');?></footer>
<?php echo R('Common/Html/html_end');?>