<h1>邮件模版编辑</h1>
<form action="<?php echo U('Marketing/Email/template_mail'); ?>" method="POST">
    <input type="hidden" name="email_template_id" value="<?php echo $email_template_result['email_template_id'] ?>">
    <input type="hidden" name="act" value="save">
    <div class="form-group">

        <div class="form-group">
            <label for="title">名称</label>
            <input type="text" name="email_template_name" class="form-control" placeholder="名称"
                   value="<?php echo isset($email_template_result['email_template_name']) ? $email_template_result['email_template_name'] : '' ?>">
        </div>

        <div class="form-group">
            <label for="title">标题</label>
            <input type="text" name="email_template_title" class="form-control" placeholder="标题"
                   value="<?php echo isset($email_template_result['email_template_title']) ? $email_template_result['email_template_title'] : '' ?>">
        </div>
        <div class="form-group">
            <label for="content">邮件内容</label>
            <textarea name="email_template_content"
                      class="form-control"><?php echo isset($email_template_result['email_template_content']) ? $email_template_result['email_template_content'] : '' ?></textarea>
        </div>
        <div style="float:right">
            是否开启邮件模版:
            <label>
                <input type="radio" name="email_template_status"
                       value="1"<?php echo $email_template_result['email_template_status'] == 1 ? ' checked="checked"' : ''; ?>/>
                <span>是</span>
            </label>
            <label>
                <input type="radio" name="email_template_status"
                       value="0"<?php echo $email_template_result['email_template_status'] == 0 ? ' checked="checked"' : ''; ?>/>
                <span>否</span>
            </label>
        </div>
        <br>
        <div>
            <a class="btn btn-default" href="<?php echo U('Marketing/Email/template_mail') ?>">返回</a>
            <button style="float:right" type="submit" class="btn btn-default pull-right">保存</button>
        </div>


</form>
<!--引入富文本编辑器><!-->
<script src="__PUBLIC__/Js/tinymce/tinymce.min.js" type="text/javascript"></script>
<script type="text/javascript">
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