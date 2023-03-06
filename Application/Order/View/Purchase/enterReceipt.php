<?php if(isset($order_info) && !empty($order_info)){?>
<h1>物流回单</h1>
<form action="<?php echo U('Order/Purchase/enterReceipt');?>" method="post" onsubmit="javascript:return check()">
	<table class="table table-bordered">
		<tr>
			<th>订单号<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" value="<?php echo $order_info['order_no'];?>" readonly="readonly">
				<input type="hidden" name="site_id" value="<?php echo $order_info['site_id'];?>">
				<input type="hidden" name="orders_id" value="<?php echo $order_info['orders_id'];?>">
			</td>
		</tr>
		<tr>
			<th>发货日期<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="delivery_date" value="<?php echo date('Y-m-d');?>" required="required">
			</td>
		</tr>
		<tr>
			<th>货运方式<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="delivery_type" value="<?php echo isset($_SESSION['delivery_type']) ? $_SESSION['delivery_type'] : 'USPS';?>" required="required">
			</td>
			<td></td>
		</tr>
		<tr>
			<th>转单号</th>
			<td>
				<input class="form-control" type="text" name="delivery_forward_no" >
			</td>
		</tr>
		<tr>
            <th>货运单号<span style="color:red;">*</span></th>
            <?php if ($order_info['indvance_no']) { ?>
                <td><input class="form-control" type="text" name="delivery_tracking_no" required="required"
                           value="<?php echo $order_info['indvance_no']; ?>"
                </td>
            <?php } else {
                ?>
                <td><input class="form-control" type="text" name="delivery_tracking_no" required="required"
                           placeholder="请扫描货运单号的条形码或者输入货运单号"></td>
            <?php } ?>
        </tr>
		<tr>
			<th>重量<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="delivery_weight" required="required">
			</td>
		</tr>
		<tr>
			<th>产品<span style="color:red;">*</span></th>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>包裹是否包含</th>
						<th>产品图片</th>
						<th>产品数量</th>
						<th>产品信息</th>
						<th>是否没货</th>
					<tr>
					<?php
					$delivery_quanlity = 0;
					foreach ($order_info['products'] as $entry) {
						$checked = $out_of_stock_checked = false;
						if(empty($entry['orders_delivery_id'])){
							if($entry['supplier_name'] == '没货' || $entry['out_of_stock'] == 1){
								$out_of_stock_checked = true;
							}else{
								$checked = true;
								$delivery_quanlity += $entry['products_quantity'];
							}
						}
					?>
					<tr>
						<td>
							<?php if($entry['remove'] != 1){?>
							<input type="checkbox" name="orders_products_id[]" value="<?php echo $entry['orders_products_id'];?>"<?php if($checked){?> checked="checked"<?php }?> data-products_quantity="<?php echo $entry['products_quantity'];?>">
							<?php }?>
						</td>
						<td><img src="<?php echo $entry['products_image']; ?>" width="100px" /></td>
						<td><?php echo $entry['products_quantity'];?></td>
						<td>
							<?php if($entry['remove'] == 1){?>
							<span style="text-decoration:line-through;">
							<?php }?>
							<?php echo $entry['products_name'];?>
							<?php if($entry['remove'] == 1){?>
							</span>
							<?php }?>
							<?php if (!empty($entry['products_attributes'])) {?>
							<br>
								<?php
								$attribute_info = array();
								foreach ($entry['products_attributes'] as $attribute) {
									$attribute_info[] =  $attribute['products_options'] . ':' . $attribute['products_options_values'];
								}
								echo implode('<br>', $attribute_info);
								?>
							<?php }?>
							<br>
							SKU:<?php echo $entry['products_model'];?>
						</td>
						<td>
							<?php if($entry['remove'] != 1){?>
							<input type="checkbox" name="out_of_stock[]" value="<?php echo $entry['orders_products_id'];?>"<?php if($out_of_stock_checked){?> checked="checked"<?php }?>>
							<?php }?>
						</td>
					<tr>
					<?php }?>
				</table>
				<input type="hidden" name="delivery_quanlity" value="<?php echo $delivery_quanlity;?>">
			</td>
		</tr>
		<tr>
			<th>赠品数量</th>
			<td>
				<input class="form-control" type="text" name="delivery_gift_quanlity">
			</td>
		</tr>
		<tr>
			<th>备注</th>
			<td>
				<textarea class="form-control" name="delivery_remark"></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-default" href="<?php echo U('Order/Purchase/enterReceipt'); ?>">返回</a>&nbsp;&nbsp;
				<button class="btn btn-default" type="submit">保存</button>
			</td>
		</tr>
	</table>
</form>
<script>
var chars = '';//暂存字符串
var timeOut = 1000;//超时时间
var lastDate = new Date();
var isFirst = true;
//keypress可以区分输入的大小写，keydown不行
$(window).on("keypress", function (e) {
	var code = e.which || e.keyCode;
	//本次时间与上次时间之差大于xxms 则强制上次扫码结束,本次作为第一个字符
	if (new Date() - lastDate > timeOut) {
		isFirst = true;
	}
	//如果是第一个字符，则将之前的字符串置空
	if (isFirst) {
		chars = '';
		isFirst = false;
	}
	lastDate = new Date();
	//如果捕获到回车键，则扫码结束，将保存的字符串解析并展示到页面上
	if (code == 13) {
		e.preventDefault();
		isFirst = true;
		if (chars != '') {
			if(chars.length >= 34) chars = chars.substr(8);
			$('input[name="delivery_tracking_no"]').val(chars);
		} else {
			layer.msg('未能获取到条形码信息！');
		}
	} else {
		//如果不是回车键，则将此次捕获到字符追加到暂存字符串中
		chars += String.fromCharCode(code);
	}
});
$('input[name="orders_products_id[]"]').click(function(){
	var out_of_stock_obj = $(this).parent('td').parent('tr').find('input[name="out_of_stock[]"]');
	if($(this).is(':checked') && out_of_stock_obj.is(':checked')) out_of_stock_obj.prop('checked', false);
	get_delivery_quanlity();
});
$('input[name="out_of_stock[]"]').click(function(){
	var orders_products_id_obj = $(this).parent('td').parent('tr').find('input[name="orders_products_id[]"]');
	if($(this).is(':checked') && orders_products_id_obj.is(':checked')){
		orders_products_id_obj.prop('checked', false);
		get_delivery_quanlity();
	}
});
function get_delivery_quanlity(){
	var delivery_quanlity = 0;
	$('input[name="orders_products_id[]"]:checked').each(function(){
		delivery_quanlity += parseInt($(this).attr('data-products_quantity'));
	});
	$('input[name="delivery_quanlity"]').val(delivery_quanlity);
}
function check(){
	if($('input[name="orders_products_id[]"]:checked').length < 1){
		layer.msg('请选择包裹中包含的产品！');
		return false;
	}else{
		return true;
	}
}
</script>
<?php }else{?>
<h1>请扫描订单号的条形码或者输入订单号</h1>
<form action="<?php echo U('Order/Purchase/enterReceipt');?>">
	<table class="table table-bordered">
		<tr>
			<th>订单号<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="order_no">
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-default" href="<?php echo U('Order/Purchase/index'); ?>">返回</a>&nbsp;&nbsp;
				<button class="btn btn-default" type="submit">提交</button>
			</td>
		</tr>
	</table>
</form>
<script>
var chars = '';//暂存字符串
var timeOut = 1000;//超时时间
var lastDate = new Date();
var isFirst = true;
//keypress可以区分输入的大小写，keydown不行
$(window).on("keypress", function (e) {
	var code = e.which || e.keyCode;
	//本次时间与上次时间之差大于xxms 则强制上次扫码结束,本次作为第一个字符
	if (new Date() - lastDate > timeOut) {
		isFirst = true;
	}
	//如果是第一个字符，则将之前的字符串置空
	if (isFirst) {
		chars = '';
		isFirst = false;
	}
	lastDate = new Date();
	//如果捕获到回车键，则扫码结束，将保存的字符串解析并展示到页面上
	if (code == 13) {
		isFirst = true;
		if (chars != '') {
			$('input[name="order_no"]').val(chars);
			$('form').submit();
		} else {
			layer.msg('未能获取到条形码信息！');
		}
	} else {
		//如果不是回车键，则将此次捕获到字符追加到暂存字符串中
		chars += String.fromCharCode(code);
	}
})
</script>
<?php }?>