<?php
namespace CustomerService\Controller;
use Common\Controller\CommonController;

class CustomerServiceController extends CommonController {

	function __construct() {
		parent::__construct();
		if(!in_array(session(C('USER_INFO').'.profile_id'), array(1,2))){
			$this->display('Common@Common/no_permission');
			exit;
		}
	}

	public function indexAction(){
		$list = M('CustomerService')->order('id')->select();
		$this->assign('list', $list);
		$this->display();
	}

	public function addAction(){
		$this->assign('form_title', '添加客服');
		$info = array(
			'email_smtp' => 'smtp.gmail.com',
			'email_port' => '587'
		);
		$this->assign('info', $info);
		$this->display('edit');
	}

	public function editAction(){
		$id = I('id', 0);
		$customer_service_model = M('CustomerService');
		if (IS_POST) {
			$email = trim(I('email', ''));
			$email_exist = $customer_service_model->where(array('id' => array('neq', $id), 'email' => $email))->count();
			if($email_exist > 0) $this->error('系统已存在此客服邮箱!');
			$nickname = trim(I('nickname', ''));
			$email_password = trim(I('email_password', ''));
			$email_smtp = trim(I('email_smtp', ''));
			$email_port = trim(I('email_port', ''));
			$data = array(
				'nickname' => $nickname,
				'email' => $email,
				'email_password' => $email_password,
				'email_smtp' => $email_smtp,
				'email_port' => $email_port
			);
			if($id > 0){
				$customer_service_model->save($data,array('where' => array('id' => $id)));
			}else{
				$customer_service_model->add($data);
			}
			$site_model = M('Site');
			Vendor('phpRPC.phprpc_client');
			$site_list = $site_model->field('site_id,site_name,site_interface,type,email_data,customer_service_name,system_cms')->where(array('email_data' => array('like', '%"address":"' . $email . '"%')))->select();
			$error = array();
			foreach ($site_list as $site_info){
				$save_data = array();
				$old_email_data = $email_data = json_decode($site_info['email_data'], true);
				$call_api_sql = '';
				foreach ($old_email_data as $k=>$v){
					if($email == $old_email_data[$k]['address']){
						$email_data[$k]['password'] = $email_password;
						$email_data[$k]['smtp'] = $email_smtp;
						$email_data[$k]['port'] = $email_port;
						$is_call_api = false;
						if($k == 0 && $site_info['type'] == 1 && $site_info['system_cms'] == 'easyshop') $is_call_api = true;
						if($is_call_api){
							if($email_password != $old_email_data[$k]['password']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $email_password . "' WHERE `configuration_key`='SEND_EMAIL_PASSWORD';";
							if($email_smtp != $old_email_data[$k]['smtp']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $email_smtp . "' WHERE `configuration_key`='SEND_EMAIL_HOST';";
							if($email_port != $old_email_data[$k]['port']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $email_port . "' WHERE `configuration_key`='SEND_EMAIL_PORT';";
						}
						if($k == 0 && $nickname != $site_info['customer_service_name']){
							$save_data['customer_service_name'] = $nickname;
							if($is_call_api) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $nickname . "' WHERE `configuration_key`='SERVICE_NAME';";
						}
					}
				}
				$email_data = json_encode($email_data);
				if($email_data != $site_info['email_data']) $save_data['email_data'] = $email_data;
				if($call_api_sql != ''){
					Vendor('phpRPC.phprpc_client');
					$client = new \PHPRPC_Client($site_info['site_interface'].'?m=Server&c=Table');
					$result = $client->exeSql($call_api_sql);
					if(is_object($result) && get_class($result) == 'PHPRPC_Error'){
						$save_data = array();
						$error[] = $site_info['site_name'] . '后台设置修改失败：' . $result->Message;
					}
				}
				if(count($save_data) > 0){
					$r = $site_model->save($save_data, array('where' => array('site_id' => $site_info['site_id'])));
					if(!r) $error[] = $site_info['site_name'] . '修改失败';
				}
			}
			if(count($error) > 0){
				$message = implode('<br>', $error);
				$this->error($message, '', intval(strlen($message)/10));
			}
			$this->success('保存成功', 'index');
		}
		$info = $customer_service_model->where(array('id'=>$id))->find();
		$this->assign('form_title', '修改客服');
		$this->assign('info', $info);
		$this->display();
	}

	public function delAction($id){
		$email = M('CustomerService')->where(array('id=' . $id))->getField('email');
		$count = M('Site')->where(array('email_data' => array('like', '%"address":"' . $email . '"%')))->count();
		if($count > 0) $this->error('还有网站使用此客服的邮箱，请修改网站邮箱后再删除！');
		M('CustomerService')->where(array('id'=>$id))->delete();
		$this->success('删除成功', U('CustomerService/CustomerService/index'));
	}
}