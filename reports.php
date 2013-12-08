<?php

include("common.php");

// Update in the year 3013
$since = 365 * 1000;

if (isset($_GET["since"])) {
	$since = intval($_GET["since"]);
}

$since = $since * 24 * 60 * 60;

pg_prepare($dbconn, "sslv3_not_tls1", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND sslv3 = 't' AND tlsv1 = 'f');");

$res = pg_execute($dbconn, "sslv3_not_tls1", array($since));

$sslv3_not_tls1 = pg_fetch_all($res);

pg_prepare($dbconn, "dnssec_srv", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE results.srv_dnssec_good = 't' AND EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND priority IS NOT NULL);");

$res = pg_execute($dbconn, "dnssec_srv", array($since));

$dnssec_srv = pg_fetch_all($res);



pg_prepare($dbconn, "total", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't');");

$res = pg_execute($dbconn, "total", array($since));

$total = pg_fetch_assoc($res);

pg_prepare($dbconn, "sslv2", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv2 = 't');");

$res = pg_execute($dbconn, "sslv2", array($since));

$sslv2 = pg_fetch_all($res);

pg_prepare($dbconn, "sslv3", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND sslv3 = 't');");

$res = pg_execute($dbconn, "sslv3", array($since));

$sslv3 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1 = 't');");

$res = pg_execute($dbconn, "tlsv1", array($since));

$tlsv1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_1", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1_1 = 't');");

$res = pg_execute($dbconn, "tlsv1_1", array($since));

$tlsv1_1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_2", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND tlsv1_2 = 't');");

$res = pg_execute($dbconn, "tlsv1_2", array($since));

$tlsv1_2 = pg_fetch_assoc($res);



pg_prepare($dbconn, "bitsizes", "SELECT COUNT(*), rsa_bitsize FROM (SELECT DISTINCT ON (results.test_id, rsa_bitsize) rsa_bitsize FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results, srv_results, srv_certificates, certificates WHERE results.test_id = srv_results.test_id AND srv_certificates.srv_result_id = srv_results.srv_result_id AND chain_index = 0 AND certificates.certificate_id = srv_certificates.certificate_id) AS bitsizes GROUP BY rsa_bitsize ORDER BY rsa_bitsize;");

$res = pg_execute($dbconn, "bitsizes", array($since));

$bitsizes = pg_fetch_all($res);



pg_prepare($dbconn, "c2s_starttls_allowed", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 AND type = 'client' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE requires_starttls = 'f' AND done = 't' AND test_id = results.test_id);");

$res = pg_execute($dbconn, "c2s_starttls_allowed", array($since));

$c2s_starttls_allowed = pg_fetch_assoc($res);

pg_prepare($dbconn, "c2s_starttls_required", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 AND type = 'client' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE requires_starttls = 't' AND done = 't' AND test_id = results.test_id);");

$res = pg_execute($dbconn, "c2s_starttls_required", array($since));

$c2s_starttls_required = pg_fetch_assoc($res);

pg_prepare($dbconn, "s2s_starttls_allowed", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 AND type = 'server' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE requires_starttls = 'f' AND done = 't' AND test_id = results.test_id);");

$res = pg_execute($dbconn, "s2s_starttls_allowed", array($since));

$s2s_starttls_allowed = pg_fetch_assoc($res);

pg_prepare($dbconn, "s2s_starttls_required", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 AND type = 'server' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE requires_starttls = 't' AND done = 't' AND test_id = results.test_id);");

$res = pg_execute($dbconn, "s2s_starttls_required", array($since));

$s2s_starttls_required = pg_fetch_assoc($res);



pg_prepare($dbconn, "trusted_valid", "SELECT COUNT(*), trusted, valid_identity FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results, srv_results WHERE done = 't' AND results.test_id = srv_results.test_id GROUP BY trusted, valid_identity ORDER BY trusted, valid_identity;");

$res = pg_execute($dbconn, "trusted_valid", array($since));

$trusted_valid = pg_fetch_all($res);



pg_prepare($dbconn, "score_A", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score >= '80');");

$res = pg_execute($dbconn, "score_A", array($since));

$score_A = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_B", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score < '80' AND total_score >= '65');");

$res = pg_execute($dbconn, "score_B", array($since));

$score_B = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_C", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score < '65' AND total_score >= '50');");

$res = pg_execute($dbconn, "score_C", array($since));

$score_C = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_D", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score < '50' AND total_score >= '35');");

$res = pg_execute($dbconn, "score_D", array($since));

$score_D = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_E", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score < '35' AND total_score >= '20');");

$res = pg_execute($dbconn, "score_E", array($since));

$score_E = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_F", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND total_score < '20');");

$res = pg_execute($dbconn, "score_F", array($since));

$score_F = pg_fetch_assoc($res);


pg_prepare($dbconn, "reorders_ciphers", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE extract(epoch from age(now(), test_date)) < $1 ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND done = 't' AND reorders_ciphers = 't');");

$res = pg_execute($dbconn, "reorders_ciphers", array($since));

$reorders_ciphers = pg_fetch_all($res);


pg_prepare($dbconn, "shares_private_keys", "select distinct on (results.server_name, results.type, results.test_id, subject_key_info_sha256) results.server_name, results.type, results.test_id, subject_key_info_sha256 from (select distinct on (server_name, type) * from test_results WHERE extract(epoch from age(now(), test_date)) < $1 order by server_name, type, test_date desc) as results, srv_results, srv_certificates, certificates where chain_index = 0 and srv_certificates.certificate_id = certificates.certificate_id and srv_results.srv_result_id = srv_certificates.srv_result_id and srv_results.test_id = results.test_id and certificates.subject_key_info_sha256 in (select subject_key_info_sha256 from (select distinct on (server_name) * from test_results order by server_name, test_date desc) as results, (select distinct on (srv_results.test_id, certificates.certificate_id) * from srv_results, srv_certificates, certificates where chain_index = 0 and srv_certificates.certificate_id = certificates.certificate_id and srv_results.srv_result_id = srv_certificates.srv_result_id) as certificates where certificates.test_id = results.test_id group by subject_key_info_sha256 having count(*) > 1) order by subject_key_info_sha256, server_name, type;");

$res = pg_execute($dbconn, "shares_private_keys", array($since));

$shares_private_keys = pg_fetch_all($res);


common_header();

?>
	<body data-spy="scroll" data-target="#sidebar">

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
					<li class="active"><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">

			<div class="col-md-3">
				<div id="sidebar" class="side-bar" role="complementary">
					<ul class="nav">
						<li class="active"><a href="#tls">TLS versions</a></li>
						<li><a href="#grades">Grades</a></li>
						<li><a href="#rsa">RSA key sizes for domain certificates</a></li>
						<li><a href="#starttls">StartTLS</a></li>
						<li><a href="#trust">Trust</a></li>
						<li><a href="#sslv3butnottls1">Servers supporting SSL 3, but not TLS 1.0</a></li>
						<li><a href="#sslv2wallofshame">Servers supporting SSL 2</a></li>
						<li><a href="#dnssecsrv">Servers with DNSSEC signed SRV records</a></li>
						<li><a href="#reordersciphers">Servers that pick their own cipher order</a></li>
						<li><a href="#sharesprivatekeys">Servers sharing private keys</a></li>
					</ul>
				</div>
			</div>

			<div class="col-md-9">

				<h1>Various reports of all servers tested</h1>

				<a href="report_2013_11.php">Report for november 2013</a> | <a href="reports.php?since=1">Results of the last day</a> | <a href="reports.php?since=7">Results of the last week</a> | <a href="reports.php?since=30">Results of the last month</a>

				<div class="row">
					<div class="col-md-6">

						<h3 id="tls">TLS versions <small class="text-muted"><?= $total["count"] ?> results</small></h3>

						<table class="table table-bordered table-striped">
							<tr>
								<td>SSL 2</td>
								<td><?= count($sslv2) ?> <span class="text-muted"><?= round(100 * count($sslv2) / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<td>SSL 3</td>
								<td><?= $sslv3["count"] ?> <span class="text-muted"><?= round(100 * $sslv3["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<td>TLS 1.0</td>
								<td><?= $tlsv1["count"] ?> <span class="text-muted"><?= round(100 * $tlsv1["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<td>TLS 1.1</td>
								<td><?= $tlsv1_1["count"] ?> <span class="text-muted"><?= round(100 * $tlsv1_1["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<td>TLS 1.2</td>
								<td><?= $tlsv1_2["count"] ?> <span class="text-muted"><?= round(100 * $tlsv1_2["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
						</table>
					</div>

					<div class="col-md-6">
						<div id="chart1" style="width: 500px; height: 300px;"></div>
					</div>
				</div>

				<h3 id="grades">Grades <small class="text-muted"><?= $total["count"] ?> results</small></h3>

				<div class="row">
					<div class="col-md-6">
						<table class="table table-bordered table-striped">
							<tr>
								<th>A</th>
								<td><?= $score_A["count"] ?> <span class="text-muted"><?= round(100 * $score_A["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<th>B</th>
								<td><?= $score_B["count"] ?> <span class="text-muted"><?= round(100 * $score_B["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<th>C</th>
								<td><?= $score_C["count"] ?> <span class="text-muted"><?= round(100 * $score_C["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<th>D</th>
								<td><?= $score_D["count"] ?> <span class="text-muted"><?= round(100 * $score_D["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<th>E</th>
								<td><?= $score_E["count"] ?> <span class="text-muted"><?= round(100 * $score_E["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
							<tr>
								<th>F</th>
								<td><?= $score_F["count"] ?> <span class="text-muted"><?= round(100 * $score_F["count"] / $total["count"], 1) ?>%</span></td>
							</tr>
						</table>
					</div>

					<div class="col-md-6">
						<div id="chart2" style="width: 500px; height: 300px;"></div>
					</div>
				</div>

				<span class="text-muted">Does not penalize untrusted certificates or SSLv2 support.</span>

				<h3 id="rsa">RSA key sizes for domain certificates</h3>

				<div class="row">
					<div class="col-md-6">
						<table class="table table-bordered table-striped">
							<tr>
								<th>RSA key size</th>
								<th>Count</th>
							</tr>
<?php
$rsa_sum = 0;

foreach ($bitsizes as $bitsize) {
		$rsa_sum += $bitsize["count"];
}

foreach ($bitsizes as $bitsize) {
?>
							<tr>
								<td><?= $bitsize["rsa_bitsize"] ?></td>
								<td><?= $bitsize["count"] ?> <span class="text-muted"><?= round(100 * $bitsize["count"] / $rsa_sum) ?>%</span></td>
							</tr>
<?php
}
?>
						</table>
					</div>

					<div class="col-md-6">
						<div id="chart3" style="width: 500px; height: 300px;"></div>
					</div>
				</div>

				<h3 id="starttls">StartTLS</h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Type</th>
						<th>Required</th>
						<th>Allowed</th>
					</tr>
					<tr>
						<td>Client to server</td>
						<td><?= $c2s_starttls_required["count"] ?> <span class="text-muted"><?= round(100 * $c2s_starttls_required["count"] / ($c2s_starttls_required["count"] + $c2s_starttls_allowed["count"])) ?>%</span></td>
						<td><?= $c2s_starttls_allowed["count"] ?> <span class="text-muted"><?= round(100 * $c2s_starttls_allowed["count"] / ($c2s_starttls_required["count"] + $c2s_starttls_allowed["count"])) ?>%</span></td>
					</tr>
					<tr>
						<td>Server to server</td>
						<td><?= $s2s_starttls_required["count"] ?> <span class="text-muted"><?= round(100 * $s2s_starttls_required["count"] / ($s2s_starttls_required["count"] + $s2s_starttls_allowed["count"])) ?>%</span></td>
						<td><?= $s2s_starttls_allowed["count"] ?> <span class="text-muted"><?= round(100 * $s2s_starttls_allowed["count"] / ($s2s_starttls_required["count"] + $s2s_starttls_allowed["count"])) ?>%</span></td>
					</tr>
				</table>

				<h3 id="trust">Trust</h3>

<?php

$sum = $trusted_valid[0]["count"] + $trusted_valid[1]["count"] + $trusted_valid[2]["count"] + $trusted_valid[3]["count"];

?>

				<table class="table table-bordered table-striped">
					<tr>
						<th></th>
						<th>Trusted</th>
						<th>Untrusted</th>
					</tr>
					<tr>
						<th>Valid</td>
						<td><?= $trusted_valid[3]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[3]["count"] / $sum) ?>%</span></td>
						<td><?= $trusted_valid[1]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[1]["count"] / $sum) ?>%</span></td>
					</tr>
					<tr>
						<th>Invalid</td>
						<td><?= $trusted_valid[2]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[2]["count"] / $sum) ?>%</span></td>
						<td><?= $trusted_valid[0]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[0]["count"] / $sum) ?>%</span></td>
					</tr>
				</table>

				<h3 id="sslv3butnottls1">Servers supporting SSL 3, but not TLS 1.0 <small class="text-muted"><?= count($sslv3_not_tls1) ?> results</small></h3>

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

				<h3 id="sslv2wallofshame">Servers supporting SSL 2 <small class="text-muted"><?= count($sslv2) ?> results</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
					</tr>
<?php
foreach ($sslv2 as $result) {
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

				<h3 id="dnssecsrv">Servers with DNSSEC signed SRV records <small class="text-muted"><?= count($dnssec_srv) ?> results</small></h3>

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

				<h3 id="reordersciphers">Servers that pick their own cipher order <small class="text-muted"><?= count($reorders_ciphers) ?> results</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
					</tr>
<?php
foreach ($reorders_ciphers as $result) {
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

				<h3 id="sharesprivatekeys">Servers sharing private keys <small class="text-muted"><?= count($shares_private_keys) ?> results</small></h3>

				<table class="table table-bordered">
					<tr>
						<th>Target</th>
						<th>SHA256(SPKI)</th>
					</tr>
<?php
$i = 0;
$prev = NULL;
foreach ($shares_private_keys as $result) {
	if ($prev !== $result["subject_key_info_sha256"]) {
		$i = 1 - $i;
	}

	$prev = $result["subject_key_info_sha256"];
?>
					<tr<?= $i === 0 ? " class='active'" : ""?>>
						<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a> <span class="text-muted"><?= $result["type"][0] ?>2s</span></td>
						<td><?= fp($result["subject_key_info_sha256"]) ?></td>
					</tr>
<?php
}
?>
				</table>
			</div>
		</div>

		
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
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable([
			['Protocol', 'Percentage', { role: 'style' }, { role: 'annotation' }],
			['SSL 2', <?= round(100 * count($sslv2) / $total["count"], 1) ?>, 'red', '<?= count($sslv2) ?>'],
			['SSL 3', <?= round(100 * $sslv3["count"] / $total["count"], 1) ?>, 'orange', '<?= $sslv3["count"] ?>'],
			['TLS 1.0', <?= round(100 * $tlsv1["count"] / $total["count"], 1) ?>, 'green', '<?= $tlsv1["count"] ?>'],
			['TLS 1.1', <?= round(100 * $tlsv1_1["count"] / $total["count"], 1) ?>, 'green', '<?= $tlsv1_1["count"] ?>'],
			['TLS 1.2', <?= round(100 * $tlsv1_2["count"] / $total["count"], 1) ?>, 'green', '<?= $tlsv1_2["count"] ?>']
		]);

		var options = {
			title: 'Protocol',
			legend: { position: "none" },
		};

		new google.visualization.ColumnChart(document.getElementById('chart1')).draw(data, options);

		var data = google.visualization.arrayToDataTable([
			['Grade', 'Percentage', { role: 'style' }, { role: 'annotation' }],
			['A', <?= round(100 * $score_A["count"] / $total["count"], 1) ?>, 'green', '<?= $score_A["count"] ?>'],
			['B', <?= round(100 * $score_B["count"] / $total["count"], 1) ?>, 'orange', '<?= $score_B["count"] ?>'],
			['C', <?= round(100 * $score_C["count"] / $total["count"], 1) ?>, 'red', '<?= $score_C["count"] ?>'],
			['D', <?= round(100 * $score_D["count"] / $total["count"], 1) ?>, 'red', '<?= $score_D["count"] ?>'],
			['E', <?= round(100 * $score_E["count"] / $total["count"], 1) ?>, 'red', '<?= $score_E["count"] ?>'],
			['F', <?= round(100 * $score_F["count"] / $total["count"], 1) ?>, 'red', '<?= $score_F["count"] ?>']
		]);

		var options = {
			title: 'Grade',
			legend: { position: "none" },
		};

		new google.visualization.ColumnChart(document.getElementById('chart2')).draw(data, options);

		var data = google.visualization.arrayToDataTable([
				['RSA size', 'Count']
<?php
foreach ($bitsizes as $bitsize) {
?>
				, ['<?= $bitsize["rsa_bitsize"] ?>', <?= $bitsize["count"] ?>]
<?php
}
?>
		]);

		var options = {
				title: 'RSA key size'
		};

		new google.visualization.PieChart(document.getElementById('chart3')).draw(data, options);
	});
	</script>

	</body>
</html>
