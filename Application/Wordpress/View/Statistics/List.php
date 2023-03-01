<h1>网站列表</h1>

<form id="form-search" class="form-inline" action="<?php echo U('Wordpress/Statistics/List')?>" method="get">
    <div class="form-group">
        <label>网站</label>
        <tagLib name="html" />
        <html:select options="site" name="site_id" selected="site_selected" first="--网站--" style="form-control" />
    </div>
    <div class="form-group">
        <label>统计日期</label>
        <input class="form-control" type="text" name="date_start" value="<?php echo $date_start?>">
        <input class="form-control" type="text" name="date_end" value="<?php echo $date_end?>">
    </div>    
    <button type="submit" class="btn btn-default">筛选</button>
</form>    
<br>
<table class="table table-bordered">
    <tr>
        <th>编号</th>
        <th>网站</th>
        <th>IP数</th>
        <th>PV数</th>
        <th>备注</th>
        <th>日期</th>
    </tr>
<?php
$total_ip = 0;
$total_pv = 0;
foreach ($data_statistics as $entry){
    $total_ip += $entry['ip_count'];
    $total_pv += $entry['pv_count'];
?>
    <tr>
        <td><?php echo $entry['site_id']?></td>
        <td><?php echo $entry['site_name']?></td>
        <td><?php echo $entry['ip_count']?></td>
        <td><?php echo $entry['pv_count']?></td>
        <td><?php echo $entry['remark']?></td>
        <td><?php echo $entry['date']?></td>
    </tr>
<?php    
}
?>
    <tr>
        <th colspan="2" style="text-align:center;">合计:</th>
        <td><?php echo $total_ip ?></td>
        <td><?php echo $total_pv ?></td>
        <td colspan="2">&nbsp;</td>
    </tr>
</table>

<script>
$("input[name^='date']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
</script>