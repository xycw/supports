{:R('Order/Order/OrderListMenu')}
{:R('Order/Order/SearchForm')}

<?php
if (I('site_id') != ''){
    $site_id = I('site_id');

    if(is_array($site_id)==false) {
        if(strpos($site_id, '_')) 
            $site_id = explode ('_', $site_id);
        else
            $site_id = array($site_id);
    }

    echo '<p class="bg-primary">当前筛选网站：';
    foreach ($site_id as $id){
        echo $options_site_name[$id].'&nbsp;&nbsp;&nbsp;';        
    }
    echo '</p>';
}
?>

<div class="page-nav">
        <div class="row">
            <div class="col-lg-6">
                <div class="page-nav-info">
                   每页
                   <tagLib name="html" />
                   <html:select options="page_num_data" selected="page_num_selected" name="page_num" />
                    个订单,(订单总数:<?php echo $count ?>)
                </div>
            </div>
            <div class="col-lg-6 right">
                <?php
                W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/ExpressDelivery/list', $page_data));
                ?>
            </div>
        </div>
</div>


<form id="form-batch" action="" method="post">
    <input type="hidden" name="action" value="">
    
    <table class="order-list" id="order-list-header" style="table-layout:fixed;">
        <colgroup>
        <col width="80px">
        <col width="170px">
        <col width="150px">
        <col width="250px">
        <col width="120px">
        <col width="70px">
        <col width="150px">
        <col width="200px">
        <col width="auto">
        </colgroup>        
        <thead>
            <tr>
                <th><input type="checkbox" name="check_all" class="pull-left"></th>
                <th>订单追踪</th>
                <th>订单号</th>
                <th>交易网址</th>
                <th>支付方式<br>订单金额</th>
                <th>订单数</th>
                <th>订单状态</th>
                <th>客户姓名<br>客户邮箱</th>
                <th>下单(北京)时间<br>状态最近修改</th>
            </tr>
        </thead>
    </table>

    <table class="order-list" style="table-layout:fixed; word-break:break-all;">
        <colgroup>
        <col width="80px">
        <col width="170px">
        <col width="150px">
        <col width="250px">
        <col width="120px">
        <col width="70px">
        <col width="150px">
        <col width="200px">
        <col width="auto">
        </colgroup>
        <?php
        //echo '<pre />';print_r($list);exit;
        foreach ($list as $kk=>$entry) {
            ?>
            <tbody id="order-info-<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>">
                
                <tr class="sep-row"><td colspan="9"></td></tr>
                <tr class="order-hd">
                    <td class="f08">
                        <input type="checkbox" name="site_to_orders[]" value="<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>">
                        <?php echo ($page-1)*$num+($kk+1);?></td>
                    <td>
                    <?php
                            //bengin benxin.2018.06.21
                            $aikuaidi_tracking_no_url=U('Order/ExpressDelivery/logistics',array('orders_delivery_id'=>$entry['orders_delivery_id'])); //货运单号 aikuaidi查询链接

                            $delivery_type = $entry['delivery_type'].$entry['delivery_status']; //货运方式

                            $aikuaidi_forward_no_url=''; //待增补代码 转单号 aikuaidi查询链接

                        //转单号非空, ①显示不带链接de货运单号;②显示带链接的转单号，同时提供17track查询链接
                        if(!empty($entry['delivery_forward_no'])){

                            $entry['delivery_tracking_no']='<del>'.$entry['delivery_tracking_no'].'</del>';
                           

                           echo $entry['delivery_tracking_no'];
                           echo '<br>'.$entry['delivery_date'];
                     ?>
                            <a class="label label-primary f09" rel="<?php echo $entry['delivery_status']?>" id="view_delivery_status<?php echo $entry['orders_delivery_id']?>" style="display: block;" href="https://t.17track.net/en#nums=<?php echo $entry['delivery_forward_no'] ;?>" data-toggle="tooltip" data-placement="top" title="<?php echo "转单号".$delivery_type; ?>"><?php echo $entry['delivery_forward_no'];?></a>
                            <div class="label label-primary f09" style="display: block;margin-top:1px;"><?php  echo $entry['delivery_date']."发货";?></div>
                    <?php            
                        }else{  //反之 转单号为空，则 ①显示带链接de货运单号,同时提供17track查询链接 ②不需考虑转单号显示
                    ?>
                            <a class="label label-primary f09" rel="<?php echo $entry['delivery_status']?>" id="view_delivery_status<?php echo $entry['orders_delivery_id']?>" style="display: block;" href="https://t.17track.net/en#nums=<?php echo $entry['delivery_tracking_no'] ;?>" data-toggle="tooltip" data-placement="top" title="<?php echo $delivery_type; ?>"><?php echo $entry['delivery_tracking_no'];?></a>
                            <div class="label label-primary f08" style="display: block;margin-top:1px;"><?php  echo $entry['delivery_date']."发货";?></div>
                    <?php

                        }
                        //end benxin.2018.06.21
                    ?>    
                    </td>
                    
                    <td<?php if((($comments=hasComment($entry['site_id'], $entry['orders_id']))!==false)) echo ' style="background-color:yellow;"';?>>                        
                        <a class="link-view f08" <?php if($comments!==false) echo 'data-toggle="tooltip" data-placement="top" data-html="true" title="'.htmlspecialchars ($comments).'"'?> id="link-view-<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>" href="<?php echo U('Order/Order/view', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>"><?php echo $entry['order_no_prefix'] . $entry['orders_id'] ?></a>
                    </td>
                    <td class="f08"><a target="_blank" href="<?php echo $entry['site_index'];?>"><?php echo $entry['site_name'] ?></a>
                    <?php 
                    echo $entry['is_sale']?'<div style="color:red;">批发</div>':'<div style="color:green;">零售</div>';
                    ?>
                        <a  target="_blank" style="color:green;margin-right: 10px;" href="<?php echo U('Order/Order/view', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>">编辑</a>
                        <a  target="_blank" style="color:blueviolet" href="<?php echo U('Order/Order/order_doc', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>">打印</a>
                    </td>
                    <td class="f08"><?php echo $entry['payment_module_code']?><br><?php echo round($entry['order_total'] * $entry['currency_value'], 2) . $entry['currency'] ?></td>
                    <td><?php echo $entry['num_products']?></td>
                    <td>
                        <?php
						$send_status_txt='';
                        if($entry['send_status']==1){
                        	$send_status_txt='<span style="color:red;">邮件发送成功</span>';
                        }elseif($entry['send_status']==2){
                        	$send_status_txt='<span style="color:green;">邮件已发送失败</span>';
                        }
                        $tip = '';
                        $num_email = 0;
                        $email_history = array();
                        if (!empty($entry['email_logs'])) {
                            $email_history = json_decode($entry['email_logs'], true);
                        }
                        if (sizeof($email_history) > 0) {
                            foreach ($email_history as $_email_history) {
                                foreach ($_email_history as $history) {
                                    $tip .= '<div style=\'text-align:left;\'>' . $history['time'] . $history['email_template_name'] . "</div>";
                                    $num_email++;
                                }
                            }
                        } else {
                            $tip = '无邮件发送记录';
                        }
						echo $send_status_txt.'<br />';
                        echo $entry['order_status_remark'] == '' ? '待处理' : '<a class="email_template_dialog" href="' . U('Order/Order/email/site_id/' . $entry['site_id'] . '/order_id/' . $entry['orders_id']) . '">' . $entry['order_status_remark'] . '</a><span class="badge" data-toggle="tooltip" data-placement="bottom" data-html="true" title="' . $tip . '">' . $num_email . '</span>';
                        ?>
                        <br>
                        <?php echo $entry['orders_status_name'].'<br><span class="text-primary">'.$entry['payment_status'].'</span>'?>
                    </td>
                    <td class="f08"><?php echo $entry['customers_name'] ?><br><?php echo preg_replace('/YK_(\d)+_/','',$entry['customers_email_address']); ?></td>
                    <td>
                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="下单(北京)时间" style="display:block;margin-bottom: 1px;" class="label label-default f08"><?php echo $entry['date_purchased'] ?></div>
                        <?php 
                        if($entry['username']!=''){
                        ?>
                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="最后更改状态时间" style="display:block;margin-bottom: 1px;" class="label label-primary f08"><?php echo $entry['last_modify'] ?></div>
                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="最后更改状态操作者" style="display:block;margin-bottom: 1px;" class="label label-primary f08"><?php echo $entry['username'].$entry['chinese_name'] ?></div>
                        <?php
                        }
                        ?>                    
                    </td>
                </tr>
            </tbody>
            <?php
        }
        ?>
    </table>

    <div class="page-nav">
        <div class="row">
            <div class="col-lg-6">
                <div class="page-nav-info">
                   <label>订单数量:</label>
                    <?php
                        $page_num_data = array(1=>1,50=>50,100=>100, 200=>200,300=>300, 500=>500);
                        $page_num_selected = I('page_num', 300);
                    ?>
                    <label>每页</label>
                    <html:select options="page_num_data" selected="page_num_selected" name="page_num" />
                    <label>条(当前总数:</label> <?php echo $count ?><label>条)</label>
                </div>
            </div>
            <div class="col-lg-6 right">
                <?php
                W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/ExpressDelivery/list', $page_data));
                ?>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="delivery_status_dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">物流查询</h4>
				<?php
				foreach($data_shipping_status as $entry){
				?>
				<button class="btn btn-default btn-xs btn-update-express-status"><?php echo $entry?></button>
				<?php		
				}
				?>
            </div>
            <div class="modal-body">
                <div class="delivery_info"></div>
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="multie_mail_dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">批量发送邮件</h4>
             </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="site_dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">网站筛选</h4>
            </div>
            <div class="row">
            <?php
            foreach ($options_site_name as $site_id=>$site_name){
            ?>
                <div class="col-xs-4">
                    <label><input type="checkbox" name="site_id[]" value="<?php echo $site_id?>"<?php if(in_array($site_id,$site_id_select)) echo ' checked'?>>
                    <?php echo ' '.$site_id.'# '.$site_name ?></label>
                </div>    
            <?php    
            }                
            ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="email_dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="padding:10px;">

        </div>
    </div>
</div>

<div class="modal fade" id="delivery_excel_dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="padding:10px;">
        </div>
    </div>
</div>

<div class="modal fade" id="payment_confirmation_dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">订单付款确认表导入</h4>
             </div>
            <div class="modal-body">
                <form action="<?php echo U('Order/Order/payment_confirmation/action/upload') ?>" enctype="multipart/form-data" method="post">
                    <div class="form-group">
                      <label>订单付款确认表</label>
                      <input type="file" name="file">
                      <p class="help-block">请按照<a href="__PUBLIC__/example/example_payment_confirmation.xls" target="__blank">example_payment_confirmation.xls</a>标准格式导入!</p>
                      <p class="help-block">导入的数据将影响 {待处理}，{付款失败/未付款}，{付款确认中} 等 状态的订单状态，确认后的订单将进入 ｛已确认付款｝状态</p>
                    </div>
                    <button type="submit" class="btn btn-default">提交</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="change_status_dialog">
    <div class="modal-dialog modal-lg" style="width:1000px;">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">订单状态(批量变更)操作</h4>
             </div>
             <div class="modal-body">
                 <?php 
                 foreach ($order_status_remark as $k=>$v){
                     echo '<button class="btn btn-default change_status" type="button" rel="'.$k.'">'.$v.'</button>&nbsp;';
                 }
                 ?>
             </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $.ajaxSetup({
            beforeSend: function () {
                layer.load(1);
            },
            complete: function () {
                layer.closeAll('loading');
            }
        });
        $('#multie_mail_dialog').on('shown.bs.modal', function () {
            var site_to_orders = new Array();
            $('input[name="site_to_orders[]"]:checked').each(function(){
                site_to_orders.push($(this).val());
            });
            $('.modal-body', '#multie_mail_dialog').empty();
            $.post("<?php echo U('Order/Order/multi_email')?>", { 'site_to_orders': site_to_orders }, function(html){
                $('.modal-body', '#multie_mail_dialog').html(html);
            });
        });        
        $('a[id^="view_delivery_status"]').click(function(){
            var link = $(this).attr('href');
			var status = $(this).attr('rel');
			var orders_delivery_id = $(this).attr('id').replace('view_delivery_status', '');
            $('#delivery_status_dialog').modal('show');
            $('.btn-update-express-status').attr('class', 'btn-update-express-status btn btn-default btn-xs').each(function(){
				var _status = $(this).text();
				if(_status==status)
					$(this).attr('class', 'btn-update-express-status btn btn-primary btn-xs');
					$(this).click(function(){
						var _this = this;
						$.ajax({
							url  : "<?php echo U('Order/ExpressDelivery/changeStatus')?>",
							type : 'post',
							data : {'orders_delivery_id':orders_delivery_id, 'status': _status},
							dataType : 'json',
							success : function(data){
								if(data.status==1){
									$('.btn-update-express-status').attr('class', 'btn-update-express-status btn btn-default btn-xs');
									$(_this).attr('class', 'btn-update-express-status btn btn-primary btn-xs');
									$('#view_delivery_status'+orders_delivery_id).attr('rel', _status);
									layer.msg('更新成功!');
								}else
									layer.msg('更新失败!');
							}
						});
					});
			});
            $('.delivery_info', '#delivery_status_dialog').html('<iframe src="'+link+'" width="100%" frameborder="0" scrolling="auto" height="800px;">');
            return false;
        });
        $('[data-toggle="tooltip"]').tooltip();
        $('#btn-export').click(function () {
            $("#form-batch").attr("target", "_blank");
            $("#form-batch").attr('action', '<?php echo U('Order/Order/export_order') ?>');
            $("#form-batch").submit();
            $("#form-batch").val('');
            $("#form-batch").attr("target", "");
        });
        $('#btn-export2').click(function () {
            $("#form-batch").attr("target", "_blank");
            $("#form-batch").attr('action', '<?php echo U('Order/Order/order_list_excel') ?>');
            $("#form-batch").submit();
            $("#form-batch").val('');
            $("#form-batch").attr("target", "");
        }); 
        $('#btn-export3').click(function () {
            $("#form-batch").attr("target", "_blank");
            $("#form-batch").attr('action', '<?php echo U('Order/Order/shipping_address_excel') ?>');
            $("#form-batch").submit();
            $("#form-batch").val('');
            $("#form-batch").attr("target", "");
        });          
        $('.change_status').click(function(){
            var to_status = $(this).attr('rel');
            $("#form-batch").attr('action', '<?php echo U('Order/Order/changeStatus') ?>/change_to_status/'+to_status);
            $("#form-batch").submit();
        });
        $('#btn-batch-cancel').click(function () {
            $("input[name='action']").val('cancel');
            $("#form-batch").submit();
        });
        $('.btn-zhuidan').click(function () {
            var href = $(this).attr('href');
            var _self = this;
            $.ajax({
                url: href,
                dataType: 'json',
                success: function (data) {
                    layer.msg(data.info);
                    if (data.info == '追单邮件发送成功!') {
                        var n = parseInt($(_self).text()) + 1;
                        $(_self).text(n);
                    }
                }
            });
            return false;
        });
    });

    $("input[name^='order_time_']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $("input[name^='delivery_date']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $('.email_template_dialog').click(function () {
        var url = $(this).attr('href');

        $('.modal-content', '#email_dialog').load(url, function () {
            $('#email_dialog').modal('show');
            layer.closeAll('loading');
        });

        return false;
    });

    $('input[name="check_all"]').click(function () {
        var checked = $(this).is(':checked');
        $('input[name="site_to_orders[]"]').prop("checked", checked);
    });

    $('.link-view').click(function (e) {

            var link = $(this).attr('href');
            var order_info_id = $(this).attr('id').replace('link-view', 'order-info');
            var order_bd = $(this).attr('id').replace('link-view', 'order-bd');
            if($('.'+order_bd).size()){
                $('.'+order_bd).toggle();
            }else{
                $.get(link, function (html) {
                    $('#' + order_info_id).append(html);
                });
            }
            return false;   
    });

    $('select[name="page_num"]').change(function(){
        window.location = "<?php echo U('Order/Order/list', $page_data)?>/page_num/"+$(this).val();       
    });
    
    $('input[name="site_id[]"]').click(function(){
        var v = '';
        $('input[name="site_id[]"]:checked').each(function(){
            if(v=='') v = $(this).val();
            else v += '_'+$(this).val();
        });
        $('input[name="site_id"]').val(v);
    });
    $(window).scroll(function(){
        var top = $(window).scrollTop();
        if(top>500){
            $('#order-list-header').css({position:'fixed', top: 0, width:'1370px'});
        }else{
            $('#order-list-header').css({position:'relative', top: 0, width:'1370px'});
        }
    });    
</script>