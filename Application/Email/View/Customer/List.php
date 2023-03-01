<h1>邮件模板选择</h1>
<tagLib name="html" />
<html:select options="template_list" name="email_template" first="--select--" style="form-control" /><br>
<button class="btn btn-default btn-block" id="btn-sendmail">发送邮件至以下客户邮箱</button>

<h1>客户邮箱</h1>
<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Email/Customer/List')?>" method="get">
    <div class="row">
        <label for="customers_email" class="col-lg-1">邮箱</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="customers_email" id="customers_email" value="<?php echo I('customers_email','')?>"></div>       
        <label for="date_send" class="col-lg-1">模板</label>
        <div class="col-lg-2"><html:select options="template_list" name="email_template" first="--select--" style="form-control" selected="email_template"/></div>
        <div class="col-lg-2">
            <select class="form-control" name="send_status">
                <option value="0"<?php if(I('send_status')=='0') echo ' selected'?>>未发送</option>
                <option value="1"<?php if(I('send_status')=='1') echo ' selected'?>>已发送</option>
            </select>
        </div>       
        <div class="col-lg-2"><input class="btn btn-default" type="submit"  value="查询"></div>
    </div>
</form>
<h1>导入客户邮箱</h1>
<form class="advanced-search form-horizontal" action="<?php echo U('Email/Customer/Import')?>" method="post" enctype="multipart/form-data">
    <div class="row">
        <label class="col-lg-2">导入邮箱csv信息</label>
        <div class="col-lg-2"><input type="file" name="file_csv"></div>     
        <div class="col-lg-2"><input class="btn btn-default" type="submit"  value="导入"></div>
    </div>    
</form>    
<table class="table table-border">
    <tr>
        <th>ID</th>
        <th>邮箱</th>
        <th>客户姓名</th>
        <th>备注</th>
    </tr>
    <?php
    if(empty($list)==false){
        foreach ($list as $entry){
    ?>
    <tr>
        <td><?php echo $entry['email_id']?></td>
        <td><?php echo $entry['customers_email']?></td>
        <td><?php echo $entry['customers_firstname'].' '.$entry['customer_lastname']?></td>
        <td><?php echo $entry['customer_remark']?></td>
    </tr>
    <?php
        }        
    }
    ?>
</table>


<div class="page-nav">
    <div class="row">
            <div class="col-lg-6">
                    <div class="page-nav-info">总数:<?php echo $count?></div>
            </div>
            <div class="col-lg-6 right">
            <?php 
            W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$count, 'name'=>'Email/Customer/List',$data_page));
            ?>
            </div>
    </div>
</div>

<script>
$("input[name='date_send']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month'});
var mailbox = function(){
    var _mail = new Array();//待发送
    var _lock = false;//一次只能发送一份邮件
    var _failure_num = 0;//连结发送失败数量
    
    var _send = function(){
        if(_mail.length>0 && _lock==false){
            _lock = true;
            var mail_data = _mail.shift();
            $.ajax({
                url : "<?php echo U('Email/Customer/Sendmail')?>",
                data: {customer_email_id:mail_data.email_id,email_template_id:mail_data.email_template_id},
                dataType : 'json',
                beforeSend: function(){
                    layer.msg("正在...发送邮件到"+mail_data.customers_email);
                },
                success: function(json){
                    if(json.error==false){
                        _failure_num = 0;
                        layer.msg("成功!!!发送邮件到"+mail_data.customers_email);
                    }else{
                        layer.msg("失败!!!发送邮件到"+mail_data.customers_email);
                        _failure_num++;
                    }    
                },
                error:function(){
                    _failure_num++;
                },
                complete: function(){
                    setTimeout(function(){_lock = false;},'2000');//发送完后，等等几秒后再进第二份邮件发送
                }
            });
        }
    }

    var _run = function(){
       layer.load(1,{shade: [0.1,'#000']});
       var n = 100; 
       
       
       if(_mail.length>0 && _failure_num<n){
           _send();
           setTimeout(_run,'2000');
       }else{
           if(_mail.length==0)
                layer.closeAll('loading');
            else{
                setTimeout(_run,'1000');
            }
           if(_failure_num>=n)
            layer.confirm('已经连续发送'+n+'份邮件失败了,是否继续?', {
                 btn: ['是','否'] //按钮
               }, function(){
                 _failure_num = 0;
                 _run();
               }, function(){
                 return;
               });
            else   
                layer.msg('邮箱中发送列表已经空了!');
       }
    }
    
    return {
        add : function(mail_entry){
            _mail.push(mail_entry);
        },
        reset :function(){
            _mail = new Array();
        },
        run : function(){
            _failure_num = 0; 
            _run();
        }
    }
}();
    
$('#btn-sendmail').click(function(){
    if(window.confirm("确定发送邮件给以下客户吗?")){
        var template_id = $('select[name="email_template"]').val();
        if(!template_id){
            alert("请选择邮件模板!");
            return;
        }
        $.ajax({
            url : "<?php echo U('Email/Customer/List')?>",
            <?php
            if(empty($data_page)==false) 
                echo "data:".json_encode($data_page).",\n";
            ?>
            type: "get",
            dataType	: 'json',
            success: function(data){
                layer.msg("加载客户邮箱数据成功!");
                mailbox.reset();
                for (var key in data) {
                    data[key].email_template_id = template_id;
                    mailbox.add(data[key]);
                }
                mailbox.run();
            }
        });
    }
});
    
</script>    