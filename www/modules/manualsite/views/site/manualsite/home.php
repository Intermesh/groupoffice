
	
<!--	<div class="jumbotron">
  <h1>Welcome!</h1>
	<?php echo $content->getHtml(); ?>
	</div>	-->
	<div class="row" style="padding: 15px;">
	<?php	
	
	$bgcolors = array('blue','green','orange','grey');
	$i=0;
	foreach($content->children as $child){
		
		$mod = $i % count($bgcolors);
		
		$color = $bgcolors[$mod];
		
		echo '<div class="col-md-4 tile '.$color.'">';
		echo '<a href="'.$child->getUrl().'">'.$child->title.'</a>';
		echo '<p>'.$child->shortText.'</p>';
		echo '</div>';
		
		$i++;
	}	
	?>
</div>
