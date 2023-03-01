<h1>用户权限分配</h1>

<form action="<?php echo U('User/Administration/permission')?>" method="post">
    <input type="hidden" name="user_id" value="<?php echo $user_info['user_id']?>">
<div class="panel panel-default">
    <div class="panel-heading">可操作的网站权限</div>
    <div class="panel-body form-inline">
        <table class="table table-striped">
            <tr>
            <th>网站ID#网站</th>
            <th>对应登录名(业务姓名)</th>
            </tr>
        <?php
        foreach($data_site as $entry){
        ?>    
        <tr>
            <td><label><input type="checkbox" name="site_id[]" value="<?php echo $entry['site_id']?>"<?php if(is_array($user_info['site']) && in_array($entry['site_id'], $user_info['site'])) echo ' checked'?>><?php echo $entry['site_id']."#".$entry['site_name'].$entry['department_name']?></label></td>
            <td>
                <?php
                if(!is_null($entry['user'])){
                    foreach ($entry['user'] as $u){
                        echo '<span class="label label-default">'.$u['username'].'('.$u['chinese_name'].')</span>';
                    }
                }                
                ?>
            </td>
        </tr>
        <?php
        }
        ?>
        </table>
    </div>
</div>
    <button type="submit" class="btn btn-default">保存</button>
</form>