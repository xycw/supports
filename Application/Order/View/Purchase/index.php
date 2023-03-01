<h1>产品订货<button class="btn btn-primary pull-right" type="button" id="autoArchiving">订单归类重置</button></h1>
<p>订单状态是待订货，已订货，部分发货的订单项目才会出现在此页面列表中</p>
<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Order/Purchase/index') ?>" method="GET">
    <div class="row">
        <label for="sku" class="col-lg-1">SKU</label>
        <div class="col-lg-11"><textarea class="form-control" name="sku" id="sku"><?php echo I('sku', '')?></textarea></div>        
    </div>
    <div class="row">
        <label for="order_number" class="col-lg-1">订单号<br><span style="color:red;font-size:0.7em;">(请不要搜索超过100个订单号)</span></label>
        <div class="col-lg-11"><textarea class="form-control" name="order_number" id="order_number"><?php echo I('order_number', '')?></textarea></div>        
    </div>    
    <div class="row">
        <label for="categories_id" class="col-lg-1">商品类别</label>
        <div class="col-lg-1">
            <tagLib name="html" />
            <html:select options="option_categories" name="categories_id" selected="option_categories_selected" style="form-control" first="--不限--" />
        </div>     
        <div class="col-lg-2">
            <input type="hidden" name="supplier_id" value="<?php echo I('supplier_id', '')?>"/>
            <button data-toggle="modal" data-target="#supplier_dialog" class="btn btn-default dropdown-toggle" type="button">供应商</button>
        </div>         
        <label for="order-status" class="col-lg-1">订单状态</label>            
        <div class="col-lg-1">
        <html:select options="option_order_status" name="order_status" selected="option_order_status_selected" first="--不限--" style="form-control" />
        </div>
        <label for="order-status" class="col-lg-1">产品状态</label>            
        <div class="col-lg-2">
        <html:select options="option_item_status" name="item_status" selected="option_item_status_selected" first="--不限--" style="form-control" />
        </div>        
        <label class="col-lg-1">是否打印</label>
        <div class="col-lg-1">
            <html:select options="option_is_print" name="is_print" selected="is_print_selected" style="form-control" first="--不限--" />
        </div>

    </div>
    <div class="row">
        <label class="col-lg-1">业务员</label>
        <div class="col-lg-1">
            <html:select options="users" name="user_id" selected="user_id_selected" style="form-control" first="--业务员--" />
        </div>
        
        <label for="last_modify-date" class="col-lg-1">业务操作日期</label>
        <div class="col-lg-2"><input class="form-control date-input" type="text" name="last_motify_date_start" value="<?php echo $last_motify_date_start ?>" placeholder="起始日期" ></div>
        <div class="col-lg-2"><input class="form-control date-input" type="text" name="last_motify_date_end" value="<?php echo $last_motify_date_end ?>" placeholder="结束日期"></div>    
        
        <label for="purchase-date" class="col-lg-1">订单日期</label>
        <div class="col-lg-2"><input class="form-control date-input" type="text" name="purchase_date_start" value="<?php echo I('purchase_date_start', '') ?>" placeholder="起始日期" ></div>
        <div class="col-lg-2"><input class="form-control date-input" type="text" name="purchase_date_end" value="<?php echo I('purchase_date_end', '') ?>" placeholder="结束日期"></div>    
        
        <button class="btn btn-primary" type="submit">筛选</button>
        
        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#flagModal">订单标记</button>
        <button class="btn btn-default hidden" type="button" data-toggle="modal" data-target="#categoriesModal">批量产品分类</button>
        <button class="btn btn-default hidden" type="button" data-toggle="modal" data-target="#printModal">打印标记</button>
        
    </div>
</form>    
<!-- Modal -->
<div class="modal fade" id="wordOrderModal" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">WORD订单导出</h4>
      </div>
      <div class="modal-body">

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
<!-- Modal -->
<div class="modal fade" id="itemRemarkLogisticsModal" >
  <div class="modal-dialog  modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">产品备注</h4>
      </div>
      <div class="modal-body">
        <textarea class="form-control" name="remark_logistics"></textarea>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="site_id" />
        <input type="hidden" name="orders_products_id" />
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary btn-save">保存</button>          
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="remarkLogisticsModal" >
  <div class="modal-dialog  modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">物流备注</h4>
      </div>
      <div class="modal-body">
        <textarea class="form-control" name="remark_logistics"></textarea>
      </div>
      <div class="modal-footer">
          <input type="hidden" name="site_id" />
          <input type="hidden" name="orders_id" />
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary btn-save">保存</button>          
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="importModal" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">订货表回单导入</h4>
      </div>
      <div class="modal-body">
        <form action="<?php echo U('Order/Purchase/import') ?>" enctype="multipart/form-data" method="post">
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
<div class="modal fade" id="importLogisticsRemarkModal" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">物流备注导入</h4>
      </div>
      <div class="modal-body">
        <form action="<?php echo U('Order/Purchase/importLogisticsRemark') ?>" enctype="multipart/form-data" method="post">
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
<div class="modal fade" id="flagModal" >
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">订单标记<label class="pull-right"><input type="checkbox" name="remark_type" value="1">按筛选条件标记</label></h4>
      </div>
      <div class="modal-body">
  <fieldset>
    <legend>产品分类整理</legend>
        <?php
        foreach($option_categories as $cat_id=>$cat_name){
        ?>
        <button type="button" class="btn btn-default btn-archiving-categories" rel="<?php echo $cat_id ?>"><?php echo $cat_name ?></button>
        <?php
        }
        ?>
  </fieldset>   
  
  <fieldset style="margin-top:20px;">
    <legend>产品状态标记</legend>
        <?php
        foreach($option_item_status as $status_name){
        ?>
        <button type="button" class="btn btn-default btn-item-status" rel="<?php echo $status_name ?>"><?php echo $status_name ?></button>
        <?php
        }
        ?>
  </fieldset>   
  
  <fieldset style="margin-top:20px;">
    <legend>打印标记</legend>
    <button type="button" class="btn btn-success btn-print" rel="1">已打印</button>
    <button type="button" class="btn btn-danger btn-print" rel="0">未打印</button>
  </fieldset>    
      </div>
    </div>
  </div>
</div>

<p class="bg-info">当前筛选条件共<?php echo $orders_num ?>份订单</p>

<button class="btn btn-default" type="button" id="btn-export-word"><i class="glyphicon glyphicon-export"></i>WORD订货表</button>
<button class="btn btn-default" type="button" id="btn-export-excel"><i class="glyphicon glyphicon-export"></i>EXCEL订货表</button>
        <button class="btn btn-default" type="button" id="btn-export-address"><i class="glyphicon glyphicon-export"></i>EXCEL地址表</button>
        <button class="btn btn-default" type="button" id="btn-ajax-export-order"><i class="glyphicon glyphicon-export"></i>WORD订单</button>
        
<a class="btn btn-default pull-right" href="<?php echo U('Order/Purchase/enterReceipt');?>" style="margin-left:5px;">录入回单</a>
<button class="btn btn-default pull-right" type="button" data-toggle="modal" data-target="#importModal" style="margin-left:5px;"><i class="glyphicon glyphicon-import"></i>订货表回单导入</button>
<button class="btn btn-default pull-right" type="button" data-toggle="modal" data-target="#importLogisticsRemarkModal"><i class="glyphicon glyphicon-import"></i>物流备注导入</button>

<form id="form-orders-products" action="<?php echo U('Order/Purchase/archiving') ?>" method="POST" style="margin-top: 10px;">
    <input type="hidden" name="archiving_type">
    <input type="hidden" name="archiving_value">
<table class="customers-list">
    <colgroup>
        <col width="50">
        <col width="100px">
        <col width="100px">
        <col width="300px">
        <col width="50px">
        <col width="180px">
        <col width="100px">
        <col width="auto">
    </colgroup>
    <thead>
		<tr>
		    <th>序号<input type="checkbox" id="check-all"></th>
		    <th>跟货号</th>
			<th>产品图片</th>	
			<th>产品信息<br>黄底项目是通过系统添加</th>
			<th>产品<br />数量</th>
			<th>订单号</th>
			<th>订单时间</th>
			<th>订货详情</th>
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
if($pre_order_number!=$order_number && (!empty($product['order_remark'])||!empty($product['logistics_remark']))){
?>
    <tr class="customers-hd">
        <td colspan="8" style="background:#fcf8e3;<?php if($product['remove']==1) echo 'text-decoration: line-through;'; ?>">
            <?php if(!empty($product['order_remark'])) echo '业务备注:'.$product['order_remark'].'<br />' ?>
            <?php if(!empty($product['logistics_remark'])) echo '物流备注:'.$product['logistics_remark'].'<br />' ?>
        </td>
    </tr>    
<?php
}
?>

    <tr class="customers-hd" <?php if($product['remove']==1) echo 'style="text-decoration: line-through;"'; ?>>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo $i-- ?><input type="checkbox" name="orders_products[]" value="<?php echo $product['site_id'].'-'.$product['orders_products_id']?>"></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo $product['site_id'].'-'.$product['orders_products_id'] ?></td>
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
                
                <li style="color:red;">产品备注：<?php echo $product['products_remark'] ?><button type="button" class="btn btn-default btn-xs btn-logistics-item-remark" rel="<?php echo $product['site_id'].'-'.$product['orders_products_id']?>">修改</button></li>

            </ul>
        </td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?><?php if($product['remove']==1) echo 'style="background:red;color:white;"'; ?>><?php echo $product['products_quantity'] ?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo $order_number?></td>
        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>><?php echo date('Ymd H:i', strtotime($product['date_purchased'])) ?></td>
        

        <td <?php if($product['remove']==1) echo 'style="background:#e1827f;color:white;"'; ?>>
            <ul>
                <li><label>分类:</label><?php echo empty($product['categories_name'])?'<span style="background:yellow;">待分类归档</span>':$product['categories_name'] ?></li>
                <li><label>供应商:</label><?php echo empty($product['supplier_name'])?'<span style="background:yellow;">待确定供应商</span>':$product['supplier_name'] ?></li>
                <li><?php echo ($product['item_status']=='待订货(待处理)'?'<span class="label label-warning">待处理</span>':'<span class="label label-success">'.$product['item_status'].'</span>') ?></li>
                <li><label>订货日期:</label><?php echo $product['date_process'] ?></li>
                <li><label>订货数量:</label><?php echo $product['quantity_process'] ?></li>
                <li><label>订货单价:</label><?php echo number_format($product['purchase_price'], 2) ?></li>
                <li><label>是否定制:</label><?php echo ($product['is_customized']?'是':'否') ?></li>
                <li><?php if(0==$product['is_print']){ ?><span class="label label-danger">未打印</span><?php }else{?><span class="label label-success">已打印</span><?php } ?></li>
                <li>
                <?php 
                if($product['supplier_name']=='没货'){
                ?>
                    <span class="label label-warning">没货</span>
                <?php    
                }elseif($product['quantity_process']>=$product['products_quantity']){
                ?>
                    <span class="label label-success">完全订货</span>
                <?php
                }elseif($product['quantity_process']>0){
                ?>
                    <span class="label label-warning">部分订货</span>
                <?php
                }else{
                ?>
                    <span class="label label-warning">未订货</span>
                <?php
                }
                ?></li>
            </ul>
            <?php
            if($pre_order_number!=$order_number){
            ?>
            <button type="button" class="btn <?php if(empty($product['logistics_remark'])) echo 'btn-default'; else echo 'btn-info'; ?> btn-xs btn-logistics-remark" rel="<?php echo $product['site_id'].'-'.$product['orders_id'] ?>">物流备注</button>
            <?php
            }
            ?>
            <?php
            if(!empty($product['detail_process'])){
                $detail_process = json_decode($product['detail_process'], true);
                $n = sizeof($detail_process);
                if($n>1){
                    for($j=$n-1;$j>0;$j--){
                        echo '<span class="label label-default">'.$detail_process[$j]['date_process'].'订'.$detail_process[$j]['quantity_process'].'</span>&nbsp;';
                    }
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
                W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/Purchase/index', $page_data));
                ?>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function(){
    $('#autoArchiving').click(function(){
        $('#form-search').attr('action', "<?php echo U('Order/Purchase/autoArchiving') ?>");
        $('#form-search').submit();
    });
    $(".date-input").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $('.btn-archiving-categories').click(function(){
        $('input[name="archiving_type"]').val('category');
        $('input[name="archiving_value"]').val($(this).attr('rel'));
        var archiving_value = $(this).attr('rel');
        var remark_type = $('input[name="remark_type"]').is(':checked');
        if(remark_type){
            $('#form-search').attr('action', "<?php echo U('Order/Purchase/index') ?>"+'/archiving_type/category/archiving_value/'+archiving_value);
            $('#form-search').submit();
        }else{        
            $('#form-orders-products').attr('action', "<?php echo U('Order/Purchase/archiving') ?>");
            $('#form-orders-products').submit();
        }
    });
    $('.btn-print').click(function(){
        $('input[name="archiving_type"]').val('print');
        $('input[name="archiving_value"]').val($(this).attr('rel'));
        var archiving_value = $(this).attr('rel');
        var remark_type = $('input[name="remark_type"]').is(':checked');
        if(remark_type){
            $('#form-search').attr('action', "<?php echo U('Order/Purchase/index') ?>"+'/archiving_type/print/archiving_value/'+archiving_value);
            $('#form-search').submit();
        }else{
            $('#form-orders-products').attr('action', "<?php echo U('Order/Purchase/archiving') ?>");
            $('#form-orders-products').submit();
        }
    });    
    $('.btn-item-status').click(function(){
        $('input[name="archiving_type"]').val('item_status');
        $('input[name="archiving_value"]').val($(this).attr('rel'));
        var archiving_value = $(this).attr('rel');
        var remark_type = $('input[name="remark_type"]').is(':checked');
        if(remark_type){
            $('#form-search').attr('action', "<?php echo U('Order/Purchase/index') ?>"+'/archiving_type/item_status/archiving_value/'+archiving_value);
            $('#form-search').submit();
        }else{
            $('#form-orders-products').attr('action', "<?php echo U('Order/Purchase/archiving') ?>");
            $('#form-orders-products').submit();
        }
    }); 
    $('button[id^="btn-export"]').click(function(){
        var a = $('#form-search').attr('action');
        $('#form-search').attr('target', '_blank');
        var id = $(this).attr('id');
        var type = id.replace('btn-export-', '');
        $('#form-search').attr('action', "<?php echo U('Order/Purchase/export') ?>/type/"+type);
        $('#form-search').submit();
        $('#form-search').attr('action', a);
        $('#form-search').attr('target', '');
    });
    $('#check-all').click(function(){
        var checked = $(this).is(':checked');
        $('input[name="orders_products[]"]').prop("checked", checked);
    });
    $('.btn-logistics-remark').click(function(){
       var rel = $(this).attr('rel');
       var p = rel.split("-");
       $('#remarkLogisticsModal input[name="site_id"]').val(p[0]);
       $('#remarkLogisticsModal input[name="orders_id"]').val(p[1]);
       $.getJSON("<?php echo U('Order/Purchase/remark') ?>/site_id/"+p[0]+'/orders_id/'+p[1]+'/field/logistics_remark', function(data){
           $('#remarkLogisticsModal textarea').text(data.logistics_remark);
           $('#remarkLogisticsModal').modal('show');
       });
    });
    $('#remarkLogisticsModal .btn-save').click(function(){
        var remark    = $('#remarkLogisticsModal textarea').val();
        var site_id   = $('#remarkLogisticsModal input[name="site_id"]').val();
        var orders_id = $('#remarkLogisticsModal input[name="orders_id"]').val();
        $.post("<?php echo U('Order/Purchase/remark') ?>/site_id/"+site_id+'/orders_id/'+orders_id+'/field/logistics_remark', {'value':remark}, function(data){
            if(data.success)
                alert('成功!');
            else
                alert('失败!');
        }, 'json');
    });
    
    $('.btn-logistics-item-remark').click(function(){
       var rel = $(this).attr('rel');
       var p = rel.split("-");
       var site_id = p[0];
       var orders_products_id = p[1];
       $('#itemRemarkLogisticsModal input[name="site_id"]').val(site_id);
       $('#itemRemarkLogisticsModal input[name="orders_products_id"]').val(orders_products_id);
       $.getJSON("<?php echo U('Order/Purchase/remark') ?>/site_id/"+site_id+'/orders_products_id/'+orders_products_id+'/field/remark', function(data){
           $('#itemRemarkLogisticsModal textarea').text(data.remark);
           $('#itemRemarkLogisticsModal').modal('show');
       });
    });   
    $('#itemRemarkLogisticsModal .btn-save').click(function(){
        var remark    = $('#itemRemarkLogisticsModal textarea').val();
        var site_id   = $('#itemRemarkLogisticsModal input[name="site_id"]').val();
        var orders_products_id   = $('#itemRemarkLogisticsModal input[name="orders_products_id"]').val();
        $.post("<?php echo U('Order/Purchase/remark') ?>/site_id/"+site_id+'/orders_products_id/'+orders_products_id+'/field/remark', {'value':remark}, function(data){
            if(data.success)
                alert('成功!');
            else
                alert('失败!');
        }, 'json');
    });    
    function ajax_export_order(page){
        var data = $("#form-search").serialize();
        $('<div id="word-order'+page+'" class="alert alert-info">正在下载第'+page+'页订单</div>').appendTo('#wordOrderModal .modal-body');

        $.ajax({
            'url':"<?php echo U('Order/Purchase/export/type/order') ?>/page/"+page,
            'data':data,
            'dataTypeString':'json',
            'type':'post',
            'success': function(json){
                if(json.link){
                    $('#word-order'+page).attr('class', 'alert alert-success').html('第'+page+'/'+json.total_page+'页订单下载完毕!<a href="'+json.link+'" target="_blank">下载链接</a>');
                }
                if(json.page<json.total_page){
                    page++;
                    ajax_export_order(page)
                }
                layer.closeAll('loading');
            },
            'beforeSend':function(){
                layer.load(1);
            }
        });
    }
    $('#btn-ajax-export-order').click(function(){
        $('#wordOrderModal .modal-body').empty();
        $('#wordOrderModal').modal({'backdrop':false, 'show':true});
        ajax_export_order(1);
    });
});    
</script>