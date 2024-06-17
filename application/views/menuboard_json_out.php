<menuboards>
<?php
	if (is_array($query)) {
		foreach ($query as $item) {
?>			
	<menuboard>
		<id><?=$item['id'];?></id>
		<channel><?=$item['channel'];?></channel>
		<background><?=$item['mb_background'];?></background>
		<frame><?=$item['mb_theme'];?></frame>
		<logo><?=$item['mb_logo'];?></logo>
		<disclaimer><?=$item['mb_disclaimer'];?></disclaimer>
		<rows>
<?php 
	if (is_array($item['items'])) {
		$count = 1;
		$patterns = array(
				'/&/' => 'and',  // Ampersand to "and"
				'/[^\da-z\s]/i' => ''     // Anything *but* a character to blank space
		);
		foreach ($item['items'] as $row) {
?>
			<row>
				<count><?=$count;?></count>
				<service><?=preg_replace(array_keys($patterns), array_values($patterns), $row['row_service']);?></service>
				<servicebadge><?=$row['row_service_badge']?></servicebadge>
				<price><?=$row['row_price'];?></price>
				<pricebadge><?=$row['row_price_badge'];?></pricebadge>
				<banner><?=$row['row_banner'];?></banner>
				<duration>7000</duration>
			</row>
<?php $count++; } } ?>
		</rows>
	</menuboard>
<?php } } ?>
</menuboards>
