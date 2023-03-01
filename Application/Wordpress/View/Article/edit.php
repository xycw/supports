<h1>文章编辑/添加</h1>

<form action="<?php echo U(ACTION_NAME)?>" method="POST">
    <?php
    if(isset($data_article['articles_id']))
        echo '<input type="hidden" name="articles_id" value="'.$data_article['articles_id'].'">';
    ?>
<table class="table table-striped table-bordered">
    <tr>
        <th>类型:</th>
        <td>
            <tagLib name="html" />
            <html:select options="options_type" name="articles_type" selected="articles_type" style="form-control" />
        </td>
    </tr>    
    <tr>
        <th>标题:</th>
        <td><input type="text" name="articles_title" class="form-control" value="<?php echo isset($data_article['articles_title'])?$data_article['articles_title']:'' ?>" /></td>
    </tr>
    <tr>
        <th>内容:</th>
        <td><textarea name="articles_content" class="form-control"><?php echo isset($data_article['articles_content'])?$data_article['articles_content']:'' ?></textarea></td>
    </tr>  
    <tr>
        <th>标签:</th>
        <td><input type="text" name="articles_tags" class="form-control" value="<?php echo isset($data_article['articles_tags'])?$data_article['articles_tags']:'' ?>" /></td>
    </tr>      
    <tr>
        <td colspan="2">
            <a class="btn btn-default" href="<?php echo U('Wordpress/Article/list')?>">返回</a>
            <button class="btn btn-default pull-right" type="submit">提交</button>
        </td>
    </tr>
</table>
</form>
<load href="__PUBLIC__/Js/ckeditor/ckeditor.js" />
<load href="__PUBLIC__/Js/ckfinder/ckfinder.js" />
<script>
var editor = CKEDITOR.replace( 'articles_content' );
CKFinder.setupCKEditor( editor) ;
</script>