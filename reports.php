<?php

include("common.php");

pg_prepare($dbconn, "sslv3", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND sslv3 = 't' AND tlsv1 = 'f' GROUP BY test_id) > 0;");

$res = pg_execute($dbconn, "sslv3", array());

$sslv3 = pg_fetch_all($res);

common_header();

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
				</ul>
			</div>
		</div>
	</div>

	<div class="container">

		<h1>Servers supporting SSL 3, but not TLS 1.0</h1>

		<table>
			<tr>
				<th>Target</th>
				<th>Type</th>
				<th>When</th>
			</tr>
<?php
foreach ($sslv3 as $result) {
?>
			<tr>
				<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a></td>
				<td><?= $result["type"] ?> to server</td>
				<td><time class="timeago" datetime="<?= date("c", strtotime($result["test_date"])) ?>"><?= date("c", strtotime($result["test_date"])) ?></time></td>
			</tr>
<?php
}
?>
		</table>
		
		<div class="footer">
			<p>Some rights reserved.</p>
		</div>
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
