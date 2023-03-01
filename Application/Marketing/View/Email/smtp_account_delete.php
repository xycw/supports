<h1>模板删除</h1>
<p class="bg-info">确定删除此STMP账号(<?php echo $email_account_info['email_username']?>)吗？</p>
<a class="btn btn-default pull-right" href="<?php echo U('Marketing/Email/smtp_account/act/del/id/'.$email_account_info['email_account_id'].'/confirmation/1') ?>">确定删除</a>
