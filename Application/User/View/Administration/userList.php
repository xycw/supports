<h1>系统用户</h1>
<table class="table table-border">
    <tr>
        <th>编号</th>
        <th>登录名</th>
        <th>中文名</th>
        <th>英文名</th>
        <th>角色</th>
        <th>状态</th>
        <td>操作</td>
    </tr>
<?php
foreach($list  as $entry){
?>    
    <tr>
        <td><?php echo $entry['user_id']?></td>
        <td><?php echo $entry['username']?></td>
        <td><?php echo $entry['chinese_name']?></td>
        <td><?php echo $entry['english_name']?></td>
        <td><?php echo $entry['profile_name']?></td>
        <td><?php echo $entry['status']=='1'?'<span class="glyphicon glyphicon-ok"></span>':'<span class="glyphicon glyphicon-remove"></span>' ?></td>
        <td><a class="btn btn-default btn-sm" href="<?php echo U('User/Administration/edit/user_id/'.$entry['user_id'])?>">编辑</a>
        <a class="btn btn-default btn-sm" href="<?php echo U('User/Administration/permission/user_id/'.$entry['user_id'])?>">权限分配</a>
        </td>
    </tr>
<?php    
}
?>    
</table>

<a class="btn btn-default btn-sm" href="<?php echo U('User/Administration/add')?>">添加新用户</a>