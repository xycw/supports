<h1>SMTP邮箱账号</h1>

<table class="table table-border">
    <tr>
        <th>账号</th>
        <th>密码</th>
        <th>SMTP域名</th>
        <th>SMTP端口</th>
        <th>操作</th>
    </tr>
    <?php
    if(empty($list)==false){
        foreach ($list as $entry){
    ?>
    <tr>
        <td><?php echo $entry['email_username']?></td>
        <td><?php echo $entry['email_password']?></td>
        <td><?php echo $entry['email_stmp_host']?></td>
        <td><?php echo $entry['email_smtp_port']?></td>
        <td>
            <a href="<?php echo U('Marketing/Email/smtp_account/act/edit/id/'.$entry['email_account_id']) ?>">编辑</a>
            <a href="<?php echo U('Marketing/Email/smtp_account/act/del/id/'.$entry['email_account_id']) ?>">删除</a>
        </td>
    </tr>
    <?php
        }        
    }
    ?>
</table>
<a class="btn btn-default" href="<?php echo isset($_GET['source']) && $_GET['source']=='no_order' ? U('Marketing/Email/no_order_customers') : U('Marketing/Email/index');?>">返回</a>
<a class="pull-right btn btn-default" href="<?php echo U('Marketing/Email/smtp_account/act/add') ?>">新增</a>