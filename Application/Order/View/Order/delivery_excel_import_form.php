<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">发货物流信息表导入</h4>
</div>
<div class="modal-body">
    <form action="<?php echo U('Order/Order/delivery_excel_import') ?>" enctype="multipart/form-data" method="post">
        <input type="hidden" name="action" value="upload">
        <div class="form-group">
          <label>发货物流信息表</label>
          <input type="file" name="file">
          <p class="help-block">请按照<a href="__PUBLIC__/example/example_shipping_address_excel.xls" target="__blank">发货物流信息表导入样例.xls</a>标准格式导入!</p>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>

