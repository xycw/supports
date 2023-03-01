<h1>订货（财务版）</h1>
<p>订单状态是待订货，已订货，部分发货，已发货的订单且已确定了产品供应商项目才会出现在此页面列表中</p>
<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Order/Finance/index') ?>" method="GET">
    <div class="row">
        <label for="order_number" class="col-lg-1">订单号<br><span style="color:red;font-size:0.8em;">(请不要搜索超过100个订单号)</span></label>
        <div class="col-lg-5"><textarea class="form-control" name="order_number" id="order_number"><?php echo I('order_number', '')?></textarea></div>        
        <label for="purchase-date" class="col-lg-1">订单日期</label>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="purchase_date_start" value="<?php echo I('purchase_date_start', '') ?>" placeholder="起始日期" style="padding: 6px 1px;font-size:8px;text-align:center;" ></div>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="purchase_date_end" value="<?php echo I('purchase_date_end', '') ?>" placeholder="结束日期" style="padding: 6px 1px;font-size:8px;text-align:center;"></div>
        <label for="purchase-date" class="col-lg-1">订货日期</label>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="date_process_start" value="<?php echo I('date_process_start', '') ?>" placeholder="起始日期" style="padding: 6px 1px;font-size:8px;text-align:center;" ></div>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="date_process_end" value="<?php echo I('date_process_end', '') ?>" placeholder="结束日期" style="padding: 6px 1px;font-size:8px;text-align:center;"></div>                    
    </div>
    <div class="row">
        <div class="col-lg-1">
            <input type="hidden" name="supplier_id" value="<?php echo I('supplier_id', '')?>"/>
            <button data-toggle="modal" data-target="#supplier_dialog" class="btn btn-default dropdown-toggle" type="button">供应商</button>
        </div>         
        <label for="system_area" class="col-lg-1">订单归属</label>
        <div class="col-lg-1">
            <tagLib name="html" />
            <html:select options="option_system_area" name="system_area" selected="option_system_area_selected" first="--不限--" style="form-control" />            
        </div>
        <label for="system_depart" class="col-lg-1">部门归属</label>
        <div class="col-lg-1">
            <html:select options="option_system_depart" name="system_depart" selected="option_system_depart_selected" first="--不限--" style="form-control" />            
        </div>        
        <label for="system_depart" class="col-lg-1">是否完单</label>
        <div class="col-lg-1">
            <html:select options="option_logistics_status" name="logistics_status" selected="option_logistics_status_selected" first="--不限--" style="form-control" />            
        </div>        
        <label for="receiving_status" class="col-lg-1">收货状态</label>
        <div class="col-lg-1">
            <html:select options="option_receiving_status" name="receiving_status" selected="option_receiving_status_selected" first="--不限--" style="form-control" />            
        </div>        
        <label for="cost_counted" class="col-lg-1">已记成本</label>
        <div class="col-lg-1">
            <html:select options="option_cost_counted" name="cost_counted" selected="option_cost_counted_selected" first="--不限--" style="form-control" />            
        </div>
    </div>
    <div class="row">
        <label for="purchase-date" class="col-lg-1">收货日期</label>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="date_received_start" value="<?php echo I('date_received_start', '') ?>" placeholder="起始日期" style="padding: 6px 1px;font-size:8px;text-align:center;" ></div>
        <div class="col-lg-1"><input class="form-control date-input" type="text" name="date_received_end" value="<?php echo I('date_received_end', '') ?>" placeholder="结束日期" style="padding: 6px 1px;font-size:8px;text-align:center;"></div>
        <button class="btn btn-primary" type="submit">筛选</button>
    </div>
    <div class="row">
        <label class="col-lg-1">整单标记</label>
        <div class="col-lg-3">
            <div class="btn-group btn-group-justified">
                <div class="btn-group" role="group"><button class="btn btn-success btn-flag" type="button" id="btn-flag-yifahuo" rel="logistics_status:1">完单标记</button></div>
                <div class="btn-group" role="group"><button class="btn btn-default btn-flag" type="button" id="btn-flag-weiwandan"  rel="logistics_status:0">未完单标记</button></div>            
            </div>
        </div>
        <label class="col-lg-1">产品标记</label>
        <div class="col-lg-4">
            <div class="btn-group btn-group-justified">
                <div class="btn-group" role="group"><button class="btn btn-danger btn-flag" type="button" id="btn-flag-wishouhuo"  rel="receiving_status:未收货">未收货</button></div>
                <div class="btn-group" role="group"><button class="btn btn-success btn-flag" type="button" id="btn-flag-wanquanshouhuo" rel="receiving_status:完全收货">完全收货</button></div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="btn-group btn-group-justified">
                <div class="btn-group" role="group"><button class="btn btn-danger btn-flag" type="button" id="btn-flag-cost-counted0" rel="cost_counted:0">未记录成本</button></div>
                <div class="btn-group" role="group"><button class="btn btn-success btn-flag" type="button" id="btn-flag-cost-counted1" rel="cost_counted:1">已记录成本</button></div>
            </div>            
        </div>    
    </div>
</form>   

<!-- Modal -->
<div class="modal fade" id="importModal" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">订货成本表导入</h4>
      </div>
      <div class="modal-body">
        <form action="<?php echo U('Order/Finance/import') ?>" enctype="multipart/form-data" method="post">
            <div class="form-group">
                <label>文件导入</label><input type="file" name="file">
            </div>
            <button type="submit" class="btn btn-default">导入</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="supplier_dialog" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">供应商</h4>
      </div>
      <div class="modal-body">
          <div class="row">
<?php
$option_supplier_id_selected = I('supplier_id', '');
$supplier_selected = explode('_', $option_supplier_id_selected);
foreach($option_supplier as $sid=>$sname){
?>
<label class="col-lg-3"><input type="checkbox" name="supplier[]" value="<?php echo $sid ?>" <?php if(in_array($sid, $supplier_selected)) echo 'checked'?>/><?php echo $sname ?></label>
<?php
}
?>
          </div>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
    $('input[name="supplier[]"]').click(function(){
        var value = '';
        $('input[name="supplier[]"]:checked').each(function(){
            if(value=='')
                value += $(this).val();
            else
                value += "_"+$(this).val();
        });
        $('input[name="supplier_id"]').val(value);
    });
});
</script>


<p class="bg-info">当前筛选条件共<?php echo $orders_num ?>份订单</p>

<button class="btn btn-default" type="button" id="btn-export-excel"><i class="glyphicon glyphicon-export"></i>EXCEL订货成本表</button>
<button class="btn btn-default" type="button" id="btn-export-delivery"><i class="glyphicon glyphicon-export"></i>EXCEL订单物流表</button>
<button class="btn btn-default pull-right" type="button" data-toggle="modal" data-target="#importModal" style="margin-left:5px;"><i class="glyphicon glyphicon-import"></i>订货成本表导入</button>

<form id="form-orders-products" action="<?php echo U('Order/Finance/archiving') ?>" method="POST" style="margin-top: 10px;">
    <input type="hidden" name="archiving_type">
    <input type="hidden" name="archiving_value">
<table class="customers-list">
    <colgroup>
        <col width="50">
        <col width="100px">
        <col width="100px">
        <col width="auto">
        <col width="120px">
        <col width="100px">
        <col width="100px">
        <col width="80px">
        <col width="100px">
    </colgroup>
    <thead>
		<tr>
		    <th>序号<input type="checkbox" id="check-all"></th>
		    <th>跟货号</th>
			<th>产品图片</th>	
			<th>产品信息<br>黄底项目是通过系统添加</th>
			<th>供应商</th>
			<th>订货日期</th>
			<th>订货单价</th>
			<th>订货<br />数量</th>
			<th>收货情况</th>
		</tr>
	</thead>
	<tbody>
<?php
$i = $count-($page-1)*$num;
$pre_order_number = '';
foreach($products as $product){
    $order_number = empty($product['order_no']) ? $product['order_no_prefix'] . $product['orders_id'] : $product['order_no'];
?>
<?php
if($pre_order_number!=$order_number){
?>
    <tr class="sep-row"><td colspan="8"></td></tr>
<?php
}
?>
<?php
if($pre_order_number!=$order_number){
?>
    <tr class="customers-hd">
        <td colspan="9" style="background:<?php  if($product['logistics_status']==1) echo '#b2e2b2;'; else echo '#fcf8e3;'; ?><?php if($product['remove']==1) echo 'text-decoration: line-through;'; ?>">
            <input type="checkbox" name="orders[]" value="<?php echo $product['site_id'].'-'.$product['orders_id']?>"><lable>订单号:</lable><?php echo $order_number?>,<lable>订单时间:</lable><?php echo date('Y-m-d H:i', strtotime($product['date_purchased'])) ?>
            <?php if(!empty($product['order_remark'])) echo '<lable>业务备注:</lable><span style="color:red;">'.$product['order_remark'].'</span>' ?>
            <span style="float:right;margin-left:10px;">订单归属：<?php echo $product['system_area'] ?></span><span style="float:right;margin-left:10px;">部门归属：<?php echo $product['department_name'] ?></span>
            <?php
            if(!empty($product['finance_remark'])){
                echo '<div>财物备注：'.$product['finance_remark'].'</div>';
            }
            ?>
        </td>
    </tr>    
<?php
}
?>

    <tr class="customers-hd" <?php if($product['remove']==1) echo 'style="text-decoration: line-through;"'; ?>>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><input type="checkbox" name="order_products[]" value="<?php echo $product['site_id'].'-'.$product['orders_products_id']?>"><?php echo $i-- ?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo $product['site_id'].'-'.$product['orders_products_id'] ?>
        <a class="btn btn-default btn-xs" href="<?php echo U('Order/Finance/itemEdit', array('site_id'=>$product['site_id'], 'orders_products_id'=>$product['orders_products_id'])); ?>" target="_blank">编辑</a>
        </td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><img src="<?php echo $product['products_image'] ?>" width="100px" /></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; elseif($product['add_from_sys']) echo 'style="background-color:yellow;"' ?>>
            <ul>
                <li><label>产品名:</label><?php echo $product['products_name'] ?></li>
                <li><label>SKU:</label><?php echo $product['products_model'] ?></li>
                <?php 
                if($product['products_attributes']){
                    foreach($product['products_attributes'] as $attributes){
                ?>
                <li><label><?php echo $attributes['products_options'] ?>:</label><?php echo $attributes['products_options_values'] ?></li>
                <?php
                    }
                }
                ?></li>
                <li><label>Qty:</label><?php echo $product['products_quantity'] ?></li>
                <li style="color:red;">产品备注：<?php echo $product['products_remark'] ?></li>
                
            </ul>
        </td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo $product['supplier_name'] ?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo date('m-d', strtotime($product['date_process'])) ?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo number_format($product['purchase_price'], 2) ?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?><?php if($product['remove']==1) echo 'style="background:red;color:white;"'; ?>><?php echo $product['quantity_process'] ?></td>
        <td>
        <?php
        if($product['received_status']){
            foreach($product['received_status'] as $receive_item){
                echo '<label class="label '.($receive_item['cost_counted']?'label-success':'label-warning').'" style="display:block;margin:2px 0;" '.($receive_item['cost_counted']?'data-toggle="tooltip" data-placement="top" title="已记录成本"':'').'><input type="checkbox" name="received[]" value="'.$product['orders_products_remark_id'].'|'.$receive_item['date_received'].'">'.$receive_item['date_received'].'收'.$receive_item['quantity_received'].'</label>';
            }
        }
        ?>
        </td>
    </tr>
<?php
    $pre_order_number = $order_number;
}
?>
    </tbody>
</table>
</form>
    <div class="page-nav">
        <div class="row">
            <div class="col-lg-6">
                <div class="page-nav-info">
                    <label>每页<?php echo $num ?>条记录，共<?php echo $count ?>条记录</label>
                </div>
            </div>
            <div class="col-lg-6 right">
                <?php
                W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/Finance/index', $page_data));
                ?>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
    $(".date-input").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $('.btn-flag').click(function(){
        var rel = $(this).attr('rel');
        var p   = rel.split(':');
        $('input[name="archiving_type"]').val(p[0]);
        $('input[name="archiving_value"]').val(p[1]);
        $('#form-orders-products').attr('action', "<?php echo U('Order/Finance/archiving') ?>");
        $('#form-orders-products').submit();
    });
    $('button[id^="btn-export"]').click(function(){
        var id = $(this).attr('id');
        var type = id.replace('btn-export-', '');
        var num = 1000;
        for (var page=1;page<=<?php echo ceil($count/1000);?>;page++){
            $.ajax({
                url: window.location.href.replace('index?','export/type/' + type + '/page/' + page + '/num/' + num + '?'),
                async: false,
                success: function (link) {
                    var $a = $("<a>");
                    $a.attr("href", link);
                    $("body").append($a);
                    $a[0].click();
                    $a.remove();
                }
            });
            if(type == 'delivery') return false;
        }
        alert('导出成功！')
    });
    $('#check-all').click(function(){
        var checked = $(this).is(':checked');
        $('input[name="orders[]"]').prop("checked", checked);
        $('input[name="order_products[]"]').prop("checked", checked);
    });
});    
</script>