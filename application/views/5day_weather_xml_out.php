<weather postalCode='<?php echo $postalCode; ?>' weather_data_available='<?php echo $weather_data_loaded; ?>' executionTime='<?php echo $execution_time; ?> seconds'>
	<?php echo $query['theme']; ?>
<data>
			<today_text><![CDATA[<?php echo $query['today_text']; ?>]]></today_text>
			<today_label>CURRENTLY</today_label>
			<today_description><![CDATA[ <?php echo $query['today_description']; ?>]]></today_description>
			<today_temp> <?php echo $query['today_temp']; ?></today_temp>
			<today_img><?php echo $query['today_img']; ?></today_img>
			<firstday_text><![CDATA[<?php echo $query['firstday_text']; ?>]]></firstday_text>
			<firstday_label><?php echo strtoupper(date('l', strtotime('+1 day'))); ?></firstday_label>
			<firstday_description><![CDATA[ <?php echo $query['firstday_description']; ?>]]></firstday_description>
			<firstday_temp><?php echo $query['firstday_temp']; ?></firstday_temp>
			<firstday_img><?php echo $query['firstday_img']; ?></firstday_img>
			<secondday_text><![CDATA[<?php echo $query['secondday_text']; ?>]]></secondday_text>
			<secondday_label><?php echo strtoupper(date('l', strtotime('+2 day'))); ?></secondday_label>
			<secondday_description><![CDATA[ <?php echo $query['secondday_description']; ?>]]></secondday_description>
			<secondday_temp><?php echo $query['secondday_temp']; ?></secondday_temp>
			<secondday_img><?php echo $query['secondday_img']; ?></secondday_img>
			<thirdday_text><![CDATA[<?php echo $query['thirdday_text']; ?>]]></thirdday_text>
			<thirdday_label><?php echo strtoupper(date('l', strtotime('+3 day'))); ?></thirdday_label>
			<thirdday_description><![CDATA[ <?php echo $query['thirdday_description']; ?>]]></thirdday_description>			
			<thirdday_temp><?php echo $query['thirdday_temp']; ?></thirdday_temp>
			<thirdday_img><?php echo $query['thirdday_img']; ?></thirdday_img>
			<fourthday_text><![CDATA[<?php echo $query['fourthday_text']; ?>]]></fourthday_text>
			<fourthday_label><?php echo strtoupper(date('l', strtotime('+4 day'))); ?></fourthday_label>
			<fourthday_description><![CDATA[ <?php echo $query['fourthday_description']; ?>]]></fourthday_description>
			<fourthday_temp><?php echo $query['fourthday_temp']; ?></fourthday_temp>
			<fourthday_img><?php echo $query['fourthday_img']; ?></fourthday_img>

</data>
</weather>
