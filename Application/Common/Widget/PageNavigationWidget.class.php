<?php
namespace Common\Widget;
use Think\Controller;

/*
 * 通用控制器
 * 作用：布局,SEO,公共变量
 */
class PageNavigationWidget extends Controller{
	/*
	 * $page 	  当前页
	 * $num	       每页记录数
	 * $count  数据记录总条数
	 * $name	 URL
	 */
	public function pageAction($page, $num, $count, $name, $data=array(), $max_show=6){
		$page_num = ceil($count/$num);//页码总数
		
		$page_from = $page-ceil($max_show/2);
		$page_from = $page_from<1?1:$page_from;
		$page_to	 = $page_from+$max_show;
		if ($page_to>$page_num) {
			$page_from = $page_from-($page_to-$page_num);
			$page_from = $page_from<1?1:$page_from;
			$page_to = $page_num;
		}
		
		$data['page'] = 1;
		$p = array();
		$p[] = array(
				'text'=>'首页',
				'url'=>U($name,$data),
		);		
		$data['page'] = $page>1?$page-1:1;
		$p[] = array(
			'text'=>'上一页',
			'url'=>U($name,$data),
		);
		for ($i=$page_from;$i<=$page_to;$i++){
			$data['page'] = $i;
			$p[] = array(
				'text'=>$i,
				'url'=>U($name,$data),
				'active'=>($i==$page?1:0)	
			);
		}
		$data['page'] = $page<$page_num?$page+1:$page_num;
		$p[] = array(
				'text'=>'下一页',
				'url'=>U($name,$data),
		);
		$data['page'] = $page_num;
		$p[] = array(
				'text'=>'尾页',
				'url'=>U($name,$data),
		);
		$this->assign('p', $p);
		layout(false);
		$this->display(T('Common@PageNavigation/page'));
		layout(true);
	}
}