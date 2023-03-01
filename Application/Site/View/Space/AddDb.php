<h1>添加数据库</h1>
<form action="<?php echo U('Site/Space/AddDb') ?>" method="post">
    <input type="hidden" name="space_id" value="<?php echo $space_id ?>">
    <table class="table table-bordered">
        <tr>
            <th>数据库名</th>
            <th>数据库账号</th>
            <th>数据库密码</th>
            <th>域名</th>
        </tr>
        <tr>
            <td><input class="form-control" type="text" name="database" placeholder="数据库名"></td>
            <td><input class="form-control" type="text" name="username" placeholder="数据库账号"></td>
            <td><input class="form-control" type="text" name="password" placeholder="数据库密码"></td>
            <td>
                <tagLib name="html" />
		<html:select options="option_site" name="site_id" first="--域名--" style="form-control" />
            </td>
        </tr>
        <tr>
            <td colspan="6"><button class="btn btn-default pull-right" type="submit">保存</button></td>
        </tr>
    </table>	
</form>

<script>
$("input[name='date_expired']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
</script>
    