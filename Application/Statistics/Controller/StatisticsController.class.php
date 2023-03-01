<?php
namespace Statistics\Controller;
use Common\Controller\CommonController;
class StatisticsController extends CommonController {
	public function indexAction() {
		$where = array();
		$page_data = array();
		if (I('site_id') != '') {
			$params_site_id = I('site_id');
			$params_site_id = explode('_', $params_site_id);
			$where['o.site_id'] = array('IN', $params_site_id);
			$page_data['site_id'] = I('site_id');
			$this->assign('site_id_select', $params_site_id);
		}
		if(I('start_date') != ''){
			$start_date = date('Y-m-d', strtotime(I('start_date')));
		}else{
			$start_date = date('Y-m-d', strtotime("-1 month"));  
		}
		if(I('end_date') != ''){
			$end_date = date('Y-m-d', strtotime(I('end_date')));
		}else{
			$end_date = date('Y-m-d');
		}
		$where['o.date_purchased'] = array('between', array($start_date . ' 00:00:00', $end_date . ' 23:59:59'));
		$page = I('page', 1);
		$join = array('__ORDERS_PRODUCTS__ op ON o.site_id=op.site_id AND o.orders_id=op.orders_id');
		if(I('order_status', 1) == 1){
			$join[] = '__ORDERS_REMARK__ r ON o.site_id=r.site_id AND o.orders_id=r.orders_id';
			$where['r.order_status_remark'] = array(array('exp', 'IS NOT NULL'), array('not in', array('待处理', '', '付款失败or未付款', '付款确认中', '订单取消', '拒付')), 'AND');
			$page_data['order_status'] = 1;
		}
		$orders = M('Orders');
		$export = I('export', '');
		if($export == '导出'){
			$list = $orders->alias('o')->join($join)->where($where)->field('op.products_model,SUM(op.products_quantity) AS num')->group('op.products_model')->order('num DESC')->select();
		}else{
			$list_row = 100;
			$sub_query = $orders->alias('o')->join($join)->where($where)->field('op.products_model')->group('op.products_model')->select(false);
			$total = M()->table($sub_query . ' a')->count();
			$list = $orders->alias('o')->join($join)->where($where)->field('op.products_model,SUM(op.products_quantity) AS num')->group('op.products_model')->order('num DESC')->page($page, $list_row)->select();
		}
		foreach ($list as $k => $v){
			$list[$k]['products_name'] = M('Orders')->alias('o')->join($join)->where("op.products_model='" . $v['products_model'] . "'")->order('o.date_purchased DESC')->getField('products_name');
		}
		if($export == '导出'){
			$file_name = '销量' . time() . rand(100,999) . '.csv';
			$csv_file = DIR_FS_TEMP . $file_name;
			$fp = fopen($csv_file, 'w');
			fputcsv($fp, array('型号','销量','产品名称'));
			foreach ($list as $v){
				fputcsv($fp, $v);
			}
			fclose($fp);
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header('Content-disposition: attachment; filename=' . $file_name); //文件名
			header("Content-Type: application/csv"); //zip格式的
			header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
			header('Content-Length: '. filesize($csv_file)); //告诉浏览器，文件大小
			@readfile($csv_file);
			@unlink($csv_file);
			exit();
		}
		$options_site_name = array();
		$data_site = M('Site')->where(array('status' => 1))->order('site_id asc')->select();
		if ($data_site) {
			foreach ($data_site as $row) {
				$site_name[$row['site_id']] = $options_site_name[$row['type']][$row['site_id']] = $row['site_name'];
			}
		}
		$this->assign('site_name', $site_name);
		$this->assign('options_site_name', $options_site_name);
		$this->assign('start_date', $start_date);
		$this->assign('end_date', $end_date);
		$page_data['start_date'] = $start_date;
		$page_data['end_date'] = $end_date;
		$this->assign('list', $list);
		$this->assign('total', $total);
		$this->assign('list_row', $list_row);
		$this->assign('page_data', $page_data);
		$this->assign('page', $page);
		$this->display();
	}

	public function searchkeywordAction() {
		$where = array();
		$page_data = array();
		if (I('site_id') != '') {
			$params_site_id = I('site_id');
			$params_site_id = explode('_', $params_site_id);
			$where['site_id'] = array('IN', $params_site_id);
			$page_data['site_id'] = I('site_id');
			$this->assign('site_id_select', $params_site_id);
		}
		$keyword = I('keyword');
		if ($keyword != '') {
			$where['keyword'] = $page_data['keyword'] = $keyword;
			$this->assign('keyword', $keyword);
		}
		if(I('start_date') != ''){
			$start_date = date('Y-m-d', strtotime(I('start_date')));
		}else{
			$start_date = date('Y-m-d', strtotime("-1 month"));
		}
		if(I('end_date') != ''){
			$end_date = date('Y-m-d', strtotime(I('end_date')));
		}else{
			$end_date = date('Y-m-d');
		}
		$where['date_added'] = array('between', array($start_date . ' 00:00:00', $end_date . ' 23:59:59'));
		$this->assign('start_date', $start_date);
		$this->assign('end_date', $end_date);
		$page_data['start_date'] = $start_date;
		$page_data['end_date'] = $end_date;
		$page = I('page', 1);
		$search_keyword = M('SearchKeyword');
		$export = I('export', '');
		if($export == '导出'){
			$list = $search_keyword->field('keyword,SUM(count) AS num')->where($where)->group('keyword')->order('num DESC')->select();
		}else{
			$list_row = 100;
			$sub_query = $search_keyword->where($where)->field('keyword')->group('keyword')->select(false);
			$total = M()->table($sub_query . ' a')->count();
			$list = $search_keyword->field('keyword,SUM(count) AS num')->where($where)->group('keyword')->order('num DESC')->page($page, $list_row)->select();
		}
		if($export == '导出'){
			$file_name = '搜索统计' . time() . rand(100,999) . '.csv';
			$csv_file = DIR_FS_TEMP . $file_name;
			$fp = fopen($csv_file, 'w');
			fputcsv($fp, array('搜索词','搜索次数'));
			foreach ($list as $v){
				fputcsv($fp, $v);
			}
			fclose($fp);
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header('Content-disposition: attachment; filename=' . $file_name); //文件名
			header("Content-Type: application/csv"); //zip格式的
			header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
			header('Content-Length: '. filesize($csv_file)); //告诉浏览器，文件大小
			@readfile($csv_file);
			@unlink($csv_file);
			exit();
		}
		$site_list = M('Site')->where(array('status' => 1,'type' => 10))->order('site_id asc')->getField('site_id,site_name');
		$this->assign('site_list', $site_list);
		$this->assign('list', $list);
		$this->assign('total', $total);
		$this->assign('list_row', $list_row);
		$this->assign('page_data', $page_data);
		$this->assign('page', $page);
		$this->display();
	}

	public function ipAccessLogAction() {
		$where = array();
		$page_data = array();
		if (I('site_id') != '') {
			$params_site_id = I('site_id');
			$params_site_id = explode('_', $params_site_id);
			$where['site_id'] = array('IN', $params_site_id);
			$page_data['site_id'] = I('site_id');
			$this->assign('site_id_select', $params_site_id);
		}
		$ip = I('ip');
		if ($ip != '') {
			$where['ip'] = $page_data['ip'] = $ip;
			$this->assign('ip', $ip);
		}
		if(I('start_date') != ''){
			$start_date = date('Y-m-d', strtotime(I('start_date')));
		}else{
			$start_date = date('Y-m-d', strtotime("-1 month"));
		}
		if(I('end_date') != ''){
			$end_date = date('Y-m-d', strtotime(I('end_date')));
		}else{
			$end_date = date('Y-m-d');
		}
		$where['date_added'] = array('between', array($start_date . ' 00:00:00', $end_date . ' 23:59:59'));
		$this->assign('start_date', $start_date);
		$this->assign('end_date', $end_date);
		$page_data['start_date'] = $start_date;
		$page_data['end_date'] = $end_date;
		$page = I('page', 1);
		$ip_access_log = M('IpAccessLog');
		$export = I('export', '');
		if($export == '导出'){
			$list = $ip_access_log->field('ip,SUM(total) AS num')->where($where)->group('ip')->order('num DESC')->select();
		}else{
			$list_row = 100;
			$sub_query = $ip_access_log->where($where)->field('ip')->group('ip')->select(false);
			$total = M()->table($sub_query . ' a')->count();
			$list = $ip_access_log->field('ip,SUM(total) AS num')->where($where)->group('ip')->order('num DESC')->page($page, $list_row)->select();
		}
		foreach ($list as $k => $v){
			$ip_access_log_info = $ip_access_log->field('http_access,http_referer,date_added')->where("ip='" . $v['ip'] . "'")->order('date_added DESC')->find();
			$list[$k] = array_merge($v,$ip_access_log_info);
		}
		if($export == '导出'){
			$file_name = 'IP统计' . time() . rand(100,999) . '.csv';
			$csv_file = DIR_FS_TEMP . $file_name;
			$fp = fopen($csv_file, 'w');
			fputcsv($fp, array('IP地址','访问次数','访问地址','来源地址','最后一次访问时间'));
			foreach ($list as $v){
				fputcsv($fp, $v);
			}
			fclose($fp);
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header('Content-disposition: attachment; filename=' . $file_name); //文件名
			header("Content-Type: application/csv"); //zip格式的
			header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
			header('Content-Length: '. filesize($csv_file)); //告诉浏览器，文件大小
			@readfile($csv_file);
			@unlink($csv_file);
			exit();
		}
		$site_list = M('Site')->where(array('status' => 1,'type' => 10))->order('site_id asc')->getField('site_id,site_name');
		$this->assign('site_list', $site_list);
		$this->assign('list', $list);
		$this->assign('total', $total);
		$this->assign('list_row', $list_row);
		$this->assign('page_data', $page_data);
		$this->assign('page', $page);
		$this->display();
	}
}