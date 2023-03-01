<h3>
	域名列表
	<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
	<a class="btn btn-default" href="<?php echo U('Domains/Domains/built_site');?>" id="built-site">设置为已建站</a>
	<button type="button" class="btn btn-default" id="change-all">更改所有域名状态</button>
	<?php }?>
	<a class="btn btn-default" href="<?php echo U('Domains/Domains/add');?>">添加域名</a>
	<a class="btn btn-default" href="<?php echo U('Domains/Promotion/index');?>" style="float: right;">推广部门管理</a>
</h3>
<div style="margin: 10px;">
	状态：
	<input type="radio" name="status"<?php if(!isset($_GET['status'])){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index');?>'"> 未到期  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if(isset($_GET['status']) && $_GET['status'] == 0){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>0));?>'"> 未建站  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 1){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>1));?>'"> 已建站  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 2){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>2));?>'"> 域名即将到期  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 3){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>3));?>'"> 域名待续费  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 4){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>4));?>'"> 域名不续费  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 5){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>5));?>'"> 域名已到期  &nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 6){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>6));?>'"> 网站待删除&nbsp;&nbsp;
	<input type="radio" name="status"<?php if($_GET['status'] == 7){?> checked="checked"<?php }?> onclick="window.location='<?php echo U('Domains/Domains/index',array('status'=>7));?>'"> 网站已删除
</div>
<table class="table table-bordered">
	<tbody>
		<tr>
			<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
			<th><input type="checkbox" id="all_check"> 全选</th>
			<?php }?>
			<th>网站id</th>
			<th>域名</th>
			<th>域名所属人</th>
			<th>网站类型</th>
			<th>域名邮箱</th>
			<th>域名&SSL到期日期</th>
			<th>状态</th>
			<th>操作</th>
		</tr>
		<?php foreach ($list as $v) {?>
		<tr>
			<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
			<td>
				<?php if($v['status'] == 0){?><input type="checkbox" name="domain_id[]" value="<?php echo $v['domain_id'];?>"><?php }?>
			</td>
			<?php }?>
			<td><?php echo $v['site_id'];?></td>
			<td><?php echo $v['domain_name'];?></td>
			<td><?php echo $v['chinese_name'];?></td>
			<td><?php if($v['site_type'] == 1){?>独立站<?php }elseif($v['site_type'] == 2){?>B站<?php }elseif($v['site_type'] == 3){?>跳转站<?php }elseif($v['site_type'] == 10){?>商城站<?php }elseif($v['site_type'] == 11){?>验证站<?php }elseif($v['site_type'] == 12){?>Facebook小组跳转站<?php }else{?>其他<?php }?></td>
			<td><?php echo $v['domain_email'];?></td>
			<td><?php echo '域名：' . $v['expire_date'] . '<br>' . 'SSL：' . $v['ssl_expire_date'];?></td>
			<td><?php if($v['status'] == 1){?><span class="btn-success">已建站<?php }elseif($v['status'] == 2){?><span class="btn-warning">域名即将到期<?php }elseif($v['status'] == 3){?><span class="btn-primary">域名待续费<?php }elseif($v['status'] == 4){?><span class="btn-info">域名不续费<?php }elseif($v['status'] == 5){?><span class="btn-danger">域名已到期<?php }elseif($v['status'] == 6){?><span class="btn-primary">网站待删除<?php }elseif($v['status'] == 7){?><span class="btn-default">网站已删除<?php }else{?><span class="btn-primary">未建站<?php }?></span></td>
			<td>
				<a class="btn btn-default btn-block btn-xs" style="display: unset; padding: 5px;" href="<?php echo U('Domains/Domains/edit/domain_id/' . $v['domain_id']);?>">编辑</a>
				<?php if($v['status'] == 2){?>
				<a class="btn btn-default btn-block btn-xs confirm" style="display: unset; padding: 5px;" href="<?php echo U('Domains/Domains/change_status',array('domain_id'=>$v['domain_id'],'status'=>3));?>">续费</a>
				<a class="btn btn-default btn-block btn-xs confirm" style="display: unset; padding: 5px;" href="<?php echo U('Domains/Domains/change_status',array('domain_id'=>$v['domain_id'],'status'=>4));?>">不续费</a>
				<?php }?>
				<?php if($v['status'] >= 1 && $v['status'] <= 5){?>
				<a class="btn btn-default btn-block btn-xs confirm" style="display: unset; padding: 5px;" href="<?php echo U('Domains/Domains/change_status',array('domain_id'=>$v['domain_id'],'status'=>6));?>">删除网站</a>
				<?php }?>
				<?php if(session(C('USER_INFO') . '.user_id') == 1 && $v['status'] == 6){?>
				<a class="btn btn-default btn-block btn-xs confirm" style="display: unset; padding: 5px;" href="<?php echo U('Domains/Domains/change_status',array('domain_id'=>$v['domain_id'],'status'=>7));?>">设置为已删除</a>
				<?php }?>
			</td>
		</tr>
		<?php }?>
	</tbody>
</table>
<div class="modal fade" id="dialog-advance-date">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">更改所有域名状态</h4>
			</div>
			<div class="modal-body">
				<form action="<?php echo U('Domains/Domains/change_all'); ?>" method="post">
					<div class="form-group">
						提前 <input type="text" name="advance_date" value="0"> 天提醒域名到期
					</div>
					<button class="btn btn-default" type="submit">提交</button>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
$('#built-site').click(function(e){
	$('#built-site').attr('disabled','disabled');
	e.preventDefault();
	if(!$('input[name="domain_id[]"]:checked').length) {
		layer.msg('请勾选！');
		$('#built-site').removeAttr('disabled');
		return false;
	}
	var href = $('#built-site').attr('href') + '?domain_ids=',
		n = 0;
	$('input[name="domain_id[]"]:checked').each(function(){
		href += (n > 0 ? ',' : '') + $(this).val();
		n++;
	});
	window.location.href = href;
});
$('#change-all').click(function(){
	$('#dialog-advance-date').modal('show');
});
$('#dialog-advance-date [type="submit"]').click(function(){
	$(this).attr('disabled','disabled');
	$('#dialog-advance-date form').submit();
});
$('#all_check').click(function(){
	if($(this).is(':checked')){
		$('input[name="domain_id[]"]').prop('checked',true);
	}else{
		$('input[name="domain_id[]"]').prop('checked',false);
	}
});
$('.table .confirm').click(function(){
	$(this).attr('disabled','disabled');
	if(window.confirm('确定' + $(this).text() + '吗?')){
		return true;
	}else{
		$(this).removeAttr('disabled');
		return false;
	}
});
</script>