<h1>邮件推广营销</h1>

<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-default">
            <div class="panel-heading">等待发送邮件的客户</div>
            <div class="panel-body">              
                <form method="post" action="<?php echo U('Marketing/Email/index')?>">
                    <div class="form-inline">
                    <div class="form-group">
                        <label>未发送过邮件模板</label>
                        <tagLib name="html" />
                        <html:select options="options_template" name="email_template_id" first="--不限--" style="form-control" />       
                    </div>
                    </div>
                    <div class="form-inline mt5">
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="text" name="customers_email" class="form-control" varle="<?php echo $data_page['customers_email']?>">
                        <button type="submit" class="btn btn-default">查询</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dialog-import">邮箱导入</button>
                    </div>                    
                    </div>                    
                    
                </form>
                <table class="table table-border mt5">
                    <tr>
                        <th><input type="checkbox" name="check_all" checked="true">ID</th>
                        <th>邮箱</th>
                    </tr>
                    <?php
                    if(empty($email_list)==false){
                        foreach ($email_list as $entry){
                    ?>
                    <tr>
                        <td><input type="checkbox" name="marketing_email[]" value="<?php echo $entry['customers_email']?>" checked="true"><?php echo $entry['email_id']?></td>
                        <td><?php echo $entry['customers_email']?></td>
                    </tr>
                    <?php
                        }        
                    }
                    ?>
                </table>

                <div class="page-nav">
                    <div class="page-nav-info">总数:<?php echo $email_count?></div>
                    <?php 
                    W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$email_count, 'name'=>'Marketing/Email/index',$data_page));
                    ?>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"><a href="<?php echo U('Marketing/Email/template_mail')?>">邮件模板管理</a></div>
                    <div class="panel-body">
                        <select name="mail_account" multiple="multiple" class="form-control" style="height:200px;">
                            <?php
                            foreach ($email_template_names as $email_template_name){
                                echo '<option value=\''. json_encode($email_template_name).'\'>'.$email_template_name['email_template_name'].'</option>';
                            }
                            ?>
                        </select>

                    </div>
                </div>
            </div>
        </div>
    </div>    
    
    <div class="col-lg-5">
        <div class="panel panel-default">
            <div class="panel-heading">选择邮件模板<a href="<?php echo U('Marketing/Email/template')?>">模板管理</a></div>
            <div class="panel-body">
                <tagLib name="html" />
                <html:select options="options_template" name="email_template" style="form-control" />                
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">可用的smtp邮箱账号,用于发送邮件<a href="<?php echo U('Marketing/Email/smtp_account')?>">账号管理</a></div>
            <div class="panel-body">
                <select name="mail_account" multiple="multiple" class="form-control" style="height:200px;">
                    <?php
                    foreach ($email_accounts as $email_account){
                        echo '<option value=\''. json_encode($email_account).'\'>'.$email_account['email_username'].'</option>';
                    }
                    ?>                    
                </select>
            </div>
        </div>          
        <div class="panel panel-default">
            <div class="panel-heading">可用的邮箱接口</div>
            <div class="panel-body">
                <select multiple="multiple" name="mail_api" class="form-control" style="height:200px;">
                    <?php
                    foreach ($send_mail_api as $api){
                        echo '<option value="'.$api.'">'.$api.'</option>';
                    }
                    ?>                    
                </select>
            </div>
        </div>    
        
        <button type="button" id="start_send" class="btn btn-default btn-block">发送</button>
    </div>         
</div>    

<div class="modal fade" id="dialog-import">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">邮箱导入</h4>
      </div>
      <div class="modal-body">
          <form method="post" action="<?php echo U('Marketing/Email/import')?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>导入邮箱csv</label>
                        <tagLib name="html" />
                        <input type="file" name="file_csv" />
                        <p class="help-block">CSV文件为三个字段,分别是firstname,lastname,email</p>
                    </div>
                    <button type="submit" class="btn btn-default">导入</button>
                </form>  
      </div>
    </div>
  </div>
</div>

<script>
    
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
                url		: this.url, 
                dataType	: 'json',
                async           : true,
                timeout 	: 60000,//请求时间
                type            : "post",
                data            : post_data,
                beforeSend     : function(){
                    fun_beforeSend();
                },
                success	: function(data){
                    _this.isfinish = true;
                    fun_success(data);
                },
                error	: function(jqXHR, textStatus, errorThrown){
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
    var _mail_api, _mail_account, _market_emails, _email_template_id;

    var _start = function(){
        if(_market_emails.length==0)
            return;
        
        while(_market_emails.length>0 && _mail_account.length>0 && _mail_api.length>0){
            var email_address = _market_emails.shift();
            var mail_acount   = _mail_account.shift();
            var mail_api      = _mail_api.shift();
            var email_template = _email_template_id;

            (function(email_address, mail_acount, mail_api, email_template){
                 var post_data = {
                    'url'        : mail_api,
                    'smtp_email' : mail_acount.email_username,
                    'smtp_pwd'   : mail_acount.email_password,
                    'smtp_host'  : mail_acount.email_stmp_host,
                    'smtp_port'  : mail_acount.email_smtp_port, 
                    'to_address' : email_address,
                    'email_template_id' : email_template,        
                };
                var fun_beforeSend = function() {layer.msg('正在发送邮件至'+email_address+'...');}
                var fun_success = function(data){
                    if(data.status==1){
                        layer.msg('发送邮件到'+email_address+'成功！');                            
                    }else{
                        layer.msg('发送邮件到'+email_address+'失败！('+data.error+')');
                    }
                    _mail_account.push(mail_acount);
                    _mail_api.push(mail_api);
                }
                var fun_error = function(){
                    layer.msg('发送邮件到'+email_address+'失败！');
                    _mail_account.push(mail_acount);
                    _mail_api.push(mail_api);
                }

                ajax_task.add("<?php echo U('Marketing/Email/send')?>", post_data, fun_beforeSend, fun_success, fun_error);
                ajax_task.run();
            })(email_address, mail_acount, mail_api, email_template);   
            ajax_task.run();
        }
        
        setTimeout(function(){_start()}, 1000);
    }
    
    return {
        setup:function(mail_api, mail_account, market_emails, email_template_id){
            _mail_api       = mail_api;
            _mail_account   = mail_account;
            _market_emails  = market_emails;
            _email_template_id = email_template_id;
        },
        
        start:function(){
            _start();
        }
    }
})();

$('input[name="check_all"]').click(function(){
    var checked = $(this).is(':checked');
    $('input[name="marketing_email[]"]').prop("checked", checked);
});

    
$('#start_send').click(function(){
    var mail_api = new Array();
    var mail_account = new Array(); 
    var market_emails = new Array();
    var email_tempate = {'subject':'', 'content':''};
    
    $('option:selected','select[name="mail_api"]').each(function(){
        mail_api.push($(this).val());
    });
    if(mail_api.length==0) alert('请选择发送邮件的接口');
   
    $('option:selected','select[name="mail_account"]').each(function(){
        eval('var obj_account='+$(this).val());
        mail_account.push(obj_account);
    });

    $('input[name="marketing_email[]"]:checked').each(function(){
        market_emails.push($(this).val());
    });
    var email_template_id = $('select[name="email_template"]').val();

    mail_send.setup(mail_api, mail_account, market_emails, email_template_id);
    mail_send.start();
});
</script>    