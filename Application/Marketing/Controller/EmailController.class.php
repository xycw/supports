<?php
namespace Marketing\Controller;

use Aws\Iam\IamClient;
use Common\Controller\CommonController;
use Marketing\Model\EmailModel;
use Customers\Model\CustomersModel;

class EmailController extends CommonController {
    
    public function indexAction(){
        //邮箱列表
        $page = I('page',1);
        $num = 10000; //每页显示订单数
        $where = array();
        $data_page = array();
        if(I('customers_email')!=''){
            $where['customers_email'] = array('like','%'.I('customers_email').'%');
            $data_page['customers_email'] = I('customers_email');
        }
        
        if(I('email_template_id', '')!=''){
            $where[] = array(
                '_complex' => array(
                    '_logic' => 'OR',
                    'l.email_template_id' =>array('exp', 'IS NULL'),
                    '_complex'=>array(
                        'l.email_template_id'=>array('neq', I('email_template_id', ''))
                    )
                )
            );
            $data_page['email_template_id'] = I('email_template_id');
        }
        
        $join = array('LEFT JOIN __MARKETING_EMAIL_LOGS__ as l ON l.customer_email_id=e.email_id');
        $email_count = D('marketing_email')->alias('e')->join($join)->where($where)->count();
        $email_list = D('marketing_email')->alias('e')->join($join)->where($where)->page($page, $num)->select();
        
        $this->assign('email_list', $email_list);
        $this->assign('email_count', $email_count);
        
        //邮箱模板
        $email_template = D('marketing_email_template')->select();
        $options_template = array();
        foreach ($email_template as $entry){
            $options_template[$entry['email_template_id']] = $entry['email_template_name'];
        }
        
        $this->assign('options_template', $options_template);
   
        
        
        //邮箱账号
        $email_accounts = D('marketing_email_account')->select();
        $this->assign('email_accounts', $email_accounts);        

        //用于发送邮件的接口
        $send_mail_api = array(
             'http://support.customize.company/api-sendmail/sendmail.php',
        );
        $this->assign('send_mail_api', $send_mail_api);
        
        $this->assign('page', $page);
        $this->assign('data_page', $data_page);
        $this->assign('num', $num);
        //邮件模版管理 template_mailAction
        $email_template_names = D('email_template')->field('email_template_name')->select();
        $this->assign('email_template_names', $email_template_names);
        
        $this->display();
    }

    public function sendAction(){
        $post_data = I('post.');
        $url = $post_data['url'];
//        $post_data['to_address'] = '';
        $email_template_info = D('marketing_email_template')->where(array('email_template_id'=>$post_data['email_template_id']))->find();
        $post_data['email_subject'] = $email_template_info['email_template_subject'];
        $post_data['email_html'] = html_entity_decode($email_template_info['email_template_content']);
//        $post_data['email_reply_to_address'] = $email_template_info['smtp_email'];//'fortunenetwebmaster@gmail.com';
//        $post_data['email_reply_to_name'] = '361d-sports E-Commerce Co., Ltd';
        $r = $this->_curl_post($url, $post_data);
        if($r=='success'){
            $customer_email_info = D('marketing_email')->where(array('customers_email'=>$post_data['to_address']))->find();
            $data = array(
                'customer_email_id'=>$customer_email_info['email_id'],
                'email_template_id'=>$post_data['email_template_id'],
                'date_send'=>date('Y-m-d H:i:s'),
            );
            D('marketing_email_logs')->add($data);
            $this->ajaxReturn(array('status'=>1));
        }else{
            $this->ajaxReturn(array('status'=>0, 'error'=>$r));
        }
    }

    //未下单客户
    public function no_order_customersAction(){
        $customer = new CustomersModel();
        $fields = array('s.site_id,s.site_name,c.customers_id,c.customers_email_address,c.customers_info_date_account_created,c.send_mail_number');
        $site_where = array('status' => 1);
        $where = $site_id_select = array();
        if(session(C('USER_INFO').'.profile_id') !=1 ){
            $site_id_arr  = D('users_to_site')->where(array('user_id'=>session(C('USER_INFO').'.user_id')))->getField('site_id', true);
            if(empty($site_id_arr)) $site_id_arr = array(0);
            $site_where['site_id'] = $where['s.site_id'] = array('IN', $site_id_arr);
        }
        $i = I('site_id');
        if (!empty($i)) {
            $site_id_select_arr = is_array(I('site_id')) ? I('site_id') : explode(',', I('site_id'));
            foreach ($site_id_select_arr as $v){
                $v = intval($v);
                if($v < 1) continue;
                if(isset($site_id_arr) && !in_array($v, $site_id_arr)) continue;
                $site_id_select[] = $v;
            }
        }
        if(count($site_id_select) > 0){
            $page_data['site_id'] =  implode(',', $site_id_select);
            $this->assign('site_id_select', $site_id_select);
            $where['s.site_id'] = array('IN', $site_id_select);
        }
        if (I('register_time_start') !== '' && I('register_time_end') !== '') {
            $time_start = date("Y-m-d H:i:s",time()-intval(I('register_time_start'))*3600);
            $time_end = date("Y-m-d H:i:s",time()-intval(I('register_time_end'))*3600);
        }else{
            $_GET['register_time_start'] = 24;
            $_GET['register_time_end'] = 0;
            $time_start = date("Y-m-d H:i:s",time()-24*3600);
            $time_end = date("Y-m-d H:i:s",time());
        }
        $where['c.customers_info_date_account_created'] = array('between', array($time_start, $time_end));
        $page_data['register_time_start'] = I('register_time_start');
        $page_data['register_time_end'] = I('register_time_end');
        $page = I('page', 1);//当前页码
        $num  = 100;//每页显示客户数
        $sql = D('orders')->alias('o')->field('count(orders_id) as order_num')->where(array('o.site_id=s.site_id AND o.customers_id=c.customers_id'))->select(false);
        $fields[] = '('.$sql.') as order_record_num';
        $sql = D('customers')->alias('c')->field($fields)->join(array('__SITE__ s ON s.site_id=c.site_id'))->where($where)->select(false);
        $list = D()->db()->query('select * from ('.$sql.') as t where order_record_num=0 order by customers_info_date_account_created desc limit '.($page-1)*$num.','.$num);
        $this->assign('list',$list);
        $count = D()->db()->query('select count(*) as num from ('.$sql.') as t where order_record_num=0');
        $count = $count[0]['num'];
        $model_site = D('site');
        $options_site_name = array();
        $data_site = $model_site->where($site_where)->order('site_id asc')->select();
        if ($data_site){
            foreach ($data_site as $row){
                $options_site_name[$row['site_id']]='#'.$row['site_id'].'-'.$row['site_name'];
            }
        }
        $this->assign('options_site_name', $options_site_name);

        //邮箱模板
        $email_template = D('marketing_email_template')->select();
        $options_template = array();
        foreach ($email_template as $entry){
            $options_template[$entry['email_template_id']] = $entry['email_template_name'];
        }
        $this->assign('options_template', $options_template);

        //用于发送邮件的接口
        $send_mail_api = array(
            'http://support.customize.company/api-sendmail/sendmail.php',
        );
        $this->assign('send_mail_api', $send_mail_api);

        $this->assign('page', $page);
        $this->assign('page_data', $page_data);
        $this->assign('num', $num);
        $this->assign('count', $count);
        $this->display();
    }

    //发送邮件给客户
    public function send_customersAction(){
        $post_data = I('post.');
        $url = $post_data['url'];
        $email_template_info = D('marketing_email_template')->where(array('email_template_id'=>$post_data['email_template_id']))->find();
        $post_data['email_subject'] = $email_template_info['email_template_subject'];
        $post_data['email_html'] = html_entity_decode($email_template_info['email_template_content']);
        $email_data = array();
        $site_id = $post_data['site_id'];
        $customers_id = $post_data['customers_id'];
        if($post_data['default_mail_account'] == 1){
            $email_data = D('site')->where(array('site_id'=>$site_id))->getField('email_data');
            $email_data = json_decode($email_data, true);
            $email_data = isset($email_data[0]) && !empty($email_data[0]) ? $email_data[0] : array();
        }
        unset($post_data['default_mail_account'],$post_data['site_id'],$post_data['customers_id']);
        if(empty($email_data)){
            $email_data = D('marketing_email_account')->limit(1)->order('rand()')->find();
            $post_data['smtp_email'] = $email_data['email_username'];
            $post_data['smtp_pwd'] = $email_data['email_password'];
            $post_data['smtp_host'] = $email_data['email_stmp_host'];
            $post_data['smtp_port'] = $email_data['email_smtp_port'];
        }else{
            $post_data['smtp_email'] = $email_data['address'];
            $post_data['smtp_pwd'] = $email_data['password'];
            $post_data['smtp_host'] = $email_data['smtp'];
            $post_data['smtp_port'] = $email_data['port'];
        }
        $r = $this->_curl_post($url, $post_data);
        if($r=='success'){
            D('customers')->where(array('site_id'=>$site_id,'customers_id'=>$customers_id))->setInc('send_mail_number');
            $this->ajaxReturn(array('status'=>1));
        }else{
            $this->ajaxReturn(array('status'=>0, 'error'=>$r));
        }
    }

    function _curl_post($url, array $post = NULL, array $options = array()) {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return $error;
        } else {
            curl_close($ch);
            return $result;
        }
    }
    
    public function importAction(){
        $file_path = $_FILES['file_csv']['tmp_name'];
        
        $num_success = 0;
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $marketing_email_model = D('marketing_email');
            fgetcsv($handle, 0, ",");//跳过第一行字段
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $row = $marketing_email_model->where(array('customers_email'=>$data['2']))->find();
                if(empty($row)){
                    $data = array(
                        'customers_firstname'=>$data['0'],
                        'customer_lastname'=>$data['1'],
                        'customers_email'=>$data['2'],
                    );
                    $num_success++;
                    $marketing_email_model->add($data);
                }
            }
            fclose($handle);
        }

        
        $this->success('成功导入'.$num_success.'条邮箱信息!',U('Marketing/Email/index'));
    }   
    //邮件模板管理
    public function templateAction(){

        if(I('act')=='save' && IS_POST){
            $email_template_id = I('id', 0);
            
            if(IS_POST){
                if($email_template_id=='0'){//新增
                    D('marketing_email_template')->add(array(
                        'email_template_name'=>I('title'),
                        'email_template_content'=>I('content'),
                        'email_template_subject'=>I('subject'),
                        'email_template_status'=>1,
                    ));
                }else{//保存
                    D('marketing_email_template')->where(array('email_template_id'=>$email_template_id))
                    ->save(array(
                        'email_template_name'=>I('title'),
                        'email_template_content'=>I('content'),
                        'email_template_subject'=>I('subject'),
                    ));
                }    
                
                $this->success('保存成功!', U('Marketing/Email/template'));
            }
            
            
            $template_info = D('marketing_email_template')->where(array('email_template_id'=>$email_template_id))->find();
            $this->assign('template_info', $template_info);
            $this->assign('id', $email_template_id);
            
            
            
        }elseif (I('act')=='add' || I('act')=='edit') {
            $email_template_id = I('id', 0);
            $this->assign('id', $email_template_id);
            if($email_template_id>0){
                $template_info = D('marketing_email_template')->where(array('email_template_id'=>$email_template_id))->find();
                $this->assign('template_info', $template_info);
            }            
            $this->display('template_edit');
        }elseif (I('act')=='del') {
            $email_template_id = I('id', 0);
            if(I('confirmation')=='1'){
                D('marketing_email_template')->where(array('email_template_id'=>$email_template_id))->save(array('email_template_status'=>0));
                $this->success('删除成功!', U('Marketing/Email/template'));
            }
            
            $template_info = D('marketing_email_template')->where(array('email_template_id'=>$email_template_id))->find();           
            $this->assign('template_info', $template_info);
            
            $this->display('template_delete');
        }else{
            $list = D('marketing_email_template')->select(array('where'=>array('email_template_status'=>1)));
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * 邮件模版操作
     * @return void
     */
    public function template_mailAction()
    {

        if ('save' == I('act') && IS_POST) {#保存
            $email_template_id = I('email_template_id', 0);
            //根据email_template_id获取编辑信息更新
            D('email_template')->where(array('email_template_id' => $email_template_id))
                ->save(array(
                    'email_template_name' => I('email_template_name'),
                    'email_template_title' => I('email_template_title'),
                    'email_template_content' => I('email_template_content'),
                    'condition' => I('condition'),
                    'email_template_status' => I('email_template_status')
                ));
            $this->success('保存成功!', U('Marketing/Email/template_mail'));
        } elseif (I('act') == 'edit') {#编辑
            //根据email_template_id获取模版信息
            $email_template_id = I('email_template_id', 0);
            $email_template_result = D('email_template')->where(array('email_template_id' => $email_template_id))->find();
            $this->assign('email_template_result', $email_template_result);
            $this->display('template_mail_edit');

        } else {
            $page = I('page', 1);
            $num = 15; //每页显示订单数
            //全部结果集
            $email_template_result = D('email_template')->page($page, $num)->order('email_template_id')->select();
            //计数
            $email_template_count = D('email_template')->where(array('email_template_id'))->count();
            //分页展示
            $this->assign('page', $page);
            $this->assign('num', $num);
            //计数统计返回
            $this->assign('email_template_count', $email_template_count);
            $this->assign('email_template_result', $email_template_result);
            $this->display();
        }
    }
    //SMTP 邮箱账号 管理
    public function smtp_accountAction(){
        if(I('act')=='save' && IS_POST){
            $email_account_id = I('id', 0);

            if($email_account_id=='0'){//新增
                D('marketing_email_account')->add(array(
                    'email_username'=>I('email_username'),
                    'email_password'=>I('email_password'),
                    'email_stmp_host'=>I('email_stmp_host'),
                    'email_smtp_port'=>I('email_smtp_port'),
                ));
            }else{//保存
                D('marketing_email_account')->where(array('email_account_id'=>$email_account_id))
                ->save(array(
                    'email_username'=>I('email_username'),
                    'email_password'=>I('email_password'),
                    'email_stmp_host'=>I('email_stmp_host'),
                    'email_smtp_port'=>I('email_smtp_port'),
                ));
            }    

            $this->success('保存成功!', U('Marketing/Email/smtp_account'));
        }elseif (I('act')=='add' || I('act')=='edit') {
            $email_account_id = I('id', 0);
            $this->assign('id', $email_account_id);
            
            if($email_account_id>0){
                $email_account_info = D('marketing_email_account')->where(array('email_account_id'=>$email_account_id))->find();
                $this->assign('email_account_info', $email_account_info);
            }
            
            $this->display('smtp_account_edit');
        }elseif (I('act')=='del') {
            $email_account_id = I('id', 0);
            if(I('confirmation')=='1'){
                D('marketing_email_account')->where(array('email_account_id'=>$email_account_id))->delete();
                $this->success('删除成功!', U('Marketing/Email/smtp_account'));
            }
            
            $email_account_info = D('marketing_email_account')->where(array('email_account_id'=>$email_account_id))->find();           
            $this->assign('email_account_info', $email_account_info);
            
            $this->display('smtp_account_delete');
        }else{
            $list = D('marketing_email_account')->select();
            $this->assign('list', $list);
            $this->display();
        }
    }    
}