<?php if (!$table_only) { ?>
<html>
<head>
	<meta charset="utf-8">
	<title>ABN Scala Feeder - PTTV Logs</title>

	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<meta name="description" content="Splash Page" />
	<meta name="keywords" content="coming soon, splash page, css3, animation, effect, web design" />
	<meta name="author" content="ABN" />
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.6.0/bootstrap-table.min.css">
	
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JQuery -->
	<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
	
	<!-- Latest compiled and minified Bootstrap JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.6.0/bootstrap-table.min.js"></script>

	
	<!-- <link rel="stylesheet" type="text/css" href="/splash/css/abn.css" />
	<link rel="stylesheet" type="text/css" href="/splash/css/style.css" /> -->
	<!--[if lt IE 10]>
		<link rel="stylesheet" type="text/css" href="css/style2IE.css" />
	<![endif]-->
</head>
<body>
<br /><br />
<div style="width: 25%; margin-left: auto; margin-right: auto; text-align: center;">
	<button class="btn btn-primary btn-lg" onClick="location.reload(true);"><strong>Refresh</strong> Page</button>
</div>
<br /><br />
<?php } ?>
<?php if ($table_only) { ?>
<table id="myTable" class="display" cellspacing="0" width="100%">
	<thead style="background-color: #383838; color: #ffffff;">
<?php } else { ?>
<table style="width: 75%; margin-left: auto; margin-right: auto;" data-toggle="table" data-sort-name="id" data-sort-order="desc">
	<thead>
<?php } ?>
		<tr>
			<th data-field="id" data-sortable="true">ID of Request</th>
			<th data-field="location_id" data-sortable="true">Location ID</th>
			<th data-field="start" data-sortable="true">Start Time</th>
			<th data-field="end" data-sortable="true">End Time</th>
			<th data-field="requested" data-sortable="true">Requested</th>
			<th data-field="remote_ip" data-sortable="true">IP Address</th>
			<th data-field="timestamp" data-sortable="true">Portal Set Timestamp</th>
			<th data-field="time_modified" data-sortable="true">Client Pickup Timestamp</th>
		</tr>
	</thead>
	<tbody id="table-pttv-logs">
	<?php foreach ($log_entries as $entry) { ?>
		<tr>
			<td><?=$entry["id"];?></td>
			<td><?=$entry["location_id"];?></td>
			<td><?=(is_null($entry["start"]) ? '<button class="btn btn-primary btn-xs">Stop</button>' : $entry["start"]); ?></td>
			<td><?=(is_null($entry["end"]) ? '<button class="btn btn-primary btn-xs">Stop</button>' : $entry["end"]); ?></td>
			<td><?=($entry["requested"] == 0) ? '<button class="btn btn-danger btn-xs">Not Requested</button>' : '<button class="btn btn-success btn-xs">Requested</button>' ?></td>
			<td><?=$entry["remote_ip"];?></td>
			<td><?=$entry["timestamp"];?></td>
			<td><?=$entry["time_modified"];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php if (!$table_only) { ?>
</body>
</html>
</body>
<?php } ?>
</html>