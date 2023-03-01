Hi <?php echo $customers_name?>


There is <?php echo  $data_customers['num_basket_vaild'] ?> products in your shopping cart.
If you have any question , you can contact with us .

<?php 
foreach ($data_customers['customers_basket'] as $basket_entry){
    if ($basket_entry['status']==0) continue;    
?>
<?php echo $basket_entry['products_name']?>		<?php echo $data_site['site_index']?>/index.php?main_page=product_info&products_id=<?php echo (int)$basket_entry['products_id']?>
<?php
}
?>


Wish you a happy shopping.


My Account:<?php echo $data_site['site_index']?>/index.php?main_page=account


My Orders:<?php echo $data_site['site_index']?>/index.php?main_page=account_history


<?php echo $data_site['site_index']?>