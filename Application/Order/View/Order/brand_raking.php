<h1>品牌销售排名</h1>
<form action="<?php echo U('Order/Order/brand_raking')?>" enctype="multipart/form-data" method="post">
<div class="row mb5">
	<div class="col-lg-1 right">国家:</div>
	<div class="col-lg-1"><input class="form-control"  type="text" name="country" value="<?php echo I('country','')?>"></div>
	<div class="col-lg-1"><button class="btn btn-default" type="submit">统计</button></div>
</div>	
</form>
<table class="table table-border">
	<tr>
		<th>品牌</th>
		<th>销量</th>
	</tr>
<?php 
foreach ($brand_raking as $entry){
?>
	<tr>
		<td><?php echo $entry['manufacturers_name']?></td>
		<td><?php echo $entry['num'];?></td>
	</tr>
<?php
}
?>	
</table>