<load href="__PUBLIC__/Js/bootstrap-select/css/bootstrap-select.css" />
<load href="__PUBLIC__/Js/bootstrap-select/js/bootstrap-select.js" />
<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Domains/Domains/edit') ?>" method="post" id="add-domain-form" onsubmit="javascript:return check()">
	<input type="hidden" name="domain_id" value="<?php echo $info['domain_id'];?>">
	<input type="hidden" name="site_id" value="<?php echo $info['site_id'];?>">
	<table class="table table-bordered">
		<tr>
			<th>域名所属人</th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="user_id">
					<option value="">请选择</option>
					<?php foreach ($users_array as $k => $v){?>
					<option value="<?php echo $k;?>"<?php if($info['user_id'] == $k){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
			<td></td>
		</tr>
		<?php if($info['status'] == 0 || in_array($info['site_type'], array_keys($site_type_array))){?>
		<tr>
			<th>网站类型<span style="color:red;">*</span></th>
			<td>
				<select class="form-control" name="site_type" required="required"<?php if($info['status'] > 0){?> readonly="readonly"<?php }?>>
					<option value="">请选择</option>
					<?php foreach ($site_type_array as $k => $v){?>
					<option value="<?php echo $k;?>"<?php if($info['site_type'] == $k){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</td>
			<td></td>
		</tr>
		<?php }else{?>
		<input type="hidden" name="site_type" value="<?php echo $info['site_type'];?>">
		<?php }?>
		<tr>
			<th>域名（请前往<a href="https://namecheap.com" target="_blank">namecheap.com</a>搜索域名是否可以购买以及价格是否合适）<span style="color:red;">*</span></th>
			<td>
				<input class="form-control<?php if($info['status'] == 0){?> verify-repeat<?php }?>" type="text" name="domain_name" placeholder="域名" required="required"<?php if($info['status'] > 0){?> readonly="readonly"<?php }?> value="<?php echo $info['domain_name'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>商品数据<span style="color:red;">*</span></th>
			<td>
				<select class="form-control" name="is_sale"<?php if(in_array($info['site_type'], array(1,10))){?> required="required"<?php }?><?php if($info['status'] > 0){?> readonly="readonly"<?php }?>>
					<option value="">请选择</option>
					<option value="1"<?php if($info['is_sale'] == 1){?> selected="selected"<?php }?>>批发</option>
					<option value="0"<?php if($info['is_sale'] == 0){?> selected="selected"<?php }?>>零售</option>
					<option value="3"<?php if($info['is_sale'] == 3){?> selected="selected"<?php }?>>帽子</option>
					<option value="10"<?php if($info['is_sale'] == 10){?> selected="selected"<?php }?>>定制</option>
				</select>
			</td>
			<td></td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>是否需要斗篷<span style="color:red;">*</span></th>
			<td>
				<input type="radio" name="need_cloak" value="1"<?php if($info['need_cloak'] == 1){?> checked="checked"<?php }?>> 是 &nbsp; &nbsp;
				<input type="radio" name="need_cloak" value="0"<?php if($info['need_cloak'] == 0){?> checked="checked"<?php }?>> 否
			</td>
			<td></td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>订单前缀<span style="color:red;">*</span></th>
			<td>
				<input class="form-control<?php if(in_array($info['site_type'], array(1,10)) && $info['status'] == 0){?> verify-repeat<?php }?>" type="text" name="order_no_prefix" value="<?php echo $info['order_no_prefix'];?>" placeholder="订单前缀"<?php if(in_array($info['site_type'], array(1,10))){?> required="required"<?php }?><?php if($info['status'] > 0){?> readonly="readonly"<?php }?>>
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>被复制网站<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="copied_website" placeholder="被复制网站"<?php if(in_array($info['site_type'], array(1,10))){?> required="required"<?php }?><?php if($info['status'] > 0){?> readonly="readonly"<?php }?> value="<?php echo $info['copied_website'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>客服邮箱<span style="color:red;">*</span></th>
			<td>
				<select class="form-control selectpicker" data-live-search="true" name="customer_service_email"<?php if(in_array($info['site_type'], array(1,10))){?> required="required"<?php }?><?php if($info['status'] > 0){?> readonly="readonly"<?php }?>>
					<?php foreach ($customer_service_email_array as $v){?>
					<option value="<?php echo $v;?>"<?php if($info['customer_service_email'] == $v){?> selected="selected"<?php }?>><?php echo $v;?></option>
					<?php }?>
				</select>
				<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
				<input type="text" value="<?php echo $info['customer_service_email'];?>">
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
				<input class="form-control<?php if($info['status'] == 0){?> verify-repeat<?php }?>" type="text" name="domain_email" placeholder="域名邮箱" required="required"<?php if($info['status'] > 0 && !empty($info['domain_email'])){?> readonly="readonly"<?php }?> value="<?php echo $info['domain_email'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>域名邮箱密码<span style="color:red;">*</span></th>
			<td>
				<input class="form-control" type="text" name="domain_email_password" placeholder="域名邮箱密码" required="required" value="<?php echo $info['domain_email_password'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>登录邮箱时验证手机号码</th>
			<td>
				<input class="form-control" type="text" name="verify_email_phone" placeholder="登录邮箱时验证手机号码" value="<?php echo $info['verify_email_phone'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>手机号码所有人</th>
			<td>
				<input class="form-control" type="text" name="phone_owners" placeholder="手机号码所有人" value="<?php echo $info['phone_owners'];?>">
			</td>
			<td></td>
		</tr>
		<tr>
			<th>登录邮箱时验证邮箱</th>
			<td>
				<input class="form-control" type="text" name="verify_email" placeholder="登录邮箱时验证邮箱" value="<?php echo $info['verify_email'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>验证邮箱密码</th>
			<td>
				<input class="form-control" type="text" name="verify_email_password" placeholder="验证邮箱密码" value="<?php echo $info['verify_email_password'];?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<?php if(session(C('USER_INFO') . '.user_id') == 1){?>
		<tr>
			<th>域名代理商URL</th>
			<td>
				<input class="form-control" type="text" name="domain_name_agent" placeholder="域名代理商URL"<?php if($info['status'] > 0 && !empty($info['domain_name_agent'])){?> readonly="readonly"<?php }?> value="<?php echo $info['domain_name_agent'] ? $info['domain_name_agent'] : ($info['status'] == 0 ? 'www.namecheap.com' : '');?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>注册账号</th>
			<td>
				<input class="form-control" type="text" name="registered_account" placeholder="注册账号"<?php if($info['status'] > 0 && !empty($info['registered_account'])){?> readonly="readonly"<?php }?> value="<?php echo $info['registered_account'] ? $info['registered_account'] : ($info['status'] == 0 ? str_replace('.', '', $info['domain_name']) : '');?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>注册密码</th>
			<td>
				<input class="form-control" type="text" name="registered_password" placeholder="注册密码" value="<?php echo $info['registered_password'] ? $info['registered_password'] : ($info['status'] == 0 ? $info['domain_email_password'] : '');?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr>
			<th>域名到期日期</th>
			<td>
				<input class="form-control" type="text" name="expire_date" placeholder="域名到期日期" value="<?php echo $info['expire_date'] ? $info['expire_date'] : ($info['status'] == 0 ? date('Y-m-d', strtotime("+1 year")) : '');?>">
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<th>SSL到期日期</th>
			<td>
				<input class="form-control" type="text" name="ssl_expire_date" placeholder="SSL到期日期" value="<?php echo $info['ssl_expire_date'] ? $info['ssl_expire_date'] : ($info['status'] == 0 ? date('Y-m-d', strtotime("+1 year")) : '');?>">
			</td>
			<td>
			</td>
		</tr>
		<tr class="no-saas<?php if(in_array($info['site_type'], array(10,11))){?> hide<?php }?>">
			<th>第三方DNS信息</th>
			<td>
				<table class="table table-bordered">
					<tr><th>第三方DNS官网</th><th>第三方DNS</th><th>第三方DNS管理账号</th><th></th><th>第三方DNS管理密码</th><th></th></tr>
					<tr>
						<td>
							<input type="text" class="form-control" name="dns_agent" value="<?php echo $info['dns_agent'] ? $info['dns_agent'] : ($info['status'] == 0 ? 'www.cloudflare.com' : '');?>">
						</td>
						<?php
						if(empty($info['dns_information'])){
							$dns_information = array(
								'email_dns' => array('','','',''),
								'system_dns_username' => '',
								'system_dns_email' => ''
							);
						}else{
							$dns_information = json_decode($info['dns_information'], true);
						}
						?>
						<td>
							<table>
								<tr><th>dns1</th><td><input type="text" class="form-control" name="dns_information[email_dns][]" value="<?php echo $dns_information[email_dns][0];?>"></td></tr>
								<tr><th>dns2</th><td><input type="text" class="form-control" name="dns_information[email_dns][]" value="<?php echo $dns_information[email_dns][1];?>"></td></tr>
								<tr><th>dns3</th><td><input type="text" class="form-control" name="dns_information[email_dns][]" value="<?php echo $dns_information[email_dns][2];?>"></td></tr>
								<tr><th>dns4</th><td><input type="text" class="form-control" name="dns_information[email_dns][]" value="<?php echo $dns_information[email_dns][3];?>"></td></tr>
							</table>
						</td>
						<td><input type="text" class="form-control" name="dns_information[system_dns_username]" value="<?php echo $dns_information['system_dns_username'] ? $dns_information['system_dns_username'] : ($info['status'] == 0 ? $info['domain_email'] : '');?>"></td>
						<td>
							<a href="javascript:;" class="btn btn-default copy">点击复制</a>
						</td>
						<td><input type="text" class="form-control" name="dns_information[system_dns_email]" value="<?php echo $dns_information['system_dns_email'] ? $dns_information['system_dns_email'] : ($info['status'] == 0 ? $info['domain_email_password'] . '~' : '');?>"></td>
						<td>
							<a href="javascript:;" class="btn btn-default copy">点击复制</a>
						</td>
					</tr>
				</table>
			</td>
			<td></td>
		</tr>
		<tr class="saas<?php if(!in_array($info['site_type'], array(1,10))){?> hide<?php }?>">
			<th>网站首页</th>
			<td>
				<input class="form-control" type="text" name="site_index"<?php if($info['status'] > 0){?> readonly="readonly"<?php }?> placeholder="网站首页" value="<?php echo $info['site_index'] ? $info['site_index'] : ($info['status'] == 0 ? 'http' . ($info['site_type'] == 1 ? 's' : '') . '://www.' . $info['domain_name'] : '');?>">
			</td>
		</tr>
		<?php
		$saas_name = $info['saas_name'];
		if($info['status'] == 0 && $info['site_type'] == 10 && empty($saas_name)){
			$saas_name = M('Users')->where('user_id=' . $info['user_id'])->getField('chinese_name');
			if($member_info['leader'] != 1){
				if($info['user_id'] == 62){
					$saas_name .= '.' . M('Users')->where(array('user_id' => 45))->getField('chinese_name');
				}elseif($info['user_id'] == 70){
					$saas_name .= '.' . M('Users')->where(array('user_id' => 44))->getField('chinese_name');
				}else{
					$saas_name .= '.' . M('PromotionDepartmentMembers')->alias('p')->join('__USERS__ u ON p.user_id=u.user_id')->where(array('p.department_id' => $member_info['department_id'],'p.leader'=>1))->getField('chinese_name');
				}
			}
			$max_site_id = M('Site')->getField('max(site_id)');
			$site_id_exist = true;
			while($site_id_exist){
				$max_site_id++;
				$site_exist = M('Domains')->field('site_id')->where("saas_name like '%." . $max_site_id . "#%'")->find();
				if(empty($site_exist)) $site_id_exist = false;
			}
			$saas_name .= '.' . $max_site_id . '#';
			$prefix = 'W';
			if(isset($info['is_sale']) && $info['is_sale'] == 0){
				$prefix = 'R';
			}elseif($info['is_sale'] == 10){
				$prefix = 'C';
			}
			$saas_name .= $prefix . '-' . $info['order_no_prefix'];
		}
		?>
		<tr class="saas only-saas<?php if($info['site_type'] != 10){?> hide<?php }?>">
			<th>商城名称</th>
			<td>
				<input class="form-control" type="text" name="saas_name" value="<?php echo $saas_name;?>">
			</td>
			<td>
				<a href="javascript:;" class="btn btn-default copy">点击复制</a>
			</td>
		</tr>
		<tr class="indep<?php if($info['site_type'] != 1){?> hide<?php }?>">
			<th>后台信息</th>
			<td>
				<table class="table table-bordered">
					<tr><th>后台路径</th><th></th><th>后台账号</th><th></th><th>后台密码</th><th></th></tr>
					<tr>
						<?php
						function makecode($num=12) {
							$re ='';
							$s = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
							while(strlen($re)<$num) {
								$re .= $s[rand(0, strlen($s)-1)];
							}
							return $re;
						}
						if(empty($info['admin_information'])){
							$admin_information = array(
								'path' => makecode(8) . '-admin',
								'username' => makecode(),
								'password' => makecode()
							);
						}else{
							$admin_information = json_decode($info['admin_information'], true);
						}
						?>
						<td>
							<input type="text" class="form-control" name="admin_information[path]" value="<?php echo $admin_information['path'];?>">
						</td>
						<td>
							<a href="javascript:;" class="btn btn-default copy">点击复制</a>
						</td>
						<td>
							<input type="text" class="form-control" name="admin_information[username]" value="<?php echo $admin_information['username'];?>">
						</td>
						<td>
							<a href="javascript:;" class="btn btn-default copy">点击复制</a>
						</td>
						<td>
							<input type="text" class="form-control" name="admin_information[password]" value="<?php echo $admin_information['password'];?>">
						</td>
						<td>
							<a href="javascript:;" class="btn btn-default copy">点击复制</a>
						</td>
					</tr>
				</table>
			</td>
			<td></td>
		</tr>
		<?php if($info['status'] == 3){?>
		<tr>
			<th>状态</th>
			<td>
				<select class="form-control" name="status">
					<option value="1">已续费</option>
					<option value="3" selected="selected">待续费</option>
				</select>
			</td>
			<td>
			</td>
		</tr>
		<?php }?>
		<?php }?>
		<tr>
			<th>备注</th>
			<td>
				<textarea class="form-control" name="remark"><?php echo $info['remark'];?></textarea>
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
		$('#add-domain-form .indep').addClass('hide');
		if($(this).val() == 1 || $(this).val() == 10){
			$('#add-domain-form .saas input[type="text"],#add-domain-form .saas select').attr('required','required');
			$('#add-domain-form .saas').removeClass('hide');
			if($(this).val() == 1){
				$('#add-domain-form .only-saas').addClass('hide');
				$('#add-domain-form .only-saas input[type="text"]').removeAttr('required','required');
				$('#add-domain-form .no-saas,#add-domain-form .indep').removeClass('hide');
			}else{
				$('#add-domain-form .no-saas').addClass('hide');
			}
			$('#add-domain-form input[name="order_no_prefix"]').addClass('verify-repeat');
		}else if($(this).val() == 11){
			$('#add-domain-form .no-saas').addClass('hide');
			$('#add-domain-form .saas').addClass('hide');
			$('#add-domain-form .saas input[type="text"],#add-domain-form .saas select').removeAttr('required','required');
			$('#add-domain-form input[name="order_no_prefix"]').removeClass('verify-repeat');
		}else{
			$('#add-domain-form .no-saas').removeClass('hide');
			$('#add-domain-form .saas').addClass('hide');
			$('#add-domain-form .saas input[type="text"],#add-domain-form .saas select').removeAttr('required','required');
			$('#add-domain-form input[name="order_no_prefix"]').removeClass('verify-repeat');
		}
	});
	$('#add-domain-form .copy').click(function(){
		var copyCon = $(this).parent('td').prev('td').find('input[type="text"]');
		copyCon.select();
		document.execCommand("Copy");
	});
	function check(){
		$('#add-domain-form [type="submit"]').attr('disabled','disabled');
		var error = false;
		$('#add-domain-form .verify-repeat').each(function(){
			var $this = $(this);
			$.ajax({
				url : "<?php echo U('Domains/Domains/verify_repeat');?>",
				data : {'domain_id':<?php echo $info['domain_id'];?>, 'site_id':<?php echo $info['site_id'];?>, 'field':$this.attr('name'), 'value':$this.val()},
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