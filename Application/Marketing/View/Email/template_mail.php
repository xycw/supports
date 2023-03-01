<h1>邮件模版</h1>

<form action="<?php echo U('Marketing/Email/template_mail') ?>" method="post">
    <table class="table table-border">
        <tr>
            <th>模板标题</th>
            <th>操作</th>
        </tr>
        <?php
        if (empty($email_template_result) == false) {
            foreach ($email_template_result as $entry) {
                ?>
                <tr>
                    <td><?php echo $entry['email_template_name'] ?></td>
                    <td>
                        <a href="<?php echo U('Marketing/Email/template_mail/act/edit/email_template_id/' . $entry['email_template_id']) ?>">编辑</a>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
    </table>

</form>

<div class="page-nav">
    <div class="page-nav-info">总数:<?php echo $email_template_count ?></div>
    <?php
    W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $email_template_count, 'name' => 'Marketing/Email/template_mail'));
    ?>
</div>