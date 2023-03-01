<h1>空间列表</h1>
<br>
<a class="btn btn-default" href="<?php echo U('Site/Space/add')?>">添加空间</a>
<br><br>
<table class="table table-bordered">
    <tr>
        <th width="50px">空间编号</th>
        <th width="120px">空间IP</th>
        <th width="120px">账号</th>
        <th width="120px">密码</th>
        <th width="100px">过期日期</th>
        <th>备注</th>
        <th>数据库</th>
        <th>绑定域名</th>
    </tr>
<?php 
foreach ($list as $entry){
?>
    <tr id="space-item<?php echo $entry['space_id']?>">
        <td><?php echo $entry['space_id']?></td>
        <td id="ip<?php echo $entry['space_id']?>"><a href="<?php echo U('Site/Space/edit/space_id/'.$entry['space_id'])?>"><?php echo $entry['ip']?></a></td>
        <td><a href="<?php echo $entry['cp_url']?>" target="_blank"><?php echo $entry['account']?></a></td>
        <td><?php echo $entry['password']?></td>
        <td<?php if($entry['days_expired']<30) echo ' style="background-color:red;color:#fff;"';?>><?php echo $entry['date_expired'].'<br>('.$entry['days_expired'].')';?></td>
        <td><?php echo $entry['remark']?></td>
        <td>
           <div class="hidden" id="table-database<?php echo $entry['space_id']?>">
        <?php 
        if(sizeof($entry['db'])>0){
        ?>
            
            <table class="table table-bordered">
            <?php
            foreach ($entry['db'] as $db_entry){
            ?>
                <tr>
                    <td><?php echo $db_entry['space_db_id']?></td>
                    <td><?php echo $db_entry['space_db_database']?></td>
                    <td><?php echo $db_entry['space_db_username']?></td>
                    <td><?php echo $db_entry['space_db_password']?></td>
                    <td><a href="<?php echo U('Site/Space/DelDb/space_db_id/'.$db_entry['space_db_id']) ?>">删除</a></td>
                </tr>    
            <?php    
            }
            ?>
            </table>    
            
        <?php    
        }else{
            echo '<p>无对应的数据库信息!</p>';
        }
        ?>
               </div>
            <a  data-toggle="modal" data-target="#database-viewer" href="javascript:void(0);" id="btn-datavase-viewer<?php echo $entry['space_id']?>">查看(<?php echo sizeof($entry['db'])?>)</a>
            <a href="<?php echo U('Site/Space/AddDb/space_id/'.$entry['space_id'])?>">添加</a>
        </td>
        <td>
        <?php 
        if(sizeof($entry['site'])>0){
        ?>  
            <ol>
            <?php
            foreach ($entry['site'] as $site_entry){
            ?>
                <li><?php echo $site_entry['site_name']?><a href="<?php echo U('Site/Space/UnbindSite/site_id/'.$site_entry['site_id'])?>">解除</a></li>
            <?php    
            }
            ?>        
            </ol>    
        <?php
        }
        ?>
            <a href="<?php echo U('Site/Space/BindSite/space_id/'.$entry['space_id'])?>">绑定域名</a>
        </td>
    </tr>
<?php
}
?>	
</table>

<div class="modal fade" id="database-viewer" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>

<script>
$('#database-viewer').on('show.bs.modal', function (e) {
  var button = $(e.relatedTarget);
  var id = button.attr('id');
  id = id.match(/\d+$/)[0];
  var ip = $('#ip'+id).text();
  var db = $('#table-database'+id).html();
  $('.modal-title', '#database-viewer').text('空间'+ip+'数据库');
  $('.modal-body', '#database-viewer').html(db);
      
    
});
</script>