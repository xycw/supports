<h1>模板编辑</h1>

<form action="<?php echo U('Marketing/Email/template'); ?>" method="POST">
    <input type="hidden" name="id" value="<?php echo $id?>">
    <input type="hidden" name="act" value="save">
    <div class="form-group">
        <label for="title">模板标题</label>
        <input type="text" name="title" class="form-control" id="title" placeholder="模板标题" value="<?php echo isset($template_info['email_template_name']) ? $template_info['email_template_name'] : '' ?>">
    </div>
    <div class="form-group">
        <label for="title">邮件主题</label>
        <input type="text" name="subject" class="form-control" id="title" placeholder="模板标题" value="<?php echo isset($template_info['email_template_subject']) ? $template_info['email_template_subject'] : '' ?>">
    </div>    
    <div class="form-group">
        <label for="content">邮件内容</label>
        <textarea name="content" class="form-control"><?php echo isset($template_info['email_template_content']) ? $template_info['email_template_content'] : '' ?></textarea>
    </div>
    <a class="btn btn-default" href="<?php echo U('Marketing/Email/template')?>">返回</a>
       
    <button type="submit" class="btn btn-default pull-right">保存</button>
</form>

<script src="__PUBLIC__/Js/tinymce/tinymce.min.js" type="text/javascript"></script>
<script type="text/javascript" >
    $(document).ready(function () {
        tinymce.init({
          selector: 'textarea',
          height: 500,
          menubar: false,
          plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code help'
          ],
          toolbar: 'insert | undo redo |  formatselect fontsizeselect | link bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | | code | help',
          fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
          content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            '//www.tinymce.com/css/codepen.min.css']
        });        
    });
</script>