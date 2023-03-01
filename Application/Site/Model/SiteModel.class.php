<?php
namespace Site\Model;
use Think\Model\RelationModel;

class SiteModel extends RelationModel {
    
    protected $tableName = 'site';
    protected $_link = array(
        'configuration' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'configuration',
            'foreign_key' => array('site_id'),
            'mapping_key' => array('site_id'),
            'mapping_name' => 'configuration',
            'mapping_order'=>'sort_order asc',
        )
    );
    protected function _options_filter(&$options) {
        parent::_options_filter($options);
        //非管理员 限制其业务权限
        if(!in_array(session(C('USER_INFO').'.profile_id'), array(1,6))){
            $sql  = D('users_to_site')->where(array('user_id'=>session(C('USER_INFO').'.user_id')))->field('site_id')->select(false);
            if(isset($options['where'])){               
                if(isset($options['where']['site_id'])){
                    if(isset($options['where']['_complex'])){
                        $options['where']['_complex'] = array(
                            $options['where']['_complex'],
                            '_complex'=>array(
                                'site_id'=>$options['where']['site_id'],
                                '_complex'=>array(
                                    'site_id'=>array('IN', $sql, 'exp'),
                                ),
                            )
                        );
                    }else                    
                        $options['where']['_complex'] = array(
                            '_logic'=>'AND',	
                            'site_id'=>$options['where']['site_id'],
                            '_complex'=>array(
                                'site_id'=>array('IN', $sql, 'exp'),
                            ),
                        );
                    unset($options['where']['site_id']);
                }else
                    $options['where'] = array_merge($options['where'], array('site_id'=>array('IN', $sql, 'exp')));
            }else{
                $options['where'] = array('site_id'=>array('IN', $sql, 'exp'));
            }            
        }
    }


    protected function _after_select(&$result,$options) {
        $time_today = time();
        foreach ($result as $k=>$entry){
            $entry['days_expired'] = floor((strtotime($entry['date_expired'])-$time_today)/86400)+1;//到过期还剩的天数
            if($entry['ssl_expired']<>'0000-00-00'){
               $entry['days_ssl_expired'] = floor((strtotime($entry['ssl_expired'])-$time_today)/86400)+1;//到过期还剩的天数 
           }else{
               $entry['days_ssl_expired']='unknown';
           }
            $result[$k] = $entry;
        }

   
        
        parent::_after_select($result, $options);
        
        foreach ($result as $k=>$entry){
            if(isset($entry['space'])){
                $entry['space']['days_expired'] = floor((strtotime($entry['space']['date_expired'])-$time_today)/86400)+1;//到过期还剩的天数
                $result[$k] = $entry;
            }
            
            if(isset($entry['configuration'])){
                foreach($entry['configuration'] as $_configuration){
                    
                    if($_configuration['configuration_key']=='MODULE_PAYMENT_TPO_STATUS' && $_configuration['configuration_value']=='True'){
                        $table = 'url_zwb';
                    }elseif($_configuration['configuration_key']=='MODULE_PAYMENT_ZDCHECKOUT2F3D_STATUS' && $_configuration['configuration_value']=='True'){
                        $table = 'url_zd';
                    }elseif($_configuration['configuration_key']=='MODULE_PAYMENT_ZDCHECKOUT3F_STATUS' && $_configuration['configuration_value']=='True'){
                        $table = 'url_zd';
                    }elseif($_configuration['configuration_key']=='MODULE_PAYMENT_RXHPAY_INLINE_STATUS' && $_configuration['configuration_value']=='True'){
                        $table = 'url_rxh';
                    }else{
                        $table = '';   
                    }
                    
                    if($table!=''){
                        $host_name = parse_url($entry['site_index'], PHP_URL_HOST);
                        $host_name = preg_replace('~^www\.~i', '', $host_name);
                        $extension_info = M($table)->where(array('urls'=>array('like', '%'.$host_name.'%')))->find();
                        if($extension_info){
                            $result[$k]['cfg'][$table] = $extension_info;
                            // var_dump($result[$k]['cfg']);exit;
                        }
                    }
                    $result[$k]['cfg'][$_configuration['configuration_key']] = $_configuration['configuration_value'];
                }
                unset($result[$k]['configuration']);
            }
        }
         //var_dump($result);exit;
    }
}

