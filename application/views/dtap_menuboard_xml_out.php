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
		<livelogo><?=dirname($item['mb_logo']).'/'.substr($item['channel'],4,5).'_ABN_LIVE_LOGO_WHITE.png';?></livelogo>	
<?php if (array_key_exists('mb_type',$item)) { ?>
		<type><?=$item['mb_type'];?></type>
<?php } ?>
<?php if (array_key_exists('mb_branding',$item)) { ?>
		<branding><?=$item['mb_branding'];?></branding>
<?php } ?>
<?php if (array_key_exists('mb_style',$item)) { ?>
		<style><?=$item['mb_style'];?></style>
<?php } ?>
<?php if (array_key_exists('mb_competitors',$item)) { ?>
		<competitors><?=$item['mb_competitors'];?></competitors>
<?php } ?>
<?php if (array_key_exists('mb_competitors_heading',$item)) { ?>
<?php   $item['mb_competitors_heading'] = json_decode($item['mb_competitors_heading'],true); ?>
<?php
    if (is_array($item['mb_competitors_heading'])) {
        if (count($item['mb_competitors_heading']) > 0) {
?>
		<compheaders>
<?php
    foreach ($item['mb_competitors_heading'] as $compHeader) {
        $compHeader = str_replace('&', 'and', $compHeader);
?>
			<compheader><label><?=$compHeader;?></label></compheader>
<?php 		} ?>
		</compheaders>
<?php 	} } ?>
<?php }?>
		<disclaimer><?=preg_replace('/[^A-Za-z0-9,\. -]/', '', str_replace("&", "and", $item['mb_disclaimer']));?></disclaimer>
		<disclaimercolor></disclaimercolor>
		<rows>
<?php 
	if (is_array($item['items'])) {
		$count = 1;
		foreach ($item['items'] as $row) {
?>
			<row>
				<count><?=$count;?></count>
				<?php
				    if (empty($row['row_service'])) {
				        $row['row_service'] = ' ';
				    }
				    if (is_array($row['row_service'])) {
				        $row['row_service'] = $row['row_service'][0];
				    }
				?>
				<service><?=$row['row_service'];?></service>
				<servicebadge>
					<?=$row['row_service_badge']?>
				</servicebadge>
				<price><?=$row['row_price'];?></price>
				<pricebadge>
					<?=$row['row_price_badge'];?>
				</pricebadge>
				<banner><?=$row['row_banner'];?></banner>
				<duration>7000</duration>
<?php if (array_key_exists('row_competitor_prices',$row)) { ?>
<?php   $row['row_competitor_prices'] = json_decode($row['row_competitor_prices'],true); ?>
<?php
        if (is_array($row['row_competitor_prices'])) {
            if (count($row['row_competitor_prices']) > 0) {
?>
				<compprices>
<?php 		foreach ($row['row_competitor_prices'] as $compPrice) { ?>
					<compprice><label><?=$compPrice;?></label></compprice>
<?php 		} ?>
				</compprices>
<?php 	} } ?>
<?php }?>

			</row>
<?php $count++; } } ?>
		</rows>
	</menuboard>
<?php } } ?>
</menuboards>
