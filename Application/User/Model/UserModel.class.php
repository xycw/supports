<?php
namespace User\Model;

use Think\Model\RelationModel;

class UserModel extends RelationModel {

    protected $tableName = 'users';
    
    protected $_link = array(
        'site' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'users_to_site',
            'foreign_key' => array('user_id'),
            'mapping_key' => array('user_id'),
            'mapping_name' => 'site',
            'mapping_fields'    =>'site_id'
        ),
    );

    public function __construct() {
        parent::__construct();
    }

    function isLogin() {
        
    }

    function _after_find(&$result, $options) {
        parent::_after_find($result, $options);
        
        if(is_array($result['site'])){
            $site = array();
            foreach ($result['site'] as $entry){
                $site[] = $entry['site_id'];
            }     
            $result['site'] = $site;
        }
    }
    
    function _after_select(&$result, $options) {
        parent::_after_select($result, $options);
        if(is_array($result)){
            foreach($result as $k=>$row){
                if(is_array($row['site'])){
                    $site = array();
                    foreach ($row['site'] as $entry){
                        $site[] = $entry['site_id'];
                    }     
                    $result[$k]['site'] = $site;
                }
            }
        }
    }
}
