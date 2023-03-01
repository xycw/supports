<?php if (!defined('THINK_PATH')) exit(); echo R('Common/Html/html_start');?> 
<header id="header"><?php echo R('Common/Layout/menu');?></header>
<div class="container" id="content">
<div class="nav-search">
	<ul class="list-inline">
		<li><a class="seleted" href="<?php echo U('Statistics/Statistics/index'); ?>">销量统计</a></li>
		<li><a href="<?php echo U('Statistics/Statistics/searchKeyword'); ?>">搜索统计</a></li>
		<li><a href="<?php echo U('Statistics/Statistics/ipAccessLog'); ?>">IP统计</a></li>
		<li><a href="<?php echo U('Order/PaymentModuleStatistics/index'); ?>">支付接口统计</a></li>
	</ul>
</div>
<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Statistics/Statistics/index') ?>" method="get">
	<div class="row">
		<div class="col-lg-3">
			<label>交易网址</label>
			<input type="hidden" name="site_id" value="<?php echo I('site_id', '') ?>">
			<button data-toggle="modal" data-target="#site_dialog" type="button" class="btn btn-default">网址筛选</button>
		</div>
		<div class="col-lg-1">
			<label>下单日期</label>
		</div>
		<div class="col-lg-2">
			<input class="form-control" type="text" name="start_date" value="<?php echo $start_date; ?>" placeholder="起始日期">
		</div>
		<div class="col-lg-2">
			<input class="form-control" type="text" name="end_date" value="<?php echo $end_date; ?>" placeholder="结束日期">
		</div>
		<div class="col-lg-2">
			<label>订单状态</label>
			<input type="radio" name="order_status" value="1"<?php if(I('order_status', 1) ==1){?> checked<?php }?>> 成功
			<input type="radio" name="order_status" value="0"<?php if(I('order_status', 1) !=1){?> checked<?php }?>> 全部
		</div>
		<div class="col-lg-1">
			<button class="btn btn-default" type="submit">查询</button>
		</div>  
		<div class="col-lg-1">
			<input type="submit" name="export" value="导出" class="btn btn-default">
		</div>
	</div>
	<hr/>
</form>
<?php
if (I('site_id') != ''){ $site_id = I('site_id'); if(is_array($site_id)==false) { if(strpos($site_id, '_')) $site_id = explode ('_', $site_id); else $site_id = array($site_id); } echo '<p class="bg-primary">当前筛选网站：'; foreach ($site_id as $id){ echo $site_name[$id].'&nbsp;&nbsp;&nbsp;'; } echo '</p>'; } ?>
<div class="page-nav">
	<div class="row">
		<div class="col-lg-6">
			<div class="page-nav-info">
				<label>每页</label>
				<?php echo $list_row ?>
				<label>条(当前总数:</label> <?php echo $total ?><label>条)</label>
			</div>
		</div>
		<div class="col-lg-6 right">
			<?php W('Common/PageNavigation/page', array('page' => $page, 'num' => $list_row, 'count' => $total, 'name' => 'Statistics/Statistics/index', $page_data));?>
		</div>
	</div>
</div>
<table class="table table-bordered">
	<thead>
		<tr>
			<th>型号</th>
			<th class="center">销量 </th>
			<th>产品名称</th>
		</tr>
	</thead>
	<tbody>
	 <?php foreach ($list as $v) {?>
		<tr>
			<td><?php echo $v['products_model'];?></td>
			<td class="center"><?php echo $v['num'];?></td>
			<td><?php echo $v['products_name'];?></td>
		</tr>
	 <?php }?>
	</tbody>
</table>
<div class="page-nav">
	<div class="row">
		<div class="col-lg-6">
			<div class="page-nav-info">
				<label>每页</label>
				<?php echo $list_row ?>
				<label>条(当前总数:</label> <?php echo $total ?><label>条)</label>
			</div>
		</div>
		<div class="col-lg-6 right">
			<?php W('Common/PageNavigation/page', array('page' => $page, 'num' => $list_row, 'count' => $total, 'name' => 'Statistics/Statistics/index', $page_data));?>
		</div>
	</div>
</div>

<div class="modal fade" id="site_dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="padding:10px;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">网站筛选</h4>
			</div>
			<div class="row">
			<?php foreach ($options_site_name as $type=>$site){?>
				<div class="col-xs-12 site-type-box">
					<div class="col-xs-12">
						<label>
							<input class="site-type" type="checkbox">
							<?php if($type == 1){?>独立站<?php }elseif($type == 2){?>B站<?php }elseif($type == 10){?>平台站<?php }else{echo $type;}?>
						</label>
					</div>
					<?php foreach ($site as $site_id => $site_name){?>
					<div class="col-xs-4">
						<label><input type="checkbox" name="site_id[]" value="<?php echo $site_id?>"<?php if(in_array($site_id,$site_id_select)) echo ' checked'?>>
						<?php echo ' '.$site_id.'# '.$site_name ?></label>
					</div>
					<?php }?>
				</div>
			<?php }?>
			</div>
		</div>
	</div>
	<script>
		$('#site_dialog .site-type').click(function(){
			if($(this).is(':checked')){
				$(this).parents('.site-type-box').find('[name="site_id[]"]').not("input:checked").click();
			}else{
				$(this).parents('.site-type-box').find('[name="site_id[]"]:checked').click();
			}
		});
	</script>
</div>
<script>
	$("input[name='start_date']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
	$("input[name='end_date']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
	$('input[name="site_id[]"]').click(function(){
		var v = '';
		$('input[name="site_id[]"]:checked').each(function(){
			if(v=='') v = $(this).val();
			else v += '_'+$(this).val();
		});
		$('input[name="site_id"]').val(v);
	});
</script>
</div>
<footer id="footer"><?php echo R('Common/Layout/footer');?></footer>
<?php echo R('Common/Html/html_end');?>