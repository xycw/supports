<?php
namespace Domains\Controller;
use Common\Controller\CommonController;

class PromotionController extends CommonController {

	function __construct() {
		parent::__construct();
		if(session(C('USER_INFO') . '.profile_id') != 1){
			$this->display('Common@Common/no_permission');
			exit;
		}
	}

	public function indexAction(){
		$list = M('PromotionDepartment')->order('department_id')->select();
		foreach ($list as $k=>$v){
			$list[$k]['members'] = M('PromotionDepartmentMembers')->alias('p')->join('LEFT JOIN __USERS__ u ON p.user_id=u.user_id')->join('LEFT JOIN __USERS__ us ON p.sales_id=us.user_id')->field('p.*,u.chinese_name,us.chinese_name AS sales_name')->where('p.department_id=' . $v['department_id'])->order('p.leader DESC,p.id')->select();
		}
		$this->assign('list', $list);
		$this->display();
	}

	public function addAction(){
		$this->assign('form_title', '添加部门');
		$system_area_array = explode('|',C('system_area'));
		$this->assign('system_area_array', $system_area_array);
		$this->display('edit');
	}

	public function editAction(){
		$department_id = I('department_id', 0);
		$promotion_department_model = M('PromotionDepartment');
		if (IS_POST) {
			$order_ascription = I('order_ascription', '');
			$data = array(
				'department_name' => trim(I('department_name', '')),
				'order_ascription' => $order_ascription
			);
			if($department_id > 0){
				$promotion_department_model->save($data,array('where' => array('department_id' => $department_id)));
				M('Site')->where(array('system_depart' => $department_id))->save(array('system_area' => $order_ascription));
			}else{
				$promotion_department_model->add($data);
			}
			$this->success('保存成功', 'index');
		}
		$info = $promotion_department_model->where(array('department_id' => $department_id))->find();
		$system_area_array = explode('|',C('system_area'));
		$this->assign('form_title', '修改部门');
		$this->assign('info', $info);
		$this->assign('system_area_array', $system_area_array);
		$this->display();
	}

	public function delAction($department_id){
		$count = M('Site')->where(array('system_depart' => $department_id, 'status' => 1))->count();
		if($count > 0) $this->error('还有归属此部门的网站，请转移网站后再删除！');
		M('PromotionDepartment')->where(array('department_id' => $department_id))->delete();
		$this->success('删除成功', U('Domains/Promotion/index'));
	}

	public function addMembersAction($department_id){
		$users_array = M('Users')->alias('u')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT_MEMBERS__ p ON u.user_id=p.user_id'))->where(array('p.user_id' => array('EXP','IS NULL'),'u.user_profile' => array('IN',array(2,3)),'u.status' => 1))->order('u.user_profile DESC,u.user_id')->getField('u.user_id,u.chinese_name',true);
		$sales_array = M('Users')->where('user_profile=2')->order('user_id')->getField('user_id,chinese_name',true);
		$customer_service_array = M('CustomerService')->order('id')->getField('email',true);
		$this->assign('form_title', '添加部门成员');
		$this->assign('department_id', $department_id);
		$this->assign('users_array', $users_array);
		$this->assign('sales_array', $sales_array);
		$this->assign('customer_service_array', $customer_service_array);
		$this->display('editMembers');
	}

	public function editMembersAction(){
		$id = I('id', 0);
		$promotion_department_members_model = M('PromotionDepartmentMembers');
		if (IS_POST) {
			$department_id = I('department_id', 0);
			$leader = I('leader', 0);
			if($leader == 1) $promotion_department_members_model->save(array('leader' => 0),array('where' => array('department_id' => $department_id)));
			$user_id = I('user_id', 0);
			$sales_id = I('sales_id', 0);
			$customer_service_email = trim(I('customer_service_email', ''));
			$data = array(
				'department_id' => $department_id,
				'user_id' => $user_id,
				'order_no_prefix' => trim(I('order_no_prefix', '')),
				'sales_id' => $sales_id,
				'customer_service_email' => $customer_service_email,
				'leader' => $leader
			);
			if($id > 0){
				$promotion_department_members_model->save($data,array('where' => array('id' => $id)));
			}else{
				$promotion_department_members_model->add($data);
			}
			$site_model = M('Site');
			if(I('synchro_sales_id') == 1){
				$site_id_array = $site_model->where('system_tuiguangy=' . $user_id)->getField('site_id',true);
				if(!empty($site_id_array)){
					$sales_id_array = M('Users')->where(array('user_profile' => 2))->getField('user_id',true);
					if(in_array($user_id, $sales_id_array)) $sales_id_array = array_diff($sales_id_array, array($user_id));
					M('UsersToSite')->where(array('user_id' => array('in',$sales_id_array), 'site_id' => array('in',$site_id_array)))->delete();
					if($sales_id != $user_id){
						$users_to_site_data = array();
						foreach ($site_id_array as $site_id){
							$users_to_site_data[] = array('user_id' => $sales_id, 'site_id' => $site_id);
						}
						M('UsersToSite')->addAll($users_to_site_data);
					}
				}
			}
			if(I('synchro_customer_service_email') == 1){
				Vendor('phpRPC.phprpc_client');
				$customer_service_info = M('CustomerService')->where(array('email'=>$customer_service_email))->find();
				$site_list = $site_model->field('site_id,site_name,site_interface,type,email_data,customer_service_name,system_cms')->where(array('status' => 1,'system_tuiguangy' => $user_id))->select();
				$error = array();
				foreach ($site_list as $site_info){
					$old_email_data = $email_data = json_decode($site_info['email_data'], true);
					$email_data[0] = array(
						'address' => $customer_service_info['email'],
						'password' => $customer_service_info['email_password'],
						'smtp' => $customer_service_info['email_smtp'],
						'port' => $customer_service_info['email_port']
					);
					$email_data = json_encode($email_data);
					$is_call_api = false;
					if($site_info['type'] == 1 && $site_info['system_cms'] == 'easyshop') $is_call_api = true;
					$save_data = array();
					$call_api_sql = '';
					if($email_data != $site_info['email_data']){
						$save_data['email_data'] = $email_data;
						if($is_call_api){
							if(!isset($old_email_data[0]['address']) ||$customer_service_info['email'] != $old_email_data[0]['address']){
								$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='STORE_EMAIL';";
								$call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='SEND_EMAIL_ACCOUNT';";
							}
							if(!isset($old_email_data[0]['password']) ||$customer_service_info['email_password'] != $old_email_data[0]['password']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_password'] . "' WHERE `configuration_key`='SEND_EMAIL_PASSWORD';";
							if(!isset($old_email_data[0]['smtp']) ||$customer_service_info['email_smtp'] != $old_email_data[0]['smtp']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_smtp'] . "' WHERE `configuration_key`='SEND_EMAIL_HOST';";
							if(!isset($old_email_data[0]['port']) ||$customer_service_info['email_port'] != $old_email_data[0]['port']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_port'] . "' WHERE `configuration_key`='SEND_EMAIL_PORT';";
						}
					}
					if($customer_service_info['nickname'] != $site_info['customer_service_name']){
						$save_data['customer_service_name'] = $customer_service_info['nickname'];
						if($is_call_api) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['nickname'] . "' WHERE `configuration_key`='SERVICE_NAME';";
					}
					if(count($save_data) > 0){
						$site_operation = true;
						if($call_api_sql != ''){
							Vendor('phpRPC.phprpc_client');
							$client = new \PHPRPC_Client($site_info['site_interface'].'?m=Server&c=Table');
							$result = $client->exeSql($call_api_sql);
							if(is_object($result) && get_class($result) == 'PHPRPC_Error'){
								$site_operation = false;
								$error[] = $site_info['site_name'] . '后台邮箱设置修改失败：' . $result->Message;
							}
						}
						if($site_operation){
							$r = $site_model->save($save_data, array('where' => array('site_id' => $site_info['site_id'])));
							if(!r) $error[] = $site_info['site_name'] . '邮箱设置修改失败';
						}
					}
				}
				M('Domains')->where(array('user_id' => $user_id,'site_type' => array('in', array(1,10))))->save(array('customer_service_email' => $customer_service_email));
				if(count($error) > 0){
					$message = implode('<br>', $error);
					$this->error($message, '', intval(strlen($message)/10));
				}
			}
			$this->success('保存成功', 'index');
		}
		$info = $promotion_department_members_model->where(array('id'=>$id))->find();
		$map = array('_complex' => array('p.user_id' => array('EXP','IS NULL'),'u.user_profile' => array('IN',array(2,3)),'u.status' => 1));
		$map['p.user_id'] = $info['user_id'];
		$map['_logic'] = 'or';
		$users_array = M('Users')->alias('u')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT_MEMBERS__ p ON u.user_id=p.user_id'))->where($map)->order('u.user_profile DESC,u.user_id')->getField('u.user_id,u.chinese_name',true);
		$sales_array = M('Users')->where('user_profile=2')->getField('user_id,chinese_name',true);
		$customer_service_array = M('CustomerService')->order('id')->getField('email',true);
		$this->assign('form_title', '修改部门成员');
		$this->assign('info', $info);
		$this->assign('users_array', $users_array);
		$this->assign('sales_array', $sales_array);
		$this->assign('customer_service_array', $customer_service_array);
		$this->display();
	}

	public function transferSiteAction(){
		if (IS_POST) {
			$user_id = I('user_id', 0);
			$system_tuiguangy = I('system_tuiguangy', 0);
			M('Domains')->where(array('user_id' => $user_id))->save(array('user_id' => $system_tuiguangy));
			$site_id_array = M('Site')->where('system_tuiguangy=' . $user_id)->getField('site_id',true);
			if(!empty($site_id_array)){
				$department_info = M('PromotionDepartmentMembers')->alias('p')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT__ pd ON p.department_id=pd.department_id'))->field('p.department_id,pd.order_ascription')->where(array('p.user_id' => $system_tuiguangy))->find();
				M('Site')->where(array('site_id' => array('in',$site_id_array)))->save(array('system_area' => $department_info['order_ascription'],'system_depart' => $department_info['department_id'],'system_tuiguangy' => $system_tuiguangy));
				$users_to_site_model = M('UsersToSite');
				$user_profile = M('Users')->where(array('user_id' => $user_id))->getField('user_profile');
				if($user_profile == 3) $users_to_site_model->where(array('user_id' => $user_id, 'site_id' => array('in',$site_id_array)))->delete();
				$users_to_site = $users_to_site_model->where(array('site_id' => array('in',$site_id_array)))->select();
				$users_to_site_data = array();
				foreach ($site_id_array as $site_id){
					$exist = false;
					foreach ($users_to_site as $k => $v){
						if($system_tuiguangy == $v['user_id'] && $site_id == $v['site_id']){
							$exist = true;
							unset($users_to_site[$k]);
							break;
						}
					}
					if(!$exist) $users_to_site_data[] = array('user_id' => $system_tuiguangy, 'site_id' => $site_id);
				}
				$users_to_site_model->addAll($users_to_site_data);
			}
			$this->success('转移网站成功', 'index');
		}
		$id = I('id', 0);
		$members_array = M('PromotionDepartmentMembers')->alias('p')->join(array('__USERS__ u ON p.user_id=u.user_id'))->getField('p.id,p.user_id,u.chinese_name',true);
		$this->assign('id', $id);
		$this->assign('members_array', $members_array);
		$this->display();
	}

	public function delMembersAction($id){
		$info = M('PromotionDepartmentMembers')->field('department_id,leader')->where(array('id' => $id))->find();
		if($info['leader'] == 1){
			$count = M('PromotionDepartmentMembers')->where(array('department_id' => $info['department_id']))->count();
			if($count > 1) $this->error('此推广人员为组长，请重新设置组长后再删除！');
		}
		$count = M('PromotionDepartmentMembers')->alias('p')->join(array('__SITE__ s ON p.user_id=s.system_tuiguangy'))->where(array('p.id' => $id, 's.status' => 1))->count();
		if($count > 0) $this->error('还有归属此推广人员的网站，请转移网站后再删除！');
		M('PromotionDepartmentMembers')->where(array('id' => $id))->delete();
		$this->success('删除成功', U('Domains/Promotion/index'));
	}
}