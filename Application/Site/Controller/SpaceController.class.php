<?php
namespace Site\Controller;
use Think\Controller;
use Site\Model\SpaceModel;

class SpaceController extends Controller {
    public function listAction(){
    	$space_model = new SpaceModel();
        
    	$space_list  = $space_model->where(array('status'=>array('eq',1)))->relation(true)->select();
            
    	$this->assign('list', $space_list);
    	$this->display();
    }
    
    
    public function addAction(){
        
        if (IS_POST) {
            $ip  = I('ip', '');
            $cp_url = I('cp_url', '');
            $account = I('account', '');
            $password  = I('password', '');
            $remark  = I('remark', '');
            $date_expired  = I('date_expired', '');
            
            $msg = array();
            if (empty($ip)){
                $msg[] = '空间IP不能为空!';
            }
            if (empty($cp_url)){
                $msg[] = '面板登录链接不能为空!';
            }
            if (empty($account)){
                $msg[] = '账号不能为空!';
            }
            if (empty($password)){
                $msg[] = '密码不能为空!';
            }
            if (sizeof($msg)){
                $this->error(implode('<br>', $msg), 'add');
                exit;
            }
            
            $site_model = D('space');
            
            $row = $site_model->find(array('where'=>array('ip'=>$ip, 'status'=>1)));
            if (is_array($row)){
                $this->error('你添加的空间已存在!', 'add');
                exit;
            }
            
            $site_model->add(
                array(
                    'ip'=>$ip,
                    'cp_url'=>$cp_url,
                    'account'=>$account,
                    'password'=>$password,
                    'remark'=>$remark,
                    'date_expired'=>$date_expired
                )
            );
            
            $this->success('添加成功', 'list');
            exit;
        }
        
        $this->assign('form_title', '添加空间');
        $this->display();
    }
    
    
    public function editAction(){
        $space_id = I('space_id', 0);
        $space_model = D('space');
        $row_space = $space_model->where(array('space_id'=>$space_id))->find();
        
        if (IS_POST) {
            $ip  = I('ip', '');
            $cp_url = I('cp_url', '');
            $account = I('account', '');
            $password  = I('password', '');
            $remark  = I('remark', '');
            $date_expired  = I('date_expired', '');
            
            $msg = array();
            if (empty($ip)){
                $msg[] = '空间IP不能为空!';
            }
            if (empty($cp_url)){
                $msg[] = '面板登录链接不能为空!';
            }
            if (empty($account)){
                $msg[] = '账号不能为空!';
            }
            if (empty($password)){
                $msg[] = '密码不能为空!';
            }
            if (sizeof($msg)){
                $this->error(implode('<br>', $msg), U('Site/Space/edit/space_id/'.$space_id));
                exit;
            }
            
            $site_model = D('space');
            
            if($row_space['ip']!=$ip){
                $row = $site_model->find(array('where'=>array('ip'=>$ip, 'status'=>1)));
                if (is_array($row)){
                    $this->error('IP已存在!', U('Site/Space/edit/space_id/'.$space_id));
                    exit;
                }
            }
            $site_model->where(array('space_id'=>$space_id))->save(
                array(
                    'ip'=>$ip,
                    'cp_url'=>$cp_url,
                    'account'=>$account,
                    'password'=>$password,
                    'remark'=>$remark,
                    'date_expired'=>$date_expired
                )
            );
            
            $this->success('编辑成功', 'list');
            exit;
        }
        
        $this->assign('form_title', '编辑空间');
        $this->assign('space_info', $row_space);
        $this->display('add');
    }
    
    
    public function delAction($site_id){
        $site_model = D('site');
        $site_model->save(array('status'=>0), array('where'=>array('site_id'=>$site_id)));
        
        $this->success('删除成功', U('Site/Space/list'));
    }
    
    public function BindSiteAction($space_id){
        if(IS_POST){
            $site_id = I('site');
            if(is_array($site_id) && sizeof($site_id)>0){
                $site_model  = D('site');
                $site_model->where(array('site_id'=>array('in', implode(',', $site_id))))->save(array('space_id'=>$space_id));
                
                $this->success('添加成功', U('Site/Space/list'));
            }else{
                $this->error('没有可添加的网站!', U('Site/Space/BindSite/space_id/'.$space_id));
            }
            exit;            
        }
        
        $space_model = D('space');
        $row_space = $space_model->where(array('space_id'=>$space_id))->field('ip')->find();
        
        $site_model  = D('site');
        $where_site  = array('status'=>1,'');
        $list_site   = $site_model->where(array('status'=>1,'space_id'=>0))->field('site_id,site_name')->select();
        
        $this->assign('space_id', $space_id);
        $this->assign('ip', $row_space['ip']);
        $this->assign('list_site', $list_site);
        $this->display();
    }
    
    public function UnbindSiteAction($site_id){
        $confirmation = I('confirmation', 0);
        $site_model  = D('site');
        if($confirmation=='1'){
            $site_model->where(array('site_id'=>$site_id))->save(array('space_id'=>0));
            $this->success('解除绑定成功!', U('Site/Space/list'));
            exit;
        }      
        $row_site    = $site_model->where(array('site_id'=>$site_id))->field('site_name')->find();
        
        $this->assign('site_id', $site_id);
        $this->assign('site_name', $row_site['site_name']);
        $this->display();
    }
    
    public function AddDbAction($space_id){
        $site_model  = D('site');
        if(IS_POST){
            $site_id  = I('site_id');
            $data = array(
                'space_id' => I('space_id'),
                'space_db_database' => I('database'),
                'space_db_username' => I('username'),
                'space_db_password' => I('password'),
            );
            $space_db_model = D('space_db');
            $space_db_id = $space_db_model->add($data);
            $site_model->where(array('site_id'=>$site_id))->save(array('space_db_id'=>$space_db_id));
            
            $this->success('数据库添加成功!');
            exit;
        }
        
        
        $where_site  = array('status'=>1);
        $list_site   = $site_model->where(array('status'=>1,'space_db_id'=>0))->field('site_id,site_name')->select();
        
        $option_site = array();
        foreach ($list_site as $entry){
            $option_site[$entry['site_id']] = $entry['site_name'];
        }
        
        $this->assign('space_id', $space_id);
        $this->assign('option_site', $option_site);
        $this->display();
    }
    
    public function DelDbAction($space_db_id) {
        $space_db_model = D('space_db');
        $space_db_model->where(array('space_db_id'=>$space_db_id))->delete();
        $site_model  = D('site');
        $site_model->where(array('space_db_id'=>$space_db_id))->save(array('space_db_id'=>0));
        
        $this->success('删除成功!',U('Site/Space/list'));
       
    }
}