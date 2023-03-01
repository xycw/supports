<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('CustomerService/CustomerService/edit') ?>" method="post">
	<input type="hidden" name="id" value="<?php echo isset($info['id']) ? $info['id'] : 0; ?>">
	<table class="table table-bordered">
		<tr>
			<th>客服昵称<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="nickname" placeholder="客服昵称" value="<?php echo isset($info['nickname']) ? $info['nickname'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>客服邮箱<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="email" placeholder="客服邮箱" value="<?php echo isset($info['email']) ? $info['email'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>客服邮箱应用密码<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="email_password" placeholder="客服邮箱应用密码" value="<?php echo isset($info['email_password']) ? $info['email_password'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>客服邮箱SMTP域名<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="email_smtp" placeholder="客服邮箱SMTP域名" value="<?php echo isset($info['email_smtp']) ? $info['email_smtp'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<th>客服邮箱SMTP端口<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="email_port" placeholder="客服邮箱SMTP端口" value="<?php echo isset($info['email_port']) ? $info['email_port'] : ''; ?>" required="required">
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<a class="btn btn-default" href="<?php echo U('CustomerService/CustomerService/index') ?>">返回</a>&nbsp;&nbsp;
				<button class="btn btn-default" type="submit">保存</button>
			</td>
		</tr>
	</table>
</form>