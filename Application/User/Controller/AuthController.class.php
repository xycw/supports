<?php
namespace User\Controller;

use Common\Controller\CommonController;
use User\Model\UserModel;

class AuthController extends CommonController {

    public function loginAction() {

        if (session('?' . C('USER_INFO'))) {
            $this->redirect(C('DEFAULT_MODULE') . '/' . C('DEFAULT_CONTROLLER') . '/' . C('DEFAULT_ACTION'), array(), 1, '你已登录!');
        }
        if (IS_POST) {
            $username = I('username');
            $password = I('password');

            $password = md5($password);
            $user_model = new UserModel();
            $user_info = $user_model->where(array('username' => $username, 'password' => $password))->find();
            if (!is_null($user_info)) {
                $users_profile = D('users_profile')->where(array('profile_id'=>$user_info['user_profile']))->getField('profile_name');

                session(C('USER_INFO'), array(
                    'user_id'=>$user_info['user_id'],
                    'username'=>$user_info['username'],
                    'english_name'=>$user_info['english_name'],
                    'chinese_name'=>$user_info['chinese_name'],                    
                    'users_profile'=>$users_profile,
                    'profile_id'=>$user_info['user_profile'],
                ));
                if($user_info['user_profile'] == 3){
                    $link = U('Domains/Domains/index');
                }elseif($user_info['user_profile'] == 4){
                    $link = U('Order/Purchase/index/order_status/待订货');
                }elseif($user_info['user_profile'] == 5){
                    $link = U('Order/Finance/index');
                }else{
                    $link = U(C('DEFAULT_MODULE') . '/' . C('DEFAULT_CONTROLLER') . '/' . C('DEFAULT_ACTION'));
                }
                $this->success('登录成功!', $link);
            } else {
                $this->error('登录失败！');
            }
        }
        layout(false);
        $this->display();
    }

    public function logoutAction() {
        session('[destroy]');
        $this->redirect('login');
    }

}
