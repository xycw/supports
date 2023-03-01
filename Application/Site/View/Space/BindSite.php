<h1>空间<?php echo $ip ?>域名绑定</h1>
<div class="row">
    <div class="col-xs-5">
        <select multiple="multiple" class="form-control" style="height:200px;" id="site_available">
            <?php
            foreach ($list_site as $entry){
            ?>
            <option value="<?php echo $entry['site_id']?>"><?php echo $entry['site_name']?></option>
            <?php
            }
            ?>
        </select>
    </div>
    <div class="col-xs-2" style="text-align: center;">
        <button type="button" class="btn btn-default btn-block" id="add">添加&gt;&gt;</button>
        
        <button type="button" class="btn btn-default btn-block" id="remove">&lt;&lt;移除</button>
    </div>
    <div class="col-xs-5">
        <form action="<?php echo U('Site/Space/BindSite/space_id/'.$space_id) ?>" method="post">
            <select multiple="multiple" class="form-control" name="site[]" style="height:200px;" id="site_bind" size="2"></select>
            <button class="btn btn-default pull-right" type="submit">保存</button>
        </form>        
    </div>
</div>

<script>
$(document).ready(function(){
    $('#add').click(function(){
        $('option:selected','#site_available').appendTo('#site_bind');
    });
    $('#remove').click(function(){
        $('option:selected','#site_bind').appendTo('#site_available');
    });    
});
</script>
    