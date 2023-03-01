<h1><?php echo $form_title ?></h1>
<form action="<?php echo U('Site/Space/'.ACTION_NAME) ?>" method="post">
    <?php if(ACTION_NAME=='edit') echo '<input type="hidden" name="space_id" value="'.$space_info['space_id'].'">'?>
    <table class="table table-bordered">
        <tr>
            <th>IP</th>
            <th>面板登录链接</th>
            <th>账号</th>
            <th>密码</th>
            <th>备注</th>
            <th>过期日期</th>
        </tr>
        <tr>
            <td><input class="form-control" type="text" name="ip" placeholder="空间IP" value="<?php if(ACTION_NAME=='edit') echo $space_info['ip']?>"></td>
            <td><input class="form-control" type="text" name="cp_url" placeholder="空间面板登录链接" value="<?php if(ACTION_NAME=='edit') echo $space_info['cp_url']?>"></td>
            <td><input class="form-control" type="text" name="account" placeholder="空间面板账号" value="<?php if(ACTION_NAME=='edit') echo $space_info['account']?>"></td>
            <td><input class="form-control" type="text" name="password" placeholder="空间面板密码" value="<?php if(ACTION_NAME=='edit') echo $space_info['password']?>"></td>
            <td><textarea class="form-control" name="remark"><?php if(ACTION_NAME=='edit') echo $space_info['remark']?></textarea></td>
            <td><input class="form-control" type="text" name="date_expired" placeholder="过期日期" value="<?php if(ACTION_NAME=='edit') echo $space_info['date_expired']?>"></td>
        </tr>
        <tr>
            <td colspan="6"><button class="btn btn-default pull-right" type="submit">保存</button></td>
        </tr>
    </table>	
</form>

<script>
$("input[name='date_expired']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
</script>
    