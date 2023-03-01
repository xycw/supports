<?php
namespace Site\Controller;

use Common\Controller\CommonController;
use Site\Model\SiteModel;

class SiteController extends CommonController {

    //zencart独立站列表
    public function listAction(){
    	$site_model = new SiteModel();
        
        $where = array('status'=>array('eq',1), 'type'=>1);      
        if(!isset($_GET['is_sale']) || $_GET['is_sale']==""){
        	$_GET['is_sale']=-1;
        }
        if($_GET['is_sale']!=-1){
        	$where['is_sale']=$_GET['is_sale'];
        	
        }
        $orders_site_ids = M('Orders')->where(array('date_purchased'=>array('gt',date("Y-m-d H:i:s", strtotime("-10 days")))))->group('site_id')->getField('site_id',true);
    	$site_list  = $site_model->relation(true)->where($where)->order('type asc,site_id asc')->select();
    	foreach ($site_list as $k=>$entry){
            $user = D('users_to_site')->alias('u2s')->join(array('__USERS__ u ON u.user_id=u2s.user_id'))->where(array('site_id'=>$entry['site_id']))->select();
            foreach($user as $_user)
                $site_list[$k]['user'][$_user['user_profile']][] = $_user;
            $site_list[$k]['exist_order'] = in_array($entry['site_id'], $orders_site_ids) ? true : false;
        }
        $system_depart_array = M('PromotionDepartment')->getField('department_id,department_name',true);
        $system_tuiguangy_array = M('Users')->where(array('user_profile' => array('in', array(2,3)), 'status' => 1))->getField('user_id,chinese_name',true);
        $customer_service_array = M('CustomerService')->order('id')->getField('email',true);
        $this->assign('customer_service_array', $customer_service_array);
        $this->assign('site_type', C('site_type'));
    	$this->assign('site_list', $site_list);
        $this->assign('system_depart_array', $system_depart_array);
        $this->assign('system_tuiguangy_array', $system_tuiguangy_array);
    	$this->display();
    }

    //saas商城站列表
    public function list2Action(){
    	$site_model = new SiteModel();
        
        $where = array('status'=>array('eq',1), 'type'=>10);      
        if(!isset($_GET['is_sale']) || $_GET['is_sale']==""){
            $_GET['is_sale']=-1;
        }
        if($_GET['is_sale']!=-1){
            $where['is_sale']=$_GET['is_sale'];
        }
        $orders_site_ids = M('Orders')->where(array('date_purchased'=>array('gt',date("Y-m-d H:i:s", strtotime("-10 days")))))->group('site_id')->getField('site_id',true);
    	$site_list  = $site_model->where($where)->order('type asc,site_id asc')->select();
    	foreach ($site_list as $k=>$entry){
            $user = D('users_to_site')->alias('u2s')->join(array('__USERS__ u ON u.user_id=u2s.user_id'))->where(array('site_id'=>$entry['site_id']))->select();
            foreach($user as $_user)
                $site_list[$k]['user'][$_user['user_profile']][] = $_user;
            $site_list[$k]['exist_order'] = in_array($entry['site_id'], $orders_site_ids) ? true : false;
        }
        $system_depart_array = M('PromotionDepartment')->getField('department_id,department_name',true);
        $system_tuiguangy_array = M('Users')->where(array('user_profile' => array('in', array(2,3)), 'status' => 1))->getField('user_id,chinese_name',true);
        $customer_service_array = M('CustomerService')->order('id')->getField('email',true);
        $this->assign('customer_service_array', $customer_service_array);
        $this->assign('site_type', C('site_type'));
    	$this->assign('site_list', $site_list);
        $this->assign('system_depart_array', $system_depart_array);
        $this->assign('system_tuiguangy_array', $system_tuiguangy_array);
    	$this->display();
    }

     //B站列表
    public function listbAction(){
        $site_model = new SiteModel();
        
        $where = array('status'=>array('eq',1), 'type'=>2);      
        if(!isset($_GET['is_sale']) || $_GET['is_sale']==""){
            $_GET['is_sale']=-1;
        }
        if($_GET['is_sale']!=-1){
            $where['is_sale']=$_GET['is_sale'];
        }
        $site_list  = $site_model->where($where)->order('type asc,site_id asc')->select();
        foreach ($site_list as $k=>$entry){
            $user = D('users_to_site')->alias('u2s')->join(array('__USERS__ u ON u.user_id=u2s.user_id'))->where(array('site_id'=>$entry['site_id']))->select();
            foreach($user as $_user)
                $site_list[$k]['user'][$_user['user_profile']][] = $_user;
        }
        $system_depart_array = M('PromotionDepartment')->getField('department_id,department_name',true);
        $system_tuiguangy_array = M('Users')->where(array('user_profile' => array('in', array(2,3)), 'status' => 1))->getField('user_id,chinese_name',true);
        $this->assign('site_type', C('site_type'));
        $this->assign('site_list', $site_list);
        $this->assign('system_depart_array', $system_depart_array);
        $this->assign('system_tuiguangy_array', $system_tuiguangy_array);
        $this->display();
    }

    public function editAction(){
        
        if (IS_POST) {
            $site_id  = I('site_id', 0);
            $site_name  = I('site_name', '');
            $site_index = I('site_index', '');
            $img_url = I('img_url', '');
            $order_no_prefix = I('order_no_prefix', '');
            $site_interface  = I('site_interface', '');
            $type = I('type', 0);
            $remark = I('remark', 0);
            $date_expired = I('date_expired', '');
            $ssl_expired = I('ssl_expired', '');
            $system_proupdate=I('post.system_proupdate','');
            $system_weburl=I('post.system_weburl','');
            $system_weblogin=I('post.system_weblogin','');
            $system_webpass=I('post.system_webpass','');
            $system_cms=I('post.system_cms','');
			$system_area=I('post.system_area','');
            $system_brand=I('post.system_brand','');
            $system_depart=I('post.system_depart',0);
            $system_tuiguangy=I('post.system_tuiguangy',0);
            $system_url=I('post.system_url','');
            $system_thirdgw=I('post.system_thirdgw','');
            $system_weburl_saas=I('post.system_weburl_saas','');
            $msg = array();
            if (empty($site_name)){
                $msg[] = '网站名称不能为空!';
            }
            if (!preg_match('~https?://[^/]+~', $site_index)){
                $msg[] = '不合法的网站首页!';
            }
            //type: 1-zencart=商品站, 10-saas=商品站, 2-zencart=B站
            if (($type==1 || $type==10 || $type==2) && !preg_match('~http(s)?://[^/]+/.+~', $site_interface)){
                $msg[] = '不合法的接口链接!';
            }
            if (sizeof($msg)){
                $this->error(implode('<br>', $msg), 'add');
                exit;
            }
            
            $site_model = D('site');
                        
            $email_data = array();
            $post_email_address = I('post.email_address');
            $n = 0;
            foreach ($post_email_address as $k=>$address){
                if(!empty($address)){
                    $customer_service_info = M('CustomerService')->where(array('email'=>$address))->find();
                    if($n == 0) $customer_service_name = $customer_service_info['nickname'];
                    $old_email_data = array();
                    if($n == 0 && $site_id > 0){
                        $site_info = $site_model->field('email_data,customer_service_name')->where(array('site_id'=>$site_id))->find();
                        $old_email_data = json_decode($site_info['email_data'], true);
                    }
                    $email_data[$n] = array(
                        'address' => $customer_service_info['email'],
                        'password' => $customer_service_info['email_password'],
                        'smtp' => $customer_service_info['email_smtp'],
                        'port' => $customer_service_info['email_port']
                    );
                    $is_call_api = false;
                    if($n == 0 && $type == 1 && $system_cms == 'easyshop') $is_call_api = true;
                    if($is_call_api){
                        $call_api_sql = '';
                        if(!isset($old_email_data[0]['address']) || $customer_service_info['email'] != $old_email_data[0]['address']){
                            $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='STORE_EMAIL';";
                            $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='SEND_EMAIL_ACCOUNT';";
                        }
                        if(!isset($old_email_data[0]['password']) || $customer_service_info['email_password'] != $old_email_data[0]['password']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_password'] . "' WHERE `configuration_key`='SEND_EMAIL_PASSWORD';";
                        if(!isset($old_email_data[0]['smtp']) || $customer_service_info['email_smtp'] != $old_email_data[0]['smtp']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_smtp'] . "' WHERE `configuration_key`='SEND_EMAIL_HOST';";
                        if(!isset($old_email_data[0]['port']) || $customer_service_info['email_port'] != $old_email_data[0]['port']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_port'] . "' WHERE `configuration_key`='SEND_EMAIL_PORT';";
                        if(!isset($site_info['customer_service_name']) || $customer_service_name != $site_info['customer_service_name']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['nickname'] . "' WHERE `configuration_key`='SERVICE_NAME';";
                        if($call_api_sql != ''){
                            Vendor('phpRPC.phprpc_client');
                            $client = new \PHPRPC_Client($site_interface.'?m=Server&c=Table');
                            $result = $client->exeSql($call_api_sql);
                            if(is_object($result) && get_class($result) == 'PHPRPC_Error'){
                                if($site_id > 0){
                                    $email_data[0] = $old_email_data[0];
                                    $customer_service_name = $site_info['customer_service_name'];
                                    $error = '后台邮箱设置修改失败：' . $result->Message;
                                }else{
                                    $error = '后台邮箱设置修改失败：' . $result->Message . ' 请登录网站后台进行修改！';
                                }
                            }
                        }
                    }
                    $n++;
                }
            }
            $system_url_block=array(
            		'system_url_username'=>I('post.system_url_username',''),
            		'system_url_email'=>I('post.system_url_email','')
            );
            $system_dns_block=array(
            		'system_dns_username'=>I('post.system_dns_username',''),
            		'system_dns_email'=>I('post.system_dns_email'),
            		'email_dns'=>I('post.email_dns','')
            );
//            echo $site_id;exit;
            if($site_id==0){
            $site_model->add(
                array(
                    'site_name'=>$site_name,
                    'order_no_prefix'=>$order_no_prefix,
                    'site_index'=>$site_index,
                    'img_url'=> str_replace (' ', '', $img_url),
                    'site_interface'=>str_replace (' ', '', $site_interface),
                    'type'=>$type,
                    'customer_service_name'=>$customer_service_name,
                    'remark'=>$remark,
                    'date_expired'=>$date_expired,
                    'ssl_expired'=>$ssl_expired,
                    'email_data'=> json_encode($email_data),
                    'is_sale'=>I('is_sale',0),
                    'system_proupdate'=>$system_proupdate,
                    'system_weburl'=>$system_weburl,
                    'system_weblogin'=>$system_weblogin,
                    'system_webpass'=>$system_webpass,
                    'system_cms'=>$system_cms,
           			'system_area'=>$system_area,
					'system_brand'=>$system_brand,
					'system_depart'=>$system_depart,
					'system_tuiguangy'=>$system_tuiguangy,
					'system_url'=>$system_url,
					'system_url_block'=>json_encode($system_url_block),
					'system_thirdgw'=>$system_thirdgw,
					'system_dns_block'=>json_encode($system_dns_block),
                    'system_weburl_saas'=>$system_weburl_saas
                )
            );
            }else{
                $site_model->save(
                    array(
                        'site_name'=>$site_name,
                        'order_no_prefix'=>$order_no_prefix,
                        'site_index'=>$site_index,
                        'img_url'=>$img_url,
                        'site_interface'=>$site_interface,
                        'type'=>$type,
                        'customer_service_name'=>$customer_service_name,
                        'remark'=>$remark,
                        'date_expired'=>$date_expired,
                        'ssl_expired'=>$ssl_expired,
                        'email_data'=> json_encode($email_data),
                        'is_sale'=>I('is_sale',0),
                        'system_proupdate'=>$system_proupdate,
                        'system_weburl'=>$system_weburl,
                        'system_weblogin'=>$system_weblogin,
                        'system_webpass'=>$system_webpass,
                        'system_cms'=>$system_cms,
						'system_area'=>$system_area,
            			'system_brand'=>$system_brand,
            			'system_depart'=>$system_depart,
            			'system_tuiguangy'=>$system_tuiguangy,
            			'system_url'=>$system_url,
            			'system_url_block'=>json_encode($system_url_block),
            			'system_thirdgw'=>$system_thirdgw,
            			'system_dns_block'=>json_encode($system_dns_block),
                        'system_weburl_saas'=>$system_weburl_saas,
                        'new_saas' => I('new_saas', 0)
                    ),
                    array('where'=>array('site_id'=>$site_id))
                );

                $domains_data = array(
                    'user_id' => $system_tuiguangy,
                    'site_type' => $type,
                    'domain_name' => $site_name,
                    'is_sale' => I('is_sale',0),
                    'order_no_prefix' => $order_no_prefix,
                    'customer_service_email' => $address,
                    //'domain_email' => I('post.system_url_email',''),
                    //'domain_name_agent' => $system_url,
                    //'registered_account' => I('post.system_url_username',''),
                    'expire_date' => $date_expired,
                    'ssl_expire_date' => $ssl_expired,
                    'dns_agent' => $system_thirdgw,
                    'dns_information' => json_encode($system_dns_block),
                    'site_index' => $site_index
                );
                M('Domains')->where('site_id=' . $site_id)->save($domains_data);

                /* $sql = D('users')->field('user_id')->where(array('user_profile'=>3))->select(false);
                D('users_to_site')->where(array('site_id'=>$site_id, 'user_id'=>array('exp', 'in '.$sql)))->delete(); */
                // if(I('tuiguang_user_id')!=''){
                //     D('users_to_site')->add(array('site_id'=>$site_id, 'user_id'=>I('tuiguang_user_id')));
                // }
            }
            if(isset($error)) $this->error($error, $site_id > 0 ? U('Site/Site/edit/site_id/' . $site_id) : 'list', intval(strlen($error)/10));
            if($type==1){
                $this->success('提交成功', 'list');
            }elseif ($type==10) {
                $this->success('提交成功', 'list2');
            }elseif ($type==2) {
                $this->success('提交成功', 'listb');
            }else{
                $this->success('提交成功', 'list');
            }
        }
        
        $site_id  = I('site_id');
        $site_model = new SiteModel();
        $site_info  = $site_model->where(array('site_id'=>$site_id))->find();
        
        if(empty($site_info['email_data']))
            $email_data = array();
        else
            $email_data = json_decode($site_info['email_data'], true);
        
        $user_tuiguang = D('users')->alias('u')->where(array('user_profile'=>3))->order('user_id')->select();
        $data_user_tuiguang = array();
        foreach($user_tuiguang as $entry){
            $data_user_tuiguang[$entry['user_id']] = $entry['chinese_name'];
        }
        $this->assign('data_user_tuiguang', $data_user_tuiguang);
        $sql = D('users')->field('user_id')->where(array('user_profile'=>3))->select(false);
        $row = D('users_to_site')->where(array('site_id'=>$site_id, 'user_id'=>array('exp', 'in '.$sql)))->find();
        // $this->assign('tuiguang_user_id', $row['user_id']);
        $customer_service_array = M('CustomerService')->order('id')->getField('email',true);
        $this->assign('customer_service_array', $customer_service_array);
        $this->assign('email_data', $email_data);
        $this->assign('site_info', $site_info);
        $this->assign('form_title', '编辑网站');
        $this->system_proupdate=$this->setArr('system_proupdate',$site_info['system_proupdate']);
        $this->system_webpass=$this->setArr('system_webpass',$site_info['system_webpass']);              
        $this->system_cms=$this->setArr('system_cms',$site_info['system_cms']);
        $this->system_area=$this->setArr('system_area',$site_info['system_area']);
        $this->system_brand=$this->setArr('system_brand',$site_info['system_brand']);
        $this->system_depart = M('PromotionDepartment')->order('department_id')->getField('department_id,department_name',true);
        $this->system_tuiguangy = M('Users')->where(array('user_profile' => array('in', array(2,3)), 'status' => 1))->order('user_profile desc,user_id asc')->getField('user_id,chinese_name',true);
        $this->system_url=$this->setArr('system_url',$site_info['system_url']);
        $this->system_thirdgw=$this->setArr('system_thirdgw',$site_info['system_thirdgw']);
        $this->system_url_block=json_decode($site_info['system_url_block'],true);
        $this->system_dns_block=json_decode($site_info['system_dns_block'],true);
        $this->system_weburl_saas=$this->setArr('system_weburl_saas',$site_info['system_weburl_saas']);
        $this->display();
    }
    
    public function addAction(){
        $this->assign('form_title', '添加网站');
        $customer_service_array = M('CustomerService')->order('id')->getField('email',true);
        $this->assign('customer_service_array', $customer_service_array);
        $this->system_proupdate=$this->setArr('system_proupdate');
        $this->system_webpass=$this->setArr('system_webpass');
        $this->system_cms=$this->setArr('system_cms');
        $this->system_area=$this->setArr('system_area');
        $this->system_brand=$this->setArr('system_brand');
        $this->system_depart = M('PromotionDepartment')->order('department_id')->getField('department_id,department_name',true);
        $this->system_tuiguangy = M('Users')->where(array('user_profile' => array('in', array(2,3)), 'status' => 1))->order('user_profile desc,user_id asc')->getField('user_id,chinese_name',true);
        $this->system_url=$this->setArr('system_url','',false);
        $this->system_thirdgw=$this->setArr('system_thirdgw','',false);
        $this->system_weburl_saas=$this->setArr('system_weburl_saas');
        $this->display('edit');
    }
    private function setArr($ziduan,$selected_val='',$please=true){
    	$array=explode('|',C($ziduan));
    	$option='';
    	if($please){
    		$option.='<option value="0">---请选择--</option>';
    	}
    	foreach($array as $t){
    		if($t==$selected_val){
    			$selected='selected="selected"';
    		}else{
    			$selected='';
    		}
    		$option.='<option value="'.$t.'" '.$selected.'>'.$t.'</option>';
    	}
		return $option;
    }
    public function AjaxUpdateAction($site_id) {
        $fileds = array('remark','site_name','site_index','site_interface','date_expired','type','site_index_spare');
        
        $data = array();
        foreach ($fileds as $filed){
            if(I($filed, false)!==false){
                $data[$filed] = I($filed);
            }
        }
        
        if(sizeof($data)>0){
            $site_model = D('site');
            $site_model->where(array('site_id'=>$site_id))->save($data);
            
            $this->ajaxReturn(array('status'=>1,'data'=>$data), 'JSON');
        }else{
            $this->ajaxReturn(array('status'=>0, 'error'=>'没有可更新的字段!'), 'JSON');
        }
        
        $this->ajaxReturn();
    }
    
    public function delAction($site_id){
        $site_model = D('site');
        $site_model->save(array('status'=>0), array('where'=>array('site_id'=>$site_id)));
        
        $this->success('删除成功', U('Site/Site/list'));
        exit;
    }

    public function import_saas_listAction() {
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) $this->error('表格上传失败！错误码：' . $_FILES['file']['error']);
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext == 'xlsx'){
            $file = $_FILES['file']['tmp_name'];
            Vendor('PHPExcel.PHPExcel');
            $php_excel_reader = new \PHPExcel_Reader_Excel2007();
            if (!$php_excel_reader->canRead($file)) $this->error('无法解析上传的表格!');
            $PHPExcel = $php_excel_reader->load($file);
            $currentSheet = $PHPExcel->getSheet(0);
            $fileds = array(
                'A' => 'saas_id',
                'C' => 'site_name'
            );
            $allRow = $currentSheet->getHighestRow();
            $site_model = D('site');
            for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
                $next = true;
                foreach ($fileds as $col => $field) {
                    $value = $currentSheet->getCell($col . $rowIndex)->getFormattedValue();
                    $$field = trim($value);
                }
                if(strstr($site_name,'www.')){
                    $site_model->where(array('site_name'=>array('in',array($site_name,str_replace('www.', '', $site_name)))))->save(array('saas_id' => intval($saas_id)));
                }
            }
            $this->success('导入成功', U('Site/Site/list2'));
        }else{
            $this->error('未知文件格式！');
        }
    }

    public function export_site_listAction() {
        header("Content-Type: text/html;charset=utf-8");
        $where = array('s.type' => array('in',array(1,10)));
        if(I('status')) $where['s.status'] = I('status');
        if(I('type')) $where['s.type'] = I('type');
        if(I('system_area')) $where['s.system_area'] = I('system_area');
        if(I('system_depart')) $where['s.system_depart'] = I('system_depart');
        if(I('system_tuiguangy')) $where['s.system_tuiguangy'] = I('system_tuiguangy');
        $list = M('Site')->alias('s')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT__ p ON s.system_depart=p.department_id','LEFT JOIN __USERS__ u ON s.system_tuiguangy=u.user_id'))
            ->field('s.type,s.saas_id,s.order_no_prefix,s.site_name,p.department_name,u.chinese_name')->where($where)->order('s.site_id')->select();
        if(empty($list)) $this->error('没有查询到数据，请重新选择筛选条件！');
        vendor('PHPExcel.PHPExcel');
        $field_array = array(
            'A' => array('title' => '订单前缀',   'width' => 20,  'key' => 'order_no_prefix'),
            'B' => array('title' => '网址', 'width' => 50,  'key' => 'site_name'),
            'C' => array('title' => '团队', 'width' => 20,  'key' => 'department_name'),
            'D' => array('title' => '负责人', 'width' => 20, 'key' => 'chinese_name')
        );
        $PHPExcel = new \PHPExcel();
        $currentSheet = $PHPExcel->getActiveSheet();
        $row = 1;
        foreach ($field_array as $k => $k_info) {
            $currentSheet->setCellValue($k . $row, $k_info['title']);
            $currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
        }
        foreach($list as $site){
            $row++;
            foreach ($field_array as $k => $k_info) {
                if(empty($k_info['key'])) continue;
                if($k_info['key']=='order_no_prefix' && $site['type']== 10 && !empty($site['saas_id']) && !empty($site['order_no_prefix'])) $site['order_no_prefix'] = $site['saas_id'] . '-' . $site['order_no_prefix'];
                $currentSheet->setCellValue($k . $row, $site[$k_info['key']]);
                $currentSheet->getStyle($k . $row)->getAlignment()->setShrinkToFit(true);
                
            }
            $currentSheet->getRowDimension($row)->setRowHeight(20);
        }
        $currentSheet->getStyle('A1:D' . $row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            )
        );
        $fileName = 'site_list_'.date('Ymdhis').'.xls';
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $fileName);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    //更换网站邮箱
    public function replace_emailAction(){
        $site_id = I('get.site_id', 0);
        if($site_id > 0){
            $email = I('get.email', '');
            if($email){
                $customer_service_info = M('CustomerService')->where(array('email'=>$email))->find();
                $site_model = M('Site');
                $site_info = $site_model->field('type,site_interface,email_data,customer_service_name,system_cms')->where(array('site_id'=>$site_id))->find();
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
                        if(!isset($old_email_data[0]['address']) || $customer_service_info['email'] != $old_email_data[0]['address']){
                            $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='STORE_EMAIL';";
                            $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email'] . "' WHERE `configuration_key`='SEND_EMAIL_ACCOUNT';";
                        }
                        if(!isset($old_email_data[0]['password']) || $customer_service_info['email_password'] != $old_email_data[0]['password']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_password'] . "' WHERE `configuration_key`='SEND_EMAIL_PASSWORD';";
                        if(!isset($old_email_data[0]['smtp']) || $customer_service_info['email_smtp'] != $old_email_data[0]['smtp']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_smtp'] . "' WHERE `configuration_key`='SEND_EMAIL_HOST';";
                        if(!isset($old_email_data[0]['port']) || $customer_service_info['email_port'] != $old_email_data[0]['port']) $call_api_sql .= "UPDATE `configuration` SET `configuration_value`='" . $customer_service_info['email_port'] . "' WHERE `configuration_key`='SEND_EMAIL_PORT';";
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
                            $this->ajaxReturn(array('status' => 0, 'error'=>'后台设置修改失败：' . $result->Message), 'JSON');
                        }
                    }
                    if($site_operation){
                        $r = $site_model->save($save_data, array('where' => array('site_id' => $site_id)));
                        if(!r) $this->ajaxReturn(array('status' => 0, 'error'=>'更换失败！'), 'JSON');
                    }
                }
                $this->ajaxReturn(array('status' => 1), 'JSON');
            }else{
                $this->ajaxReturn(array('status' => 0, 'error'=>'请选择邮箱！'), 'JSON');
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'error'=>'请选择网站！'), 'JSON');
        }
    }
}