<p>Hi <?php echo $order_info['customers_name']?>,</p>
<p>You have placed a order on <a href="<?php echo $order_info['site_index']?>" target="_blank"><?php echo $order_info['site_index']?></a>,and the order status is <?php echo $order_info['orders_status_name']?>.</p>
<?php 
foreach ($order_info['product'] as $entry){
?>
<p><a href="<?php echo $order_info['site_index']?>/index.php?main_page=product_info&products_id=<?php echo $entry['products_id']?>" target="_blank"><?php echo $entry['products_name']?></a></p>
<?php
}
?>

<p><?php echo nl2br($content);?></p>


<p><?php echo $order_info['site_index']?></p>