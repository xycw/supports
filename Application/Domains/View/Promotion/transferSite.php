<load href="__PUBLIC__/Js/bootstrap-select/css/bootstrap-select.css" />
<load href="__PUBLIC__/Js/bootstrap-select/js/bootstrap-select.js" />
<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Domains/Promotion/transferSite') ?>" method="post">
	<table class="table table-bordered">
		<tr>
			<th>推广人</th>
			<td>
				<input type="hidden" name="user_id" value="<?php echo $members_array[$id]['user_id']; ?>">
				<?php echo $members_array[$id]['chinese_name'];unset($members_array[$id]); ?>
			</td>
		</tr>
		<tr>
			<th>转移目标人<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="system_tuiguangy" required="required">
					<option value="">请选择</option>
					<?php foreach ($members_array as $v){?>
					<option value="<?php echo $v['user_id'];?>"><?php echo $v['chinese_name'];?></option>
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