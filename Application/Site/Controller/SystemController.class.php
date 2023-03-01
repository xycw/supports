<?php 
namespace Site\Controller;
use Think\Controller;
class SystemController extends Controller{
	public function listAction(){
		if(IS_POST){
			$system_area=I('system_area','');
			if($system_area=='') $this->error('订单归属不能为空');
			$system_brand=I('system_brand','');
			if($system_brand=='') $this->error('品牌不能为空');
			$system_url=I('system_url','');
			if($system_url=='') $this->error('域名代理商URL不能为空');
			$system_thirdgw=I('system_thirdgw','');
			if($system_thirdgw=='') $this->error('第三方DNS官网不能为空');
			$save=array(
					'system_area'=>$system_area,
					'system_brand'=>$system_brand,
					'system_url'=>$system_url,
					'system_thirdgw'=>$system_thirdgw
			);
			file_put_contents(CONF_PATH.'/config.system.php',"<?php\nreturn ".var_export($save,true).";\n?>");
			$this->success('写入成功');
		}
		
		$this->display();
	}
}
?>