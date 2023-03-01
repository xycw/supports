<style>
    .ms-choice{border:none;}
</style>
<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Customers/Customers/list')?>" method="get">
	<div class="row">
		<div class="col-lg-1"><input class="form-control" type="text" name="customer_email" id="customer-email" value="<?php echo I('customer_email','')?>" placeholder="邮箱"></div>
        <div class="col-lg-2">
            <tagLib name="html"/>
            <html:select options="options_department_info" name="type"  first="部门筛选" style="form-control" />
        </div>
        <div class="row" >
            <input name="sex" type="radio" value="" checked/>全部
            <input name="sex" type="radio" value="1" />独立站
            <input name="sex" type="radio" value="10"/>商城站
        </div>
		<div class="col-lg-3">
    		<tagLib name="html" />
    		<html:select options="options_site_name" multiple="true" name="site_id[]" selected="site_id_select" first="--网站--" />
		</div>
		<label class="col-lg-1">注册日期</label>
        <div class="col-lg-1"><input class="form-control" type="text" name="register_time_start" value="<?php echo I('register_time_start', '') ?>" placeholder="起始日期" style="font-size: 0.8em;" /></div>
        <div class="col-lg-1"><input class="form-control" type="text" name="register_time_end" value="<?php echo I('register_time_end', '') ?>" placeholder="结束日期" style="font-size: 0.8em;" /></div>
        <div class="col-lg-2">
            <tagLib name="html" />
            <html:select options="order_status_record" name="order_status" selected="order_status_record_selected" first="--订单记录--" style="form-control" />
        </div>
		    <button class="btn btn-primary" type="submit">查询</button>
		    <button class="btn btn-default" type="button" id="btn-export">导出</button>
            <button type="button" data-toggle="modal" data-backdrop="static" data-target="#dialog-customer-upload" class="btn btn-default" id="btn-customer-upload2">按查询条件上传到网站</button>    
	</div>
</form>
<button type="button" data-toggle="modal" data-backdrop="static" data-target="#dialog-customer-upload" class="btn btn-default" id="btn-customer-upload">批量上传到网站</button>
<div class="modal fade" id="dialog-customer-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">客户信息上传</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                <label>1、选择要上传到网站</label>
                <select class="form-control" name="site_customer_upload">
                    <?php
                    $xuhao=0;
                    foreach($sites as $entry){
                    	$xuhao=$xuhao+1;
                        $web_site='xxx';
                    switch ($entry['is_sale']) {
                        case '-1':
                            $web_site = '不清缓存';
                        case '0':
                            $web_site = '零售';
                            break;
                        case '1':
                            $web_site = '批发';
                            break;
                        case '2':
                            $web_site = 'B站';
                            break;
                        case '3':
                            $web_site = '帽子';
                            break;
                        case '4':
                            $web_site = '面罩';
                            break;
                        case '5':
                            $web_site = '袜子';
                            break;
                        case '6':
                            $web_site = 'anti-virus';
                            break;
                        case '7':
                            $web_site = '游戏手柄';
                            break;
                        case '8':
                            $web_site = '测试';
                            break;
                        case '9':
                            $web_site = 'Other';
                            break;
                        default:
                            # code...
                            break;
                    }

                        switch ($entry['site_name']) {
                            case 'footballgreatjersey.com':
                                $beizhu = ' <span style="color:#ff0000">jersey+10</span>~';
                                break;
                            case 'footballonlinejerseys.com':
                                $beizhu = ' <span style="color:#ff0000">jersey+20</span>~';
                                break;
                            case 'onlinejerseysell.com':
                                $beizhu = ' <span style="color:#ff0000">jersey+2</span>~';
                                break;
                            case 'topstitchedgears.com':
                                $beizhu = ' <span style="color:#ff0000">jersey+10</span>~';
                                break;
                            default:
                                $beizhu = '';
                                break;
                        }

                     if($entry['system_proupdate']=='True'){
                        echo '<option value="'.$entry['site_id'].'">'.'(序号:'.$xuhao.'==ID:'.$entry['site_id'].'#'.') '.$entry['site_name'].'('.$web_site.','.$beizhu.$zwb.$rxh.$entry['system_cms'].')'.'</option>';
                     }

                        
                    }

                    ?>
                </select>
                </div>
                <div class="result"></div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="type" value="1">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btn-start-customer-upload">开始上传</button>
            </div>
        </div>
    </div>
</div>



<div class="page-nav">
	<div class="row">
		<div class="col-lg-6">
			<div class="page-nav-info">当前页客户总数:<?php echo $count?></div>
		</div>
		<div class="col-lg-6 right">
		<?php 
		W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$count, 'name'=>'Customers/Customers/list', $page_data));
		?>
		</div>
	</div>
</div>
<table class="customers-list">
	<thead>
		<tr>
		    <th>序号<input type="checkbox" id="check-all"></th>
		    <th>网站ID</th>
			<th>网站</th>	
			<th>客户邮箱</th>
			<th>创建时间</th>
			<th>最后登录时间</th>
			<th>登录次数</th>
			<th>订单情况</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$k=($page-1)*$num;
	foreach ($list as $customer){
	    $k=$k+1;
	?>
	<tr class="sep-row"><td colspan="6"></td></tr>
	<tr class="customers-hd<?php if($customer['customers_basket']!=null) echo ' has-order'?>" id="tr<?php echo $customer['site_id'].'-'.$customer['customers_id'] ?>">
	    <td><input type="checkbox" name="customers_id[]" value="<?php echo $customer['site_id'].'-'.$customer['customers_id'] ?>"><?php echo $k ?></td>
	    <td><?php echo '#'.$customer['site_id'] ?></td>
		<td><?php echo $customer['site_name']?></td>
		<td><a href="<?php echo U('Order/Order/list',array('customer_email'=>$customer['customers_email_address']))?>" target="_blank"><?php echo $customer['customers_email_address']?></a></td>
		<td><?php echo $customer['customers_info_date_account_created']?></td>
		<td><?php echo $customer['customers_info_date_of_last_logon']?></td>
		<td><?php echo $customer['customers_info_number_of_logons']?></td>
		<td>
		<?php 
		if(empty($customer['orders'])==false){
		  foreach ($customer['orders'] as $row_order_status){
		    echo '<a href="'.U('Order/Order/list',array('customer_email'=>$customer['customers_email_address'],'order_status_remark'=>$row_order_status['order_status'])).'" target="_blank">'.$row_order_status['order_status'].' <span class="badge">'.$row_order_status['num'].'</span></a>';
		  }  
		}
		?>
		</td>
	</tr>
	<?php 
	if (false == empty($customer['customers_basket'])){
	?>
	<tr class="customers-basket">
		<th>购物车</th>
		<td colspan="8">
			<table>
	<?php    
	    foreach ($customer['customers_basket'] as $i=>$basket_entry){
    ?>
    		<tr <?php if ($basket_entry['status']==0) echo 'class="removed" data-toggle="tooltip" data-placement="bottom" title="该宝贝已不在客户购物车中"'?>>
    			<td>
    				<a href="<?php echo getProductLink($customer['site_index'], $basket_entry['products_id'])?>" target="_blank"><?php echo $basket_entry['products_name']?>(<?php echo $basket_entry['manufacturers_name']?>)</a><br>
    				<?php 
    				if (!empty($basket_entry['customers_basket_attributes'])){
        				$attributes = json_decode($basket_entry['customers_basket_attributes'], true);
        				if (is_array($attributes)){
        				    echo '<ul>';
                            foreach ($attributes as $option_name=>$option_value){
                                echo '<li>'.$option_name.'-'.$option_value.'</li>';
                            }
                            echo '</ul>';
        				}
    				}
    				?>
    			</td>
    			<td>x<?php echo $basket_entry['customers_basket_quantity']?></td>
    			<td class="last"><span data-toggle="tooltip" data-placement="bottom" title="添加购物车时间"><?php echo $basket_entry['customers_basket_date_added']?></span></td>
    		</tr>
    <?php	        
	    }
	?>
			</table>
		</td>
	</tr>	
	<?php    
	}
	?>
	<?php    
	}
	?>
	</tbody>
</table>	
<div class="page-nav">
	<div class="row">
		<div class="col-lg-6">
			<div class="page-nav-info">当前页客户总数:<?php echo $count?></div>
		</div>
		<div class="col-lg-6 right">
		<?php 
		W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$count, 'name'=>'Customers/Customers/list', $page_data));
		?>
		</div>
	</div>
</div>
<div class="modal fade" id="down-links-dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">客户数据下载</h4>
      </div>        
      <div class="modal-body"></div>
      <div class="modal-footer"><button type="button" id="btn-restart">重新下载失败任务</button></button></div>
    </div>
  </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.css">
<script src="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.js"></script>
<script>
$("input[name^='register_time_']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $('#btn-customer-upload2').click(function(){
        $('input[name="type"]').val(2);
    });
    $('#btn-customer-upload').click(function(){
        $('input[name="type"]').val(1);
    });
    
    $('#check-all').click(function () {
        var checked = $(this).is(':checked');
        $('input[name="customers_id[]"]').prop("checked", checked);
    });
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
                type            : 'post',
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
        add : function(url, data, fun_beforeSend, fun_success, fun_error){
     
            var task = new _task(url, data, fun_beforeSend, fun_success, fun_error);
            _queue_waiting.push(task);
        },
        stop : function(){
            if(_queue_runing.length>0){
                for(var i in _queue_runing){
                    _queue_runing.splice(0,_queue_runing.length);
                }
            }      
            if(_queue_waiting.length>0){
                _queue_waiting.splice(0,_queue_waiting.length);
            }            
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

$('select[name="site_id[]"]').multipleSelect();
$('[data-toggle="tooltip"]').tooltip();
$('#btn-restart').click(function(){
    $('#btn-export').trigger('click');
});
$('#btn-export').click(function(){
    if($('option:selected', 'select[name="site_id[]"]').size()==0){
        alert('请选择要导出的网站!');
        return false;
    }
    $('#down-links-dialog').modal({backdrop:'static', show:true});    
    $('#down-links-dialog .modal-body').html("");
    var date_start = $('input[name="register_time_start"]').val();
    var date_end   = $('input[name="register_time_end"]').val();
    var order_status   = $('select[name="order_status"]').val();
    $('option:selected', 'select[name="site_id[]"]').each(function(){
        var id = $(this).attr('value');
        var type = $("select[name='type']").val();
        var site_name = $('option[value="'+id+'"]', 'select[name="site_id[]"]').text();
        var fun_success = function(data){
            var site_name = $('option[value="'+id+'"]', 'select[name="site_id[]"]').text();
            $('#down-link'+id).removeClass('bg-info');
            if(data.success){
                $('#down-link'+id).addClass('bg-success');
                if(data.url=='')
                    $('#down-link'+id).html(site_name+'无客户数据下载');
                else
                    $('#down-link'+id).html('<a href="'+data.url+'" target="_blank">点击下载'+site_name+'客户数据</a>');
            }else{
                $('#down-link'+id).addClass('bg-danger');
                $('#down-link'+id).html(site_name+'客户数据下载失败');
            }
            $('option[value="'+id+'"]', 'select[name="site_id[]"]').attr('selected', false);
        }
        var fun_error = function(){
            $('#down-link'+id).removeClass('bg-info');
            $('#down-link'+id).addClass('bg-danger');
            $('#down-link'+id).html(site_name+'客户数据下载失败');
        }
        var fun_beforeSend = function(){
            $('<p class="bg-info" id="down-link'+id+'">'+site_name+'</p>').appendTo('#down-links-dialog .modal-body');
        }
        
        ajax_task.add("<?php echo U('Customers/Customers/export')?>", {'site_id':id, 'type': type, 'register_time_start':date_start, 'register_time_end':date_end, 'order_status':order_status},fun_beforeSend, fun_success, fun_error);   
    });
    ajax_task.run();    
});

    $('#btn-start-customer-upload').click(function(){
        if($('input[name="type"]').val()=='1'){
            var site_id = $('select[name="site_customer_upload"]').val();
            if($('input[name="customers_id[]"]:checked').size()==0){
                alert('请勾选要上传的客户!');
                return;
            }

            var i = 0;
            var n = 100;
            var customers = new Array();
            $('input[name="customers_id[]"]:checked').each(function(){
                i++;
                customers.push($(this).val());
                if(i%n==0){
                    var _customers = [].concat(customers);
                    var fun_success = function(data){
                        if(data.status==1){
                            for(var i in data.result){
                                if(data.result[i].error){
                                    $('#tr'+data.result[i].customer_id+' td').css('background-color','#f2dede');
                                    $('#tr'+data.result[i].customer_id).tooltip({title:data.result[i].error});
                                }else{
                                    $('#tr'+data.result[i].customer_id+' td').css('background-color','#dff0d8');
                                    $('#tr'+data.result[i].customer_id).tooltip('destroy');
                                }
                            }
                        }else{
                            for(var i in _customers){
                                $('#tr'+_customers[i]+' td').css('background-color','#f2dede');
                            }
                        }
                    }
                    var fun_error = function(){
                        for(var i in _customers){
                            $('#tr'+_customers[i]+' td').css('background-color','#f2dede');
                        }
                    }
                    var fun_beforeSend = function(){
                        for(var i in _customers){
                            $('#tr'+_customers[i]+' td').css('background-color','#d9edf7');
                        }
                    }
                    ajax_task.add("<?php echo U('Customers/Data/upload')?>", {'site_id':site_id, 'customers[]':customers}, fun_beforeSend, fun_success, fun_error);
                    customers = new Array();
                }
            });
            if(i%n!=0){
                var _customers = [].concat(customers);
                
                var fun_success = function(data){
                    if(data.status==1){
                        for(var i in data.result){
                            if(data.result[i].error){
                                $('#tr'+data.result[i].customer_id+' td').css('background-color','#f2dede');
                                $('#tr'+data.result[i].customer_id).tooltip({title:data.result[i].error});
                            }else{
                                $('#tr'+data.result[i].customer_id+' td').css('background-color','#dff0d8');
                                $('#tr'+data.result[i].customer_id).tooltip('destroy');
                            }
                        }
                    }else{
                        for(var i in _customers){
                            $('#tr'+_customers[i]+' td').css('background-color','#f2dede');
                        }
                    }
                }
                    
                var fun_error = function(){
                    for(var i in _customers){
                        $('#tr'+_customers[i]+' td').css('background-color','#f2dede');
                    }
                }
                
                var fun_beforeSend = function(){
                    for(var i in _customers){
                        $('#tr'+_customers[i]+' td').css('background-color','#d9edf7');
                    }
                }         
                ajax_task.add("<?php echo U('Customers/Data/upload')?>", {'site_id':site_id, 'customers[]':customers}, fun_beforeSend, fun_success, fun_error);
            }
            ajax_task.run();
        }else{
            var upload_site_id = $('select[name="site_customer_upload"]').val();
            
            var customer_email                = $('input[name="customer_email"]').val();
            var type                = $('select[name="type"]').val();
            var site_id             = $('select[name="site_id[]"]').val();
            var register_time_start = $('input[name="register_time_start"]').val();
            var register_time_end   = $('input[name="register_time_end"]').val();
            var order_status        = $('select[name="order_status"]').val();
            
            $('.result', '#dialog-customer-upload').html('<p class="bg-success">计算需要上传的客户记录数</p>');
            
            $.ajax({
                url         : "<?php echo U('Customers/Data/upload2')?>",
                data        : {'site_id':site_id, 'customer_email':customer_email, 'type':type, 'register_time_start':register_time_start,'register_time_end':register_time_end, 'order_status':order_status, 'action': 'count'},
                type         : 'post',
                dataType     : 'json',
                success      : function(data){
                    $('.result', '#dialog-customer-upload').append('<p class="bg-success">'+data.tip+'</p>');
                    if(data.num_page){
                        for(var i = 1;i<=data.num_page;i++){
                            $('.result', '#dialog-customer-upload').append('<p class="bg-info" id="page'+i+'">第'+i+'批准备上传.</p>');
                            var fun_success = function(data){
                                if(data.success)
                                    $('#page'+data.cur_page, '#dialog-customer-upload').attr('class', 'bg-success');
                                else
                                    $('#page'+data.cur_page, '#dialog-customer-upload').attr('class', 'bg-danger');
                                    
                                $('#page'+data.cur_page, '#dialog-customer-upload').text(data.tip);
                            }    
                            var fun_beforeSend = function(){}
                            var fun_error = function(){}                            
                            ajax_task.add("<?php echo U('Customers/Data/upload2')?>", {'site_id':site_id,  'customer_email':customer_email,'type':type, 'register_time_start':register_time_start,'register_time_end':register_time_end, 'order_status':order_status, 'page': i, 'upload_site_id':upload_site_id}, fun_beforeSend, fun_success, fun_error);
                        }
                        ajax_task.run();
                    }
                }
            });
        }
    });
</script>