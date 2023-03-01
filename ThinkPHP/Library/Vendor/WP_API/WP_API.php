<?php
namespace WP_API;

vendor('Request.Requests');
\Requests::register_autoloader();

class WP_API{
    private $_base_url;
    private $_jwt_token;
    private $_username;
    private $_password;
    
    const API_GET_JWT_TOKEN  = '/wp-json/jwt-auth/v1/token';
    const API_VALIDATE_JWT_TOKEN = '/wp-json/jwt-auth/v1/token/validate';
    const API_POST = '/wp-json/wp/v2/posts';
    const API_MEDIA = '/wp-json/wp/v2/media';
    const API_TAGS = '/wp-json/wp/v2/tags';
    const API_CATEGORIES = '/wp-json/wp/v2/categories';

    
    function __construct($base_url, $username, $password) {
        $this->_base_url = $base_url;
        $this->_username = $username;
        $this->_password = $password;
        $this->get_jwt_token();
    }
    
    function get_jwt_token(){
        if(isset($this->_jwt_token)) return $this->_jwt_token;
        
        $data = $this->_request('post', $this->_base_url . \WP_API\WP_API::API_GET_JWT_TOKEN, array(), array('username'=>$this->_username, 'password'=>$this->_password));
        $token = $data['token'];
        $this->_jwt_token = $token;
        return $token;
    }
    
    function validate_jwt_token($token){
        $data = $this->_request('post', $this->_base_url . \WP_API\WP_API::API_VALIDATE_JWT_TOKEN, array('Authorization'=>'Bearer '.$token));
        if($data['data']['status']==200 && $data['code']=='jwt_auth_valid_token')
            return true;
        else
            return false;
    }
    
    function add_media($file){
        $data = array(
            'file' => '@' . $file
        );
        $data = $this->_request('post', $this->_base_url . \WP_API\WP_API::API_MEDIA, array(), $data);
        return $data;
    }
    
    function search_category($category=''){
        $data = $this->_request('get', $this->_base_url . \WP_API\WP_API::API_CATEGORIES.($category==''?'':'?search='.$category));
        
        return $data;
    }
    
    
    function search_tag($tag){
        $data = $this->_request('get', $this->_base_url . \WP_API\WP_API::API_TAGS.'?search='.$tag);
        
        return $data;
    }
    
    function create_tag($tag){
        $data = $this->_request('post', $this->_base_url . \WP_API\WP_API::API_TAGS, array(), array('name'=>$tag));
        
        if($data['message'])
            return $data['message'];
        else
            return $data;
    }


    //失败返回失败字符串原因 
    //成功返回POST数组
    function write_post($title, $content, $post_id=0, $tag=array(), $category=array()){
        $post = array(
            'title'   => $title, 
            'content' => $content,
            'status'  => 'publish',  
            'tags'    => $tag,
            'categories'    => $category,
        );
        if($post_id)
            $url = $this->_base_url . \WP_API\WP_API::API_POST . '/'.$post_id;
        else
            $url = $this->_base_url . \WP_API\WP_API::API_POST;

        $data = $this->_request('post', $url, array(), $post);

        if($data['message'])
            return $data['message'];
        else
            return $data;
    }
    
    function delete_post($id, $force=true){
        $data = $this->_request('delete', $this->_base_url . \WP_API\WP_API::API_POST.'/'.$id.'?force='.($force?'true':'false'));
        if(isset($data['deleted']))
            return $data['deleted'];
        else 
            return $data['code'];
       
    }
    
    private function _request($method, $url, $head=array(), $data=array()){
        if(!isset($head['Authorization']) && $this->_jwt_token!='')
            $head['Authorization'] = 'Bearer '.$this->_jwt_token;
        $response = \Requests::$method($url, $head, $data);
//        echo $response->body;
        $data  = json_decode($response->body, true);       
        if(!is_array($data))
            throw new \WP_API\WP_API_ERROR('prase data from '.$method.' request '.$url. ' failure!');
        return $data;        
    }
    
}

class WP_API_ERROR extends \Exception{
    function __construct($message = "", $code = 0) {
        parent::__construct($message, $code);
    }
}