<form id="form-search" class="advanced-search form-horizontal" action="<?php echo U('Order/Order/list') ?>" method="get">
    <input type="hidden" name="action" value="">
    <?php if(I('is_paid') == 1){?>
    <input type="hidden" name="is_paid" value="1">
    <?php }?>
    <?php if(I('order_status_press') == '支付失败需催款'){?>
    <input type="hidden" name="order_status_press" value="支付失败需催款">
    <?php }?>
    <div class="row">
        <label for="taobao-no" class="col-lg-1">交易网址</label>
        <div class="col-lg-2">
                <input type="hidden" name="site_id" value="<?php echo I('site_id', '') ?>">
                <button data-toggle="modal" data-target="#site_dialog" type="button" class="btn btn-default">网址筛选</button>
        </div>     
        <label for="zencart-order-no" class="col-lg-1 f09" data-toggle="tooltip" data-placement="top" title="订单号">订单号</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="zencart_order_no" id="zencart-order-no" placeholder="订单号/商城单号/通道交易号" value="<?php echo I('zencart_order_no', '') ?>"></div>
		<label for="zencart-order-no" class="col-lg-1 f09" data-toggle="tooltip" data-placement="top" title="货运单号/转运单号">发货单号</label>
		<div class="col-lg-2"><input class="form-control" type="text" name="delivery_no" id="delivery_no" placeholder="货运单号/转运单号" value="<?php echo I('delivery_no', '') ?>"></div>        
        <label for="rp-no" class="col-lg-1">客户反馈</label>
        <div class="col-lg-2">
            <tagLib name="html" />
            <html:select options="data_customer_feedback" name="customer_feedback" selected="customer_feedback" first="--select--" style="form-control" />
        </div>      

    </div>
    <div class="row">
    <label for="customer-email" class="col-lg-1">邮箱</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="customer_email" id="customer-email" placeholder="邮箱" value="<?php echo I('customer_email', '') ?>"></div>
         

        <div class="col-lg-2">
            <input type="hidden" name="order_status_remark" value="<?php echo I('order_status_remark', '') ?>">
            <button data-toggle="modal" data-target="#order_status_remark_dialog" class="btn btn-default dropdown-toggle" type="button" >订单状态</button>            
        </div>
        <label for="site" class="col-lg-1">支付方式</label>
        <div class="col-lg-2">
            <html:select options="payment_methods" name="payment_method" selected="payment_method_selected" style="form-control" first="--付款方式--" />
        </div>
        <label for="site" class="col-lg-1">网站订单状态</label>
        <div class="col-lg-2">
            <html:select options="zencart_orders_status" name="zencart_orders_status" selected="zencart_orders_status_selected" style="form-control" first="--网站订单状态--" />
        </div>        
    </div>
    <div class="row">
        <label for="site-name" class="col-lg-1">产品型号</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="products_name" id="products_name" placeholder="产品型号" value="<?php echo I('products_name') ?>"></div>

                      
        <label for="taobao-no" class="col-lg-1">下单时间</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="order_time_start" value="<?php echo I('order_time_start', '') ?>" placeholder="起始日期"></div>
        <div class="col-lg-2"><input class="form-control" type="text" name="order_time_end" value="<?php echo I('order_time_end', '') ?>" placeholder="结束日期"></div>
		<label for="taobao-no" class="col-lg-1">零售|批发</label>
       <div class="col-lg-2">
       <?php 
       	 $sale=array(
       	 		array('val'=>-1,'txt'=>'ALL','sel'=>$_GET['is_sale']==-1?'selected="selected"':''),
       	 		array('val'=>0,'txt'=>'零售','sel'=>$_GET['is_sale']==0?'selected="selected"':''),
       	 		array('val'=>1,'txt'=>'批发','sel'=>$_GET['is_sale']==1?'selected="selected"':''),
       	 );
       ?>
       	<select name="is_sale" class="form-control">
       		<?php 
       			foreach($sale as $ts){
       				echo "<option value=".$ts['val']." ".$ts['sel'].">".$ts['txt']."</option>\n";
       			}
       		?>
       	</select>
       </div>
	</div>
    <div class="row">
        <label for="site" class="col-lg-1">业务员</label>
        <div class="col-lg-2">
            <html:select options="users" name="user_id" selected="user_id_selected" style="form-control" first="--业务员--" />
        </div>

        <label for="site" class="col-lg-1">修改时间</label>
        <div class="col-lg-2">
        <?php 
        	$ss=array(
        			array('val'=>'1','txt'=>'降序排列','sel'=>$_GET['last_modify']==1 ? 'selected="selected"':''),
        			array('val'=>'2','txt'=>'升序排列','sel'=>$_GET['last_modify']==2 ? 'selected="selected"':''),
        	)
        ?>
            	<select name="last_modify"  class="form-control">
            	<?php 
            	foreach($ss as $tt){
            		echo "<option value=".$tt['val']." ".$tt['sel'].">".$tt['txt']."</option>\n";
            	}
            	?>
            	</select>
        </div>              
        <label class="col-lg-1">发货时间</label>
        <div class="col-lg-2"><input class="form-control" type="text" name="delivery_date_start" value="<?php echo I('delivery_date_start', '') ?>" placeholder="起始日期"></div>
        <div class="col-lg-2"><input class="form-control" type="text" name="delivery_date_end" value="<?php echo I('delivery_date_end', '') ?>" placeholder="结束日期"></div>
		

    </div>  
    <div class="row">
        <label for="site" class="col-lg-1">客户姓名</label>
        <div class="col-lg-1"><input class="form-control" type="text" name="customer_name" id="customer-name" placeholder="客户姓名" value="<?php echo I('customer_name', '') ?>"></div>
        
        <label for="site" class="col-lg-1">客户IP</label>
        <div class="col-lg-1"><input class="form-control" type="text" name="ip_address" id="ip-address" placeholder="客户IP" value="<?php echo I('ip_address', '') ?>"></div>

         <label for="site" class="col-lg-1">客户电话</label>
        <div class="col-lg-1"><input class="form-control" type="text" name="customers_telephone" id="customers-telephone" placeholder="客户电话" value="<?php echo I('customers_telephone', '') ?>"></div>
        <label for="last-email" class="col-lg-2">最近未发送的邮件</label>
        <div class="col-lg-2">
            <html:select options="options_email_templates" name="last_email_template" selected="last_email_template_selected" style="form-control" first="--最近未发送的邮件--" />
        </div>
    </div>
	<div class="row">
        <label for="last-email" class="col-lg-2">是否成功发送邮件</label>
        <div class="col-lg-2">
            <html:select options="options_send_status" name="send_status" selected="send_status_selected" style="form-control" first="--请选择--" />
        </div>
		<div class="col-lg-4"></div>
	   <div class="col-lg-4">
            <button class="btn btn-default" type="submit">查询</button>
            <?php if(session(C('USER_INFO').'.profile_id') != 6){?>
            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#change_status_dialog">订单状态(变更)</button> 
            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#multie_mail_dialog">邮件批量发送</button>
            <?php }?>
        </div>  
    </div>  
    <hr/>
    <?php if(session(C('USER_INFO').'.profile_id') != 6){?>
    <div class="row">
        <div class="col-lg-12 right" >
            <label>表格处理:</label>
            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#payment_sys_dialog">通道表格</button>
            
            <label>导入:</label>
            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#payment_confirmation_dialog">付款确认</button>
            <a class="btn btn-default" data-toggle="modal" data-target="#delivery_excel_dialog" href="<?php echo U('Order/Order/delivery_excel_import') ?>">物流回单</a>
  
            <label>导出:</label>
            <button class="btn btn-default" type="button" id="btn-export2">管理表(业务)</button>
            <button class="btn btn-default" type="button" id="btn-export3">地址单(To物流)</button>
            <button class="btn btn-default" type="button" id="btn-export">订单打印(To物流)</button>
            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#delivery_order_dialog">物流信息导出</button>
            <button class="btn btn-default" type="button" id="btn-export-purchase">订货表</button>
        </div>
    </div>  
    <hr/>
    <?php }?>
</form>