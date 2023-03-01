<p>Hi <?php echo $customers_name?></p>

<p>There is <?php echo $data_customers['num_basket_vaild']?> products in your shopping cart.</p>
<p>If you have any question , you can contact with us .</p>

<?php 
foreach ($data_customers['customers_basket'] as $basket_entry){
    if ($basket_entry['status']==0) continue;
?>
<p><a href="<?php echo $data_site['site_index']?>/index.php?main_page=product_info&products_id=<?php echo (int)$basket_entry['products_id']?>" target="_blank"><?php echo $basket_entry['products_name']?></a>------<?php echo $data_site['site_index']?>/index.php?main_page=product_info&amp;products_id=<?php echo (int)$basket_entry['products_id']?></p>
<?php
}
?>

<p>Wish you a happy shopping.</p>

<ul>
	<li>My Account:<?php echo $data_site['site_index']?>/index.php?main_page=account</li>
	<li>My Orders:<?php echo $data_site['site_index']?>/index.php?main_page=account_history</li>
</ul>

<p><?php echo $data_site['site_index']?></p>