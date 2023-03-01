<?php

namespace Common\Controller;
use Think\Controller;

/*
 * 通用控制器
 * 作用：布局,SEO,公共变量
 */

class CommonController extends Controller {

    function __construct() {
        $this->checkLogin();
        parent::__construct();
        $this->checkPermission();
    }

    function __destruct() {
        parent::__destruct();
    }

    /*
     * 登录验证
     */

    private function checkLogin() {
        //var_dump(session(C('USER_INFO')));exit;

        if (get_class($this) != C('USER_LOGIN_CONTROLLER') &&
                session('?' . C('USER_INFO')) == false
        ) {
            $this->redirect('User/Auth/login', array(), 1, '你需要要登录才能访问!');
        }
    }

    /*
     * 系统级权限
     * 只有最高权限才能访问
     */
    private function checkPermission(){
        $no_permission = false;
        if(session(C('USER_INFO').'.profile_id')!=1 && 
            ((MODULE_NAME=='User' && CONTROLLER_NAME=='Administration') || (MODULE_NAME=='Sys'))
        ){
            $no_permission = true;
        }elseif(session(C('USER_INFO').'.profile_id')==4){//订货
            if(MODULE_NAME=='Order' && CONTROLLER_NAME=='Finance' && session(C('USER_INFO').'.user_id') == 7){
                $no_permission = false;
            }elseif((MODULE_NAME=='Order' && CONTROLLER_NAME=='Purchase') || (MODULE_NAME=='Order' && CONTROLLER_NAME=='Order' && ACTION_NAME=='clear_doc')){
                $no_permission = false;
            }else{
                $no_permission = true;    
            }
        }elseif(session(C('USER_INFO').'.profile_id')==3 || session(C('USER_INFO').'.user_id')==36){//推广
            if(MODULE_NAME=='Order' && CONTROLLER_NAME=='Purchase'){
                $no_permission = true;    
            }else{
                if(session(C('USER_INFO').'.profile_id') == 3 && !in_array(MODULE_NAME, array('Domains','User')) && (MODULE_NAME != 'Site' || CONTROLLER_NAME != 'Site' || ACTION_NAME != 'list')){
                    $no_permission = true;
                }else{
                    $no_permission = false;
                }
            }
        }elseif(session(C('USER_INFO').'.profile_id') == 5){//财务
            if(MODULE_NAME != 'Order' || CONTROLLER_NAME != 'Finance') $no_permission = true;
        }elseif(session(C('USER_INFO').'.profile_id') == 6){//客服
            if((MODULE_NAME != 'Order' || CONTROLLER_NAME != 'Order' || !in_array(ACTION_NAME, array('list','view','ipQuery'))) && (MODULE_NAME != 'Site' || CONTROLLER_NAME != 'Site' || !in_array(ACTION_NAME, array('list','list2')))){
                $no_permission = true;
            }
        }
        if(MODULE_NAME=='User' && in_array(CONTROLLER_NAME, array('Auth','User')))
            $no_permission = false;
        if($no_permission){
            $this->display('Common@Common/no_permission');
            exit;
            
        }
    }
}
