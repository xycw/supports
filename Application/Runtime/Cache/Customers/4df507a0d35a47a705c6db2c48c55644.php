<?php if (!defined('THINK_PATH')) exit(); echo R('Common/Html/html_start');?> 
<header id="header"><?php echo R('Common/Layout/menu');?></header>
<div class="container" id="content">
<h1>客户留言</h1>

<table class="table table-bordered">
    <tr>
        <th>编号</th>
        <th>网站</th>
        <th>姓名</th>
        <th>邮箱</th>
        <th>手机</th>
        <th>IP</th>
        <th>留言时间</th>
        <th>操作</th>
        <th>标记</th>
    </tr>
<?php
$i = $count-($page-1)*$num; foreach ($list as $entry){ ?>
    <tr<?php if($entry['state']!=1) echo ' class="bg-info"'?>>
        <td><?php echo $i--?></td>
        <td><?php echo $entry['site_name']?></td>
        <td><?php echo $entry['r_full_name']?></td>
        <td><?php echo $entry['r_email_address']?></td>
        <td><?php echo $entry['r_telephone']?></td>
        <td><?php echo $entry['r_ip']?></td>
        <td><?php echo $entry['r_send_time']?></td>
        <td><a data-toggle="modal" data-target="#view_dialog" class="btn btn-default btn-view" href="<?php echo U('Customers/Contact/view', array('site_id'=>$entry['site_id'], 'contact_id'=>$entry['contact_us_records_id']))?>">查看</a></td>
        <td>
            <?php
 if($entry['state']==1){ $title = '标记未读'; $link = U('Customers/Contact/mark', array('site_id'=>$entry['site_id'], 'contact_id'=>$entry['contact_us_records_id'], 'state'=>0)); }else{ $title = '标记已读'; $link = U('Customers/Contact/mark', array('site_id'=>$entry['site_id'], 'contact_id'=>$entry['contact_us_records_id'], 'state'=>1)); } ?>
            <button class="btn btn-default" id="btn-mark-readed" onclick="change_statu('<?php echo $link?>');"><?php echo $title?></button>
        </td>
    </tr>
<?php  } ?>
</table>

<div class="page-nav">
	<div class="row">
		<div class="col-lg-6">
			<div class="page-nav-info">留言总数:<?php echo $count?></div>
		</div>
		<div class="col-lg-6 right">
		<?php  W('Common/PageNavigation/page', array('page'=>$page,'num'=>$num,'count'=>$count, 'name'=>'Customers/Contact/list', $page_data)); ?>
		</div>
	</div>
</div>

<div class="modal fade" id="view_dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>

<script>
$.ajaxSetup({
    beforeSend: function () {
        layer.load(1);
    },
    complete: function () {
        layer.closeAll('loading');
    }
});    
$('.btn-view').click(function(){
    var link = $(this).attr('href');
    $.get(link, function(html){
        $('#view_dialog .modal-content').html(html);
        $('#view_dialog').modal('show');
    })
    
    return false;
});    
</script>    

<script>
// $('#btn-mark-readed').click(function(){
//     $.getJSON($(this).attr('href'), function(data){
//        if(data.state==1){ 
//            layer.msg('标记成功!');
//        }else{
//            layer.msg('标记失败!');
//        }
//     });
//     return false;
// });

function change_statu(obj){
    $.getJSON(obj, function(data){
       if(data.state==1){ 
           layer.msg('标记成功!');
       }else{
           layer.msg('标记失败!');
       }
    });
    window.location.reload();
    return false;
}
</script>
</div>
<footer id="footer"><?php echo R('Common/Layout/footer');?></footer>
<?php echo R('Common/Html/html_end');?>