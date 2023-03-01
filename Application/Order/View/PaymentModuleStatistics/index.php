<div class="nav-search">
	<ul class="list-inline">
		<li><a href="<?php echo U('Statistics/Statistics/index'); ?>">销量统计</a></li>
		<li><a href="<?php echo U('Statistics/Statistics/searchKeyword'); ?>">搜索统计</a></li>
		<li><a href="<?php echo U('Statistics/Statistics/ipAccessLog'); ?>">IP统计</a></li>
		<li><a class="seleted" href="<?php echo U('Order/PaymentModuleStatistics/index'); ?>">支付接口统计</a></li>
	</ul>
</div>
<h1>支付接口统计</h1>

<table class="table table-bordered">

	<tbody>

		<tr><th>接口</th><th>独立站(zen-cart)</th><th>商城站(SAAS)</th></tr>

		<tr><td>TW</td><td>moneytransfers</td><td>custom</td></tr>

		<tr><td>乒乓</td><td>security_pingpong</td><td>pingpong/pingpong2f</td></tr>

		<tr><td>佐道2f3d</td><td>zdcheckout2f3d</td><td>zdcheckout2f3d</td></tr>

		<tr><td>佐道3f</td><td>zdcheckout3f</td><td>zdcheckout3f</td></tr>

		<tr><td>中外宝</td><td>tpo</td><td>mycheckout2f3d</td></tr>

		<tr><td>融信汇</td><td>rxhpay_inline</td><td>rxhpay</td></tr>

	</tbody>

</table>

<hr>

   <p>

   	<label>温馨提示,统计数据已去重:</label><br/>

    前提：同一天，同一接口中。<br/>

	如果一个客户有多个订单，其中只有一个成功，那直接保留那一单成功的。<br/>

	如果一个客户有多个订单，没有成功，但只有一个失败，那直接保留那一单失败的。<br/>

	如果一个客户有多个订单，没有成功和失败，都是pending，那直接保留那一单pending。<br/>

	如果一个客户有多个订单，其中只有2个以上成功，那直接保留2单成功的。

	</p>

<hr>

<form action="<?php echo U('Order/PaymentModuleStatistics/index') ?>" method="POST">

    <div  class="form-inline">

  <div class="form-group">

    <label>统计开始日期</label>

    <input type="text" class="form-control" name="date_start" placeholder="统计开始日期" value="<?php echo $date_start ?>">

  </div>

  <div class="form-group">

    <label>统计结束日</label>

    <input type="text" class="form-control" name="date_end" placeholder="统计结束日期" value="<?php echo $date_end ?>">

  </div>

  <div class="form-group">

    <label>网站类型</label>

    <tagLib name="html" />

    <html:select options="option_site_type" name="site_type" selected="site_type_selected" style="form-control" first="--不限--" />

  </div> 

  <div class="form-group mt5">

    <label>网站</label>

    <style>button.ms-choice{border:none;}</style>

    <select multiple="multiple" class="form-control" name="site_id[]">

        <?php

        foreach($option_site_name as $type=>$site){

        ?>

        <optgroup label="<?php if($type==1) echo '独立站'; else echo '平台站';?>">

            <?php

            foreach($site as $site_id=>$site_name){

            ?>

            <option value="<?php echo $site_id?>"<?php if(in_array($site_id, $site_id_selected)) echo ' selected'; ?>><?php echo $site_name ?></option>

            <?php

            }

            ?>

        </optgroup>

        <?php

        }

        ?>

    </select>

  </div> 

  

  <div class="form-group">

    <label>接口</label>

    <html:select options="option_payment_module" multiple="true" name="payment_module[]" selected="payment_module_selected" style="form-control"/>

  </div>   

  </div>

  <div  class="form-inline">

  <div class="form-group">

    <label>统计方式</label>

    <html:select options="option_statistic_type" name="statistic_type" selected="statistic_type_selected" style="form-control"/>

  </div>     

  <button type="submit" class="btn btn-default">查看</button>

  </div>

</form>

<br>

<table class="table table-bordered">

    <tr>

        <th><input type="checkbox" id="check-all" /></th>

        <th>统计日期</th>

        <th>商城类型</th>

        <th>网站名称</th>

        <th>支付模块</th>

        <th>成功订单数</th>

        <th>失败订单数</th>

        <th>待支付订单数</th>

        <th>通道成功率</th>

        <th>实际成功率</th>

        <th>待支付率</th>

        <th>更新日期<button class="btn btn-primary btn-xs" type="button" id="btn-update-statistics">更新</button></th>

    </tr>

<?php

foreach($list as $date=>$data){

    $n = sizeof($data);

?>

    <tr id="tr-<?php echo $date ?>">

        <td<?php if($n) echo ' rowspan="'.$n.'"' ?>><input type="checkbox" name="date[]" value="<?php echo $date ?>" /></td>

        <?php 

        if($n){

            if($n==1 && is_null($data[0]['payment_module'])){

                echo '<td colspan="10">此日期没有订单</td><td>'.$data[0]['update_time'].'</td></tr>';                

            }else{

                $i = 0;

                foreach($data as $entry){

        ?>

        <?php

        if($n>1&&$i>0) echo '<tr>';

        ?>

            <td><?php echo $date ?></td>        

            <td><?php echo ($entry['site_type']==1?'独立站':'平台站') ?></td>

            <td><?php echo (isset($entry['site_id'])?'#'.$entry['site_id'].'-'.$entry['site_name']:'——') ?></td>

            <?php 

            	switch ($entry['payment_module']) {

            		case 'moneytransfers':

            			$payment_module_name ='TW(独立站)';

            			break;

            		case 'custom':

            			$payment_module_name ='TW(商城站)';

            			break;

            		case 'security_pingpong':

            			$payment_module_name ='乒乓(独立站)';

            			break;

            		case 'pingpong':
                    case 'pingpong2f':

            			$payment_module_name ='乒乓(商城站)';

            			break;

            		case 'zdcheckout2f3d':

            			$payment_module_name ='佐道2f3d';

            			break;

            		case 'zdcheckout3f':

            			$payment_module_name ='佐道3f';

            			break;

            		case 'tpo':

            			$payment_module_name ='中外宝(独立站)';

            			break;

            		case 'mycheckout2f3d':

            			$payment_module_name ='中外宝(商城站)';

            			break;

            		case 'rxhpay_inline':

            			$payment_module_name ='融信汇(独立站)';

            			break;

            		case 'rxhpay':

            			$payment_module_name ='融信汇(商城站)';

            			break;

            		

            		default:

            			$payment_module_name=$entry['payment_module'];

            			break;

            	}

            ?>

            <td><?php echo $payment_module_name ?></td>

            <td><?php echo $entry['num_success'] ?></td>

            <td><?php echo $entry['num_failure'] ?></td>

            <td><?php echo $entry['num_pending'] ?></td>

            <td><?php $total = $entry['num_success']+$entry['num_failure'];if($total) echo (round($entry['num_success']/$total, 4)*100).'%'; ?></td>

            <td><?php $total = $entry['num_success']+$entry['num_failure']+$entry['num_pending'];if($total) echo (round($entry['num_success']/$total, 4)*100).'%'; ?></td>

            <td><?php $total = $entry['num_success']+$entry['num_failure']+$entry['num_pending'];if($total) echo (round($entry['num_pending']/$total, 4)*100).'%'; ?></td>

            <td><?php echo $entry['update_time'] ?></td>

        <?php

        if($n>1) echo '</tr>';

        ?>        

        <?php

                    $i++;

                }

            }

        }else{

        ?>

        <td><?php echo $date ?></td>

        <td colspan="10">无统计记录</td>

        <?php

        }

        ?>

    </tr>

<?php

}

?>

</table>

<div class="modal fade" id="dialog-statistics-updating">

    <div class="modal-dialog modal-sm">

        <div class="modal-content">

            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">统计更新</h4></div>

            <div class="modal-body">

            </div>        

        </div>

    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.css">

<script src="https://unpkg.com/multiple-select@1.4.0/dist/multiple-select.min.js"></script>

<script>$('select[name="payment_module[]"]').multipleSelect({placeholder:'接口选择'});

$('select[name="site_id[]"]').multipleSelect({placeholder:'--网站选择--'});

</script>

<script>

    $('#check-all').click(function () {

        var checked = $(this).is(':checked');

        $('input[name="date[]').prop("checked", checked);

    });

    $("input[name^='date_']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});

    var ajax_task = (function () {

        var _num_max_runing = 2;//最大同时ajax的数量

        var _queue_runing = new Array();//运行下载的队列

        var _queue_waiting = new Array();//等待下载的队列

        function _task(url, fun_beforeSend, fun_success, fun_error) {

            this.url = url;

            this.isfinish = false;

            this.run = function () {

                var _this = this;

                $.ajax({

                    url: this.url,

                    dataType: 'json',

                    async: true,

                    timeout: 60000, //请求时间

                    type: "post",

                    beforeSend: function () {

                        fun_beforeSend();

                    },

                    success: function (data) {

                        this.isfinish = true;

                        fun_success(data);

                    },

                    error: function (jqXHR, textStatus, errorThrown) {

                        _this.isfinish = true;

                        fun_error();

                    },

                    complete: function () {

                        _this.isfinish = true;

                    }

                });

            }

        }

        var _loading = new (function () {

            var _status = false;

            return {

                start: function () {

                    if (_status == false) {

                        _status = true;

                        layer.load(1);

                    }

                },

                end: function () {

                    if (_status == true) {

                        _status = false;

                        layer.closeAll('loading');

                    }

                },

                listen_end: function (condition) {

                    if (eval(condition)) {

                        _loading.end();

                    } else {

                        setTimeout("ajax_task._loading().listen_end(" + condition + ")", 1200);

                    }

                }

            }

        })();

        return {

            _loading: function () {

                return _loading;

            },

            idle: function () {

                return (_queue_waiting.length == 0 && _queue_runing.length == 0);

            },

            add: function (url, fun_beforeSend, fun_success, fun_error) {

                var task = new _task(url, fun_beforeSend, fun_success, fun_error);

                _queue_waiting.push(task);

            },

            run: function () {

                _loading.start();

                if (_queue_runing.length > 0) {

                    //将已完成的任务从队列移除

                    for (var i in _queue_runing) {

                        if (_queue_runing[i].isfinish) {

                            _queue_runing.splice(i, 1);

                        }

                    }

                }

                if (_queue_waiting.length > 0) {

                    if (_queue_runing.length < _num_max_runing) {

                        do {

                            var task = _queue_waiting.shift();

                            task.run();

                            _queue_runing.push(task);

                        } while (_queue_runing.length < _num_max_runing && _queue_waiting.length > 0);

                    }

                }

                if (_queue_runing.length > 0 || _queue_waiting.length > 0) {

                    setTimeout("ajax_task.run()", 1000);

                }

                _loading.listen_end('ajax_task.idle()');

            }

        };

    })();

$(document).ready(function(){

   $('#btn-update-statistics').click(function(){

        if ($('input[name="date[]"]:checked').size() == 0) {

            alert('请选择要更新的日期!');

            return false;

        }

        $('#dialog-statistics-updating').modal('show');

        $('#dialog-statistics-updating .modal-body').html("");

        $('input[name="date[]"]:checked').each(function () {

            var date = $(this).val();

            var url = '/index.php/Order/PaymentModuleStatistics/update/date/' + date;

            var fun_success = function (data) {

                $('#alert-'+date).text(date+'统计更新成功。').attr('class','alert alert-success');

            }

            var fun_error = function () {

                $('#alert-'+date).text(date+'统计更新失败。').attr('class','alert alert-warning');

            }

            var fun_beforeSend = function () {

                $('<div class="alert alert-info" id="alert-'+date+'"></div>').text(date+'统计更新中...').appendTo('#dialog-statistics-updating .modal-body');

            }

            ajax_task.add(url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

   });

});  

</script>

<style type="text/css">

	

</style>