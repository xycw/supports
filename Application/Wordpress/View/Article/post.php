<h1>文章发布</h1>

<form action="<?php echo U(ACTION_NAME)?>" method="POST" class="form-inline" name="post_form" id="post_form">
<input type="hidden" name="id" value="<?php echo $data_article['articles_id'] ?>" />
<table class="table table-striped table-bordered">  
    <tr>
        <th width="150px">标题:</th>
        <td><?php echo $data_article['articles_title'] ?></td>
    </tr>
    <tr>
        <th>内容:</th>
        <td>
            <div style="width:1100px;overflow: scroll;">
            <?php echo html_entity_decode($data_article['articles_content']) ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>发布到网站:</th>
        <td>
            <?php
            foreach ($site_available as $entry){
            ?>
            <div>
            <div class="checkbox"><label><input type="checkbox" name="post_site[]" value="<?php echo $entry['site_id'] ?>"><?php echo $entry['site_name'] ?></label></div>            
            <div class="form-group"></div>
            </div>
            <?php
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>已发布的网站:</th>
        <td>
            <?php
            foreach ($site_unavailable as $entry){
            ?>
            <div>
            <div class="checkbox"><label><input type="checkbox" name="post_site[]" value="<?php echo $entry['site_id'] ?>"><?php echo $entry['site_name'] ?></label></div>
            <div class="form-group"></div>
            </div>
            <?php
            }
            ?>
        </td>
    </tr>    
    <tr>
        <td colspan="2" class="text-center">
            <a class="btn btn-default pull-left" href="<?php echo U('Wordpress/Article/list')?>">返回</a>
            <input name="action" type="hidden" value="">
            <button class="btn btn-default" type="buton">提交</button>
            <button class="btn btn-default pull-right" type="button">删除</button>
        </td>
    </tr>
</table>
</form>

<script>
$(document).ready(function(){
    $('button[class^="btn"]').click(function(){
       var act = $(this).text();
       if(act=='提交'){
           $('input[name="action"]').val('post');
       }else if(act=='删除'){
           $('input[name="action"]').val('delete');
       }
       post_form.submit();
    });
    $.ajaxSetup({
        beforeSend : function(){
            layer.load(1);
        },
        complete : function(){
            layer.closeAll('loading');
        }
    });
    $('input[name="post_site[]"]').click(function(){
        if($(this).parent().parent().parent().find('.form-group select').size()==0){
            var site_id = $(this).val();
            var _this   = this;
            $.getJSON("<?php echo U('Wordpress/Article/ajax_get_categories/site_id')?>/"+site_id, function(data){
                var obj_select = $('<select class="form-control" name="category['+site_id+']"></select>').appendTo($(_this).parent().parent().parent().find('.form-group'));
                for(var i in data){
                    obj_select.append('<option value="'+data[i].id+'">'+data[i].name+'</option>');
                }                
            });
        }        
    });
});
</script>
