<?php

include("common.php");

pg_prepare($dbconn, "list_results", "SELECT * FROM test_results ORDER BY test_date DESC LIMIT 200;");

pg_prepare($dbconn, "find_score", "SELECT grade, total_score, certificate_score, done, error, warn_rc4_tls11, warn_no_fs FROM srv_results WHERE test_id = $1;");

$res = pg_execute($dbconn, "list_results", array());

$list = pg_fetch_all($res);

common_header(NULL);

?>
	<body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">IM Observatory</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
					<li><a href="about.php">About</a></li>
					<li><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
<?php
if (!$list) {

?>
		<h1>Not found</h1>
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
	$final_score = NULL;

	foreach ($scores as $score) {
		if (grade($score) && (!$final_score || grade($score) < $final_score)) {
			$final_score = grade($score);
		}
	}
?>
				<td><span class="<?= color_label_text_grade($final_score) ?> label"><?= $final_score === NULL ? "?" : $final_score ?></span><?= count($scores) > 1 ? "*" : "" ?></td>
				<td><time class="timeago" datetime="<?= date("c", strtotime($result["test_date"])) ?>"><?= date("c", strtotime($result["test_date"])) ?></time></td>
			</tr>
<?php
}
?>
		</table>
		
<?php } ?>
		
		<div class="footer">
			<p>Some rights reserved.</p>
		</div>
	</div> <!-- /container -->

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="./js/jquery.js"></script>
	<script src="./js/jquery.timeago.js"></script>
	<script src="./js/bootstrap.min.js"></script>

	<script src="./js/main.js"></script>

	</body>
</html>
