<tagLib name="html" />
<h1>编辑订货项目</h1>
<?php
if($message_success){
?>
<p class="bg-success"><?php echo $message_success ?></p>
<?php
}
?>
<form action="<?php echo U('Order/Finance/itemEdit') ?>" method="post">
    <input type="hidden" name="site_id" value="<?php echo $data['site_id'] ?>" />
    <input type="hidden" name="orders_products_id" value="<?php echo $data['orders_products_id'] ?>" />
    <input type="hidden" name="orders_id" value="<?php echo $data['orders_id'] ?>" />
<table class="table table-bordered">
    <tr>
        <th width="200px">订单号</th>
        <td><?php echo empty($data['order_no'])?$data['order_no_prefix'].$data['orders_id']:$data['order_no']; ?></td>
    </tr>   
    <tr>
        <th width="200px">财务订单备注</th>
        <td>
        <textarea name="finance_remark" class="form-control"><?php echo $data['finance_remark'] ?></textarea>
        </td>
    </tr>       
    <tr>
        <th width="200px">跟单号</th>
        <td><?php echo $data['site_id'].'-'.$data['orders_products_id'] ?></td>
    </tr>
    <tr>
        <th width="200px">产品名称</th>
        <td>
            <p><?php echo $data['products_name'] ?></p>
                <?php 
                if($data['products_attributes']){
                    echo '<ul>';
                    foreach($data['products_attributes'] as $attributes){
                ?>
                <li><label><?php echo $attributes['products_options'] ?>:</label><?php echo $attributes['products_options_values'] ?></li>
                <?php
                    }
                    echo '</ul>';
                }
                ?>
        </td>
    </tr> 
    <tr>
        <th width="200px">产品SKU</th>
        <td><?php echo $data['products_model'] ?></td>
    </tr>     
    <tr>
        <th width="200px">产品图片</th>
        <td><img src="<?php echo $data['products_image'] ?>" /></td>
    </tr>       
    <tr>
        <th width="200px">订单数量</th>
        <td><?php echo $data['products_quantity'] ?></td>
    </tr>    
    <tr>
        <th width="200px">供应商</th>
        <td><html:select options="option_supplier" name="supplier_id" selected="supplier_id_selected" style="form-control js-example-basic-single" first="--供应商--" /></td>
    </tr>      
    <tr>
        <th width="200px">订货日期</th>
        <td><input type="text" name="date_process" class="form-control" value="<?php echo $data['date_process'] ?>" /></td>
    </tr> 
    <tr>
        <th width="200px">订货单价</th>
        <td><input type="text" name="purchase_price" class="form-control" value="<?php echo number_format($data['purchase_price'], 2, '.', '') ?>" /></td>
    </tr>    
    <tr>
        <th width="200px">订货数量</th>
        <td><input type="text" name="quantity_process" class="form-control" value="<?php echo $data['quantity_process'] ?>" /></td>
    </tr>      
    <tr>
        <td colspan="2" align="center"><button type="submit" class="btn btn-default">保存</button></td>
    </tr>
</table>
</form>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$("input[name='date_process']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});    
$('.js-example-basic-single').select2();
</script>