<assets>

	<?php foreach ($query as $item):?>
		
		
	<?php $f = "True"; //$f = @( fopen($item, "r") ? 'True' : 'False' ); ?>
	
	<?php $l = ( substr($item, 0, 1) == 'D' ? 'Local' : 'Remote' ); ?>	
		
		<asset file_exists="<?=$f;?>" asset_location="<?=$l;?>">
		
			<url><?php echo $item;?></url>
	
		</asset>

	<?php endforeach;?>

</assets>