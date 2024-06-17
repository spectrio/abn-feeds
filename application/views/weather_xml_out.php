<weather postalCode='<?php echo $postalCode; ?>' weather_data_available='<?php echo $weather_data_loaded; ?>' executionTime='<?php echo $execution_time; ?> seconds'>
	<?php echo $query['theme']; ?>
<data>
			<today_label><?php echo $query['today_label']; ?></today_label>
			<today_temp> <?php echo $query['today_temp']; ?></today_temp>
			<today_img><?php echo $query['today_img']; ?></today_img>
			<tomorrow_label>Tomorrow</tomorrow_label>
			<tomorrow_temp><?php echo $query['tommorow_temp']; ?></tomorrow_temp>
			<tomorrow_img><?php echo $query['tommorow_img']; ?></tomorrow_img>
			<dayafter_label>Day After</dayafter_label>
			<dayafter_temp><?php echo $query['dayafter_temp']; ?></dayafter_temp>
			<dayafter_img><?php echo $query['dayafter_img']; ?></dayafter_img>
</data>
</weather>
