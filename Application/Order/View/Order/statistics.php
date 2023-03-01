<load href="__PUBLIC__/Js/morris.js/morris.css" />
<load href="__PUBLIC__/Js/morris.js/raphael-min.js" />
<load href="__PUBLIC__/Js/morris.js/morris.min.js" />
<script>
$(document).ready(function(){
	$("input[name^='month']").datetimepicker({format: 'yyyy-mm','autoclose': true,'startView':3,'minView':'year','language':'zh-CN'});
	new Morris.Line({
		  // ID of the element in which to draw the chart.
		  element: 'myfirstchart',
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: [
			<?php
			$total_num = 0;
			foreach($data as $entry){
				if($view_type=='d'){
					echo '{year:'.(strtotime($entry['date'])*1000).',value:'.$entry['num'].'},'."\n";
				}elseif($view_type=='w'){
					$weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
					echo '{year:\''.$weekday[$entry['date']].'\',value:'.$entry['num'].'},'."\n";
				}else{
					echo '{year:\''.$entry['date'].'\',value:'.$entry['num'].'},'."\n";
				}
				$total_num += $entry['num'];
			}
			?>
		  ],
		  // The name of the data record attribute that contains x-values.
		  xkey: 'year',
		  // A list of names of data record attributes that contain y-values.
		  ykeys: ['value'],
		  // Labels for the ykeys -- will be displayed when you hover over the
		  // chart.
		  pointSize:5,
		  labels: ['Value'],
		  <?php if($view_type=='d'){?>
		  xLabelFormat:function(x){
			  var weekday=new Array(7)
			  weekday[0]="星期日";
			  weekday[1]="星期一";
			  weekday[2]="星期二";
			  weekday[3]="星期三";
			  weekday[4]="星期四";
			  weekday[5]="星期五";
			  weekday[6]="星期六";
			  return weekday[x.getDay()]+"\\n"+(x.getMonth()+1)+'-'+x.getDate();
			},
		  <?php }else{
		  	?>
		  	parseTime:false,
		  <?php	
		  } ?>	
		});
});
</script>
<h1>订单销量统计</h1>
<form action="<?php echo U('Order/Order/statistics')?>" enctype="multipart/form-data" method="post">
<div class="row mb5">
	<div class="col-lg-1 right">开始时间:</div>
	<div class="col-lg-1"><input class="form-control"  type="text" name="month_start" value="<?php echo $month_start?>"></div>
	<div class="col-lg-1 right">结束时间:</div>
	<div class="col-lg-1"><input class="form-control"  type="text" name="month_end" value="<?php echo $month_end?>"></div>
	<div class="col-lg-1 right">国家:</div>
	<div class="col-lg-1"><input class="form-control"  type="text" name="country" value="<?php echo I('country','')?>"></div>
	<div class="col-lg-1 right">网站:</div>
	<div class="col-lg-2"><input class="form-control"  type="text" name="site_name" value="<?php echo I('site_name','')?>"></div>
	<div class="col-lg-1 right">视图:</div>
	<div class="col-lg-1">
	<tagLib name="html" />
	<html:select name="view_type" options="view_type_select" selected="view_type" style="form-control"/></div>
	<div class="col-lg-1"><button class="btn btn-default" type="submit">统计</button></div>
</div>	
</form>
<div id="myfirstchart" style="height: 250px;"></div>
<p><?php echo '订单总数:'.$total_num ?></p>
