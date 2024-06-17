<dtv>
<?php
	if (is_array($query)) {
		foreach ($query as $item) {
?>			
	<id><?=$item->id;?></id>
	<channel><?=$item->channel;?></channel>
	<background><?=$item->dtv_background;?></background>
	<frame><?=$item->dtv_frame;?></frame>
	<chrome><?=$item->dtv_chrome_logo;?></chrome>
	<chromebackground><?=$item->dtv_chrome_background;?></chromebackground>
	<cube><?=$item->dtv_cube;?></cube>
<?php } } ?>
</dtv>
