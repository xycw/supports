<?php

namespace Common\Controller;

use Think\Controller;

class LayoutController extends Controller {

    public function menuAction() {
        layout(false); //一定要关闭全局布局,否则如果其它模块调用时会可能可能会陷入无限循环
        $this->display(T('Common@Layout/menu'));
        layout(true);
    }

    public function footerAction() {
        layout(false); //一定要关闭全局布局,否则如果其它模块调用时会可能可能会陷入无限循环
        $this->display(T('Common@Layout/footer'));
        layout(true);
    }

}
