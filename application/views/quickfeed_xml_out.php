<quickfeed requestip="<?=$remote_ip;?>" notifieschecked="<?=$notifies_checked;?>">
<?php
	if (is_array($pttv)) {
		foreach ($pttv as $item) {
			if (is_null($item->start) || is_null($item->end)) {
				$item->start = "stop";
				$item->end = "stop";
?>
	<item>
		<type>tv</type>
		<start><?php echo $item->start;?></start>
		<end><?php echo $item->end;?></end>
	</item>
<?php
			} else {
?>
	<item>
		<type>tv</type>
		<start><![CDATA[ <?php echo $item->start;?> ]]></start>
		<end><![CDATA[ <?php echo $item->end;?> ]]></end>
	</item>
<?php
			}
		}
	}
	if (is_array($notifications)) {
		foreach ($notifications as $item) {
?>
	<item>
		<type>alert</type>
		<timestamp><![CDATA[ <?php echo $item->entry_datetime;?> ]]></timestamp>
		<display><![CDATA[ <?php echo $item->content ?> ]]></display>
	</item>
<?php
		}
	}
	//$refreshchannel = array("true");
	if (is_array($refreshchannel)) {
		foreach ($refreshchannel as $item) {
?>
	<item>
		<type>update</type>
	</item>
<?php
		}
	} else {
		if ($refreshchannel) {
?>
	<item>
		<type>update</type>
	</item>
<?php
		}
	} 
?>
</quickfeed>
