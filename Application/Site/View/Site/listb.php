<h1>B站.列表</h1>

<a class="btn btn-default" href="<?php echo U('Site/Site/add') ?>">添加网站</a>

<table class="table table-bordered mt5">

    <tr>

        <th>勾选<input type="checkbox" name="check_all" class="pull-left"></th>

        <th>序号</th>

        <th>

            <ul>

                <li>域名(ID#)</li>

                <li>网站首页</li>

                <li>订单前缀</li>

                <li>备用网站</li>

            </ul>

        </th>

        <!-- <th>备注</th> -->

        <th>

        	<ul>

                <li>收款通道:标注</li>

	            <li>域名:到期标注</li>

				<li>网站SSL:到期标注</li>

				<li>remark备注</li>

            </ul>

        </th>

        <th>邮箱信息<br>

            此邮箱用于发送通知客户邮件

        </th>

        <th>操作</th>

    </tr>

    <?php

    $n = 1;

    foreach ($site_list as $entry) {

        ?>

        <tr>

            <td>

                <?php //if($entry['is_sale'] <> 5 || $entry['is_sale']<>-1){?>

                <input type="checkbox" name="site_id_loaddown[]" value="<?php echo $entry['site_id'] ?>">

                <?php// }?>

            </td>

            <td><?php echo $n++ ?></td>

            <td>

                <ul>

                    <li id="site<?php echo $entry['site_id'] ?>-name"><?php echo "域名(ID:" . $entry['site_id'] . "#) " .'<a target="_blank" href="'.$entry['site_index'].'">'. $entry['site_name'].'</a>'; ?> </li>

                    <li class="site_index_spare" rel="<?php echo $entry['site_id'] ?>"><?php echo $entry['site_index_spare'] == '' ? '双击添加域名' : $entry['site_index_spare'] ?></li>

                    <li><?php echo "订单前缀:   <span style='color:#FF0000;font-weight:bold;'>" . $entry['order_no_prefix'] . "</span>"; ?></li>

                    <li>

                        业务员:

                        

						    <?php

							    if (isset($entry['user'][2])) {

							        foreach ($entry['user'][2] as $user) {

							            echo '<span style="color:#FF0000;font-weight:bold;">'.$user['chinese_name'] . '</span>';

							        }

							    }

						    ?>

						 客服昵称:

						    <?php echo '<span style="color:#FF0000;font-weight:bold;">'.$entry['customer_service_name']. '</span>' ?>

                        

                    </li>

                    <li>订单归属:<?php echo '<span style="color:#FF0000;font-weight:bold;">'.$entry['system_area']. '</span>' ?></li>

                    <li>部门归属:<?php echo '<span style="color:#FF0000;font-weight:bold;">'.$system_depart_array[$entry['system_depart']]. '</span>' ?></li>

                    <li>推广员:<?php echo '<span style="color:#FF0000;font-weight:bold;">'.$system_tuiguangy_array[$entry['system_tuiguangy']]. '</span>' ?></li>
                </ul>

            </td>

           <!--  <td id="remark<?php //echo $entry['site_id'] ?>"><?php //echo $entry['remark'] ?> -->

            </td>

            <td>

                <?php

                $payment_module = array(

                    array('key'=>'MODULE_PAYMENT_SECURITY_PINGPONG_STATUS', 'sort'=>'MODULE_PAYMENT_SECURITY_PINGPONG_SORT_ORDER', 'name'=>'pingpong','show'=>'MODULE_PAYMENT_SECURITY_PINGPONG_JUMP_GATE'),

                    array('key'=>'MODULE_PAYMENT_ZDCHECKOUT2F3D_STATUS', 'sort'=>'MODULE_PAYMENT_ZDCHECKOUT2F3D_SORT_ORDER', 'name'=>'佐道(2方3D)', 'extension'=>array('key'=>'url_zd', 'show'=>'<b>通道</b>status')),

                    array('key'=>'MODULE_PAYMENT_ZDCHECKOUT3F_STATUS', 'sort'=>'MODULE_PAYMENT_ZDCHECKOUT3F_SORT_ORDER', 'name'=>'佐道(3方)', 'extension'=>array('key'=>'url_zd', 'show'=>'<b>通道</b>status')),

                    array('key'=>'MODULE_PAYMENT_TPO_STATUS', 'sort'=>'MODULE_PAYMENT_TPO_SORT_ORDER', 'name'=>'中外宝', 'extension'=>array('key'=>'url_zwb', 'show'=>'<b>通道</b>status,<b>交易号</b>transaction_id')),

                    array('key'=>'MODULE_PAYMENT_RXHPAY_STATUS|MODULE_PAYMENT_RXHPAY_INLINE_STATUS', 'sort'=>'MODULE_PAYMENT_RXHPAY_INLINE_SORT_ORDER', 'name'=>'融信汇', 'extension'=>array('key'=>'url_rxh', 'show'=>'<b>通道</b>status')),

                    array('key'=>'MODULE_PAYMENT_MONEYTRANSFERS_STATUS', 'sort'=>'MODULE_PAYMENT_MONEYTRANSFERS_SORT_ORDER', 'name'=>'TW'),

                    array('key'=>'MODULE_PAYMENT_WESTERNUNION_STATUS', 'sort'=>'MODULE_PAYMENT_WESTERNUNION_SORT_ORDER', 'name'=>'WU'),

                    array('key'=>'MODULE_PAYMENT_WIRE_STATUS', 'sort'=>'MODULE_PAYMENT_WIRE_SORT_ORDER', 'name'=>'TT'),

                    array('key'=>'MODULE_PAYMENT_MONEYGRAM_STATUS', 'sort'=>'MODULE_PAYMENT_MONEYGRAM_SORT_ORDER', 'name'=>'MG'),

                );

                ?>

                <ul>

                    <?php

                    // var_dump($entry['cfg']);

                    foreach($payment_module as $_payment_module){

                        if(strpos($_payment_module['key'], '|')){

                            $keys = explode('|', $_payment_module['key']);

                            $status = true;

                            foreach($keys as $key){

                                if(!isset($entry['cfg'][$key]) || $entry['cfg'][$key]=='False'){

                                    $status = false;

                                }

                            }

                        }else{

                            $status = ((isset($entry['cfg'][$_payment_module['key']]) && $entry['cfg'][$_payment_module['key']]=='True')?true:false);

                        }

                        

                        if(isset($_payment_module['extension']) && isset($entry['cfg'][$_payment_module['extension']['key']])){

                            $extension = ' ==> '.strtr($_payment_module['extension']['show'], $entry['cfg'][$_payment_module['extension']['key']]);

                        }else{

                            $extension = '';

                        }

                        

                    ?>

                    <?php

                    if($status){

                    ?>

                    <li style="padding-bottom: 0.5em;"><?php echo '<b>'.$_payment_module['name'].'</b>'.($status?'<span style="color:green;"> 已开启</span> 排序:':'<span style="color:red;"> 未开启</span>').$entry['cfg'][$_payment_module['sort']].$extension; ?></li>

                    <?php

                    }

                    ?>

                    

                    <?php

                    }

                    ?>

                </ul>

                

                	<?php 

                	$styledomain=' ';

                	$stylessl=' ';

                    $styleDshow = ' style="color:#ff0000"';

                	if (($entry['days_expired'] < 7) and ($entry['date_expired'] <> '0000-00-00')){

                		$styledomain= ' style="background-color:#ffff00;color:#ff0000;"'; 

                	}

                	if ((($entry['days_ssl_expired'] < 7)and($entry['ssl_expired'] <> '0000-00-00'))||($entry['days_ssl_expired']=='unknown')){

                		$stylessl= ' style="background-color:#ffff00;color:#ff0000;"';

                	}

                	?>

                <ul>



                	<li><?php echo '<b>系统类型:</b><span style="color:#ff0000;font-weight:bold">'.$entry['system_cms'].'</span>' ?> 

                	 <?php echo '<b>品牌:</b><span style="color:#ff0000;font-weight:bold">'.$entry['system_brand'].'</span>' ?>

                    <?php

                    //echo $entry['is_sale'] ? '<span style="color:red;">批发</span>' : '<span style="color:green;">零售</span>';

                    $is_sale_mark='xxx';

                    switch ($entry['is_sale']) {

                        case '-1':

                            $is_sale_mark = '不清缓存';

                        case '0':

                            $is_sale_mark = '零售';

                            break;

                        case '1':

                            $is_sale_mark = '批发';

                            break;

                        case '2':

                            $is_sale_mark = 'B站';

                            break;

                        case '3':

                            $is_sale_mark = '帽子';

                            break;

                        case '4':

                            $is_sale_mark = '面罩';

                            break;

                        case '5':

                            $is_sale_mark = '袜子';

                            break;

                        case '6':

                            $is_sale_mark = 'anti-virus';

                            break;

                        case '7':

                            $is_sale_mark = '游戏手柄';

                            break;

                        case '8':

                            $is_sale_mark = '测试';

                            break;

                        case '9':

                            $is_sale_mark = 'Other';

                            break;
                        case '10':

                            $is_sale_mark = '定制';

                            break;

                        default:

                            # code...

                            break;

                    }

                    echo '<b>商品数据:</b><span style="color:#ff0000;font-weight:bold">'.$is_sale_mark.'</span>';

                    ?>

                	</li>

                	<hr>

                    <li <?php echo $styledomain; ?> ><?php echo '<b>域名:</b><strong '.$styleDshow.'>'.$entry['days_expired'] . '天后</strong>到期(到期日:<strong '.$styleDshow.'>'.$entry['date_expired'].'</strong>)'; ?></li>

                    <li <?php echo $stylessl; ?> ><?php echo $entry['days_ssl_expired']=='unknown'?'<b>网站SSL:</b>Unkonwn到期日':'<b>网站SSL:</b><strong '.$styleDshow.'>'.$entry['days_ssl_expired'] . '天后</strong>到期(到期日:<strong '.$styleDshow.'>'.$entry['ssl_expired'].'</strong>)'; ?></li>

                    <hr>

                	<li id="remark<?php echo $entry['site_id'] ?>"><?php echo '<b>备注</b>:<br>'.$entry['remark'] ?></li>

                </ul>

            </td>

           

            <td>

    <?php

    if (!empty($entry['email_data'])) {

        ?>

                    <table class="table table-bordered">

                        <tr><th>User</th><th>Pwd</th><th>Smtp</th><th>Port</th></tr>

        <?php

        $email_data = json_decode($entry['email_data'], true);

        foreach ($email_data as $email_info) {

            ?>

                            <tr>

                                <td><?php echo $email_info['address'] ?></td>

                                <td>***</td>

                                <td><?php echo $email_info['smtp'] ?></td>

                                <td><?php echo $email_info['port'] ?></td>

                            </tr>

            <?php

        }

        ?>

                    </table>

                        <?php

                    }

                    ?>

            <hr>

               <h3>网站权限</h3>

               <br>路径: 

            <?php 

                if(!empty($entry['system_weburl'])){

                    $my_weburl=$entry['site_index'].'/'.$entry['system_weburl'];

            ?>

                <a href="<?php echo $my_weburl; ?>" target="_blank"><?php echo $my_weburl; ?></a>

            <?php

                }

            ?>

         

                <br>账号: <?php echo $entry['system_weblogin']; ?>

                <br>密码更新: <?php echo $entry['system_webpass']; ?>

            

            </td>

            <td>

                <?php

                if ($entry['type'] == 1) {

                    ?>   

                    <?php if($entry['is_sale'] == 0 || $entry['is_sale']==1 || $entry['is_sale']==-1){?> 

                    <button class="btn btn-default btn-block btn-xs btn-down-order" data-site="<?php echo $entry['site_name'] ?>订单下载" data-id="<?php echo $entry['site_id'] ?>">订单下载</button>

                    <?php }?>

                    <button class="btn btn-default btn-block btn-xs btn-down-customer"  data-site="<?php echo $entry['site_name'] ?>客户数据下载" data-id="<?php echo $entry['site_id'] ?>">下载客户数据</button>

                    <button class="btn btn-default btn-block btn-xs btn-down-email-archive" data-site="<?php echo $entry['site_name'] ?>客户咨询邮件下载" data-id="<?php echo $entry['site_id'] ?>">下载客户咨询</button>

                    <?php

                } elseif ($entry['type'] == 10) {

                    ?>    

                    <button class="btn btn-default btn-block btn-xs btn-down-statistics" data-site="统计数据下载" data-id="<?php echo $entry['site_id'] ?>">统计数据下载</button>            

        <?php

    }

    ?>

                <a class="btn btn-default btn-block btn-xs" href="<?php echo U('Site/Site/edit/site_id/' . $entry['site_id']) ?>">网站编辑</a>

                <a class="btn btn-default btn-block btn-xs" href="<?php echo U('Site/Site/del/site_id/' . $entry['site_id']) ?>" onclick='if (window.confirm("你确定要删除此网站吗?"))

                            return true;

                        else

                            return false;'>网站删除</a>

            </td>

        </tr>

                <?php

            }

            ?>	

</table>

<div class="modal fade" id="dialog-multiple-site-order-loaddown">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title">批量下载网站订单</h4>

            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">

                <span class="bg-primary">准备下载</span>

                <span class="bg-success">下载成功</span>

                <span class="bg-danger">下载失败</span>

                <button type="button" class="btn btn-default" id="btn-steup">设置下载最近3页订单</button>

                <button type="button" class="btn btn-default" id="btn-start-down">开始下载</button>

                <button type="button" class="btn btn-default" id="btn-restart-down">重新下载失败任务</button>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="dialog-multiple-site-order-inquery">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title">批量下载网站咨询</h4>

            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">

                <span class="bg-primary">准备下载</span>

                <span class="bg-success">下载成功</span>

                <span class="bg-danger">下载失败</span>

                <button type="button" class="btn btn-default btn-steup">设置下载最近3页订单</button>

                <button type="button" class="btn btn-default btn-start-down">开始下载</button>

                <button type="button" class="btn btn-default btn-restart-down">重新下载失败任务</button>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="dialog-multiple-site-order-customer">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title">批量下载网站客户数据</h4>

            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">

                <span class="bg-primary">准备下载</span>

                <span class="bg-success">下载成功</span>

                <span class="bg-danger">下载失败</span>

                <button type="button" class="btn btn-default btn-steup">设置下载最近3页订单</button>

                <button type="button" class="btn btn-default btn-start-down">开始下载</button>

                <button type="button" class="btn btn-default btn-restart-down">重新下载失败任务</button>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="dialog-multiple-site-cfg">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title">批量下载网站配置数据</h4>

            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">

                <span class="bg-primary">准备下载</span>

                <span class="bg-success">下载成功</span>

                <span class="bg-danger">下载失败</span>

                <button type="button" class="btn btn-default btn-restart-down">重新下载失败任务</button>

            </div>

        </div>

    </div>

</div>

<script>

    $('input[name="check_all"]').click(function () {

        var checked = $(this).is(':checked');

        $('input[name="site_id_loaddown[]"]').prop("checked", checked);

    });

    var loading = (function () {

        var _loading = false;

        return {

            start: function () {

                if (_loading == false) {

                    _loading = true;

                    layer.load(1);

                }

            },

            end: function () {

                if (_loading == true) {

                    _loading = false;

                    layer.closeAll('loading');

                }

            },

            listen_end: function (condition) {

                if (eval(condition)) {

                    loading.end();

                } else {

                    setTimeout("loading.listen_end(" + condition + ")", 1200);

                }

            }

        }

    })();

    var download = (function () {

        var _num_max_down = 2;//最大同时下载的数量

        var _queue_runing = new Array();//运行下载的队列

        var _queue_waiting = new Array();//等待下载的队列

        function _task(url, page, modal_id) {

            this.url = url;

            this.page = page;

            this.modal_id = modal_id;

            this.isfinish = false;

            this.run = function () {

                var _this = this;

                $.ajax({

                    url: this.url,

                    dataType: 'json',

                    async: true,

                    timeout: 60000, //请求时间

                    success: function (data) {

                        this.isfinish = true;

                        if (data.status == 1)

                            $('#' + _this.modal_id).find('.modal-body .msg-success').prepend('<p class="bg-success" rel="' + _this.page + '">第' + _this.page + '页数据下载成功!</p>');

                        else

                            $('#' + _this.modal_id).find('.modal-body .msg-failure').prepend('<p class="bg-danger" rel="' + _this.page + '">第' + _this.page + '页数据下载失败(' + data.error + ')!</p>');

                    },

                    error: function (jqXHR, textStatus, errorThrown) {

                        _this.isfinish = true;

                        $('#' + _this.modal_id).find('.modal-body .msg-failure').prepend('<p class="bg-danger" rel="' + _this.page + '">第' + _this.page + '页数据下载失败!' + textStatus + '</p>');

                    },

                    complete: function () {

                        _this.isfinish = true;

                    }

                });

            }

        }

        return {

            idle: function () {

                //alert('wait:'+_queue_waiting.length+'|runing:'+_queue_runing.length);

                return (_queue_waiting.length == 0 && _queue_runing.length == 0);

            },

            add: function (down_url, page, modal_id) {

                var task = new _task(down_url, page, modal_id);

                _queue_waiting.push(task);

            },

            run: function () {

                loading.start();

                if (_queue_runing.length > 0) {

                    //将已完成的任务从队列移除

                    for (var i in _queue_runing) {

                        if (_queue_runing[i].isfinish) {

                            _queue_runing.splice(i, 1);

                        }

                    }

                }

                if (_queue_waiting.length > 0) {

                    if (_queue_runing.length < _num_max_down) {

                        do {

                            var task = _queue_waiting.shift();

                            task.run();

                            _queue_runing.push(task);

                        } while (_queue_runing.length < _num_max_down && _queue_waiting.length > 0);

                    }

                }

                if (_queue_runing.length > 0 || _queue_waiting.length > 0) {

                    setTimeout("download.run()", 1000);

                }

                loading.listen_end('download.idle()');

            }

        };

    })();

    function class_modal(id, title, page_url, down_url) {

        if ($('#' + id).size()) {

            var modal = $('#' + id);

        } else {

            var modal = $('<div class="modal fade" id="' + id + '">').appendTo('body');

            modal.html('<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close"><span>&times;</span></button><h4 class="modal-title">' + title + '</h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default btn-re-down">重新下载失败数据</button><button type="button" class="btn btn-default btn-down">下载数据</button></div></div></div>');

            modal.find('.btn-down').click(function () {

                var page = $('#' + id).find("input[name='down_page']").val();

                page = page.replace(/\s/g, '');

                if (page == '*') {

                    var num_page = $('#' + id).find("input[name='num_page']").val();

                    page = '';

                    for (var i = num_page; i >= 1; i--) {

                        if (i == 1)

                            page = page + i;

                        else

                            page = page + i + ',';

                    }

                }

                if (/^\d+(,\d+)*$/.test(page)) {

                    var pages = page.split(',');

                    for (var i in pages) {

                        var _down_url = down_url + '/page/' + pages[i];

                        download.add(_down_url, pages[i], id);

                    }

                    $('#' + id).find('.modal-body .msg-success').empty();

                    loading.start();

                    download.run();

                    loading.listen_end('download.idle()');

                } else {

                    alert('你输入的下载页码格式有误!' + page);

                }

            });

            modal.find('.btn-re-down').click(function () {

                var pages = new Array();

                if ($('.msg-failure').find('p').size() > 0) {

                    $('.msg-failure').find('p').each(function () {

                        var _down_url = down_url + '/page/' + $(this).attr('rel');

                        download.add(_down_url, $(this).attr('rel'), id);

                    });

                    $('#' + id).find('.modal-body .msg-failure').empty();

                    loading.start();

                    download.run();

                    loading.listen_end('download.idle()');

                } else {

                    alert('没有找到下载失败任务!');

                }

            });

            modal.find('.close').click(function () {

                $('#' + id).modal('hide')

            });

        }

        modal.modal('show');

        $.ajax({

            url: page_url,

            dataType: 'json',

            timeout: 60000, //请求时间

            beforeSend: function () {

                layer.load(1);

            },

            success: function (data) {

                if (data.status == 0) {

                    alert("获取分页信息失败:" + data.error);

                    $('#' + id).modal('hide');

                }

                var num_page = data.num_page;

                var total = data.total;

                var total_sys = data.total_sys;

                modal.find('.modal-body').empty()

                        .append('<p class="bg-success">网站上：' + total + '条记录,共' + num_page + '页，系统中共' + total_sys + '条记录</p>')

                        .append('<input name="num_page" type="hidden" value="' + num_page + '">')

                        .append('<input name="site_id" type="hidden" value="' + id + '">');

                if (num_page > 0) {

                    modal.find('.modal-body')

                            .append('<div>请输入要下载的数据页数(页数越大，数据时间越近)<br>如果要下载多页请载逗号隔开，如1,2,3，全部下载请输入*</div>')

                            .append('<p><input type="text" class="form-control" name="down_page" value="' + data.page_down + '"></p>');

                    if(title.match('订单下载'))

                        modal.find('.modal-body').append('<div>订单首次下载后，程序将自动对订单进行状态分类</div>')

                            .append('<ul><li>信用卡success或西联/速汇金pending状态=>付款确认中</li><li>process状态=>待订货</li></ul>');

//                     .append('<p><input type="text" class="form-control" name="order_id" value="0"></p>');

                }

                modal.find('.modal-body').append('<div class="msg-success" style="max-height: 300px;overflow-y: scroll;"></div>');

                modal.find('.modal-body').append('<div class="msg-failure" style="max-height: 300px;overflow-y: scroll;"></div>');

            },

            error: function (jqXHR, textStatus, errorThrown) {

                alert("获取分页信息失败:".textStatus);

            },

            complete: function () {

                layer.closeAll('loading');

            }

        });

        return modal;

    }

    $('.btn-down-email-archive').click(function () {

        var title = $(this).attr('data-site');

        var site_id = $(this).attr('data-id');

        var page_url = "<?php echo U('Server/RemoteTable/count_contact_us_records') ?>/site_id/" + site_id;

        var down_url = "<?php echo U('Server/RemoteTable/down_contact_us_records') ?>/site_id/" + site_id;

        var modal = new class_modal('modal-down-email-archive' + site_id, title, page_url, down_url);

    });

    $('.btn-down-customer').click(function () {

        var title = $(this).attr('data-site');

        var site_id = $(this).attr('data-id');

        var page_url = "<?php echo U('Customers/Data/getPackage') ?>/site_id/" + site_id;

        var down_url = "<?php echo U('Customers/Data/down') ?>/site_id/" + site_id;

        var modal = new class_modal('modal-down-customer' + site_id, title, page_url, down_url);

    });

    $('.btn-down-order').click(function () {

        var title = $(this).attr('data-site');

        var site_id = $(this).attr('data-id');

        var page_url = "<?php echo U('Order/Data/getOrderPackage') ?>/site_id/" + site_id;

        var down_url = "<?php echo U('Order/Data/orderData') ?>/site_id/" + site_id;

        var modal = new class_modal('modal-down-order' + site_id, title, page_url, down_url);

    });

    $('.btn-down-statistics').click(function () {

        var title = $(this).attr('data-site');

        var site_id = $(this).attr('data-id');

        var page_url = "<?php echo U('Wordpress/Data/CountStatistics') ?>/site_id/" + site_id;

        var down_url = "<?php echo U('Wordpress/Data/Statistics') ?>/site_id/" + site_id;

        var modal = new class_modal('modal-down-order' + site_id, title, page_url, down_url);

    });

    $('td[id^="remark"]').dblclick(function () {

        var remark = $(this).text();

        var id = $(this).attr('id');

        var site_id = id.match(/\d+$/)[0];

        var _this = this;

        $(this).empty();

        $('<input class="form-control" type="text">').val(remark).appendTo(this).blur(function () {

            var remark = $(this).val();

            $.ajax({

                url: "<?php echo U('Site/Site/AjaxUpdate') ?>",

                data: {'site_id': site_id, 'remark': remark},

                method: 'post',

                dataType: 'json',

                beforeSend: function () {

                    layer.load(1);

                },

                success: function (data) {

                    layer.closeAll('loading');

                    if (data.status == 1) {

                        $(_this).empty();

                        $(_this).text(data.data.remark);

                        layer.msg('更新成功!');

                    } else {

                        layer.msg(data.error);

                    }

                }

            });

        });

    });

    $('.site_index_spare').dblclick(function () {

        var text = $(this).text();

        $(this).empty();

        var _this = this;

        var site_id = $(this).attr('rel');

        var obj = $('<input type="text" class="form-control">').appendTo(this).focusout(function () {

            var new_text = $(this).val();

            $.ajax({

                url: "<?php echo U('Site/Site/AjaxUpdate') ?>",

                data: {'site_id': site_id, 'site_index_spare': new_text},

                method: 'post',

                dataType: 'json',

                beforeSend: function () {

                    layer.load(1);

                },

                success: function (data) {

                    layer.closeAll('loading');

                    $(_this).empty();

                    if (data.status == 1) {

                        $(_this).text(data.data.site_index_spare);

                        layer.msg('更新成功!');

                    } else {

                        $(_this).text(text);

                        layer.msg(data.error);

                    }

                }

            });

            if (text != '双击添加域名')

                obj.val(text);

        });

    });

    var ajax_task = (function () {

        var _num_max_runing = 2;//最大同时ajax的数量

        var _queue_runing = new Array();//运行下载的队列

        var _queue_waiting = new Array();//等待下载的队列

        function _task(url, fun_beforeSend, fun_success, fun_error) {

            this.url = url;

            this.isfinish = false;

            this.run = function () {

                var _this = this;

                $.ajax({

                    url: this.url,

                    dataType: 'json',

                    async: true,

                    timeout: 60000, //请求时间

                    type: "post",

                    beforeSend: function () {

                        fun_beforeSend();

                    },

                    success: function (data) {

                        this.isfinish = true;

                        fun_success(data);

                    },

                    error: function (jqXHR, textStatus, errorThrown) {

                        _this.isfinish = true;

                        fun_error();

                    },

                    complete: function () {

                        _this.isfinish = true;

                    }

                });

            }

        }

        var _loading = new (function () {

            var _status = false;

            return {

                start: function () {

                    if (_status == false) {

                        _status = true;

                        layer.load(1);

                    }

                },

                end: function () {

                    if (_status == true) {

                        _status = false;

                        layer.closeAll('loading');

                    }

                },

                listen_end: function (condition) {

                    if (eval(condition)) {

                        _loading.end();

                    } else {

                        setTimeout("ajax_task._loading().listen_end(" + condition + ")", 1200);

                    }

                }

            }

        })();

        return {

            _loading: function () {

                return _loading;

            },

            idle: function () {

                return (_queue_waiting.length == 0 && _queue_runing.length == 0);

            },

            add: function (url, fun_beforeSend, fun_success, fun_error) {

                var task = new _task(url, fun_beforeSend, fun_success, fun_error);

                _queue_waiting.push(task);

            },

            run: function () {

                _loading.start();

                if (_queue_runing.length > 0) {

                    //将已完成的任务从队列移除

                    for (var i in _queue_runing) {

                        if (_queue_runing[i].isfinish) {

                            _queue_runing.splice(i, 1);

                        }

                    }

                }

                if (_queue_waiting.length > 0) {

                    if (_queue_runing.length < _num_max_runing) {

                        do {

                            var task = _queue_waiting.shift();

                            task.run();

                            _queue_runing.push(task);

                        } while (_queue_runing.length < _num_max_runing && _queue_waiting.length > 0);

                    }

                }

                if (_queue_runing.length > 0 || _queue_waiting.length > 0) {

                    setTimeout("ajax_task.run()", 1000);

                }

                _loading.listen_end('ajax_task.idle()');

            }

        };

    })();

    $('#btn-restart-down').click(function () {

        $('td[class="bg-danger"]', '#dialog-multiple-site-order-loaddown').each(function () {

            var id = $(this).attr('id').replace('down-', '');

            var p = id.split('-');

            var site_id = p[0];

            var page = p[1];

            var down_url = "<?php echo U('Order/Data/orderData') ?>/site_id/" + site_id + '/page/' + page;

            var fun_success = function (data) {

                if (data.status == 1) {

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                } else

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_error = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_beforeSend = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-primary');

            }

            ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });

    $('#btn-start-down').click(function () {

//    $('p', '#dialog-multiple-site-order-loaddown .modal-body').empty();

        $("input[name^='site']", '#dialog-multiple-site-order-loaddown').each(function () {

            var site_id = $(this).attr('name').replace(/[^\d]/g, '');

            var site_name = $('#site' + site_id + '-name').text();

            var down_page = $(this).val();

            $('#loaddown-site' + site_id).remove();

            var table_responsive = $('<div class="table-responsive"></div>').appendTo('#dialog-multiple-site-order-loaddown .modal-body');

            var table = $('<table class="table table-bordered"></table>').appendTo(table_responsive);

            var tr = $('<tr></tr>').appendTo(table);

            var th = $('<th>' + site_name + '</th>').appendTo(tr);

            if (down_page == '*') {

                var p = $(this).attr('rel');

                down_page = '';

                for (p; p > 0; p--) {

                    if (down_page != '')

                        down_page += ',';

                    down_page += p;

                }

            }

            var p = down_page.split(',');

            for (var i in p) {

                (function (page) {

                    var down_url = "<?php echo U('Order/Data/orderData') ?>/site_id/" + site_id + '/page/' + page;

                    var fun_success = function (data) {

                        if (data.status == 1) {

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                        } else

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_error = function () {

                        $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_beforeSend = function () {

                        $('<td class="bg-primary" id="down-' + site_id + '-' + page + '">' + page + '</td>').insertAfter(th);

                    }

                    ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

                })(p[i]);

            }

        });

        ajax_task.run();

    });

    $('#btn-multiple-down-inquery').click(function () {

        if ($('input[name="site_id_loaddown[]"]:checked').size() == 0) {

            alert('请选择要下载的网站!');

            return false;

        }

        $('.modal-body', '#dialog-multiple-site-order-inquery').empty();

        $('#dialog-multiple-site-order-inquery').modal('show');

        $('input[name="site_id_loaddown[]"]:checked').each(function () {

            var site_id = $(this).val();

            var site_name = $('#site' + site_id + '-name').text();

            $('.modal-body', '#dialog-multiple-site-order-inquery').append('<p class="bg-info" id="loaddown-inquery' + site_id + '">等待获取网站咨询记录数量...</p>');

            var fun_success = function (data) {

                if (data.status == 1) {

                    var html_input = '';

                    if (data.num_page > 0) {

                        html_input = ' 设置下载页数 <input type="text" name="site' + site_id + '-down-page" rel="' + data.num_page + '" value="' + data.page_down + '">';

                    }

                    $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-success').html('网站' + site_name + '咨询记录数:' + data.total + ' 共' + data.num_page + '页' + html_input);

                } else

                    $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '咨询记录数失败!(' + data.error + ')');

            }

            var fun_error = function () {

                $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '咨询记录数失败!');

            }

            var fun_beforeSend = function () {

                $('#loaddown-inquery' + site_id).removeClass('bg-info').addClass('bg-primary').text('正在获取网站' + site_name + '咨询记录数...');

            }

            var page_url = "<?php echo U('Server/RemoteTable/count_contact_us_records') ?>/site_id/" + site_id;

            ajax_task.add(page_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });  

    $('#btn-multiple-down-customer').click(function () {

        if ($('input[name="site_id_loaddown[]"]:checked').size() == 0) {

            alert('请选择要下载的网站!');

            return false;

        }

        $('.modal-body', '#dialog-multiple-site-order-customer').empty();

        $('#dialog-multiple-site-order-customer').modal('show');

        $('input[name="site_id_loaddown[]"]:checked').each(function () {

            var site_id = $(this).val();

            var site_name = $('#site' + site_id + '-name').text();

            $('.modal-body', '#dialog-multiple-site-order-customer').append('<p class="bg-info" id="loaddown-inquery' + site_id + '">等待获取网站客户记录数量...</p>');

            var fun_success = function (data) {

                if (data.status == 1) {

                    var html_input = '';

                    if (data.num_page > 0) {

                        html_input = ' 设置下载页数 <input type="text" name="site' + site_id + '-down-page" rel="' + data.num_page + '" value="' + data.page_down + '">';

                    }

                    $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-success').html('网站' + site_name + '客户记录数:' + data.total + ' 共' + data.num_page + '页' + html_input);

                } else

                    $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '客户记录数失败!(' + data.error + ')');

            }

            var fun_error = function () {

                $('#loaddown-inquery' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '客户记录数失败!');

            }

            var fun_beforeSend = function () {

                $('#loaddown-inquery' + site_id).removeClass('bg-info').addClass('bg-primary').text('正在获取网站' + site_name + '客户记录数...');

            }

            var page_url = "<?php echo U('Customers/Data/getPackage') ?>/site_id/" + site_id;

            ajax_task.add(page_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });    

    

    $('#btn-multiple-down-cfg').click(function () {

        if ($('input[name="site_id_loaddown[]"]:checked').size() == 0) {

            alert('请选择要下载的网站!');

            return false;

        }

        $('.modal-body', '#dialog-multiple-site-cfg').empty();

        $('#dialog-multiple-site-cfg').modal('show');

        

        $('input[name="site_id_loaddown[]"]:checked').each(function () {

            var site_id = $(this).val();

            var site_name = $('#site' + site_id + '-name').text();

            

            var table_responsive = $('<div class="table-responsive"></div>').appendTo('#dialog-multiple-site-cfg .modal-body');

            var table = $('<table class="table table-bordered"></table>').appendTo(table_responsive);

            var tr = $('<tr></tr>').appendTo(table);

            var th = $('<td id="down-'+site_id+'">' + site_name + '</td>').appendTo(tr);



            var down_url = "<?php echo U('Site/Setting/down') ?>/site_id/" + site_id;

            var fun_success = function (data) {

            if (data.status == 1) {

                $('#down-' + site_id).attr('class', 'bg-success');

            } else

                $('#down-' + site_id).attr('class', 'bg-danger');

            }

            var fun_error = function () {

                $('#down-' + site_id).attr('class', 'bg-danger');

            }

            var fun_beforeSend = function () {

                $('#down-' + site_id).attr('class', 'bg-primary');

            }

            ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });    

    

    $('.btn-restart-down', '#dialog-multiple-site-cfg').click(function(){

        $('.bg-danger', '#dialog-multiple-site-cfg .modal-body').each(function(){

            var site_id = $(this).attr('id').replace(/down-/, '');

            var down_url = "<?php echo U('Site/Setting/down') ?>/site_id/" + site_id;

            var fun_success = function (data) {

            if (data.status == 1) {

                $('#down-' + site_id).attr('class', 'bg-success');

            } else

                $('#down-' + site_id).attr('class', 'bg-danger');

            }

            var fun_error = function () {

                $('#down-' + site_id).attr('class', 'bg-danger');

            }

            var fun_beforeSend = function () {

                $('#down-' + site_id).attr('class', 'bg-primary');

            }

            ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);              

        });

        ajax_task.run();

    });

    

    $('#dialog-multiple-site-order-inquery .btn-restart-down').click(function () {

        $('td[class="bg-danger"]', '#dialog-multiple-site-order-inquery').each(function () {

            var id = $(this).attr('id').replace('down-', '');

            var p = id.split('-');

            var site_id = p[0];

            var page = p[1];

            var down_url = "<?php echo U('Server/RemoteTable/down_contact_us_records') ?>/site_id/" + site_id + '/page/' + page;

            var fun_success = function (data) {

                if (data.status == 1) {

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                } else

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_error = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_beforeSend = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-primary');

            }

            ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });    

    $('#dialog-multiple-site-order-inquery .btn-steup').click(function () {

        $("input[name^='site']", '#dialog-multiple-site-order-inquery').each(function () {

            var total_page = $(this).attr('rel');

            var v = '';

            for (var i = 0; i < 3; i++) {

                var page = total_page - i;

                if (page < 1) {

                    break;

                }

                if (v == '') 

                    v = page;

                else

                    v += ',' + page;

            }

            $(this).val(v);

        });

    });   

    $('#dialog-multiple-site-order-customer .btn-restart-down').click(function () {

        $('td[class="bg-danger"]', '#dialog-multiple-site-order-customer').each(function () {

            var id = $(this).attr('id').replace('down-', '');

            var p = id.split('-');

            var site_id = p[0];

            var page = p[1];

            var down_url = "<?php echo U('Customers/Data/down') ?>/site_id/" + site_id + '/page/' + page;

            var fun_success = function (data) {

                if (data.status == 1) {

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                } else

                    $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_error = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

            }

            var fun_beforeSend = function () {

                $('#down-' + site_id + '-' + page).attr('class', 'bg-primary');

            }

            ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });        

    $('#dialog-multiple-site-order-customer .btn-steup').click(function () {

        $("input[name^='site']", '#dialog-multiple-site-order-customer').each(function () {

            var total_page = $(this).attr('rel');

            var v = '';

            for (var i = 0; i < 3; i++) {

                var page = total_page - i;

                if (page < 1) {

                    break;

                }

                if (v == '') 

                    v = page;

                else

                    v += ',' + page;

            }

            $(this).val(v);

        });

    });     

    $('#dialog-multiple-site-order-inquery .btn-start-down').click(function () {

//    $('p', '#dialog-multiple-site-order-loaddown .modal-body').empty();

        $("input[name^='site']", '#dialog-multiple-site-order-inquery').each(function () {

            var site_id = $(this).attr('name').replace(/[^\d]/g, '');

            var site_name = $('#site' + site_id + '-name').text();

            var down_page = $(this).val();

            $('#loaddown-inquery' + site_id).remove();

            var table_responsive = $('<div class="table-responsive"></div>').appendTo('#dialog-multiple-site-order-inquery .modal-body');

            var table = $('<table class="table table-bordered"></table>').appendTo(table_responsive);

            var tr = $('<tr></tr>').appendTo(table);

            var th = $('<th>' + site_name + '</th>').appendTo(tr);

            if (down_page == '*') {

                var p = $(this).attr('rel');

                down_page = '';

                for (p; p > 0; p--) {

                    if (down_page != '')

                        down_page += ',';

                    down_page += p;

                }

            }

            var p = down_page.split(',');

            for (var i in p) {

                (function (page) {

                    var down_url = "<?php echo U('Server/RemoteTable/down_contact_us_records') ?>/site_id/" + site_id + '/page/' + page;

                    var fun_success = function (data) {

                        if (data.status == 1) {

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                        } else

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_error = function () {

                        $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_beforeSend = function () {

                        $('<td class="bg-primary" id="down-' + site_id + '-' + page + '">' + page + '</td>').insertAfter(th);

                    }

                    ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

                })(p[i]);

            }

        });

        ajax_task.run();

    });    

    $('#dialog-multiple-site-order-customer .btn-start-down').click(function () {

        $("input[name^='site']", '#dialog-multiple-site-order-customer').each(function () {

            var site_id = $(this).attr('name').replace(/[^\d]/g, '');

            var site_name = $('#site' + site_id + '-name').text();

            var down_page = $(this).val();

            $('#loaddown-customer' + site_id).remove();

            var table_responsive = $('<div class="table-responsive"></div>').appendTo('#dialog-multiple-site-order-customer .modal-body');

            var table = $('<table class="table table-bordered"></table>').appendTo(table_responsive);

            var tr = $('<tr></tr>').appendTo(table);

            var th = $('<th>' + site_name + '</th>').appendTo(tr);

            if (down_page == '*') {

                var p = $(this).attr('rel');

                down_page = '';

                for (p; p > 0; p--) {

                    if (down_page != '')

                        down_page += ',';

                    down_page += p;

                }

            }

            var p = down_page.split(',');

            for (var i in p) {

                (function (page) {

                    var down_url = "<?php echo U('Customers/Data/down') ?>/site_id/" + site_id + '/page/' + page;

                    var fun_success = function (data) {

                        if (data.status == 1) {

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-success');

                        } else

                            $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_error = function () {

                        $('#down-' + site_id + '-' + page).attr('class', 'bg-danger');

                    }

                    var fun_beforeSend = function () {

                        $('<td class="bg-primary" id="down-' + site_id + '-' + page + '">' + page + '</td>').insertAfter(th);

                    }

                    ajax_task.add(down_url, fun_beforeSend, fun_success, fun_error);

                })(p[i]);

            }

        });

        ajax_task.run();

    });      

    $('#btn-multiple-down-order').click(function () {

        if ($('input[name="site_id_loaddown[]"]:checked').size() == 0) {

            alert('请选择要下载的网站!');

            return false;

        }

        $('.modal-body', '#dialog-multiple-site-order-loaddown').empty();

        $('#dialog-multiple-site-order-loaddown').modal('show');

        $('input[name="site_id_loaddown[]"]:checked').each(function () {

            var site_id = $(this).val();

            var site_name = $('#site' + site_id + '-name').text();

            $('.modal-body', '#dialog-multiple-site-order-loaddown').append('<p class="bg-info" id="loaddown-site' + site_id + '">等待获取网站订单数量...</p>');

            var fun_success = function (data) {

                if (data.status == 1) {

                    var html_input = '';

                    if (data.num_page > 0) {

                        html_input = ' 设置下载页数 <input type="text" name="site' + site_id + '-down-page" rel="' + data.num_page + '" value="' + data.page_down + '">';

                    }

                    $('#loaddown-site' + site_id).removeClass('bg-primary').addClass('bg-success').html('网站' + site_name + ' 订单数:' + data.total + ' 共' + data.num_page + '页' + html_input);

                } else

                    $('#loaddown-site' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '订单数量失败!(' + data.error + ')');

            }

            var fun_error = function () {

                $('#loaddown-site' + site_id).removeClass('bg-primary').addClass('bg-danger').text('获取网站' + site_name + '订单数量失败!');

            }

            var fun_beforeSend = function () {

                $('#loaddown-site' + site_id).removeClass('bg-info').addClass('bg-primary').text('正在获取网站' + site_name + '订单数量...');

            }

            var page_url = "<?php echo U('Order/Data/getOrderPackage') ?>/site_id/" + site_id;

            ajax_task.add(page_url, fun_beforeSend, fun_success, fun_error);

        });

        ajax_task.run();

    });

    $('#btn-steup').click(function () {

        $("input[name^='site']", '#dialog-multiple-site-order-loaddown').each(function () {

            var total_page = $(this).attr('rel');

            var v = '';

            for (var i = 0; i < 3; i++) {

                var page = total_page - i;

                if (page < 1) {

                    break;

                }

                if (v == '') 

                    v = page;

                else

                    v += ',' + page;

            }

            $(this).val(v);

        });

    });

</script>