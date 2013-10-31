<?php

include("common.php");

pg_prepare($dbconn, "sslv3_not_tls1", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND sslv3 = 't' AND tlsv1 = 'f');");

$res = pg_execute($dbconn, "sslv3_not_tls1", array());

$sslv3_not_tls1 = pg_fetch_all($res);

pg_prepare($dbconn, "dnssec_srv", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE results.srv_dnssec_good = 't' AND EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND priority IS NOT NULL);");

$res = pg_execute($dbconn, "dnssec_srv", array());

$dnssec_srv = pg_fetch_all($res);

pg_prepare($dbconn, "total", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't');");

$res = pg_execute($dbconn, "total", array());

$total = pg_fetch_assoc($res);

pg_prepare($dbconn, "sslv2", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv2 = 't');");

$res = pg_execute($dbconn, "sslv2", array());

$sslv2 = pg_fetch_assoc($res);

pg_prepare($dbconn, "sslv3", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv3 = 't');");

$res = pg_execute($dbconn, "sslv3", array());

$sslv3 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1 = 't');");

$res = pg_execute($dbconn, "tlsv1", array());

$tlsv1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_1", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1_1 = 't');");

$res = pg_execute($dbconn, "tlsv1_1", array());

$tlsv1_1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_2", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1_2 = 't');");

$res = pg_execute($dbconn, "tlsv1_2", array());

$tlsv1_2 = pg_fetch_assoc($res);

pg_prepare($dbconn, "bitsizes", "SELECT COUNT(*), rsa_bitsize FROM (SELECT DISTINCT ON (results.test_id, rsa_bitsize) rsa_bitsize FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results, srv_results, srv_certificates, certificates WHERE results.test_id = srv_results.test_id AND srv_certificates.srv_result_id = srv_results.srv_result_id AND chain_index = 0 AND certificates.certificate_id = srv_certificates.certificate_id) AS bitsizes GROUP BY rsa_bitsize ORDER BY rsa_bitsize;");

$res = pg_execute($dbconn, "bitsizes", array());

$bitsizes = pg_fetch_all($res);

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

		<table class="table table-bordered table-striped">
			<tr>
                <td>SSL 2</td>
                <td><?= (int)(100 * $sslv2["count"] / $total["count"]) ?>%</td>
				<td style="width: 50%;">
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $sslv2["count"] ?>" aria-valuemin="0" aria-valuemax="<?= $total["count"] ?>" style="width: <?= 100 * $sslv2["count"] / $total["count"] ?>%"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>SSL 3</td>
				<td><?= (int)(100 * $sslv3["count"] / $total["count"]) ?>%</td>
				<td>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $sslv3["count"] ?>" aria-valuemin="0" aria-valuemax="<?= $total["count"] ?>" style="width: <?= 100 * $sslv3["count"] / $total["count"] ?>%"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>TLS 1.0</td>
				<td><?= (int)(100 * $tlsv1["count"] / $total["count"]) ?>%</td>
				<td>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $tlsv1["count"] ?>" aria-valuemin="0" aria-valuemax="<?= $total["count"] ?>" style="width: <?= 100 * $tlsv1["count"] / $total["count"] ?>%"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>TLS 1.1</td>
				<td><?= (int)(100 * $tlsv1_1["count"] / $total["count"]) ?>%</td>
				<td>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $tlsv1_1["count"] ?>" aria-valuemin="0" aria-valuemax="<?= $total["count"] ?>" style="width: <?= 100 * $tlsv1_1["count"] / $total["count"] ?>%"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>TLS 1.2</td>
				<td><?= (int)(100 * $tlsv1_2["count"] / $total["count"]) ?>%</td>
				<td>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $tlsv1_2["count"] ?>" aria-valuemin="0" aria-valuemax="<?= $total["count"] ?>" style="width: <?= 100 * $tlsv1_2["count"] / $total["count"] ?>%"></div>
					</div>
				</td>
			</tr>
		</table>

		<h3>RSA key sizes for domain certificates</h3>

		<table class="table table-bordered table-striped">
			<tr>
				<th>RSA key size</th>
				<th>Count</th>
			</tr>
<?php
foreach ($bitsizes as $bitsize) {
?>
			<tr>
				<td><?= $bitsize["rsa_bitsize"] ?></td>
				<td><?= $bitsize["count"] ?></td>
			</tr>
<?php
}
?>
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
