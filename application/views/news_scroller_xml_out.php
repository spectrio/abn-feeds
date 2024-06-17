<scroller uniqueDisplayTotal='<?php echo count($query); ?>' cacheone='<?=$cacheone;?>' cachetwo='<?=$cachetwo;?>' executionTime='<?php echo $execution_time; ?> seconds'>
<?php
	if (is_array($query)) {
		foreach ($query as $item) {
?>			
	<item>
		<story stringLength='<?php echo strlen($item["story"]); ?>'><![CDATA[<?php echo $item["story"]; ?>]]></story>
		<background><?php echo strip_tags($item["image"]); ?></background>
		<icon><?php echo $item["icon"]; ?></icon>
	</item>
<?php } } ?>
</scroller>
