<?php if (!defined('THINK_PATH')) exit();?><nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">订单管理系统</a>
        </div>

        <style>
            .nav>li>a {
                padding: 10px 12px;
            }
        </style>
        <ul class="nav navbar-nav">
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2,6))): ?><li><a href="<?php echo U('Order/Order/list/order_status_remark/已确认付款');?>">订单</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,4))): ?><li><a href="<?php echo U('Order/Purchase/index/order_status/待订货');?>">订货</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,4,5))): ?><li><a href="<?php echo U('Order/Finance/index');?>">订货(财务版)</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2))): ?><li><a href="<?php echo U('Customers/Customers/list');?>">客户</a></li>
            <li><a href="<?php echo U('Customers/Contact/list');?>">客户留言</a></li>
            <li><a href="<?php echo U('Product/Database/list');?>">产品管理</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2,3,6))): ?><li><a href="<?php echo U('Site/Site/list');?>">独立站</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2,6))): ?><li><a href="<?php echo U('Site/Site/list2');?>">平台站</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2))): ?><li><a href="<?php echo U('Site/Site/listb');?>">B站</a></li>
            <li><a href="<?php echo U('Order/PaymentModuleStatistics/index');?>">统计</a></li>
            <li><a href="<?php echo U('Marketing/Email/index');?>">邮件推广(beta)</a></li>
            <li><a href="<?php echo U('Marketing/Email/no_order_customers');?>">未下单客户</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2,3))): ?><li><a href="<?php echo U('Domains/Domains/index');?>">域名</a></li><?php endif; ?>
            <?php if(in_array(session(C('USER_INFO').'.profile_id'), array(1,2))): ?><li><a href="<?php echo U('CustomerService/CustomerService/index');?>">客服</a></li><?php endif; ?>
        </ul>

        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">配置<span class="caret"></span></a>
                <ul class="dropdown-menu">
            		<?php if(session(C('USER_INFO').'.profile_id') == 1): ?><li><a href="<?php echo U('Site/System/list');?>">配置信息</a></li><?php endif; ?>
                    <li><a href="<?php echo U('Order/Order/clear_doc');?>" style="color:red" id="link-clear-cache">清理系统缓存</a></li>
                </ul>
            </li>            
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">用户中心<span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo U('User/User/userList');?>">系统用户</a></li>
                    <li><a href="<?php echo U('User/Auth/logout');?>">退出(<?php echo session(C('USER_INFO').'.username')?>)</a></li>
                </ul>
            </li>
            <?php if(session(C('USER_INFO').'.profile_id') == 1): ?><li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">开发人员工具<span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo U('Sys/FileUpload/file_list');?>">网站文件上传</a></li>
                    <li><a href="<?php echo U('Sys/SqlExe/index');?>">网站SQL批量执行</a></li>
                    <li><a href="<?php echo U('Sys/Upgrade/patch_list');?>">网站接口更新</a></li>
                </ul>
            </li><?php endif; ?>
        </ul>
    </div>
</nav>