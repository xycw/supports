<h3>
	推广团队列表
	<a class="btn btn-default" href="{:U('Domains/Promotion/add')}">添加团队</a>
</h3>
<table class="table table-bordered">
	<tbody>
		<tr>
			<th>部门名称</th>
			<th>订单归属</th>
			<th>操作</th>
		</tr>
		<?php foreach ($list as $v) {?>
		<tr style="border-top: 2px solid #000;">
			<td><b><?php echo $v['department_name'];?></b></td>
			<td><?php echo $v['order_ascription'];?></td>
			<td>
				<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/addMembers/department_id/' . $v['department_id']);?>">添加成员</a>
				<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/edit/department_id/' . $v['department_id']);?>">编辑</a>
				<?php if(empty($v['members'])){?>
				<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/del/department_id/' . $v['department_id']);?>" onclick='if(window.confirm("你确定要删除此部门吗?")){ return true;}else{ return false;}'>删除</a>
				<?php }?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th>姓名</th>
							<th>订单前缀</th>
							<th>业务员</th>
							<th>客服邮箱</th>
							<th>职务</th>
							<th>操作</th>
						</tr>
						<?php foreach ($v['members'] as $_v){?>
						<tr>
							<td><?php echo $_v['chinese_name'];?></td>
							<td><?php echo $_v['order_no_prefix'];?></td>
							<td><?php echo $_v['sales_name'];?></td>
							<td><?php echo $_v['customer_service_email'];?></td>
							<td><?php if($_v['leader'] == 1){?>组长<?php }else{?>组员<?php }?></td>
							<td>
								<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/editMembers/id/' . $_v['id']);?>">编辑</a>
								<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/transferSite/id/' . $_v['id']);?>">转移网站</a>
								<?php if(count($v['members']) == 1 || $_v['leader'] != 1){?>
								<a class="btn btn-default btn-block btn-xs" href="<?php echo U('Domains/Promotion/delMembers/id/' . $_v['id']);?>" onclick='if(window.confirm("你确定要删除此成员吗?")){ return true;}else{ return false;}'>删除</a>
								<?php }?>
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php }?>
	</tbody>
</table>