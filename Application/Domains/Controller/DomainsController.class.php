<?php
namespace Domains\Controller;
use Common\Controller\CommonController;

class DomainsController extends CommonController {
	private $members_info = array();

	function __construct() {
		parent::__construct();
		$no_permission = false;
		if(!in_array(session(C('USER_INFO') . '.profile_id'), array(1,2,3))){
			$no_permission = true;
		}elseif(in_array(session(C('USER_INFO') . '.profile_id'), array(2,3))){
			$members_info = M('PromotionDepartmentMembers')->where('user_id=' . session(C('USER_INFO') . '.user_id'))->field('department_id,leader')->find();
			if(!$members_info) $no_permission = true;
			$this->members_info = $members_info;
		}
		if($no_permission){
			$this->display('Common@Common/no_permission');
			exit;
		}
	}

	public function indexAction(){
		$where = array();
		if(session(C('USER_INFO') . '.profile_id') != 1){
			$where['d.user_id'] = session(C('USER_INFO') . '.user_id');
			if($this->members_info['leader'] == 1){
				$department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=' . $this->members_info['department_id'])->getField('user_id',true);
				if($this->members_info['department_id'] == 1){
					$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=20')->getField('user_id',true);
					if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
				}elseif($this->members_info['department_id'] == 12){
					$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=22')->getField('user_id',true);
					if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
				}elseif($this->members_info['department_id'] == 11){
					$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=24')->getField('user_id',true);
					if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
				}
				$where['d.user_id'] = array('in',$department_members_user_id);
			}
		}
		$where['d.status'] = array('lt',5);
		if(isset($_GET['status'])){
			$status = intval(I('status'));
			if(in_array($status, array(0,1,2,3,4,5,6,7))) $where['d.status'] = $status;
			$_SESSION['checked_status'] = $status;
		}else{
			unset($_SESSION['checked_status']);
		}
		$list = M('Domains')->alias('d')->join(array('LEFT JOIN __USERS__ u ON d.user_id=u.user_id'))->field('d.status,d.site_id,d.domain_name,u.chinese_name,d.site_type,d.domain_email,d.expire_date,d.ssl_expire_date,d.domain_id')->where($where)->order('d.expire_date,d.domain_id')->select();
		$this->assign('list', $list);
		$this->display();
	}

	public function addAction(){
		$domains_model = M('Domains');
		if (IS_POST) {
			$datas = array();
			$domain_names = I('domain_name');
			foreach ($domain_names as $k => $domain_name){
				$domain_name = trim(str_replace('www.', '', strtolower($domain_name)));
				if(empty($domain_name)){
					unset($domain_names[$k]);
					continue;
				}
				$domain_names[$k] = $domain_name;
				$site_type = I('site_type');
				$domain_email = trim(I('domain_email'));
				$data = array(
					'user_id' => I('user_id'),
					'site_type' => $site_type,
					'domain_name' => $domain_name,
					'domain_email' => $domain_email,
					'domain_email_password' => trim(I('domain_email_password')),
					'verify_email_phone' => trim(I('verify_email_phone')),
					'phone_owners' => trim(I('phone_owners')),
					'verify_email' => trim(I('verify_email')),
					'verify_email_password' => trim(I('verify_email_password')),
					'remark' => trim(I('remark'))
				);
				if(in_array($site_type, array(1,10))){
					$data['is_sale'] = I('is_sale');
					$data['need_cloak'] = I('need_cloak');
					$data['order_no_prefix'] = trim(I('order_no_prefix'));
					$data['copied_website'] = trim(I('copied_website'));
					$data['customer_service_email'] = trim(I('customer_service_email'));
				}
				$domain_email_exist_info = $domains_model->where("domain_email='" . $domain_email . "'")->find();
				if(!empty($domain_email_exist_info)){
					if(empty($data['domain_email_password']) && !empty($domain_email_exist_info['domain_email_password'])) $data['domain_email_password'] = $domain_email_exist_info['domain_email_password'];
					if(empty($data['verify_email_phone']) && !empty($domain_email_exist_info['verify_email_phone'])) $data['verify_email_phone'] = $domain_email_exist_info['verify_email_phone'];
					if(empty($data['phone_owners']) && !empty($domain_email_exist_info['phone_owners'])) $data['phone_owners'] = $domain_email_exist_info['phone_owners'];
					if(empty($data['verify_email']) && !empty($domain_email_exist_info['verify_email'])) $data['verify_email'] = $domain_email_exist_info['verify_email'];
					if(empty($data['verify_email_password']) && !empty($domain_email_exist_info['verify_email_password'])) $data['verify_email_password'] = $domain_email_exist_info['verify_email_password'];
					if(empty($data['domain_name_agent']) && !empty($domain_email_exist_info['domain_name_agent'])) $data['domain_name_agent'] = $domain_email_exist_info['domain_name_agent'];
					if(empty($data['registered_account']) && !empty($domain_email_exist_info['registered_account'])) $data['registered_account'] = $domain_email_exist_info['registered_account'];
					if(empty($data['registered_password']) && !empty($domain_email_exist_info['registered_password'])) $data['registered_password'] = $domain_email_exist_info['registered_password'];
				}
				$datas[] = $data;
			}
			if($domains_model->addAll($datas)){
				$super_administrator_info = M('Users')->field('chinese_name,email')->where('user_id=1')->find();
				$send_mail_result = array('status' => 0);
				if(!empty($super_administrator_info['email'])) $send_mail_result = send_mail($super_administrator_info['email'], $super_administrator_info['chinese_name'], '新建网站', '域名：' . implode('，', $domain_names) . ' 需要新建网站，请及时处理！');
				if($send_mail_result['status'] == 1){
					$this->success('添加成功，已发送邮件通知技术人员处理！', 'index');
				}else{
					$this->success('添加成功，通知邮件发送失败，请及时通知技术人员处理！', 'index');
				}
			}else{
				$this->error('添加失败！');
			}
		}
		$this->assign('form_title', '添加域名');
		$users_array = M('PromotionDepartmentMembers')->alias('p')->join(array('__USERS__ u ON p.user_id=u.user_id'))->where(array('u.status' => 1))->order('p.department_id,p.leader DESC')->getField('p.user_id,u.chinese_name',true);
		$this->assign('users_array',$users_array);
		$site_type_array = array('1'=>'独立站','10'=>'商城站','11'=>'验证站','12'=>'Facebook小组跳转站','13'=>'其他');
		if(session(C('USER_INFO') . '.user_id') == 1){
			$site_type_array['2'] = 'B站';
		}
		$this->assign('site_type_array',$site_type_array);
		$customer_service_email_array = M('CustomerService')->order('id')->getField('email',true);
		$this->assign('customer_service_email_array',$customer_service_email_array);
		$member_info = M('PromotionDepartmentMembers')->field('order_no_prefix,customer_service_email')->where('user_id=' . session(C('USER_INFO') . '.user_id'))->find();
		$order_no_prefix = '';
		if(isset($member_info['order_no_prefix']) && !empty($member_info['order_no_prefix'])){
			for($letter = 'A'; $letter <= 'ZZ'; $letter++){
				$order_no_prefix = $member_info['order_no_prefix'] . $letter;
				$exist = $domains_model->where("order_no_prefix='" . $order_no_prefix . "'")->field('domain_id')->find();
				if(!$exist) break;
			}
		}
		$this->assign('order_no_prefix',$order_no_prefix);
		$this->assign('customer_service_email_selected',isset($member_info['customer_service_email']) ? $member_info['customer_service_email'] : '');
		$this->display();
	}

	public function editAction(){
		$domain_id = I('domain_id');
		$domains_model = M('Domains');
		$domains_info = $domains_model->field('user_id,status')->where('domain_id=' . $domain_id)->find();
		if(empty($domains_info)) $this->error('域名不存在！');
		if(session(C('USER_INFO') . '.profile_id') != 1){
			if(session(C('USER_INFO') . '.user_id') != $domains_info['user_id']){
				$no_permission = false;
				if($this->members_info['leader'] == 1){
					$department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=' . $this->members_info['department_id'])->getField('user_id',true);
					if($this->members_info['department_id'] == 1){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=20')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}elseif($this->members_info['department_id'] == 12){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=22')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}elseif($this->members_info['department_id'] == 11){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=24')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}
					if(!in_array($domains_info['user_id'], $department_members_user_id)) $no_permission = true;
				}else{
					$no_permission = true;
				}
				if($no_permission){
					$this->display('Common@Common/no_permission');
					exit;
				}
			}
		}
		if (IS_POST) {
			$user_id = I('user_id');
			$site_type = I('site_type');
			$domain_email = trim(I('domain_email'));
			$data = array(
				'user_id' => $user_id,
				'site_type' => $site_type,
				'domain_name' => trim(I('domain_name')),
				'domain_email' => $domain_email,
				'remark' => trim(I('remark'))
			);
			if(session(C('USER_INFO') . '.user_id') == 1){
				$domain_name_agent = trim(I('domain_name_agent'));
				$data['domain_name_agent'] = $domain_name_agent;
				$expire_date = trim(I('expire_date'));
				$expire_date = empty($expire_date) ? date('Y-m-d', strtotime("+1 year")) : date('Y-m-d',strtotime($expire_date));
				$ssl_expire_date = trim(I('ssl_expire_date'));
				$dns_agent = trim(I('dns_agent'));
				$dns_information = json_encode(I('dns_information'));
				$admin_information = json_encode(I('admin_information'));
				$data['expire_date'] = $expire_date;
				$data['ssl_expire_date'] = $ssl_expire_date;
				if(in_array($site_type, array(1,10))){
					$data['site_index'] = trim(I('site_index'));
				}
				if($site_type == 10){
					$data['saas_name'] = trim(I('saas_name'));
				}
				$status = I('status');
				if(in_array($status, array(1,3))) $data['status'] = $status;
			}
			if(in_array($site_type, array(1,10))){
				$data['is_sale'] = I('is_sale');
				$data['need_cloak'] = I('need_cloak');
				$data['order_no_prefix'] = trim(I('order_no_prefix'));
				$data['copied_website'] = trim(I('copied_website'));
				$data['customer_service_email'] = trim(I('customer_service_email'));
				if($site_type == 1 && session(C('USER_INFO') . '.user_id') == 1) $data['admin_information'] = $admin_information;
			}
			if(!in_array($site_type, array(10,11)) && session(C('USER_INFO') . '.user_id') == 1){
				$data['dns_agent'] = $dns_agent;
				$data['dns_information'] = $dns_information;
			}
			$common_data = array(
				'domain_email_password' => trim(I('domain_email_password')),
				'verify_email_phone' => trim(I('verify_email_phone')),
				'phone_owners' => trim(I('phone_owners')),
				'verify_email' => trim(I('verify_email')),
				'verify_email_password' => trim(I('verify_email_password'))
			);
			$domains_model->where('domain_id=' . $domain_id)->save($data);
			$domains_model->where("domain_email='" . $domain_email . "'")->save($common_data);
			if(session(C('USER_INFO') . '.user_id') == 1){
				$registered_account = trim(I('registered_account'));
				$common_data = array(
					'registered_account' => $registered_account,
					'registered_password' => trim(I('registered_password'))
				);
				if($domains_info['status'] == 0){
					$common_data['domain_name_agent'] = $domain_name_agent;
					$common_data['expire_date'] = $expire_date;
					$common_data['ssl_expire_date'] = $ssl_expire_date;
					$domains_model->where(array('status' => 0, 'domain_email' => $domain_email))->save($common_data);
				}else{
					$domains_model->where(array('domain_email' => $domain_email,'domain_name_agent' => $domain_name_agent))->save($common_data);
				}
			}
			$site_id = I('site_id');
			if($site_id > 0){
				if($user_id > 0) $department_info = M('PromotionDepartmentMembers')->alias('p')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT__ pd ON p.department_id=pd.department_id'))->field('p.department_id,pd.order_ascription')->where(array('p.user_id' => $user_id))->find();
				$site_data = array(
					'site_type' => $site_type,
					'system_area' => isset($department_info) ? $department_info['order_ascription'] : 0,
					'system_depart' => isset($department_info) ? $department_info['department_id'] : 0,
					'system_tuiguangy' => $user_id
				);
				if(session(C('USER_INFO') . '.user_id') == 1){
					$site_data['date_expired'] = $expire_date;
					$site_data['ssl_expired'] = $ssl_expire_date;
					$site_data['system_url'] = $domain_name_agent;
					$site_data['system_url_block'] = json_encode(array('system_url_username' => $registered_account, 'system_url_email' => $domain_email));
					if(!in_array($site_type,array(10,11))){
						$site_data['system_thirdgw'] = $dns_agent;
						$site_data['system_dns_block'] = $dns_information;
					}
				}
				M('Site')->where('site_id=' . $site_id)->save($site_data);
			}
			$jump_url = isset($_SESSION['checked_status']) ? U('Domains/Domains/index',array('status'=>$_SESSION['checked_status'])) : U('Domains/Domains/index');
			$this->success('编辑成功！', $jump_url);
		}
		$this->assign('form_title', '修改域名');
		$info = $domains_model->where('domain_id=' . $domain_id)->find();
		if(empty($info)) $this->error('域名不存在！');
		$users_array = M('PromotionDepartmentMembers')->alias('p')->join(array('__USERS__ u ON p.user_id=u.user_id'))->where(array('u.status' => 1))->order('p.department_id,p.leader DESC')->getField('p.user_id,u.chinese_name',true);
		$this->assign('users_array',$users_array);
		$site_type_array = array('1'=>'独立站','10'=>'商城站','11'=>'验证站','12'=>'Facebook小组跳转站','13'=>'其他');
		if($info['status'] > 0){
			$site_type_array['3'] = '跳转站';
		}
		if(session(C('USER_INFO') . '.user_id') == 1){
			$site_type_array['2'] = 'B站';
		}
		$this->assign('site_type_array',$site_type_array);
		$customer_service_email_array = M('CustomerService')->order('id')->getField('email',true);
		$this->assign('customer_service_email_array',$customer_service_email_array);
		$member_info = M('PromotionDepartmentMembers')->field('department_id,order_no_prefix,customer_service_email,leader')->where('user_id=' . $info['user_id'])->find();
		if($info['status'] == 0){
			if(empty($info['customer_service_email'])) $info['customer_service_email'] = $member_info['customer_service_email'];
			if(empty($info['order_no_prefix'])){
				$info['order_no_prefix'] = '';
				if(isset($member_info['order_no_prefix']) && !empty($member_info['order_no_prefix'])){
					for($letter = 'A'; $letter <= 'ZZ'; $letter++){
						$info['order_no_prefix'] = $member_info['order_no_prefix'] . $letter;
						$exist = $domains_model->where("order_no_prefix='" . $info['order_no_prefix'] . "' and status<6")->field('domain_id')->find();
						if(!$exist) break;
					}
				}
			}
		}
		$this->assign('info',$info);
		$this->assign('member_info',$member_info);
		$this->display();
	}

	public function verify_repeatAction(){
		$domain_id = I('domain_id',0);
		$site_id = I('site_id',0);
		$field = I('field');
		$value = trim(I('value'));
		if($field == 'domain_name[]' && M('Domains')->where(array('domain_id' => array('neq',$domain_id),'status' => array('lt',5),'domain_name' => $value))->count() > 0){
			echo json_encode(array('status' => 0,'info' => '域名：' . $value . '已存在，请重新输入！'));exit();
		}elseif($field == 'order_no_prefix' && M('Site')->where(array('site_id' => array('neq',$site_id),'status' => 1,'order_no_prefix' => $value))->count() > 0){
			echo json_encode(array('status' => 0,'info' => '订单前缀：' . $value . '已存在，请重新输入！'));exit();
		}elseif($field == 'domain_email'){
			if(M('Domains')->where(array('domain_id' => array('neq',$domain_id),'domain_email' => $value))->count() >= 10 && session(C('USER_INFO') . '.user_id') != 1){
				echo json_encode(array('status' => 0,'info' => '域名邮箱：' . $value . '绑定的账号购买了超过10个域名，请重新输入！'));exit();
			}
			if(strpos($value,'@qq.com') !== false){
				echo json_encode(array('status' => 0,'info' => '为了方便登录操作，域名邮箱不能是QQ邮箱，请重新输入！'));exit();
			}
			if(strpos($value,'@gmail.com') !== false){
				echo json_encode(array('status' => 0,'info' => '为了方便登录操作，域名邮箱不能是Gmail邮箱，请重新输入！'));exit();
			}
		}
		echo json_encode(array('status' => 1));exit();
	}

	public function built_siteAction(){
		if(session(C('USER_INFO') . '.profile_id') != 1){
			$this->display('Common@Common/no_permission');
			exit;
		}
		$domain_ids = I('domain_ids');
		$domains_model = M('Domains');
		$domain_list = $domains_model->alias('d')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT_MEMBERS__ p ON d.user_id=p.user_id','LEFT JOIN __PROMOTION_DEPARTMENT__ pd ON p.department_id=pd.department_id','LEFT JOIN __CUSTOMER_SERVICE__ c ON p.customer_service_email=c.email'))
			->field('d.user_id,d.domain_name,c.email,c.email_password,c.email_smtp,c.email_port,d.site_index,d.order_no_prefix,d.expire_date,d.ssl_expire_date,d.saas_name,d.site_type,c.nickname,d.is_sale,pd.order_ascription,p.department_id,d.domain_name_agent,d.registered_account,d.domain_email,d.dns_agent,d.dns_information,p.leader,d.domain_id,p.sales_id,d.admin_information')
			->where(array('d.domain_id' => array('in',explode(',', $domain_ids)),'d.status' => 0))->order('d.expire_date,d.domain_id')->select();
		$user_domains_array = $users_to_site_data = $email_body = array();
		$site_model = M('Site');
		Vendor('phpRPC.phprpc_client');
		$message = '设置为已建站成功！';
		foreach ($domain_list as $v){
			$user_domains_array[$v['user_id']][] = $v['domain_name'];
			if(in_array($v['site_type'], array(1,10)) && empty($v['site_id'])){
				$email_data = array(
					'address' => $v['email'],
					'password' => $v['email_password'],
					'smtp' => $v['email_smtp'],
					'port' => $v['email_port']
				);
				$site_data = array(
					'site_name' => $v['domain_name'],
					'site_index' => $v['site_index'],
					'img_url'=> 'http://' . $_SERVER['HTTP_HOST'] . '/images/',
					'order_no_prefix' => $v['order_no_prefix'],
					'site_interface' => $v['site_index'] . '/' . ($v['site_type'] == 1 ? 'cs2020pi/' : 'api_v1_orders/get'),
					'date_expired' => $v['expire_date'],
					'ssl_expired' => $v['ssl_expire_date'],
					'remark' => $v['site_type'] == 1 ? '' : $v['saas_name'],
					'type' => $v['site_type'],
					'email_data' => json_encode(array($email_data)),
					'customer_service_name' => $v['nickname'],
					'is_sale' => $v['is_sale'],
					'system_proupdate' => 'False',
					'system_area' => $v['order_ascription'],
					'system_brand' => 'Jersey',
					'system_depart' => $v['department_id'],
					'system_tuiguangy' => $v['user_id'],
					'system_url' => $v['domain_name_agent'],
					'system_url_block' => json_encode(array('system_url_username' => $v['registered_account'], 'system_url_email' => $v['domain_email'])),
					'system_thirdgw' => $v['dns_agent'],
					'system_dns_block' => $v['dns_information'],
					'system_cms' => $v['site_type'] == 1 ? 'easyshop' : 'SAAS'
				);
				$call_api_sql = '';
				if($v['site_type'] == 1){
					$admin_information = json_decode($v['admin_information'], true);
					$site_data['system_weburl'] = $admin_information['path'];
					$site_data['system_weblogin'] = $admin_information['username'];
					$site_data['system_webpass'] = $admin_information['password'];
					$email_body[$v['user_id']] .= '<br><br>后台地址：' . $v['site_index'] . '/' . $admin_information['path'] . '<br>斗篷后台地址：' . $v['site_index'] . '/' . $admin_information['path'] . '_zp<br>后台账号：' . $admin_information['username'] . '<br>后台密码：' . $admin_information['password'];
					if($site_data['system_cms'] == 'easyshop'){
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['email'] . "' WHERE `configuration_key`='STORE_EMAIL';";
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['email'] . "' WHERE `configuration_key`='SEND_EMAIL_ACCOUNT';";
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['email_password'] . "' WHERE `configuration_key`='SEND_EMAIL_PASSWORD';";
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['email_smtp'] . "' WHERE `configuration_key`='SEND_EMAIL_HOST';";
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['email_port'] . "' WHERE `configuration_key`='SEND_EMAIL_PORT';";
						$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $v['nickname'] . "' WHERE `configuration_key`='SERVICE_NAME';";
					}
				}
				if($v['site_type'] == 10) $site_data['new_saas'] = 1;
				if($exist_info = $site_model->field('site_id,site_name,site_index')->where(array('site_name' => $v['domain_name']))->find()){
					$time = time();
					$site_model->where(array('site_id' => $exist_info['site_id']))->save(array('site_name' => $exist_info['site_name'] . '（已删' . $time . '）','site_index' => $exist_info['site_index'] . '（已删' . $time . '）'));
				}
				$site_id = $site_model->add($site_data);
				if($v['leader'] != 1){
					if($v['user_id'] == 62){
						$leader_user_id = 45;
					}elseif($v['user_id'] == 70){
						$leader_user_id = 44;
					}else{
						$leader_user_id = M('PromotionDepartmentMembers')->where(array('department_id' => $v['department_id'],'leader' => 1))->getField('user_id');
					}
					$users_to_site_data[] = array('user_id' => $leader_user_id,'site_id' => $site_id);
				}
				$users_to_site_data[] = array('user_id' => $v['user_id'],'site_id' => $site_id);
				if($v['sales_id'] != $v['user_id']) $users_to_site_data[] = array('user_id' => $v['sales_id'],'site_id' => $site_id);
				$domains_model->where('domain_id=' . $v['domain_id'])->save(array('site_id' => $site_id, 'status' => 1));
				if($call_api_sql != ''){
					$client = new \PHPRPC_Client($site_data['site_interface'].'?m=Server&c=Table');
					$result = $client->exeSql($call_api_sql);
					if(is_object($result) && get_class($result) == 'PHPRPC_Error') $message .= '<br>' . $site_data['site_name'] . '后台邮箱设置修改失败：' . $result->Message . '<br>请登录网站后台进行修改！';
				}
			}else{
				if($v['site_type'] == 11) $email_body[$v['user_id']] .= '<br><br>后台地址：' . $v['domain_name'] . '/manager<br>后台账号：manager<br>后台密码：zp123456';
				$domains_model->where('domain_id=' . $v['domain_id'])->save(array('status' => 1));
			}
		}
		if(!empty($users_to_site_data)) M('UsersToSite')->addAll($users_to_site_data);
		foreach ($user_domains_array as $user_id => $domain_names){
			$user_info = M('Users')->field('chinese_name,email')->where('user_id=' . $user_id)->find();
			if(!empty($user_info)){
				if(empty($user_info['email'])){
					$message .= '<br>' . $user_info['chinese_name'] . '的邮箱为空';
				}else{
					$send_mail_result = send_mail($user_info['email'], $user_info['chinese_name'], '建站完成', '域名：' . implode('、', $domain_names) . ' 已完成建站！' . (isset($email_body[$user_id]) ? $email_body[$user_id] : ''));
					if($send_mail_result['status'] != 1) $message .= '<br>未能成功发送通知邮件给' . $user_info['chinese_name'];
				}
			}
		}
		$jump_url = isset($_SESSION['checked_status']) ? U('Domains/Domains/index',array('status'=>$_SESSION['checked_status'])) : U('Domains/Domains/index');
		$this->success($message, $jump_url, intval(strlen($message)/10));
	}

	public function change_allAction(){
		if(session(C('USER_INFO') . '.profile_id') != 1){
			$this->display('Common@Common/no_permission');
			exit;
		}
		$advance_date = intval(I('advance_date'));
		if($advance_date < 0) $advance_date = 0;
		$today = date("Y-m-d");
		$domains_model = M('Domains');
		$domains_model->where(array('status' => 1,'expire_date' => array('lt',date("Y-m-d",strtotime("+" . (8 + $advance_date) . " day")))))->save(array('status' => 2));
		$domains_model->where(array('status' => 2,'expire_date' => array('lt',$today)))->save(array('status' => 5));
		$domains_model->where(array('status' => 3,'expire_date' => array('lt',date("Y-m-d",strtotime("-10 day")))))->save(array('status' => 6));
		$domains_model->where(array('status' => 4,'expire_date' => array('lt',$today)))->save(array('status' => 6));
		$domains_model->where(array('status' => 5,'expire_date' => array('lt',date("Y-m-d",strtotime("-7 day")))))->save(array('status' => 6));
		$delete_site_id = $domains_model->where(array('site_id' => array('gt',0),'expire_date' => array('lt',$today),'status' => array('neq',3)))->getField('site_id',true);
		if(!empty($delete_site_id)){
			M('Site')->where(array('status' => array('neq',0),'site_id' => array('in',$delete_site_id)))->save(array('status' => 0));
			M('Crontab')->where(array('site_id' => array('in',$delete_site_id)))->delete();
		}
		$expire_list = $domains_model->field('user_id,domain_name,expire_date')->where(array('status' => 2))->select();
		$message = '操作成功！';
		$users_model = M('Users');
		if(!empty($expire_list)){
			$remind_date = date("Y-m-d",strtotime("+" . $advance_date . " day"));
			$user_array = $sales_array = array();
			foreach ($expire_list as $v){
				if(empty($v['user_id'])){
					$user_array[1][] = $v;
				}else{
					$user_array[$v['user_id']][] = $v;
					$sales_id = M('PromotionDepartmentMembers')->where(array('user_id' => $v['user_id']))->getField('sales_id');
					if($v['expire_date'] <= $remind_date && $v['user_id'] != $sales_id) $sales_array[$sales_id][] = $v;
				}
			}
			$send_user_id_array = array_keys($user_array);
			if(!in_array(1, $send_user_id_array)) $send_user_id_array[] = 1;
			if(!empty($sales_array)) $send_user_id_array = array_unique(array_merge($send_user_id_array,array_keys($sales_array)));;
			$send_user_array = $users_model->where(array('user_id' => array('in',$send_user_id_array)))->getField('user_id,chinese_name,email',true);
			foreach ($user_array as $user_id => $val){
				$user_info = isset($send_user_array[$user_id]) ? $send_user_array[$user_id] : $send_user_array[1];
				if(empty($user_info['email'])){
					$message .= '<br>' . $user_info['chinese_name'] . '的邮箱为空';
				}else{
					$content = array();
					foreach ($val as $v){
						$content[] = '域名：' . $v['domain_name'] . '将于' . $v['expire_date'] . '到期，';
					}
					$send_mail_result = send_mail($user_info['email'], $user_info['chinese_name'], '域名即将到期', implode('<br>',$content) . '请前往http://support.customize.company' . U('Domains/Domains/index',array('status'=>2)) . ' 确认是否续费！');
					if($send_mail_result['status'] != 1) $message .= '<br>未能成功发送通知邮件给' . $user_info['chinese_name'];
				}
			}
			if(!empty($sales_array)){
				foreach ($sales_array as $user_id => $val){
					$user_info = isset($send_user_array[$user_id]) ? $send_user_array[$user_id] : $send_user_array[1];
					if(empty($user_info['email'])){
						$message .= '<br>' . $user_info['chinese_name'] . '的邮箱为空';
					}else{
						$content = array();
						foreach ($val as $k => $v){
							$content[] = '域名：' . $v['domain_name'] . '将于' . $v['expire_date'] . '到期，请联系' . (isset($send_user_array[$v['user_id']]) ? $send_user_array[$v['user_id']]['chinese_name'] : $send_user_array[1]['chinese_name']) . '确认是否续费！';
						}
						$send_mail_result = send_mail($user_info['email'], $user_info['chinese_name'], '域名即将到期', implode('<br>',$content));
						if($send_mail_result['status'] != 1) $message .= '<br>未能成功发送通知邮件给' . $user_info['chinese_name'];
					}
				}
			}
		}
		$ssl_expire_list = $domains_model->field('domain_name,ssl_expire_date')->where('`status`=1 AND `ssl_expire_date`<DATE_SUB(`expire_date`, INTERVAL 5 DAY) AND `ssl_expire_date`<DATE_ADD(CURDATE(),INTERVAL 4 DAY)')->select();
		if(!empty($ssl_expire_list)){
			if(isset($send_user_array)){
				$super_administrator_info = $send_user_array[1];
			}else{
				$super_administrator_info = $users_model->field('chinese_name,email')->where('user_id=1')->find();
			}
			if(empty($super_administrator_info['email'])){
				$message .= '<br>' . $super_administrator_info['chinese_name'] . '的邮箱为空';
			}else{
				$content = array();
				foreach ($ssl_expire_list as $v){
					$content[] = '域名：' . $v['domain_name'] . '的SSL将于' . $v['ssl_expire_date'] . '到期';
				}
				$send_mail_result = send_mail($super_administrator_info['email'], $super_administrator_info['chinese_name'], 'SSL即将到期', implode('，',$content)) . '请续费！';
				if($send_mail_result['status'] != 1) $message .= '<br>未能成功发送通知邮件给' . $super_administrator_info['chinese_name'];
			}
		}
		$jump_url = isset($_SESSION['checked_status']) ? U('Domains/Domains/index',array('status'=>$_SESSION['checked_status'])) : U('Domains/Domains/index');
		$this->success($message, $jump_url);
	}

	public function change_statusAction(){
		$status = I('status');
		if(session(C('USER_INFO') . '.user_id') != 1 && !in_array($status, array(3,4,6))) $this->error('状态参数不正确！');
		$domain_id = I('domain_id',0);
		$domains_model = M('Domains');
		$domain_info = $domains_model->field('site_id,domain_name,site_type')->where('domain_id=' . $domain_id)->find();
		if(empty($domain_info['domain_name'])) $this->error('域名不存在！');
		if(session(C('USER_INFO') . '.profile_id') != 1){
			$domains_user_id = $domains_model->where('domain_id=' . $domain_id)->getField('user_id');
			if(session(C('USER_INFO') . '.user_id') != $domains_user_id){
				$no_permission = false;
				if($this->members_info['leader'] == 1){
					$department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=' . $this->members_info['department_id'])->getField('user_id',true);
					if($this->members_info['department_id'] == 1){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=20')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}elseif($this->members_info['department_id'] == 12){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=22')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}elseif($this->members_info['department_id'] == 11){
						$child_department_members_user_id = M('PromotionDepartmentMembers')->where('department_id=24')->getField('user_id',true);
						if(count($child_department_members_user_id) > 0) $department_members_user_id = array_merge($department_members_user_id,$child_department_members_user_id);
					}
					if(!in_array($domains_user_id, $department_members_user_id)) $no_permission = true;
				}else{
					$no_permission = true;
				}
				if($no_permission){
					$this->display('Common@Common/no_permission');
					exit;
				}
			}
		}
		$domains_model->where('domain_id=' . $domain_id)->save(array('status' => $status));
		$send_email = false;
		$message = '';
		if($status == 3){
			$send_email = true;
			$content = '待续费';
		}elseif(in_array($status, array(4,6)) && in_array($domain_info['site_type'], array(1,10))){
			$message = '请在网站删除前通知客服下载好客户资料。';
		}elseif($status == 7 && $domain_info['site_id'] > 0){
			M('Site')->where(array('site_id' => $domain_info['site_id']))->save(array('status' => 0));
			M('Crontab')->where(array('site_id' => $domain_info['site_id']))->delete();
		}
		$jump_url = isset($_SESSION['checked_status']) ? U('Domains/Domains/index',array('status'=>$_SESSION['checked_status'])) : U('Domains/Domains/index');
		if($send_email){
			$super_administrator_info = M('Users')->field('chinese_name,email')->where('user_id=1')->find();
			$send_mail_result = array('status' => 0);
			if(!empty($super_administrator_info['email'])) $send_mail_result = send_mail($super_administrator_info['email'], $super_administrator_info['chinese_name'], $content, '域名：' . $domain_info['domain_name'] . ' ' . $content . '，请及时处理！');
			if($send_mail_result['status'] == 1){
				$this->success('更改状态成功，已发送邮件通知技术人员处理！', $jump_url);
			}else{
				$this->success('更改状态成功，通知邮件发送失败，请及时通知技术人员处理！', $jump_url);
			}
		}
		$this->success('更改状态成功！' . $message, $jump_url);
	}
}