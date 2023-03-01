<h1>邮件账号新增/编辑</h1>

<form action="<?php echo U('Marketing/Email/smtp_account'); ?>" method="POST">
    <input type="hidden" name="id" value="<?php echo $id?>">
    <input type="hidden" name="act" value="save">
    <div class="form-group">
        <label for="title">SMTP账号</label>
        <input type="text" name="email_username" class="form-control" placeholder="SMTP账号" value="<?php echo isset($email_account_info['email_username']) ? $email_account_info['email_username'] : '' ?>">
    </div>
    <div class="form-group">
        <label for="title">SMTP密码</label>
        <input type="text" name="email_password" class="form-control" placeholder="SMTP密码" value="<?php echo isset($email_account_info['email_password']) ? $email_account_info['email_password'] : '' ?>">
    </div>
    <div class="form-group">
        <label for="title">SMTP域名</label>
        <input type="text" name="email_stmp_host" class="form-control" placeholder="SMTP域名" value="<?php echo isset($email_account_info['email_stmp_host']) ? $email_account_info['email_stmp_host'] : '' ?>">
    </div>
    <div class="form-group">
        <label for="title">SMTP端口</label>
        <input type="text" name="email_smtp_port" class="form-control" placeholder="SMTP端口" value="<?php echo isset($email_account_info['email_smtp_port']) ? $email_account_info['email_smtp_port'] : '' ?>">
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
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste code'
            ],
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            content_css: '//www.tinymce.com/css/codepen.min.css'
        });
    });
</script>