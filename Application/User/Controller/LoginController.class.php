<?php
namespace User\Controller;
use Common\Controller\CommonController;
use User\Model\UserModel;

class LoginController extends CommonController {
	
	public function loginAction(){

		if(session('?'.C('USER_ID_SESSION_NAME'))){
			$this->redirect(C('DEFAULT_MODULE').'/'.C('DEFAULT_CONTROLLER').'/'.C('DEFAULT_ACTION'));
		}
		if(IS_POST){
			$username = I('username');
			$password = I('password');
			
			$password 	= md5($password);
			$user_model = new UserModel();
			$user_info = $user_model->where(array('username'=>$username,'password'=>$password))->find();
			if (!is_null($user_info)) {
				session(C('USER_ID_SESSION_NAME'), $user_info['user_id']);
				$this->success('登录成功!', U(C('DEFAULT_MODULE').'/'.C('DEFAULT_CONTROLLER').'/'.C('DEFAULT_ACTION')));
				exit;
			}else{
				$this->error('登录失败！');
			}
		}
		layout(false);
		$this->display();
	}
	
	public function logoutAction(){
		session('[destroy]');
		$this->redirect('login');
	}
	
}