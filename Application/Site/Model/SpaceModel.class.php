<?php
namespace Site\Model;
use Think\Model\RelationModel;

class SpaceModel extends RelationModel {
    
    protected $tableName = 'space';
    
    protected $_link = array(
        'db'=>array(
            'mapping_type' => self::HAS_MANY,
            'class_name'   => 'Site/SpaceDb',
            'foreign_key'  => array('space_id'),
            'mapping_key'  => array('space_id'),
            'mapping_name' => 'db',
        ),
        'site'=>array(
            'mapping_type' => self::HAS_MANY,
            'class_name'   => 'Site/Site',
            'foreign_key'  => array('space_id'),
            'mapping_key'  => array('space_id'),
            'mapping_name' => 'site',
            'mapping_fields'=>array('site_name','site_id'),
        ),
    );    
    
    protected function _after_select(&$result,$options) {
        $time_today = time();
        foreach ($result as $k=>$entry){
            $entry['days_expired'] = floor((strtotime($entry['date_expired'])-$time_today)/86400);//到过期还剩的天数
            
            $result[$k] = $entry;
        }
        
        parent::_after_select($result, $options);
    }
}

