<form class="form-horizontal" action="<?php echo U('Order/Order/orderHistory/site_id/'.$site_id.'/order_id/'.$order_id)?>" method="post">
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title">订单历史状态</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		<label for="inputEmail3" class="col-sm-2">状态</label>
		<div class="col-sm-10">
			<tagLib name="html" />
			<html:select options="select_status" name="orders_status_id" style="form-control" />		</div>
	</div>
	<div class="form-group">
		<label for="inputEmail3" class="col-sm-2">备注</label>
		<div class="col-sm-10"><textarea class="form-control" name="comments"></textarea></div>
	</div>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
	<button type="submit" class="btn btn-primary">保存</button>
</div>
</form>