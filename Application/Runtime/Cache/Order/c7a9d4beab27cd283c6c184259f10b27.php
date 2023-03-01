<?php if (!defined('THINK_PATH')) exit(); echo R('Common/Html/html_start');?> 
<header id="header"><?php echo R('Common/Layout/menu');?></header>
<div class="container" id="content">
<?php echo R('Order/Order/OrderListMenu');?>

<?php echo R('Order/Order/SearchForm');?>



<?php
 if (I('site_id') != ''){ $site_id = I('site_id'); if(is_array($site_id)==false) { if(strpos($site_id, '_')) $site_id = explode ('_', $site_id); else $site_id = array($site_id); } echo '<p class="bg-primary">当前筛选网站：'; foreach ($site_id as $id){ echo $options_site_name[$id].'&nbsp;&nbsp;&nbsp;'; } echo '</p>'; } if(I('order_status_remark')!=''){ echo '<p class="bg-primary">当前筛选订单状态：'; foreach ($order_status_remark_select as $status){ echo $status.'&nbsp;&nbsp;&nbsp;'; } echo '</p>'; } ?>



<div class="page-nav">

        <div class="row">

            <div class="col-lg-6">

                <div class="page-nav-info">

                   每页

                   
                   <select id="" name="page_num" onchange="" ondblclick="" class="" ><?php  foreach($page_num_data as $key=>$val) { if(!empty($page_num_selected) && ($page_num_selected == $key || in_array($key,$page_num_selected))) { ?><option selected="selected" value="<?php echo $key ?>"><?php echo $val ?></option><?php }else { ?><option value="<?php echo $key ?>"><?php echo $val ?></option><?php } } ?></select>

                    个订单,(订单总数:<?php echo $count ?>)

                </div>

            </div>

            <div class="col-lg-6 right">

                <?php
 W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/Order/list', $page_data)); ?>

            </div>

        </div>

</div>





<form id="form-batch" action="" method="post">

    <input type="hidden" name="action" value="">

    

    <table class="order-list" id="order-list-header">

        <colgroup>

        <col>

        <col width="150px">

        <col width="250px">

        <col width="120px">

        <col width="70px">

        <col width="150px">

        <col width="200px">

        <col width="200px">

        <col width="auto">

        </colgroup>        

        <thead>

            <tr>

                <th><input type="checkbox" name="check_all" class="pull-left">序号
            
                </th>

                <th>订单号
                                        <?php
 if(I('order_status_remark')=='待订货'){ ?>
                        <br>
                        <button type="button" id="check-yidinghuo">勾选已订货</button>
                        <?php
 } ?>                
                </th>

                <th>交易网址</th>

                <th>支付方式<br>订单金额</th>

                <th>订单数</th>

                <th>订单状态</th>

                <th>客户姓名<br>客户邮箱</th>

                <th width="200px">下单(北京)时间<br>状态最近修改</th>

                <th>订单追踪</th>

            </tr>

        </thead>

    </table>



</script>

    <table class="order-list">

        <colgroup>

        <col width="80px">

        <col width="150px">

        <col width="250px">

        <col width="120px">

        <col width="70px">

        <col width="150px">

        <col width="200px">

        <col width="200px">

        <col width="auto">

        </colgroup>          

        <?php
 foreach ($list as $kk=>$entry) { ?>

            <tbody id="order-info-<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>">

                

                <tr class="sep-row"><td colspan="9"></td></tr>

                <tr class="order-hd<?php if(risk_order($entry)) echo ' risk-order'?>">

                    <td class="f08">

                        <input type="checkbox" name="site_to_orders[]" value="<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>">

                        <?php echo ($page-1)*$num+($kk+1);?></td>

                    <td<?php if((($comments=hasComment($entry['site_id'], $entry['orders_id']))!==false)) echo ' style="background-color:yellow;"';?>  width="150px">                        

                        <a class="link-view f08" <?php if($comments!==false) echo 'data-toggle="tooltip" data-placement="top" data-html="true" title="'.htmlspecialchars ($comments).'"'?> id="link-view-<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>" href="<?php echo U('Order/Order/view', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>">
                            <?php  $order_no = $entry['order_no']; if(empty($entry['order_no'])) $order_no = $entry['order_no_prefix'] . $entry['orders_id']; echo $order_no; ?></a>

                    </td>

                    <td class="f08">

                        <a target="_blank" href="<?php echo $entry['site_index'];?>"><?php echo '(ID:'.$entry['site_id'].'#) '.$entry['site_name'] ?></a>

                        <?php echo $entry['is_sale']?'<div style="color:red;">批发</div>':'<div style="color:green;">零售</div>'; ?>
                        <?php if(session(C('USER_INFO').'.profile_id') != 6){?>
                        <a  target="_blank" style="color:green;margin-right:10px;" href="<?php echo U('Order/Order/view', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>">编辑</a>

                        <a  target="_blank" style="color:blueviolet" href="<?php echo U('Order/Order/order_doc', array('site_id' => $entry['site_id'], 'order_id' => $entry['orders_id'])) ?>">打印</a>                        
                        <?php }?>
                    </td>

                    <td class="f08"><?php echo $entry['payment_module_code']?><br><?php echo round($entry['order_total'] * $entry['currency_value'], 2) . $entry['currency'] ?></td>

                    <td><?php echo $entry['num_products']?></td>

                    <td>

                        <?php
 $send_status_txt=''; if($entry['send_status']==1){ $send_status_txt='<span style="color:red;">邮件发送成功</span>'; }elseif($entry['send_status']==2){ $send_status_txt='<span style="color:green;">邮件已发送失败</span>'; } $tip = ''; $num_email = 0; $email_history = array(); if (!empty($entry['email_logs'])) { $email_history = json_decode($entry['email_logs'], true); } if (sizeof($email_history) > 0) { foreach ($email_history as $_email_history) { foreach ($_email_history as $history) { $tip .= '<div style=\'text-align:left;\'>' . $history['time'] . $history['email_template_name'] . "</div>"; $num_email++; } } } else { $tip = '无邮件发送记录'; } echo $send_status_txt.'<br />'; echo $entry['order_status_remark'] == '' ? '待处理' : (session(C('USER_INFO').'.profile_id') == 6 ? $entry['order_status_remark'] : '<a class="email_template_dialog" href="' . U('Order/Order/email/site_id/' . $entry['site_id'] . '/order_id/' . $entry['orders_id']) . '">' . $entry['order_status_remark'] . '</a>') . '<span class="badge" data-toggle="tooltip" data-placement="bottom" data-html="true" title="' . $tip . '">' . $num_email . '</span>'; ?>

                        <br>

                        <?php echo $entry['orders_status_name'].'<br><span class="text-primary">'.$entry['payment_status'].'</span>'?>

                    </td>

                    <td class="f08"><?php echo $entry['customers_name'] ?><br><?php echo preg_replace('/YK_(\d)+_/','',$entry['customers_email_address']); ?></td>

                    <td>

                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="下单(北京)时间" style="display:block;margin-bottom: 1px;" class="label label-default f08"><?php echo $entry['date_purchased'] ?></div>

                        <?php  if($entry['username']!=''){ ?>

                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="最后更改状态时间" style="display:block;margin-bottom: 1px;" class="label label-primary f08"><?php echo $entry['last_modify'] ?></div>

                        <div data-toggle="tooltip" data-placement="left" data-html="true" title="最后更改状态操作者" style="display:block;margin-bottom: 1px;" class="label label-primary f08"><?php echo $entry['username'].$entry['chinese_name'] ?></div>

                        <?php
 } ?>                    

                    </td>

                    <td>
<?php
if($entry['orders_products_remark_count']){ $yidinghuo = true; foreach($entry['orders_products_remark_count'] as $entry_orders_products_remark_count){ ?>
    <?php
 if($entry_orders_products_remark_count['item_status']=='已处理'){ ?>
        <div class="label label-success"><?php  echo $entry_orders_products_remark_count['item_status']."-".$entry_orders_products_remark_count['num'];?></div>
    <?php
 }else{ $yidinghuo = false; ?>
        <div class="label label-warning"><?php  echo $entry_orders_products_remark_count['item_status']."-".$entry_orders_products_remark_count['num'];?></div>
    <?php
 } ?>
    <input type="hidden" name="yidinghuo<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?>" value="<?php if($yidinghuo) echo 1; else echo 0; ?>" disabled="disabled"  />
<?php
 } } ?>

                        <?php
 if(sizeof($entry['delivery'])){ foreach($entry['delivery'] as $delivery_entry){ ?> 



                    <?php
 $aikuaidi_tracking_no_url=U('Order/ExpressDelivery/logistics',array('orders_delivery_id'=>$delivery_entry['orders_delivery_id'])); $delivery_type = $delivery_entry['delivery_type'].$delivery_entry['delivery_status']; $aikuaidi_forward_no_url=''; if(!empty($delivery_entry['delivery_forward_no'])){ $delivery_entry['delivery_tracking_no']='<del>'.$delivery_entry['delivery_tracking_no'].'</del>'; echo $delivery_entry['delivery_tracking_no']; echo '<br>'.$delivery_entry['delivery_date']; ?>

                            <a class="label label-primary f08" rel="<?php echo $delivery_entry['delivery_status']?>" target="_blank" style="margin: 1px 0;display: block;" href="https://t.17track.net/en#nums=<?php echo $delivery_entry['delivery_forward_no'] ;?>" data-toggle="tooltip" data-placement="top" title="<?php echo "转单号".$delivery_type; ?>"><?php echo $delivery_entry['delivery_forward_no'];?></a>
                    <?php  }else{ ?>

                            <a class="label label-primary f08" rel="<?php echo $delivery_entry['delivery_status']?>" target="_blank" style="margin: 1px 0;display: block;" href="https://t.17track.net/en#nums=<?php echo $delivery_entry['delivery_tracking_no'] ;?>" data-toggle="tooltip" data-placement="top" title="<?php echo $delivery_type; ?>"><?php echo $delivery_entry['delivery_tracking_no'];?></a>
                    <?php
 } ?>    
						<div class="label label-primary f08 view_delivery_products" style="margin: 1px 0;display: block;cursor: pointer;" data-toggle="tooltip" data-placement="top" title="点击查看发货信息" data-orders_delivery_id=<?php echo $delivery_entry['orders_delivery_id'];?> data-delivery_tracking_no=<?php echo $delivery_entry['delivery_tracking_no'];?>><?php  echo $delivery_entry['delivery_date']."发货";?></div>
                    <?php  } if($entry['out_of_stock'] == 1){ ?>
						<div class="label label-default f08 view_out_of_stock" style="margin: 1px 0;display: block;cursor: pointer;" data-toggle="tooltip" data-placement="top" title="点击查看没货的产品" data-site_orders_id=<?php echo $entry['site_id'] . '-' . $entry['orders_id'] ?> data-order_no=<?php echo $order_no;?>>没货产品</div>
					<?php
 } } ?>

                    </td>

                </tr>

            </tbody>

            <?php
 } ?>

    </table>

    

    <div class="page-nav">

        <div class="row">

            <div class="col-lg-6">

                <div class="page-nav-info">

                   <label>订单数量:</label>

                    <?php
 $page_num_data = array(1=>1,25=>25,50=>50,100=>100, 200=>200,300=>300, 500=>500); $page_num_selected = I('page_num', 300); ?>

                    <label>每页</label>

                    <select id="" name="page_num" onchange="" ondblclick="" class="" ><?php  foreach($page_num_data as $key=>$val) { if(!empty($page_num_selected) && ($page_num_selected == $key || in_array($key,$page_num_selected))) { ?><option selected="selected" value="<?php echo $key ?>"><?php echo $val ?></option><?php }else { ?><option value="<?php echo $key ?>"><?php echo $val ?></option><?php } } ?></select>

                    <label>条(当前总数:</label> <?php echo $count ?><label>条)</label>

                </div>

            </div>

            <div class="col-lg-6 right">

                <?php
 W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Order/Order/list', $page_data)); ?>

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
 foreach($data_shipping_status as $entry){ ?>

				<button class="btn btn-default btn-xs btn-update-express-status"><?php echo $entry?></button>

				<?php	 } ?>

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
 foreach ($options_site_name as $type=>$site){ ?>
                <div class="col-xs-12 site-type-box">
                    <div class="col-xs-12">
                        <label>
                            <input class="site-type" type="checkbox">
                            <?php if($type == 1){?>独立站<?php }elseif($type == 2){?>B站<?php }elseif($type == 10){?>平台站<?php }else{echo $type;}?>
                        </label>
                    </div>
                    <?php foreach ($site as $site_id => $site_name){?>
                    <div class="col-xs-4">
                        <label><input type="checkbox" name="site_id[]" value="<?php echo $site_id?>"<?php if(in_array($site_id,$site_id_select)) echo ' checked'?>>
                        <?php echo ' '.$site_id.'# '.$site_name ?></label>
                    </div>
                    <?php }?>
                </div>
            <?php  } ?>

            </div>

        </div>

    </div>
    <script>
        $('#site_dialog .site-type').click(function(){
            if($(this).is(':checked')){
                $(this).parents('.site-type-box').find('[name="site_id[]"]').not("input:checked").click();
            }else{
                $(this).parents('.site-type-box').find('[name="site_id[]"]:checked').click();
            }
        });
    </script>
</div>



<div class="modal fade" id="order_status_remark_dialog">

    <div class="modal-dialog modal-lg">

        <div class="modal-content" style="padding:10px;">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title">订单状态</h4>

            </div>

            <div class="row">

            <?php
 foreach ($order_status_remark as $k=>$status){ ?>

                <div class="col-xs-4">

                    <label><input type="checkbox" name="status_remark[]" value="<?php echo $k?>"<?php if(in_array($k,$order_status_remark_select)) echo ' checked'?>>

                    <?php echo ' '.$status ?></label>

                </div>    

            <?php  } ?>

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

<div class="modal fade" id="payment_sys_dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">表格处理</h4>
             </div>
            <div class="modal-body">
                <form action="<?php echo U('Order/Order/table_handle') ?>" enctype="multipart/form-data" method="post">
                    <div class="form-group">
                        <label>文件导入</label>
                      <input type="file" name="file">
                      <p class="help-block">支持融信汇,中外宝1，中外宝2等表格的处理。文件格式必须xls</p>
                    </div>
                    <button type="submit" class="btn btn-default">提交</button>
                </form>
            </div>
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

                      <p class="help-block">程序将根据文件的扩展名来判断是哪个通道支付接口表。</p>

                      <p class="help-block">请按照<a href="/supportsGit/Public/example/example_payment_confirmation.xls" target="__blank">example_payment_confirmation.xls</a>标准格式导入!</p>

                      <p class="help-block">请按照<a href="/supportsGit/Public/example/stripe_payment.csv" target="__blank">stripe_payment.csv</a>标准格式导入!</p>

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

                 <?php  foreach ($order_status_remark as $k=>$v){ echo '<button class="btn btn-default change_status" type="button" rel="'.$k.'">'.$v.'</button>&nbsp;'; } ?>

             </div>

        </div>

    </div>

</div>

<div class="modal fade" id="delivery_order_dialog">
    <div class="modal-dialog" style="width:1000px;">
        <div class="modal-content" style="padding:10px;">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">物流信息导出</h4>
             </div>
             <div class="modal-body">
                <form action="<?php echo U('Order/ExpressDelivery/export'); ?>" method="POST" target="_blank">
                    <div class="form-inline">
                    <div class="form-group">
                        <label for="date-send-start">发货日期范围</label>
                        <input type="text" class="form-control date_send_start" id="date-send-start" name="date_send_start" placeholder="起始日期">
                        <input type="text" class="form-control date_send_end" id="date-send-end" name="date_send_end" placeholder="结束日期">
                    </div>
                    </div>
                    <div class="form-group">
                        <label for="date-send-start">订单号(一行一个订单号)</label>
                        <textarea class="form-control" name="order_no" rows="10"></textarea>
                    </div>
                    <button class="btn btn-default" type="submit">导出</button>
                </form>
             </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delivery_products_dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="padding:10px;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<div class="products_info"></div>
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
    
    $("input[name^='date_send']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});



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

    $('input[name="status_remark[]"]').click(function(){

        var v = '';

        $('input[name="status_remark[]"]:checked').each(function(){

            if(v=='') v = $(this).val();

            else v += '_'+$(this).val();

        });

        $('input[name="order_status_remark"]').val(v);

    });    

    $(window).scroll(function(){

        var top = $(window).scrollTop();

        if(top>500){

            $('#order-list-header').css({position:'fixed', top: 0, width:'1370px'});

        }else{

            $('#order-list-header').css({position:'relative', top: 0, width:'1370px'});

        }

    });
    $('#check-yidinghuo').click(function(){
        $('input[name="site_to_orders[]"]').each(function(){
            var yidinghuo = $('input[name="yidinghuo'+$(this).val()+'"]').val();
            var checked = (yidinghuo==1?true:false);
            $(this).prop("checked", checked);
        });
    });
        $('#btn-export-purchase').click(function () {
            var action = $("#form-search").attr('action');
            $("#form-search").attr("target", "_blank");
            $("#form-search").attr('action', '<?php echo U('Order/Order/list/parchase_table/1') ?>');
            $("#form-search").submit();
            $("#form-search").attr('action', action);
            $("#form-search").attr("target", "");
        });    

	$('.view_delivery_products').click(function(){
		$('#delivery_products_dialog .modal-header h4').text('货运单号：' + $(this).attr('data-delivery_tracking_no'));
		var orders_delivery_id = $(this).attr('data-orders_delivery_id');
		$.ajax({
			url  : "<?php echo U('Order/Order/getDeliveryProducts');?>",
			type : 'post',
			data : {'orders_delivery_id':orders_delivery_id},
			dataType : 'json',
			success : function(data){
				if(data.status==1){
					$('#delivery_products_dialog .modal-body .products_info').html(data.html);
					$('#delivery_products_dialog').modal('show');
				}else{
					layer.msg(data.msg);
				}
			}
		});
	});

	$('.view_out_of_stock').click(function(){
		$('#delivery_products_dialog .modal-header h4').text('订单号：' + $(this).attr('data-order_no') + '没货的产品');
		var site_orders_id = $(this).attr('data-site_orders_id');
		$.ajax({
			url  : "<?php echo U('Order/Order/getOutOfStockProducts');?>",
			type : 'post',
			data : {'site_orders_id':site_orders_id},
			dataType : 'json',
			success : function(data){
				if(data.status==1){
					$('#delivery_products_dialog .modal-body .products_info').html(data.html);
					$('#delivery_products_dialog').modal('show');
				}else{
					layer.msg(data.msg);
				}
			}
		});
	});
</script>
</div>
<footer id="footer"><?php echo R('Common/Layout/footer');?></footer>
<?php echo R('Common/Html/html_end');?>