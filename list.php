<?php

$dbconn = pg_connect("port=5433 host=localhost dbname=xmppoke user=xmppoke password=xmppoke") or die('Could not connect: ' . pg_last_error());

pg_prepare($dbconn, "list_results", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results ORDER BY test_date DESC LIMIT 200;");

pg_prepare($dbconn, "find_score", "SELECT DISTINCT ON (grade) grade, total_score FROM srv_results WHERE test_id = $1;");

$res = pg_execute($dbconn, "list_results", array());

$list = pg_fetch_all($res);

function color_label_text($score) {
	if ($score >= 80) {
		return "label-success";
	} else if ($score >= 40) {
		return "label-warning";
	}
	return "label-danger";
}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>XMPPoke results</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="css/bootstrap.css" rel="stylesheet" media="screen">

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<style type="text/css">
		body {
		    padding-top: 50px;
		}

		td .label {
			font-size: 90%;
		}
		</style>

		<link rel="shortcut icon" href="./ico/favicon.png">
	</head>

	<body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">XMPPoke</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Test results</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
<?php
if (!$list) {

?>
		<h1>404</h1>
		<div class="alert alert-block alert-error">
			Test results could not be found.
		</div>
<?php

} else {

?>

		<h1>Recent XMPP TLS reports</h1>

		<table class="table table-bordered table-striped">
			<tr>
				<th>Target</th>
				<th>Type</th>
				<th>Grade</th>
				<th>When</th>
			</tr>
<?php

foreach ($list as $result) {
	$res = pg_execute($dbconn, "find_score", array($result["test_id"]));

	$scores = pg_fetch_all($res);
?>
			<tr>
				<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a></td>
				<td><?= $result["type"] ?> to server</td>
<?php
	if (count($scores) > 1) {
?>
				<td><span class="muted">Multiple</span></td>
<?php
	} else {
?>
				<td><span class="<?= $scores[0]["grade"] === 'F' ? "label-danger" : color_label_text($scores[0]["total_score"]) ?> label"><?= $scores[0]["grade"] ?></span></td>
<?php
}
?>
				<td><time class="timeago" datetime="<?= date("c", strtotime($result["test_date"])) ?>"><?= date("c", strtotime($result["test_date"])) ?></time></td>
			</tr>
<?php
}
?>
		</table>
		
<?php } ?>

	</div> <!-- /container -->

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="./js/jquery.js"></script>
	<script src="./js/jquery.timeago.js"></script>
	<script src="./js/bootstrap.js"></script>

	<script src="./js/main.js"></script>

	</body>
</html>
