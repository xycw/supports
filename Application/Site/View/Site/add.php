<h1>添加网站</h1>

<h2>商城站</h2>

<form action="<?php echo U('Site/Site/add') ?>" method="post">

    <input type="hidden" name="type" value="1">

    <table class="table table-bordered">

        <tr>

            <th>域名</th>

            <th>订单前缀</th>

            <th>网站首页</th>

            <th>图片URL</th>

            <th>接口</th>

            <th>备注</th>

            <th>域名过期</th>
            <th>SSL过期11</th>

        </tr>

        <tr>

            <td><input class="form-control" type="text" name="site_name" placeholder="demo.com"></td>

            <td><input class="form-control" type="text" name="order_no_prefix" placeholder="请用纯英文字符"></td>

            <td><input class="form-control" type="text" name="site_index" placeholder="http://www.demo.com"></td>

            <td><input class="form-control" type="text" name="img_url" placeholder="http://www.demo.com/images/"></td>

            <td><input class="form-control" type="text" name="site_interface" placeholder="http://www.demo.com/interface-name/"></td>

            <td><textarea class="form-control" name="remark"></textarea></td>

            <td><input class="form-control" type="text" name="date_expired" placeholder="域名过期日期"></td>
            <td><input class="form-control" type="text" name="ssl_expired" placeholder="SSL过期日期11"></td>

        </tr>

        <tr>

            <td colspan="7"><button class="btn btn-default pull-right" type="submit">保存</button></td>

        </tr>

    </table>	

</form>





<script>

    $("input[name='date_expired']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});

</script>