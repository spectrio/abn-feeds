<dtv>
<?php
	if (is_array($query)) {
		foreach ($query as $item) {
?>			
	<lightlogo><?=$item->lightlogo;?></lightlogo>
	<darklogo><?=$item->darklogo?></darklogo>
<?php } } ?>
</dtv>
