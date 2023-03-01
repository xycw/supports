<h1>订货表回单导入</h1>
<form action="<?php echo U('Order/Purchase/importConfirm') ?>" method="POST">
<table class="customers-list">
    <colgroup>
        <col width="50">
        <col width="100px">
        <col width="100px">
        <col width="300px">
        <col width="50px">
        <col width="130px">
        <col width="150px">
        <col width="150px">
        <col width="100px">
        <col width="100px">
        <col width="100px">
        <col width="100px">
        <col width="auto">
    </colgroup>
    <thead>
		<tr>
		    <th>序号</th>
		    <th>跟货号</th>
			<th>产品图片</th>	
			<th>产品信息</th>
			<th>产品数量</th>
			<th>订货日期</th>
			<th>供应商</th>
			<th>产品分类</th>
			<th>是否定制</th>
			<th>已订数量</th>
			<th>订货数量</th>
			<th>订货单价</th>
			<th>历史订货详情</th>
		</tr>
	</thead>
	<tbody>
<?php
$i = 1;
foreach($data as $entry){
?>
    <tr class="sep-row"><td colspan="10"></td></tr>
    <tr class="customers-hd">
        <td><?php echo $i ?></td>
        <td><?php echo $entry['process_id'] ?></td>
        <td><img src="<?php echo $entry['order']['products_image'] ?>" width="100px" /></td>
        <td>
            <ul>
                <li><label>产品名:</label><?php echo $entry['order']['products_name'] ?></li>
                <li><label>SKU:</label><?php echo $entry['order']['products_model'] ?></li>
                <?php 
                if($entry['order']['products_attributes']){
                    foreach($entry['order']['products_attributes'] as $attributes){
                ?>
                <li><label><?php echo $attributes['products_options'] ?>:</label><?php echo $attributes['products_options_values'] ?></li>
                <?php
                    }   
                }
                ?></li>
            </ul>        
        </td>
        <td><?php echo $entry['order']['products_quantity'] ?></td>
        <td><input class="form-control" type="text" name="date_process[<?php echo $entry['order']['orders_products_remark_id'] ?>]" value="<?php echo date('Y-m-d', strtotime($entry['date_process'])) ?>"></td>
        <td>
            <select class="form-control" name="supplier_id[<?php echo $entry['order']['orders_products_remark_id'] ?>]">
                <?php
                foreach($supplier as $supplier_entry){
                ?>
                <option value="<?php echo $supplier_entry['supplier_id']?>"<?php if($supplier_entry['supplier_id']==$entry['supplier_id']) echo ' selected' ?>><?php echo $supplier_entry['supplier_name']?></option>
                <?php
                }
                ?>
            </select>    
        </td>
        <td>
            <select class="form-control" name="categories_id[<?php echo $entry['order']['orders_products_remark_id'] ?>]">
                <?php
                foreach($categories as $categories_entry){
                ?>
                <option value="<?php echo $categories_entry['categories_id']?>"<?php if($categories_entry['categories_id']==$entry['categories_id']) echo ' selected' ?>><?php echo $categories_entry['categories_name']?></option>
                <?php
                }
                ?>
            </select>    
        </td>        
        <td>
            <select class="form-control" name="is_customized[<?php echo $entry['order']['orders_products_remark_id'] ?>]">
                <option value="1"<?php if($entry['is_customized']) echo ' selected' ?>>是</option>
                <option value="0"<?php if(!$entry['is_customized']) echo ' selected' ?>>否</option>
            </select>
        </td>
        <td><?php echo $entry['order']['quantity_process'] ?></td>
        <td><input class="form-control" type="text" name="quantity_process[<?php echo $entry['order']['orders_products_remark_id'] ?>]" value="<?php echo $entry['quantity_process'] ?>"></td>
        <td><input class="form-control" type="text" name="purchase_price[<?php echo $entry['order']['orders_products_remark_id'] ?>]" value="<?php echo $entry['purchase_price'] ?>"></td>
        <td>
            <select class="form-control" name="times_process[<?php echo $entry['order']['orders_products_remark_id'] ?>]">
            <?php
            if(empty($entry['order']['detail_process'])){
            ?>
            <option value="1" selected>第1次订货回单</option>
            <?php
            }else{
                $detail_process = json_decode($entry['order']['detail_process'], true);
                $n = sizeof($detail_process);
                $n = ($n==0?1:$n+1);
                for($j=$n;$j>0;$j--){
            ?>
                <option value="<?php echo $j?>" <?php if($j==1) echo 'selected' ?>>第<?php echo $j?>次订货回单</option>
            <?php
                }
            }
            ?>
            </select>
        </td>
    </tr>
<?php
    $i++;
}
?>
	</tbody>
</table>
<br>
<button class="btn btn-default" type="submit">确认导入</button>
</form>