<?php
return array (
    'TMPL_CACHE_ON'=>false,
    'DB_FIELDS_CACHE'=>false,
    
    
    // 'MULTI_MODULE' => false,// 关闭多模块访问
    // 'MODULE_DENY_LIST' => array('Common','Runtime','Api'),// 设置禁止访问的模块列表
    // 'MODULE_ALLOW_LIST' => array('Home','Admin','User'),//设置允许访问列表
    'DEFAULT_MODULE' => 'Order', // 默认模块
    'DEFAULT_CONTROLLER' => 'Order', // 默认控制器
    'DEFAULT_ACTION' => 'list', // 默认控制器
    'ACTION_SUFFIX' => 'Action',
    'URL_CASE_INSENSITIVE' => true, //设置为true的时候表示URL地址不区分大小写，这个也是框架在部署模式下面的默认设置,当开启调试模式的情况下，这个参数是false
    'TMPL_TEMPLATE_SUFFIX' => '.php',
    'LAYOUT_ON' => true,
    'LOAD_EXT_FILE' => 'functions,define',
    'LOAD_EXT_CONFIG' => 'config.auth,config.database,config.system',
    'URL_HTML_SUFFIX' => '',
	'SHOW_PAGE_TRACE'=>true
);