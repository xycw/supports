<load href="__PUBLIC__/Js/bootstrap-select/css/bootstrap-select.css" />
<load href="__PUBLIC__/Js/bootstrap-select/js/bootstrap-select.js" />
<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Domains/Domains/add') ?>" method="post" id="add-domain-form" onsubmit="javascript:return check()">
	<table class="table table-bordered">
		<tr>
			<th>域名所属人<?php if(session(C('USER_INFO') . '.user_id') != 1){?> <span style="color:red;">*</span><?php }?></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="user_id"<?php if(session(C('USER_INFO') . '.user_id') != 1){?> required="required"<?php }?>>
					<option value="">请选择</option>
					<?php foreach ($users_array as $k => $v){?>
					<option value="<?php echo $k;?>"<?php if(session(C('USER_INFO') . '.user_id') == $k){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
			<td></td>
		</tr>
		<tr>
			<th>网站类型<span style="color:red;">*</span></th>
			<td>
				<select class="form-control" name="site_type" required="required">
					<option value="">请选择</option>
					<?php foreach ($site_type_array as $k => $v){?>
					<option value="<?php echo $k;?>"><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
			<td></td>
		</tr>
		<tr>
			<th>域名（请前往<a href="https://namecheap.com" target="_blank">namecheap.com</a>搜索域名是否可以购买以及价格是否合适）<span style="color:red;">*</span></th>
			<td>
				<input class="form-control verify-repeat" type="text" name="domain_name[]" placeholder="域名" required="required">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default add-domain verify hide">增加域名</a>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas hide">
			<th>商品数据<span style="color:red;">*</span></th>
			<td>
				<select class="form-control" name="is_sale">
					<option value="">请选择</option>
					<option value="1" selected="selected">批发</option>
					<option value="0">零售</option>
					<option value="3">帽子</option>
					<option value="10">定制</option>
				</select>
			</td>
			<td></td>
		</tr>
		<tr class="saas hide">
			<th>是否需要斗篷<span style="color:red;">*</span></th>
			<td>
				<input type="radio" name="need_cloak" value="1" checked="checked"> 是 &nbsp; &nbsp;
				<input type="radio" name="need_cloak" value="0"> 否
			</td>
			<td></td>
		</tr>
		<tr class="saas hide">
			<th>订单前缀<span style="color:red;">*</span></th>
			<td>
				<input class="form-control verify-repeat" type="text" name="order_no_prefix" value="<?php echo $order_no_prefix;?>" placeholder="订单前缀">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas hide">
			<th>被复制网站<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="copied_website" placeholder="被复制网站">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas hide">
			<th>客服邮箱<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="customer_service_email">
					<?php foreach ($customer_service_email_array as $v){?>
					<option value="<?php echo $v;?>"<?php if($customer_service_email_selected == $v){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
				<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
				<input type="text" value="<?php echo $customer_service_email_selected;?>">
				<?php }?>
			</td>
			<td>
				<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
				<?php }?>
			</td>
		</tr>
		<tr>
			<th>域名邮箱<span style="color:red;">*</span></th>
			<td>
				<input class="form-control verify-repeat" type="text" name="domain_email" placeholder="域名邮箱" required="required">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>域名邮箱密码<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="domain_email_password" placeholder="域名邮箱密码" required="required">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>登录邮箱时验证手机号码</th>
			<td>
				<input class="form-control" type="text" name="verify_email_phone" placeholder="登录邮箱时验证手机号码">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>手机号码所有人</th>
			<td>
				<input class="form-control" type="text" name="phone_owners" placeholder="手机号码所有人">
			</td>
			<td></td>
		</tr>
		<tr>
			<th>登录邮箱时验证邮箱</th>
			<td>
				<input class="form-control" type="text" name="verify_email" placeholder="登录邮箱时验证邮箱">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>验证邮箱密码</th>
			<td>
				<input class="form-control" type="text" name="verify_email_password" placeholder="验证邮箱密码">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>备注</th>
			<td>
				<textarea class="form-control" name="remark"></textarea>
			</td>
			<td></td>
		</tr>
		<tr>
			<td colspan="3" class="text-center">
				<a class="btn btn-default" href="<?php echo isset($_SESSION['checked_status']) ? U('Domains/Domains/index',array('status'=>$_SESSION['checked_status'])) : U('Domains/Domains/index'); ?>">返回</a>&nbsp;&nbsp;
				<button class="btn btn-default" type="submit">保存</button>
			</td>
		</tr>
	</table>
</form>
<script>
	$('#add-domain-form select[name="site_type"]').change(function(){
		if($(this).val() == 1 || $(this).val() == 10){
			$('#add-domain-form .saas input[type="text"],#add-domain-form .saas select').attr('required','required');
			$('#add-domain-form .saas').removeClass('hide');
			$('#add-domain-form input[name="order_no_prefix"]').addClass('verify-repeat');
		}else{
			$('#add-domain-form .saas').addClass('hide');
			$('#add-domain-form .saas input[type="text"],#add-domain-form .saas select').removeAttr('required','required');
			$('#add-domain-form input[name="order_no_prefix"]').removeClass('verify-repeat');
		}
		if($(this).val() == 11){
			$('#add-domain-form .verify').removeClass('hide');
			$('#add-domain-form tr.verify input[name="domain_name[]"]').addClass('verify-repeat');
		}else{
			$('#add-domain-form .verify').addClass('hide');
			$('#add-domain-form tr.verify input[name="domain_name[]"]').removeClass('verify-repeat');
		}
	});
	$('#add-domain-form .copy').click(function(){
		var copyCon = $(this).parent('td').prev('td').find('input[type="text"]');
		copyCon.select();
		document.execCommand("Copy");
	});
	$('#add-domain-form .add-domain').click(function(){
		var html = '<tr class="verify">'
			+ '<th></th>'
			+ '<td>'
			+ '<input class="form-control verify-repeat" type="text" name="domain_name[]" placeholder="域名">'
			+ '</td>'
			+ '<td>'
			+ '<a href="javascript:;" class="btn btn-default remove-domain">删除域名</a>'
			+ '</td>'
			+ '</tr>';
		$(this).parents('tbody').find('.saas:first').before(html);
	});
	$('#add-domain-form').on('click','.remove-domain',function(){
		$(this).parent().parent().remove();
	});
	function check(){
		$('#add-domain-form [type="submit"]').attr('disabled','disabled');
		var error = false;
		$('#add-domain-form .verify-repeat').each(function(){
			var $this = $(this);
			$.ajax({
				url : "<?php echo U('Domains/Domains/verify_repeat')?>",
				data : {'domain_id':0, 'site_id':0, 'field':$this.attr('name'), 'value':$this.val()},
				type : 'post',
				dataType : 'json',
				async: false,
				success : function(data){
					if(data.status != 1){
						error = true;
						layer.msg(data.info);
					}
				}
			});
			if(error){
				$('#add-domain-form [type="submit"]').removeAttr('disabled');
				return false;
			}
		});
		if(error){
			return false;
		}else{
			return true;
		}
	}
</script>