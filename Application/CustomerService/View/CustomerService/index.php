<h3>
	客服列表
	<a class="btn btn-default" href="{:U('CustomerService/CustomerService/add')}">添加客服</a>
</h3>
<table class="table table-bordered">
	<tbody>
		<tr>
			<th>客服昵称</th>
			<th>客服邮箱</th>
			<th>客服邮箱应用密码</th>
			<th>客服邮箱SMTP域名</th>
			<th>客服邮箱SMTP端口</th>
			<th>操作</th>
		</tr>
		<?php foreach ($list as $v) {?>
		<tr>
			<td><?php echo $v['nickname'];?></td>
			<td><?php echo $v['email'];?></td>
			<td><?php echo $v['email_password'];?></td>
			<td><?php echo $v['email_smtp'];?></td>
			<td><?php echo $v['email_port'];?></td>
			<td>
				<a class="btn btn-default btn-block btn-xs" href="<?php echo U('CustomerService/CustomerService/edit/id/' . $v['id']);?>">编辑</a>
				<a class="btn btn-default btn-block btn-xs" href="<?php echo U('CustomerService/CustomerService/del/id/' . $v['id']);?>" onclick='if(window.confirm("你确定要删除此客服吗?")){ return true;}else{ return false;}'>删除</a>
			</td>
		</tr>
		<?php }?>
	</tbody>
</table>