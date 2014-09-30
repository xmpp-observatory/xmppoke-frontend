<?php

include("common.php");

header("Cache-Control: max-age=1800");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Different policy for the charts API
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://www.google.com https://ajax.googleapis.com");

common_header("");

$since = "2014-01-25 17:00:00 GMT";

if (isset($_GET["since"])) {
	$since = strftime("%F %T %Z", time() - intval($_GET["since"]));
}

pg_prepare($dbconn, "recent_results_table", "CREATE TEMPORARY TABLE recent_results AS (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE test_date > $1);");

$res = pg_execute($dbconn, "recent_results_table", array($since));

pg_prepare($dbconn, "sslv3_not_tls1", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND sslv3 = 't' AND tlsv1 = 'f');");

$res = pg_execute($dbconn, "sslv3_not_tls1");

$sslv3_not_tls1 = pg_fetch_all($res);

if ($sslv3_not_tls1 === FALSE) {
	$sslv3_not_tls1 = array();
}

pg_prepare($dbconn, "dnssec_srv", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE results.srv_dnssec_good = 't' AND EXISTS (SELECT * FROM srv_results WHERE test_id = results.test_id AND priority IS NOT NULL);");

$res = pg_execute($dbconn, "dnssec_srv");

$dnssec_srv = pg_fetch_all($res);

if ($dnssec_srv === FALSE) {
	$dnssec_srv = array();
}

pg_prepare($dbconn, "dnssec_dane", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND priority IS NOT NULL AND tlsa_dnssec_good = 't' AND EXISTS (SELECT * FROM tlsa_records WHERE tlsa_records.srv_result_id = srv_results.srv_result_id));");

$res = pg_execute($dbconn, "dnssec_dane");

$dnssec_dane = pg_fetch_all($res);

if ($dnssec_dane === FALSE) {
	$dnssec_dane = array();
}


pg_prepare($dbconn, "total", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL);");

$res = pg_execute($dbconn, "total");

$total = pg_fetch_assoc($res);

pg_prepare($dbconn, "c2s_total", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name) * FROM test_results WHERE test_date > $1 AND type = 'client' ORDER BY server_name, test_date DESC) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL);");

$res = pg_execute($dbconn, "c2s_total");

$c2s_total = pg_fetch_assoc($res);



pg_prepare($dbconn, "sslv2", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND sslv2 = 't');");

$res = pg_execute($dbconn, "sslv2");

$sslv2 = pg_fetch_all($res);

if ($sslv2 === FALSE) {
	$sslv2 = array();
}

pg_prepare($dbconn, "sslv3", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND sslv3 = 't');");

$res = pg_execute($dbconn, "sslv3");

$sslv3 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND tlsv1 = 't');");

$res = pg_execute($dbconn, "tlsv1");

$tlsv1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_1", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND tlsv1_1 = 't');");

$res = pg_execute($dbconn, "tlsv1_1");

$tlsv1_1 = pg_fetch_assoc($res);

pg_prepare($dbconn, "tlsv1_2", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND tlsv1_2 = 't');");

$res = pg_execute($dbconn, "tlsv1_2");

$tlsv1_2 = pg_fetch_assoc($res);



pg_prepare($dbconn, "bitsizes", "SELECT COUNT(*), pubkey_bitsize FROM (SELECT DISTINCT ON (results.test_id, pubkey_bitsize) pubkey_bitsize FROM (SELECT * FROM recent_results) AS results, srv_results, srv_certificates, certificates WHERE results.test_id = srv_results.test_id AND srv_certificates.srv_result_id = srv_results.srv_result_id AND chain_index = 0 AND certificates.certificate_id = srv_certificates.certificate_id AND (certificates.pubkey_type = 'RSA' OR certificates.pubkey_type = 'DSA')) AS bitsizes GROUP BY pubkey_bitsize ORDER BY pubkey_bitsize;");

$res = pg_execute($dbconn, "bitsizes");

$bitsizes = pg_fetch_all($res);


pg_prepare($dbconn, "1024-2014", "SELECT results.*, certificates.certificate_id, certificate_name(certificates.signed_by_id) AS issuer_certificate_name, trusted, valid_identity FROM (SELECT * FROM recent_results) AS results, srv_results, srv_certificates, certificates WHERE srv_results.test_id = results.test_id AND srv_results.done = 't' AND srv_results.error IS NULL AND srv_certificates.certificate_id = certificates.certificate_id AND pubkey_bitsize < 2048 AND notafter > '2013-12-31' AND notbefore > '2012-07-01' AND chain_index = 0 AND srv_certificates.srv_result_id = srv_results.srv_result_id ORDER BY server_name, type, test_date DESC;");

$res = pg_execute($dbconn, "1024-2014");

$too_weak_1024_2014 = pg_fetch_all($res);

if ($too_weak_1024_2014 === FALSE) {
	$too_weak_1024_2014 = array();
}



pg_prepare($dbconn, "c2s_starttls_allowed", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE test_date > $1 AND type = 'client' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE requires_starttls = 'f' AND done = 't' AND error IS NULL AND test_id = results.test_id);");

$res = pg_execute($dbconn, "c2s_starttls_allowed");

$c2s_starttls_allowed = pg_fetch_assoc($res);

pg_prepare($dbconn, "c2s_starttls_required", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE test_date > $1 AND type = 'client' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE requires_starttls = 't' AND done = 't' AND error IS NULL AND test_id = results.test_id);");

$res = pg_execute($dbconn, "c2s_starttls_required");

$c2s_starttls_required = pg_fetch_assoc($res);

pg_prepare($dbconn, "s2s_starttls_allowed", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE test_date > $1 AND type = 'server' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE requires_starttls = 'f' AND done = 't' AND error IS NULL AND test_id = results.test_id);");

$res = pg_execute($dbconn, "s2s_starttls_allowed");

$s2s_starttls_allowed = pg_fetch_assoc($res);

pg_prepare($dbconn, "s2s_starttls_required", "SELECT COUNT(*) FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results WHERE test_date > $1 AND type = 'server' ORDER BY server_name, type, test_date DESC) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE requires_starttls = 't' AND done = 't' AND error IS NULL AND test_id = results.test_id);");

$res = pg_execute($dbconn, "s2s_starttls_required");

$s2s_starttls_required = pg_fetch_assoc($res);



pg_prepare($dbconn, "trusted_valid", "SELECT COUNT(*), trusted, valid_identity FROM (SELECT * FROM recent_results) AS results, srv_results WHERE done = 't' AND srv_results.error IS NULL AND results.test_id = srv_results.test_id GROUP BY trusted, valid_identity ORDER BY trusted, valid_identity;");

$res = pg_execute($dbconn, "trusted_valid");

$trusted_valid = pg_fetch_all($res);



pg_prepare($dbconn, "score_A", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'A');");

$res = pg_execute($dbconn, "score_A");

$score_A = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_B", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'B');");

$res = pg_execute($dbconn, "score_B");

$score_B = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_C", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'C');");

$res = pg_execute($dbconn, "score_C");

$score_C = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_D", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'D');");

$res = pg_execute($dbconn, "score_D");

$score_D = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_E", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'E');");

$res = pg_execute($dbconn, "score_E");

$score_E = pg_fetch_assoc($res);

pg_prepare($dbconn, "score_F", "SELECT COUNT(*) FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND grade = 'F');");

$res = pg_execute($dbconn, "score_F");

$score_F = pg_fetch_assoc($res);

pg_prepare($dbconn, "shares_private_keys", "select distinct on (results.server_name, results.type, results.test_id, subject_key_info_sha256) results.server_name, results.type, results.test_id, subject_key_info_sha256 from (select distinct on (server_name, type) * from test_results WHERE test_date > $1 order by server_name, type, test_date desc) as results, srv_results, srv_certificates, certificates as c where chain_index = 0 and srv_certificates.certificate_id = c.certificate_id and srv_results.srv_result_id = srv_certificates.srv_result_id and srv_results.test_id = results.test_id and exists (select 1 from (select distinct on (server_name) test_id, server_name from test_results WHERE test_date > $1 order by server_name, test_date desc) as r, (select * from srv_results, srv_certificates, certificates where chain_index = 0 and srv_certificates.certificate_id = certificates.certificate_id and srv_results.srv_result_id = srv_certificates.srv_result_id) as certificates where certificates.test_id = r.test_id and results.server_name != r.server_name and certificates.subject_key_info_sha256 = c.subject_key_info_sha256) order by subject_key_info_sha256, server_name, type;");

$res = pg_execute($dbconn, "shares_private_keys");

$shares_private_keys = pg_fetch_all($res);

if ($shares_private_keys === FALSE) {
	$shares_private_keys = array();
}


pg_prepare($dbconn, "mechanisms", "SELECT mechanism, COUNT(*) FROM (SELECT DISTINCT mechanism, test_id FROM srv_mechanisms, srv_results WHERE srv_mechanisms.srv_result_id = srv_results.srv_result_id AND srv_results.test_id IN (SELECT test_id FROM recent_results WHERE type = 'client' AND error IS NULL) AND srv_results.done = 't' AND srv_results.error IS NULL AND after_tls = $1 GROUP BY mechanism, test_id) AS q GROUP BY mechanism ORDER BY count DESC;");

$res = pg_execute($dbconn, "mechanisms", array(0));

$pre_tls_mechanisms = pg_fetch_all($res);

$res = pg_execute($dbconn, "mechanisms", array(1));

$post_tls_mechanisms = pg_fetch_all($res);


pg_prepare($dbconn, "onions", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error IS NULL AND target like '%.onion');");

$res = pg_execute($dbconn, "onions");

$onions = pg_fetch_all($res);

if ($onions === FALSE) {
	$onions = array();
}

pg_prepare($dbconn, "unencrypted", "SELECT * FROM (SELECT * FROM recent_results) AS results WHERE EXISTS (SELECT 1 FROM srv_results WHERE test_id = results.test_id AND done = 't' AND error = 'Server does not support encryption.');");

$res = pg_execute($dbconn, "unencrypted");

$unencrypted = pg_fetch_all($res);

if ($unencrypted === FALSE) {
	$unencrypted = array();
}


pg_prepare($dbconn, "cas", "SELECT certificate_name(certificates.signed_by_id), issuer.digest_sha1, count(*) AS c FROM certificates, certificates as issuer WHERE certificates.certificate_id IN (SELECT certificate_id FROM srv_results, srv_certificates where test_id in (SELECT DISTINCT ON (server_name, type) test_id FROM test_results WHERE test_date > $1) AND error IS NULL AND done = 't' AND srv_certificates.srv_result_id = srv_results.srv_result_id AND chain_index = 0) GROUP BY certificates.signed_by_id, issuer.digest_sha1, issuer.certificate_id HAVING issuer.certificate_id = certificates.signed_by_id ORDER BY c DESC LIMIT 30;");

$res = pg_execute($dbconn, "cas");

$cas = pg_fetch_all($res);

if ($cas === FALSE) {
	$cas = array();
}

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
						<li><a href="#rsa">RSA key sizes</a></li>
						<li><a href="#starttls">StartTLS</a></li>
						<li><a href="#trust">Trust</a></li>
						<li><a href="#saslmechanisms">SASL</a></li>
						<li><a href="#sslv3butnottls1">SSL 3, but not TLS 1.0</a></li>
						<li><a href="#sslv2wallofshame">SSL 2</a></li>
						<li><a href="#cas">CAs</a></li>
						<li><a href="#1024-2014">1024-bit RSA after 2014</a></li>
						<li><a href="#dnssecsrv">DNSSEC signed SRV</a></li>
						<li><a href="#dnssecdane">DANE</a></li>
						<li><a href="#onions">Tor hidden services</a></li>
						<li><a href="#unencrypted">Servers not offering encryption</a></li>
						<li><a href="#sharesprivatekeys">Private key sharing</a></li>
					</ul>
				</div>
			</div>

			<div class="col-md-9">

				<h1>Various reports of all servers tested</h1>

				<a href="report_2013_12.php">Report for december 2013</a> | <a href="reports.php?since=1">Results of the last day</a> | <a href="reports.php?since=7">Results of the last week</a> | <a href="reports.php?since=30">Results of the last month</a>

				<br>

				<div class="alert alert-block alert-warning">
					<strong>Warning:</strong> On January 25th 2014 the test was updated, so results prior to this are not taken into account.
				</div>

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
						<div id="chart1" class="chart"></div>
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
						<div id="chart2" class="chart"></div>
					</div>
				</div>

				<span class="text-muted">Does not penalize untrusted certificates.</span>

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
								<td><?= $bitsize["pubkey_bitsize"] ?></td>
								<td><?= $bitsize["count"] ?> <span class="text-muted"><?= round(100 * $bitsize["count"] / $rsa_sum, 1) ?>%</span></td>
							</tr>
<?php
}
?>
						</table>
					</div>

					<div class="col-md-6">
						<div id="chart3" class="chart"></div>
					</div>
				</div>

				<h3 id="starttls">StartTLS</h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Type</th>
						<th>Client to server</th>
						<th>Server to server</th>
					</tr>
					<tr>
						<th>Required</th>
						<td><?= $c2s_starttls_required["count"] ?> <span class="text-muted"><?= round(100 * $c2s_starttls_required["count"] / ($c2s_starttls_required["count"] + $c2s_starttls_allowed["count"]), 1) ?>%</span></td>
						<td><?= $s2s_starttls_required["count"] ?> <span class="text-muted"><?= round(100 * $s2s_starttls_required["count"] / ($s2s_starttls_required["count"] + $s2s_starttls_allowed["count"]), 1) ?>%</span></td>
					</tr>
					<tr>
						<th>Allowed</th>
						<td><?= $c2s_starttls_allowed["count"] ?> <span class="text-muted"><?= round(100 * $c2s_starttls_allowed["count"] / ($c2s_starttls_required["count"] + $c2s_starttls_allowed["count"]), 1) ?>%</span></td>
						<td><?= $s2s_starttls_allowed["count"] ?> <span class="text-muted"><?= round(100 * $s2s_starttls_allowed["count"] / ($s2s_starttls_required["count"] + $s2s_starttls_allowed["count"]), 1) ?>%</span></td>
					</tr>
				</table>

				<div class="row">
					<div class="col-md-6">
						<div id="chart4" class="chart"></div>
					</div>

					<div class="col-md-6">
						<div id="chart5" class="chart"></div>
					</div>
				</div>

				<h3 id="trust">Trust</h3>

<?php

$sum = $trusted_valid[0]["count"] + $trusted_valid[1]["count"] + $trusted_valid[2]["count"] + $trusted_valid[3]["count"];

?>
				<p>To do authenticated encryption, a certificate needs to be both trusted and valid. Trusted means it is issued by a well-known CA and valid means it is valid for the domain we want to connect to.</p>

				<table class="table table-bordered table-striped">
					<tr>
						<th></th>
						<th>Trusted</th>
						<th>Untrusted</th>
					</tr>
					<tr>
						<th>Valid</td>
						<td><?= $trusted_valid[3]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[3]["count"] / $sum, 1) ?>%</span></td>
						<td><?= $trusted_valid[1]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[1]["count"] / $sum, 1) ?>%</span></td>
					</tr>
					<tr>
						<th>Invalid</td>
						<td><?= $trusted_valid[2]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[2]["count"] / $sum, 1) ?>%</span></td>
						<td><?= $trusted_valid[0]["count"] ?> <span class="text-muted"><?= round(100 * $trusted_valid[0]["count"] / $sum, 1) ?>%</span></td>
					</tr>
				</table>

				<h3 id="saslmechanisms">SASL mechanisms <small class="text-muted"><?= $c2s_total["count"] ?> results</small></h3>

<?php
$both_mechanisms = array();
foreach ($post_tls_mechanisms as $mechanism) {
	if ($both_mechanisms[$mechanism["mechanism"]] == NULL) {
		$both_mechanisms[$mechanism["mechanism"]] = array();
	}
	$both_mechanisms[$mechanism["mechanism"]]["post"] = $mechanism["count"];
}
foreach ($pre_tls_mechanisms as $mechanism) {
	if ($both_mechanisms[$mechanism["mechanism"]] == NULL) {
		$both_mechanisms[$mechanism["mechanism"]] = array();
	}
	$both_mechanisms[$mechanism["mechanism"]]["pre"] = $mechanism["count"];
}
?>
				<div class="row">
					<div class="col-md-6">
						<table class="table table-bordered table-striped">
							<tr>
								<th>Mechanism</th>
								<th># times offered before TLS</th>
								<th># times offered after TLS</th>
							</tr>
<?php
foreach ($both_mechanisms as $mechanism => $v) {
?>
							<tr>
								<td><?= $mechanism ?></td>
								<td><?= (int)$v["pre"] ?> <span class="text-muted"><?= round(100 * $v["pre"] / $c2s_total["count"], 1) ?>%</span></td>
								<td><?= (int)$v["post"] ?> <span class="text-muted"><?= round(100 * $v["post"] / $c2s_total["count"], 1) ?>%</span></td>
							</tr>
<?php
}
?>
						</table>
					</div>
				</div>

				<h3 id="sslv3butnottls1">Servers supporting SSL 3, but not TLS 1.0 <small class="text-muted"><?= count($sslv3_not_tls1) ?> results</small></h3>

				<p>SSL 3 and TLS 1.0 are very similar, but TLS 1.0 has some small improvements. This table is meant to help judge whether SSL 3 can be disabled by listing the servers that do support SSL 3, but not TLS 1.0.</p>

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

				<p>SSL 2 is broken and insecure. It is <b>not</b> required for compatibility and servers should disable it.</p>

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

				<h3 id="cas">CAs used <small class="text-muted">Top 30</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Name/Organization</th>
						<th>SHA1</th>
						<th>Count</th>
					</tr>
<?php
foreach ($cas as $result) {
?>
					<tr>
						<td><?= $result["certificate_name"] === NULL ? "(Unknown)" : $result["certificate_name"] ?></td>
						<td><?= fp($result["digest_sha1"]) ?></td>
						<td><?= $result["c"] ?></td>
<?php
}
?>
					</tr>
				</table>

				<h3 id="1024-2014">Servers using &lt;2048-bit RSA certificates which expires after 01-01-2014 <small class="text-muted"><?= count($too_weak_1024_2014) ?> results</small></h3>

				<p>As described in the <a href="https://cabforum.org/Baseline_Requirements_V1.pdf">CA/Browser Forum Baseline Requirements</a>, certificates with RSA keys with less than 2048 bits should not be issued with an notAfter date after 31-12-2013. This list lists all certificates which violate that rule.</p>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
						<th>Issuer</th>
					</tr>
<?php
foreach ($too_weak_1024_2014 as $result) {
?>
					<tr>
						<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a></td>
						<td><?= $result["type"] ?> to server</td>
						<td><time class="timeago" datetime="<?= date("c", strtotime($result["test_date"])) ?>"><?= date("c", strtotime($result["test_date"])) ?></time></td>
						<td><span<?= ($result["trusted"] === 'f' || $result["valid_identity"] === 'f') ? " class='text-danger'" : ""?>><?= htmlspecialchars($result["issuer_certificate_name"]) ?></span></td>
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

				<h3 id="dnssecdane">Servers with DNSSEC signed DANE records <small class="text-muted"><?= count($dnssec_dane) ?> results</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
					</tr>
<?php
foreach ($dnssec_dane as $result) {
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

				<h3 id="onions">Servers with a hidden service <small class="text-muted"><?= count($onions) ?> results</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
					</tr>
<?php
foreach ($onions as $result) {
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

				<h3 id="unencrypted">Servers not offering encryption <small class="text-muted"><?= count($unencrypted) ?> results</small></h3>

				<table class="table table-bordered table-striped">
					<tr>
						<th>Target</th>
						<th>Type</th>
						<th>When</th>
					</tr>
<?php
foreach ($unencrypted as $result) {
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
				, ['<?= $bitsize["pubkey_bitsize"] ?>', <?= $bitsize["count"] ?>]
<?php
}
?>
		]);

		var options = {
				title: 'RSA key size'
		};

		new google.visualization.PieChart(document.getElementById('chart3')).draw(data, options);

		var data = google.visualization.arrayToDataTable([
			['c2s StartTLS', 'Count'],
			['Required', <?= $c2s_starttls_required["count"] ?>],
			['Allowed', <?= $c2s_starttls_allowed["count"] ?>],
		]);

		var options = {
			title: 'c2s StartTLS',
			legend: { position: "none" },
			slices: {
				0: {offset: 0.2, color: 'green'},
				1: {color: 'grey'}
			}
		};

		new google.visualization.PieChart(document.getElementById('chart4')).draw(data, options);

		var data = google.visualization.arrayToDataTable([
			['s2s <?= $c2s_starttls_required["count"] ?>', 'Count'],
			['Required', <?= $s2s_starttls_required["count"] ?>],
			['Allowed', <?= $s2s_starttls_allowed["count"] ?>],
		]);

		var options = {
			title: 's2s StartTLS',
			legend: { position: "none" },
			slices: {
				0: {offset: 0.2, color: 'green'},
				1: {color: 'grey'} }
		};

		new google.visualization.PieChart(document.getElementById('chart5')).draw(data, options);
	});
	</script>

	</body>
</html>
