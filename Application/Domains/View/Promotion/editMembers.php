<load href="__PUBLIC__/Js/bootstrap-select/css/bootstrap-select.css" />
<load href="__PUBLIC__/Js/bootstrap-select/js/bootstrap-select.js" />
<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Domains/Promotion/editMembers') ?>" method="post">
	<input type="hidden" name="id" value="<?php echo isset($info['id']) ? $info['id'] : 0; ?>">
	<input type="hidden" name="department_id" value="<?php echo isset($info['department_id']) ? $info['department_id'] : (isset($department_id) ? $department_id : 0); ?>">
	<table class="table table-bordered">
		<tr>
			<th>成员<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="user_id" required="required">
					<?php foreach ($users_array as $k => $v){?>
					<option value="<?php echo $k;?>"<?php if(isset($info['user_id']) && $info['user_id'] == $k){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
		</tr>
		<tr>
			<th>订单前缀<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="order_no_prefix" placeholder="订单前缀" value="<?php echo isset($info['order_no_prefix']) ? $info['order_no_prefix'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>业务员<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="sales_id" required="required">
					<?php foreach ($sales_array as $k=>$v){?>
					<option value="<?php echo $k;?>"<?php if(isset($info['sales_id']) && $info['sales_id'] == $k){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
				<?php if(isset($info['id'])){?><b>是否同步修改已建网站</b> &nbsp; <input type="radio" name="synchro_sales_id" value="1"> 是 &nbsp;&nbsp; <input type="radio" name="synchro_sales_id" value="0" checked="checked"> 否<?php }?>
			</td>
		</tr>
		<tr>
			<th>客服邮箱<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="customer_service_email" required="required">
					<?php foreach ($customer_service_array as $v){?>
					<option value="<?php echo $v;?>"<?php if(isset($info['customer_service_email']) && $info['customer_service_email'] == $v){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
				<?php if(isset($info['id'])){?><b>是否同步修改已建网站</b> &nbsp; <input type="radio" name="synchro_customer_service_email" value="1"> 是 &nbsp;&nbsp; <input type="radio" name="synchro_customer_service_email" value="0" checked="checked"> 否<?php }?>
			</td>
		</tr>
		<tr>
			<th>组长<span style="color:red;">*</span></th>
			<td>
				<input type="radio" name="leader" value="1"<?php if(isset($info['leader']) && $info['leader'] == 1){?> checked="checked"<?php }?>> 是&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="leader" value="0"<?php if((isset($info['leader']) && $info['leader'] == 0) || !isset($info['leader'])){?> checked="checked"<?php }?>> 否
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