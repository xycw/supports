<nav>
  <ul class="pagination">
  <?php 
  foreach ($p as $entry){
  ?>
  <li<?php if ($entry['active']) {echo ' class="active"';}?>><a href="<?php echo $entry['url']?>"><?php echo $entry['text']?></a></li>
  <?php	
  }
  ?>
  </ul>
</nav>