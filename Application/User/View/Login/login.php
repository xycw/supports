{:R('Common/Html/html_start')} 

<div class="panel panel-default" id="login-panel">
  <div class="panel-heading">Zencart网站集群管理-用户登录</div>
  <div class="panel-body">
<form class="form-horizontal" action="<?php echo U('User/Login/login')?>" method="post">
  <div class="form-group">
    <label for="username" class="col-sm-3 control-label">用户名</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="username" id="username" placeholder="用户名">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-3 control-label">密码</label>
    <div class="col-sm-9">
      <input type="password" class="form-control" name="password" id="password" placeholder="密码">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <button type="submit" class="btn btn-default">登录</button>
    </div>
  </div>
</form>
  </div>
</div>

{:R('Common/Html/html_end')}