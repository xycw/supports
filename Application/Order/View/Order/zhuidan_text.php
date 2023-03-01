Hi <?php echo $order_info['customers_name']?>,
You have placed a order on <?php echo $order_info['site_index']?> ,

And the order status is <?php echo $order_info['orders_status_name']?>.

<?php 
foreach ($order_info['product'] as $entry){
?>
<?php echo $entry['products_name']?>		<?php echo $order_info['site_index']?>/index.php?main_page=product_info&products_id=<?php echo $entry['products_id']?>
<?php
}
?>
<?php echo $content;?>

<?php echo $order_info['site_index']?>