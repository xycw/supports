<h1>邮件模板列表</h1>

<table class="table table-border">
    <tr>
        <th>模板ID</th>
        <th>模板标题</th>
        <th>创建时间</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    <?php
    if(empty($list)==false){
        foreach ($list as $entry){
    ?>
    <tr>
        <td><?php echo $entry['email_template_id']?></td>
        <td><?php echo $entry['email_template_title']?></td>
        <td><?php echo $entry['date']?></td>
        <td><?php echo ($entry['status']==1?'启用':'关闭') ?></td>
        <td><a href="<?php echo U('Email/Template/Edit/id/'.$entry['email_template_id']) ?>">编辑</a></td>
    </tr>
    <?php
        }        
    }
    ?>
</table>