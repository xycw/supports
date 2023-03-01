<?php
namespace Wordpress\Model;

use Think\Model;

class ArticleModel extends Model {

    protected $tableName = 'articles';

    protected function _after_select(&$result,$options) {       
        parent::_after_select($result, $options);

        foreach ($result as $k=>$entry){
            $row = D('articles_to_site')->where(array('articles_id'=>$entry['articles_id']))->find();
            $result[$k]['is_post'] = empty($row)?false:true;
        }        
    }
    
    public function get_types(){
        $data = M('articles_type')->select();
        
        $type = array();
        foreach($data as $row){
            $type[$row['type_id']] = $row['type_name'];
        }
        
        return $type;
    }
}
