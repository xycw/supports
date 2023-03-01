<table class="table table-bordered">
    <colgroup>
        <col width="150px">
        <col width="100px">
        <col width="80px">
        <col width="100px">
        <col width="auto">
        <col width="100px">
    </colgroup>    
    <tr>
        <th>订单号</th>
        <th>付款方式</th>
        <th>邮件记录</th>
        <th>订单状态</th>
        <th>邮件模板<br>
            <tagLib name="html" />
            <html:select options="option_email_template" name="all_email_tempalte" first="--批量切换模板--" style="form-control" />
        </th>
        <th>预览</th>
    </tr>
<?php
foreach($order_list as $entry_order){
    $options_email_templates = $entry_order['email_templates'];
?>
    <tr id="tr_<?php echo $entry_order['site_id'].'_'.$entry_order['orders_id']?>">
        <td><?php echo $entry_order['order_no_prefix'].$entry_order['orders_id'];?></td>
        <td><?php echo $entry_order['payment_module_code']?></td>
        <td>
        <?php
        $tip = '';
        $num_email = 0;
        $email_history = array();
        if(!empty($entry_order['email_logs'])){
            $email_history = json_decode($entry_order['email_logs'], true);
        }
        if(sizeof($email_history)>0){
            foreach ($email_history as $_email_history){
                foreach($_email_history as $history){
                    $tip .= '<div style=\'text-align:left;\'>'.$history['time'].$history['email_template_name']."</div>";
                    $num_email++;
                }
            }
        }else{
            $tip = '无邮件发送记录';
        }        
        ?>
            <span class="badge" data-toggle="tooltip" data-placement="bottom" data-html="true" title="<?php echo $tip?>"><?php echo $num_email?></span></td>
        <td><?php echo $entry_order['order_status_remark']?></td>
        <td>
            <tagLib name="html" />
            <html:select options="options_email_templates" name="email_templates[]" style="form-control" />
        </td>
        <td><button class="btn btn-default btn-xs email-preview" data-order="<?php echo $entry_order['orders_id']?>" data-site="<?php echo $entry_order['site_id']?>">预览</button></td>
    </tr>
<?php    
}    
?>
</table>
<button class="btn btn-default" id="start_multi_send">发送</button>
<div id="email_preview_wrapper">
    <h2 class="title"></h2>
    <div class="content"></div>
</div>
<script>
    var ajax_task = (function(){
    var _num_max_runing  = 1;//最大同时ajax的数量
    var _queue_runing  = new Array();//运行下载的队列
    var _queue_waiting = new Array();//等待下载的队列
      
    function _task(url, data, fun_beforeSend, fun_success, fun_error){
        this.url = url;
        this.isfinish = false;
        
        this.run = function(){
            var _this = this;
            $.ajax({
                url		: this.url, 
                dataType	: 'json',
                async           : true,
                timeout 	: 60000,//请求时间
                'data'          : data,
                type            : "post",
                beforeSend    : function(){
                    fun_beforeSend();
                },
                success	: function(data){
                    this.isfinish = true;
                    fun_success(data);
                },
                error	: function(jqXHR, textStatus, errorThrown){
                    _this.isfinish = true;
                    fun_error();
                },
                complete 	: function(){
                    _this.isfinish = true;                                
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
$('select[name="all_email_tempalte"]').change(function(){
    var v = $(this).val();
    $('select[name="email_templates[]"]').val(v);
});
$('[data-toggle="tooltip"]').tooltip();
$('#start_multi_send').click(function(){
    $('select[name="email_templates[]"]').each(function(){
        var email_templates_id = $(this).val();
        var orders_id = $(this).parent().next().find('button').attr('data-order');
        var site_id   = $(this).parent().next().find('button').attr('data-site');
        var url = "<?php echo U('Order/Order/email/')?>/site_id/"+site_id+'/order_id/'+orders_id;
        var fun_success = function(data){
            if(data.status==1){
                $('#tr_'+site_id+'_'+orders_id).attr('class', 'bg-success');
                $('select,button','#tr_'+site_id+'_'+orders_id).remove();
            }else
                $('#tr_'+site_id+'_'+orders_id).attr('class', 'bg-danger');
        }
        var fun_error = function(){
            $('#tr_'+site_id+'_'+orders_id).attr('class','bg-danger');
        }
        var fun_beforeSend = function(){
            $('#tr_'+site_id+'_'+orders_id).attr('class','bg-primary');
        }
        ajax_task.add(url, {'action':'send', 'email_templates_id':email_templates_id}, fun_beforeSend, fun_success, fun_error);  
    });    
    ajax_task.run();
});
$('.email-preview').click(function(){
    var orders_id = $(this).attr('data-order');
    var site_id   = $(this).attr('data-site');
    var email_templates_id = $(this).parent().prev().find('select').val();
    $.ajax({
        type: "POST",
        url: "<?php echo U('Order/Order/email/')?>/site_id/"+site_id+'/order_id/'+orders_id,
        data:{'email_templates_id':email_templates_id, 'action':'get_template'},
        dataType: 'json',
        beforeSend: function () {layer.load(1);},
        success:function(data){
            $('.title', '#email_preview_wrapper').text(data.email_template_title);
            $('.content', '#email_preview_wrapper').html( data.email_template_content );
            layer.closeAll('loading');
        }
    });
});
</script>
