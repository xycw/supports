<?php
namespace Email\Controller;
use Think\Controller;

class CustomerController extends Controller {
    public function ListAction(){
        $email_customer_model = new \Email\Model\CustomerModel();
        
        $page = I('page',1);
        $num = 30; //每页显示订单数
        $where = array();
        $data_page = array();
        if(I('customers_email')!=''){
            $where['customers_email'] = array('like','%'.I('customers_email').'%');
            $data_page['customers_email'] = I('customers_email');
        }
        if(I('date_send')!=''){
            $sub_sql = D('email_logs')->field('customer_email_id')->where(array('date_send'=>array('like','%'.I('date_send').'%')))->select(false);
            $exp = (I('send_status')=='1')?'in':'notin';
            $where['email_id'] = array($exp, $sub_sql, 'exp');
            $data_page['date_send'] = I('date_send');
            $data_page['send_status'] = I('send_status');
        }
        if(I('email_template')!=''){
            $sub_sql = D('email_logs')->field('customer_email_id')->where(array('email_template_id'=>I('email_template')))->select(false);
            $exp = (I('send_status')=='1')?'in':'notin';
            $where['email_id'] = array($exp, $sub_sql, 'exp');
            $data_page['email_template'] = I('email_template');
            $data_page['send_status'] = I('send_status');
            
            $this->assign('email_template', I('email_template'));
        }
        
        if(IS_AJAX){
            $where['status'] = 1;
            $list = $email_customer_model->where($where)->select();
            
            $this->ajaxReturn($list, 'JSON');
        }
        $count = $email_customer_model->where($where)->count();
        $list = $email_customer_model->where($where)->page($page, $num)->select();
//        echo $list;exit;
        
        $email_template_model = new \Email\Model\EmailTemplateModel();
        $email_template_list = $email_template_model->where(array('status'=>1))->select();
        $template_list = array();
        foreach($email_template_list as $entry){
            $template_list[$entry['email_template_id']] = $entry['email_template_title'];
        }
        $this->assign('template_list', $template_list);

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('data_page', $data_page);
        $this->assign('num', $num);
        $this->assign('count', $count);
        $this->display();
    }

    public function ImportAction(){
        $file_path = $_FILES['file_csv']['tmp_name'];
        
        $num_success = 0;
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $email_customer_model = new \Email\Model\CustomerModel();
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                
                $row = $email_customer_model->where(array('customers_email'=>$data['2']))->find();

                if(empty($row)){
                    $data = array(
                        'customers_firstname'=>$data['0'],
                        'customer_lastname'=>$data['1'],
                        'customers_email'=>$data['2'],
                    );
                    $num_success++;
                    $email_customer_model->add($data);
                }
            }
            fclose($handle);
        }

        
        $this->success('成功导入'.$num_success.'条邮箱信息!',U('Email/Customer/List'));
    }
    
    public function SendmailAction($customer_email_id, $email_template_id){
        $email_customer_model = new \Email\Model\CustomerModel();
        $row = $email_customer_model->where(array('email_id'=>$customer_email_id))->find();
        $email = $row['customers_email'];
        $email_name = empty($row['customers_firstname'])?substr($email,0, strpos($email, '@')):$entry['customers_firstname'].' '.$entry['customer_lastname'];
        
        $email_template_model = new \Email\Model\EmailTemplateModel();
        $row = $email_template_model->where(array('email_template_id'=>$email_template_id))->find();
        $subject = html_entity_decode($row['email_template_title']);
        $email_html = html_entity_decode($row['email_template_content']);
        $email_text = html_entity_decode($row['email_template_content']);
        
        
        $smtp_configure = array(
            'host'=>'smtp-mail.outlook.com',
            'user'=>'fortunenetwebmaster@hotmail.com',
            'pwd' =>'19871020F0rtune',
            'port'=>25,
            'from'=>'fortunenetwebmaster@hotmail.com',
            'from_name'=>'fortunenetwebmaster',
            'replyto'=>'fortunenetwebmaster@hotmail.com',
            'replyto_name'=>'fortunenetwebmaster'
        );
        
        
        vendor('phpMailer.PHPMailerAutoload');
        $mail = new \PHPMailer;
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host     = $smtp_configure['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $smtp_configure['user'];                 // SMTP username
        $mail->Password = $smtp_configure['pwd'];                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $smtp_configure['port'];                                    // TCP port to connect to
        $mail->Timeout = 30;

        $mail->From     = $smtp_configure['from'];
        $mail->FromName = $smtp_configure['from_name'];
        $mail->addAddress($email, $email_name);     // Add a recipient
        $mail->addReplyTo($smtp_configure['replyto'], $smtp_configure['replyto_name']);

        $mail->Subject = $subject;
        $mail->isHTML();
        $mail->Body = $email_html;
        $mail->AltBody = $email_text;

        $result = $mail->send();
       
        if($result){
            $response = array('error'=>false);
            D('email_logs')->add(array(
                'customer_email_id'=>$customer_email_id,
                'email_template_id'=>$email_template_id,
                'date_send'=>date('Y-m-d H:i:s')
            ));
            
        }else
            $response = array('error'=>true, 'erro_info'=>$mail->ErrorInfo);
        
        sleep(1);
        $this->ajaxReturn($response, 'JSON');
    }
}