<h1>邮件模板</h1>

<table class="table table-border">
    <tr>
        <th>模板ID</th>
        <th>模板标题</th>
        <th>操作</th>
    </tr>
    <?php
    if(empty($list)==false){
        foreach ($list as $entry){
    ?>
    <tr>
        <td><?php echo $entry['email_template_id']?></td>
        <td><?php echo $entry['email_template_name']?></td>
        <td>
            <a href="<?php echo U('Marketing/Email/template/act/edit/id/'.$entry['email_template_id']) ?>">编辑</a>
            <a href="<?php echo U('Marketing/Email/template/act/del/id/'.$entry['email_template_id']) ?>">删除</a>
        </td>
    </tr>
    <?php
        }        
    }
    ?>
</table>

<a class="btn btn-default" href="<?php echo isset($_GET['source']) && $_GET['source']=='no_order' ? U('Marketing/Email/no_order_customers') : U('Marketing/Email/index');?>">返回</a>
<a class="pull-right btn btn-default" href="<?php echo U('Marketing/Email/template/act/add') ?>">新增</a>