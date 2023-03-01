<?php
namespace Wordpress\Controller;

use Think\Controller;

class ArticleController extends Controller {

    function listAction(){
        $articles = new \Wordpress\Model\ArticleModel();
        $join = array(
            'JOIN __ARTICLES_TYPE__ at ON at.type_id=a.type_id',
        );
        $where  = array();
        $data_page = array();
        if(I('article_type', '')!=''){
            $where['a.type_id'] = I('article_type');
            $data_page['article_type'] = I('article_type');
            $this->assign('article_type_selected', I('article_type'));
        }
        if(I('post_status', '')!=''){
            $join[] = 'LEFT JOIN __ARTICLES_TO_SITE__ a2s ON a2s.articles_id=a.articles_id';
            $post_status = I('post_status');
            $data_page['article_type'] = $post_status;
            $this->assign('post_status_selected', $post_status);
            if($post_status=='y'){
                $where['a2s.site_id'] = I('site_id');
            }else{
                $where[] = array(
                     '_complex' => array(
                        '_logic' => 'OR',
                         'a2s.articles_id' =>array('exp', 'IS NULL'),
                         'a2s.site_id' =>array('neq', I('site_id')),
                    )
                );
            }
            $this->assign('site_id_selected', I('site_id'));
        }        
        $page   = I('page', 1); //当前页码
        $num    = 1000; //每页显示订单数
        $count  = $articles->alias('a')->join($join)->where($where)->count('DISTINCT(a.articles_id)');
        $this->assign('num', $num);
        $this->assign('page', $page);
        $this->assign('count', $count);
        $array_articles = $articles->alias('a')->join($join)->where($where)->order('add_date desc,articles_id desc')->field(array('a.*', 'at.type_name'))->page($page, $num)->select();
        $this->assign('array_articles', $array_articles);
        
        $where = array('type'=> \Site\Model\SiteModel::TYPE_WORDPRESS, 'status'=>1);
        $wp_site   = M('site')->where($where)->select();      
        $options_wp_site = array();
        foreach($wp_site as $entry){
            $options_wp_site[$entry['site_id']] = $entry['site_name'];
        }
        $this->assign('options_wp_site', $options_wp_site);
        
        $articles_typs          = D('articles_type')->select();
        $options_articles_types  = array();
        foreach($articles_typs as $entry){
            $options_articles_types[$entry['type_id']] = $entry['type_name'];
        }
        $this->assign('options_post_status', array('y'=>'已发', 'n'=>'未发', ''=>'不限'));
        $this->assign('options_articles_types', $options_articles_types);
        $this->assign('data_page', $data_page);
        $this->display();
    }
       
    function addAction(){
        $model_article = new \Wordpress\Model\ArticleModel();
        if(IS_POST){            
            $data = array(
                'type_id'               =>I('articles_type'),
                'articles_title'        =>I('articles_title'),
                'articles_content'      =>I('articles_content'),
                'articles_tags'         =>I('articles_tags'),
                'add_date'              =>date('Y-m-d')
            );
            $articles_id = $model_article->add($data);
            if($articles_id)
                $this->success('新增成功!',U('edit', 'id='.$articles_id));
            else
                $this->error ('新增失败!');
        }
        
        $this->assign('options_type', $model_article->get_types());
        $this->display('edit');
    }
    
    function editAction(){
        $model_article = new \Wordpress\Model\ArticleModel();
        if(IS_POST){
            $data = array(
                'type_id'               =>I('articles_type'),                
                'articles_id'           =>I('articles_id'),
                'articles_title'        =>I('articles_title'),
                'articles_content'      =>I('articles_content'),
                'articles_tags'         =>I('articles_tags'),
            );
            $model_article->save($data);
            $this->success('保存成功!',U('edit', 'id='.I('articles_id')));
        }
        
        $data_article  = $model_article->where(array('articles_id'=>I('id')))->find();

        $this->assign('options_type', $model_article->get_types());
        $this->assign('articles_type', $data_article['type_id']);
        $this->assign('data_article', $data_article);
        $this->display();
    }
    
    function ajax_post_linkAction($article_id){
        $join = array(
            'JOIN __SITE__ s ON s.site_id=a2s.site_id',
        );        
        $data = D('articles_to_site')->alias('a2s')->join($join)->where(array('articles_id'=>$article_id))->select();
        
        $this->ajaxReturn($data);
    }
            
    function ajax_get_categoriesAction($site_id){
        vendor('WP_API.WP_API');
        $site_row = D('site')->find($site_id);
        $site_url = trim($site_row['site_index'], ' /');
        $wp_api = new \WP_API\WP_API($site_url, $site_row['wp_admin'], $site_row['wp_password']);
        $data = $wp_api->search_category();
        $this->ajaxReturn($data);
    }
    
    function postAction(){
        $model_article = new \Wordpress\Model\ArticleModel();
        $data_article  = $model_article->where(array('articles_id'=>I('id')))->find();

        if(IS_POST){
            $post_site = I('post_site', array());
            if(sizeof($post_site)==0)
                $this->error ('请选择要发布的网站!');
            vendor('WP_API.WP_API');
            $articles_id = I('id');
            
            $num_failure = 0;
            $num_success = 0;
            $action = I('action');
            if($action=='delete'){
                foreach ($post_site as $site_id){
                    $site_row = D('site')->find($site_id);
                    $site_url = trim($site_row['site_index'], ' /');
                    $wp_api = new \WP_API\WP_API($site_url, $site_row['wp_admin'], $site_row['wp_password']);
                    $row = D('articles_to_site')->where(array('articles_id'=>$articles_id, 'site_id'=>$site_id))->find();
                    if($row){
                        $data = $wp_api->delete_post($row['post_id']);
                        if($data===true || $data=='rest_post_invalid_id'){
                            $num_success++;
                            D('articles_to_site')->where(array('articles_id'=>$articles_id, 'site_id'=>$site_id))->delete();
                        }else
                            $num_failure++;
                    }else
                        $num_failure++;
                }
                
                $this->success('成功删除:'.$num_success.'<br>失败删除:'.$num_failure, U('Wordpress/Article/post/id/'.$articles_id));
            }else{
                $category = I('category', array());
                foreach ($post_site as $site_id){
                    $site_row = D('site')->find($site_id);
                    $site_url = trim($site_row['site_index'], ' /');
                    $wp_api = new \WP_API\WP_API($site_url, $site_row['wp_admin'], $site_row['wp_password']);
                    $row = D('articles_to_site')->where(array('articles_id'=>$articles_id, 'site_id'=>$site_id))->find();
                    if($row){
                        $articles_post_id = $row['post_id'];
                    }else{
                        $articles_post_id = 0;
                    }
                    $content = html_entity_decode($data_article['articles_content'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
                    $media_upload_success = true;
                    if(preg_match_all('~src="(.*?)"~', $content, $match)){
                        foreach($match[1] as $src){
                            $local_file_key = md5($src);
                            $row = D('medias_to_site')->where(array('local_file_key'=>$local_file_key))->find();
                            if($row && !empty($row['remote_file_link'])){
                                $content = str_replace($src, $row['remote_file_link'], $content);
                            }elseif(file_exists(DIW_FS_ROOT . $src)){
                                $media_data = $wp_api->add_media(DIW_FS_ROOT . $src);
                                if(isset($media_data['id'])){
                                    $post_id = $media_data['id'];
                                    D('medias_to_site')->save(array(
                                        'site_id'=>$site_id,
                                        'post_id'=>$post_id,
                                        'local_file_key'=>$local_file_key,
                                        'local_file_path'=>$src,
                                        'remote_file_link'=>$media_data['media_details']['sizes']['full']['source_url'])
                                    );
                                    $content = str_replace($src, $media_data['media_details']['sizes']['full']['source_url'], $content);
                                }else{
                                    $media_upload_success = false;
                                    break;
                                }
                            }
                        }
                    }
                    if($media_upload_success){
                        $tarticles_tags = explode(',', $data_article['articles_tags']);
                        $tags = array();
                        foreach($tarticles_tags  as $tag){
                            $tags_array = $wp_api->search_tag($tag);
                            if(sizeof($tags_array)){
                                foreach($tags_array as $obj_tag){
                                    if($tag==$obj_tag['name'])
                                        break;
                                }
                            }else{
                                $obj_tag = $wp_api->create_tag($tag);
                            }
                            $tags[] = $obj_tag['id'];
                        }
                        
                        $cat = isset($category[$site_id])?array($category[$site_id]):array();
                        $result = $wp_api->write_post($data_article['articles_title'], $content, $articles_post_id, $tags, $cat);
                        if(isset($result['id'])){
                            $num_success++;
                            if(!$articles_post_id)
                                D('articles_to_site')->add(array('articles_id'=>$articles_id, 'site_id'=>$site_id, 'post_id'=>$result['id']));
                        }else{
                            $num_failure++;
                        }
                    }else
                        $num_failure++;


                }
                if(IS_AJAX)
                    $this->ajaxReturn (array('success'=>$num_success, 'failure'=>$num_failure));
                else
                    $this->success('成功发布:'.$num_success.'<br>失败发布:'.$num_failure, U('Wordpress/Article/post/id/'.$articles_id));
            }
        }
        
        $where = array('type'=> \Site\Model\SiteModel::TYPE_WORDPRESS, 'status'=>1);

        $where_available  = array_merge($where, array('site_id'=>array('exp','not in '.M('articles_to_site')->where(array('articles_id'=>I('id')))->field('site_id')->select(false))));
        $site_available   = M('site')->where($where_available)->select();//未发布的网站

        $where_unavailable = array_merge($where, array('site_id'=>array('exp','in '.M('articles_to_site')->where(array('articles_id'=>I('id')))->field('site_id')->select(false))));
        $site_unavailable  = M('site')->where($where_unavailable)->select();//未发布的网站

        $this->assign('site_available', $site_available);
        $this->assign('site_unavailable', $site_unavailable);
        $this->assign('data_article', $data_article);
        $this->display();
    }
    
    
}
