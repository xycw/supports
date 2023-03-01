<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Domains/Promotion/edit') ?>" method="post">
	<input type="hidden" name="department_id" value="<?php echo isset($info['department_id']) ? $info['department_id'] : 0; ?>">
	<table class="table table-bordered">
		<tr>
			<th>部门名称<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="department_name" placeholder="部门名称" value="<?php echo isset($info['department_name']) ? $info['department_name'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>订单归属<span style="color:red;">*</span></th>
			<td>
				<select class="form-control" name="order_ascription" required="required">
					<?php foreach ($system_area_array as $v){?>
					<option value="<?php echo $v;?>"<?php if((isset($info['order_ascription']) && $info['order_ascription'] == $v) || ((!isset($info['order_ascription']) || empty($info['order_ascription'])) && $v == '新团队.')){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-default" href="<?php echo U('Domains/Promotion/index') ?>">返回</a>&nbsp;&nbsp;
				<button class="btn btn-default" type="submit">保存</button>
			</td>
		</tr>
	</table>
</form>
<script>
	$('form').submit(function(){
		$('form [type="submit"]').attr('disabled','disabled');
	});
</script>