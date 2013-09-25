<?php

$dbconn = pg_connect("port=5433 host=localhost dbname=xmppoke user=xmppoke password=xmppoke") or die('Could not connect: ' . pg_last_error());

pg_prepare($dbconn, "list_results", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results ORDER BY test_date DESC LIMIT 200;");

pg_prepare($dbconn, "find_score", "SELECT DISTINCT ON (grade) grade, total_score FROM srv_results WHERE test_id = $1;");

$res = pg_execute($dbconn, "list_results", array());

$list = pg_fetch_all($res);

function color_badge_text($score) {
	if ($score >= 80) {
		return " badge-success";
	} else if ($score >= 40) {
		return " badge-warning";
	}
	return " badge-important";
}

?><!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<title>XMPPoke results</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link href="./css/bootstrap.css" rel="stylesheet">
	<style>
		body {
		padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
		}
	</style>
	<link href="./css/bootstrap-responsive.css" rel="stylesheet">

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="./js/html5shiv.js"></script>
	<![endif]-->

	<!-- Fav and touch icons -->
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="./ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="./ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="./ico/apple-touch-icon-72-precomposed.png">
					<link rel="apple-touch-icon-precomposed" href="./ico/apple-touch-icon-57-precomposed.png">
									 <link rel="shortcut icon" href="./ico/favicon.png">
	</head>

	<body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
		<div class="container">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
			<a class="brand" href="#">XMPPoke</a>
			<div class="nav-collapse collapse">
			<ul class="nav">
				<li><a href="#">Test results</a></li>
				<li class="active"><a href="#">Recent tests</a></li>
			</ul>
			</div><!--/.nav-collapse -->
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
				<th>Score</th>
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
				<td><span class="<?= $scores[0]["grade"] === 'F' ? "badge-important" : color_badge_text($scores[0]["total_score"]) ?> badge"><?= $scores[0]["grade"] ?></span></td>
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
