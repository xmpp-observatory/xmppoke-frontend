<?php

include("common.php");

pg_prepare($dbconn, "sslv3_not_tls1", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND sslv3 = 't' AND tlsv1 = 'f' GROUP BY test_id) > 0;");

$res = pg_execute($dbconn, "sslv3_not_tls1", array());

$sslv3_not_tls1 = pg_fetch_all($res);

pg_prepare($dbconn, "dnssec_srv", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE results.srv_dnssec_good = 't' AND (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND priority IS NOT NULL GROUP BY test_id) > 0;");

$res = pg_execute($dbconn, "dnssec_srv", array());

$dnssec_srv = pg_fetch_all($res);

pg_prepare($dbconn, "total", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND done = 't' GROUP BY test_id) > 0;"

$res = pg_execute($dbconn, "total", array());

$total = pg_fetch_assoc($res)[0];

pg_prepare($dbconn, "sslv2", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv2 = 't' GROUP BY test_id) > 0;"

$res = pg_execute($dbconn, "sslv2", array());

$sslv2 = pg_fetch_assoc($res)[0];

pg_prepare($dbconn, "sslv3", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE (SELECT COUNT(*) FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv2 = 't' GROUP BY test_id) > 0;"

$res = pg_execute($dbconn, "sslv3", array());

$sslv3 = pg_fetch_assoc($res)[0];

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
					<li><a href="list.php">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
					<li><a href="about.php">About</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">

		<h1>Various reports of all servers tested</h1>

		<h3>TLS versions</h3>

		<table>
			<tr>
				<td>SSL 2</td>
				<td><?= $sslv2 / $total ?></td>
			</tr>
			<tr>
				<td>SSL 3</td>
				<td><?= $sslv3 / $total ?></td>
			</tr>
		</table>

		<h3>Servers supporting SSL 3, but not TLS 1.0</h3>

		<table class="table table-bordered table-striped">
			<tr>
				<th>Target</th>
				<th>Type</th>
				<th>When</th>
			</tr>
<?php
foreach ($sslv3_not_tls1 as $result) {
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

		<h3>Servers with DNSSEC signed SRV records</h3>

		<table class="table table-bordered table-striped">
			<tr>
				<th>Target</th>
				<th>Type</th>
				<th>When</th>
			</tr>
<?php
foreach ($dnssec_srv as $result) {
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
