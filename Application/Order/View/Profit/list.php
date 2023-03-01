<h1>利润表</h1>
<form class="form-inline" action="<?php U('Order/Profit/list')?>" method="post">
  <div class="form-group">
    <label class="sr-only" for="date-start">开始日期</label>
    <input type="text" class="form-control form_datetime" name="date_start" id="date-start" placeholder="开始日期" value="<?php echo I('date_start')?>">
  </div>
  <div class="form-group">
    <label class="sr-only" for="date-end">结束日期</label>
    <input type="text" class="form-control form_datetime" name="date_end" id="date-end" placeholder="结束日期" value="<?php echo I('date_end')?>">
  </div>
  <div class="form-group">
  	<label class="sr-only" for="date-end">收款方式</label>
  	<select name="payment_method" class="form-control">
  		<option>请选择</option>
  		<option value="not-credit-card">西联|速汇金|银行转账</option>
  		<option value="credit-card">信用卡付款</option>
  	</select>
  </div>
  <button type="submit" class="btn btn-default btn-xs">查询</button>
</form>
<br>
<table class="table table-bordered">
	<thead>
		<tr>
			<th>序号</th>
			<th>网站</th>
			<th width="8%">订单总额</th>
			<th width="8%">宝贝成本</th>
			<th width="8%">国内运费</th>
			<th width="8%">国际运费</th>
			<th width="8%">其它费用</th>
			<th width="15%">通道扣费</th>
			<th width="8%">利润</th>
		</tr>
	</thead>
<?php 
$total_profit = 0;
if (!empty($profit_list)) {
	$i = 1;
	$total_order_amount = 0;
	$total_product_cost = 0;
	$total_express     = 0;
	$total_shipping    = 0;
	$total_other_cost  = 0;
	$total_payment_fee = 0;
	foreach ($profit_list as $profit_entry){
?>		
	<tr>
		<td><?php echo $i++?></td>
		<td><?php echo $profit_entry['site_name'].'#'.$profit_entry['orders_id']?></td>
		<td><?php echo showCurrency($profit_entry['order_pay_rmb']); $total_order_amount+=$profit_entry['order_pay_rmb'];?></td>
		<td>
		<?php 
		$product_cost = 0;
		foreach ($profit_entry['product'] as $product){
			$product_cost += $product['product_cost'];
		}
		$total_product_cost += $product_cost;
		echo showCurrency($product_cost);
		?>
		</td>
		<td><?php echo showCurrency($profit_entry['express_cost']); $total_express+=$profit_entry['express_cost'];?></td>
		<td><?php echo showCurrency($profit_entry['shipping_cost']); $total_shipping+=$profit_entry['shipping_cost'];?></td>
		<td><?php echo showCurrency($profit_entry['other_cost']); $total_other_cost+=$profit_entry['other_cost'];?></td>
		<td>
		<?php
		$payment_cost = 0;
		if ($profit_entry['payment_method']=='Credit Card Payment') {
			$jufu = $profit_entry['customer_feedback']=='拒付'?true:false;
			$payment_cost = $payment->cost($profit_entry['payment_module_code'], $profit_entry['order_pay_rmb'], $jufu);
		}
		echo showCurrency($payment_cost);
		$total_payment_fee += $payment_cost;
		if(!empty($profit_entry['rp_no'])){
			echo '<br>'.$profit_entry['rp_no'];
		}
		?>
		</td>
		<td>
		<?php 
		echo $profit_entry['order_status_remark'].'<br>';
		if (in_array($profit_entry['customer_feedback'], array('拒付','全额退款'))){
                    $profit = -($payment_cost+$product_cost+$profit_entry['express_cost']+$profit_entry['shipping_cost']+$profit_entry['other_cost']);
                    echo $profit_entry['customer_feedback'];	
		}else
                    $profit = $profit_entry['order_pay_rmb']-$product_cost-$profit_entry['express_cost']-$profit_entry['shipping_cost']-$profit_entry['other_cost']-$payment_cost;
		$total_profit += $profit;
		if($profit<0){
                    echo '<span class="bg-danger">';
		}
		echo showCurrency($profit);
		if($profit<0){
			echo '</span>';
		}
		?>
		</td>
	</tr>
<?php
	}
}
?>
	<tr>
		<td colspan="2"><b>合计</b></td>
		<td><?php echo showCurrency($total_order_amount)?></td>
		<td><?php echo showCurrency($total_product_cost)?></td>
		<td><?php echo showCurrency($total_express)?></td>
		<td><?php echo showCurrency($total_shipping)?></td>
		<td><?php echo showCurrency($total_other_cost)?></td>
		<td><?php echo showCurrency($total_payment_fee)?></td>
		<td><?php echo showCurrency($total_profit)?></td>
	</tr>	
</table>
<?php
if ($yidinghuo_num>0) {
	echo '<p class="bg-warning p5">统计的日期范围内还有'.$yidinghuo_num.'个订单没有发货</p>';
} 
?>	



<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        format: "yyyy-mm-dd",
        language: 'zh-CN',
        minView: 2,
        autoclose: true
    });
</script> 