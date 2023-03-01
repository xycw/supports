<?php
namespace Common\Controller;
use Think\Controller;

class HtmlController extends Controller{
	
	public function html_startAction(){
		layout(false);//一定要关闭全局布局,否则如果其它模块调用时会可能可能会陷入无限循环
		echo $this->fetch(T('Common@Html/html_start'));
		layout(true);
	}
	
	public function html_endAction(){
		layout(false);
		echo $this->fetch(T('Common@Html/html_end'));
		layout(true);
	}
	
}