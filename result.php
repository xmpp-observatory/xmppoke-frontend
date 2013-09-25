<?php

$result_id = $_GET['id'];
$result_domain = $_GET['domain'];
$result_type = $_GET['type'];

if (isset($result_id) || (isset($result_domain) && isset($result_type))) {
	$dbconn = pg_connect("port=5433 host=localhost dbname=xmppoke user=xmppoke password=xmppoke") or die('Could not connect: ' . pg_last_error());

	if (isset($result_id)) {	
		pg_prepare($dbconn, "find_result", "SELECT * FROM test_results WHERE test_id = $1");
	
		$res = pg_execute($dbconn, "find_result", array($result_id));

		$result = pg_fetch_object($res);
	} else {
		pg_prepare($dbconn, "find_result", "SELECT * FROM test_results WHERE server_name = $1 AND type = $2 ORDER BY test_date DESC LIMIT 1");

		$res = pg_execute($dbconn, "find_result", array($result_domain, $result_type));

		$result = pg_fetch_object($res);

		$result_id = $result->test_id;
	}

	pg_prepare($dbconn, "find_srvs", "SELECT * FROM srv_results WHERE test_id = $1 ORDER BY priority, weight DESC, port, target");

	$res = pg_execute($dbconn, "find_srvs", array($result_id));

	$srvs = pg_fetch_all($res);

	pg_prepare($dbconn, "find_ciphers", "SELECT * FROM srv_ciphers, ciphers WHERE srv_ciphers.srv_result_id = $1 AND srv_ciphers.cipher_id = ciphers.cipher_id ORDER BY cipher_index;");

	pg_prepare($dbconn, "find_certs", "SELECT * FROM srv_certificates, certificates WHERE srv_certificates.certificate_id = certificates.certificate_id AND srv_certificates.srv_result_id = $1 ORDER BY chain_index;");

	pg_prepare($dbconn, "find_cert", "SELECT * FROM certificates WHERE certificates.certificate_id = $1;");

	pg_prepare($dbconn, "find_errors", "SELECT * FROM srv_certificate_errors WHERE srv_certificates_id = $1 ORDER BY message");

	pg_prepare($dbconn, "find_subjects", "SELECT * FROM certificate_subjects WHERE certificate_id = $1 ORDER BY name;");

	pg_prepare($dbconn, "find_tlsas", "SELECT * FROM tlsa_records WHERE srv_result_id = $1 ORDER BY tlsa_record_id;");
}

function tlsa_usage($usage) {
	if ($usage == 0) return "CA constraint";
	if ($usage == 1) return "service certificate constraint";
	if ($usage == 2) return "trust anchor assertion";
	if ($usage == 3) return "domain-issued certificate";
	return $usage;
}

function tlsa_selector($selector) {
	if ($selector == 0) return "full";
	if ($selector == 1) return "SPKI";
	return $selector;
}

function tlsa_match($match) {
	if ($match == 0) return "exact";
	if ($match == 1) return "SHA-256";
	if ($match == 2) return "SHA-512";
	return $match;
}

function color_score_text($score) {
	if ($score >= 80) {
		return " text-success";
	} elseif ($score >= 60) {
		return "";
	} else if ($score >= 40) {
		return " text-warning";
	}
	return " text-error";
}

function color_score_bar($score) {
	if ($score >= 80) {
		return "progress-success";
	} elseif ($score >= 40) {
		return "progress-warning";
	}
	return "progress-danger";
}

function color_bitsize($size) {
	if ($size > 128) {
		return "badge-success";
	} elseif ($size > 64) {
		return "";
	} elseif ($size > 40) {
		return "badge-warning";
	}
	return "badge-important";
}

function fp($x) {
	return strtoupper(join(':', str_split($x, 2)));
}

function grade($score) {
	if ($score >= 80) return "A";
	if ($score >= 65) return "B";
	if ($score >= 50) return "C";
	if ($score >= 35) return "D";
	if ($score >= 20) return "E";
	return "F";
}

function show_cert($dbconn, $cert, $errors, $prev_signed_by_id, $server_name, $srv, $i) {

	$res = pg_execute($dbconn, "find_subjects", array($cert["certificate_id"]));

	$subjects = pg_fetch_all($res);

	$name = "";

	foreach ($subjects as $subject) {
		if ($subject["name"] == "commonName") {
			$name = $subject["value"];
			break;
		}
	}

	if (!$name) {
		foreach ($subjects as $subject) {
			if ($subject["name"] == "organizationName") {
				$name = $subject["value"];
				break;
			}
		}
	}
	

	?>
		<h4 class="page-header">#<?= $cert["chain_index"] ?> <?= $name ?></h4>

		<h5>Subject</h5>

		<dl class="dl-horizontal">
<?php

foreach ($subjects as $subject) {
		
?>
			<dt><?= $subject["name"] ? htmlspecialchars($subject["name"]) : $subject["oid"] ?></dt>
			<dd><?= htmlspecialchars($subject["value"]) ?></dd>
<?php
}
?>
		</dl>

		<h5>Details</h5>

<?php
if ($cert["trusted_root"] === 't' && $cert["chain_index"]) {
?>
		<div class="alert alert-block alert-warning">
						<strong>Warning:</strong> Trusted root certificate is included in the chain.
				</div>
<?php
}
if ($cert["trusted_root"] === 'f' && $cert["chain_index"] == NULL) {
?>
		<div class="alert alert-block alert-error">
						<strong>Error:</strong> Intermediate certificate was not included in the chain.
				</div>
<?php
}
if ($prev_signed_by_id != $cert["certificate_id"] && $cert["chain_index"] != 0) {
?>
				<div class="alert alert-block alert-warning">
						<strong>Warning:</strong> Certificate is unused.
				</div>

<?php
} else {
	$prev_signed_by_id = $cert["signed_by_id"];
}

foreach ($errors as $error) {
?>
		<div class="alert alert-block alert-error">
						<strong>Error:</strong> <?= $error["message"] ?>.
				</div>

<?php
}
?>
		<dl class="dl-horizontal">
			<dt>Signature algorithm</dt>
			<dd><?= $cert["sign_algorithm"] ?></dd>
			<dt>Key size</dt>
			<dd><?= $cert["rsa_bitsize"] ?></dd>
			<dt>Valid from</dt>
			<dd><?= $cert["notbefore"] ?> UTC <time class="<?= strtotime($cert["notbefore"]) > strtotime("now") ? "text-error" : "muted" ?> timeago" datetime="<?= date("c", strtotime($cert["notbefore"])) ?>"></time></dd>
			<dt>Valid to</dt>
			<dd><?= $cert["notafter"] ?> UTC <time class="<?= strtotime($cert["notafter"]) < strtotime("now") ? "text-error" : "muted" ?> timeago" datetime="<?= date("c", strtotime($cert["notafter"])) ?>"></time></dd>
<?php
if (isset($cert["crl_url"])) {
?>
			<dt>CRL</dt>
			<dd><a href="<?= htmlspecialchars($cert["crl_url"]) ?>"><?= htmlspecialchars($cert["crl_url"]) ?></a></dd>
<?php
}
?>
<?php
if (isset($cert["ocsp_url"])) {
?>
						<dt>OCSP</dt>
						<dd><a href="<?= htmlspecialchars($cert["ocsp_url"]) ?>"><?= htmlspecialchars($cert["ocsp_url"]) ?></a></dd>
<?php
}

if ($i === 0) {
?>
			<dt>Valid for <?= $server_name ?></dt>
			<dd class="<?= $srv["valid_identity"] === 't' ? "" : "text-error" ?>"><?= $srv["valid_identity"] === 't' ? "Yes" : "No" ?></dd>
<?php } ?>
			<dt>
				<select class="hash-select input-small">
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha1"]) ?>'>SHA-1</option>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha256"]) ?>'>SHA-256</option>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha512"]) ?>'>SHA-512</option>
				</select> hash
			</dt>
			<dd><pre type="text" id="hashfield<?= $i ?>"><?= fp($cert["digest_sha1"]) ?></pre></dd>
		</dl>


		<button class="btn pem" data-pem="<?= $cert["pem"] ?>">Show PEM</button>
	<?php
	return $prev_signed_by_id;
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
				<li class="active"><a href="#">Test results</a></li>
				<li><a href="list.php">Recent tests</a></li>
			</ul>
			</div><!--/.nav-collapse -->
		</div>
		</div>
	</div>

	<div class="container">
<?php
if (!$result) {

?>
		<h1>404</h1>
		<div class="alert alert-block alert-error">
			Test result could not be found.
		</div>
<?php

} else {

?>

		<h1>XMPP <?= $result->type ?> TLS report for <?= htmlspecialchars($result->server_name) ?></h1>
		<p class="muted">On <time><?= date('Y-m-d H:i:s T', strtotime($result->test_date)) ?></time>.</p>

				<h2>Score</h2>
<?php

$i = 0;

foreach ($srvs as $srv) {

	if ($srv["done"] === 'f') {
?>
		<div class="alert alert-block alert-warning">
			Test did not complete successfully or is still in progress.
		</div>

<?php
	}

	if ($i === 1) {
?>
		<div class="collapse-group">
				<div class="collapse">
<?php
	}
?>

		<h5><?= $srv["target"] ?>:<?= $srv["port"] ?></h5>

		<div class="row">
			<div class="span9">
				<div class="span3 text-right">
					<strong>Certificate score:</strong>
				</div>
				<div class="span4">
					<div class="progress <?= color_score_bar($srv["certificate_score"]) ?>">
						<div class="bar" style="width: <?= $srv["certificate_score"] ?>%"></div>
					</div>
				</div>
				<div class="span1">
					<strong class="<?= color_score_text($srv["certificate_score"]) ?>"><?= $srv["certificate_score"] ?></strong>
				</div>
				<div class="span3 text-right">
					<strong>Public key score:</strong>
				</div>
				<div class="span4">
					<div class="progress <?= color_score_bar($srv["keysize_score"]) ?>">
						<div class="bar" style="width: <?= $srv["keysize_score"]?>%"></div>
					</div>
				</div>
				<div class="span1">
					<strong class="<?= color_score_text($srv["keysize_score"]) ?>"><?= $srv["keysize_score"]?></strong>
				</div>
				<div class="span3 text-right">
					<strong>Protocol score:</strong>
				</div>
				<div class="span4">
					<div class="progress <?= color_score_bar($srv["protocol_score"]) ?>">
						<div class="bar" style="width: <?= $srv["protocol_score"]?>%"></div>
					</div>
				</div>
				<div class="span1">
					<strong class="<?= color_score_text($srv["protocol_score"]) ?>"><?= $srv["protocol_score"]?></strong>
				</div>
				<div class="span3 text-right">
					<strong>Cipher score:</strong>
				</div>
				<div class="span4">
					<div class="progress <?= color_score_bar($srv["cipher_score"]) ?>">
						<div class="bar" style="width: <?= $srv["cipher_score"]?>%"></div>
					</div>
				</div>
				<div class="span1">
					<strong class="<?= color_score_text($srv["cipher_score"]) ?>"><?= $srv["cipher_score"]?></strong>
				</div>
			</div>

			<div class="span2 offset1 text-center">
				<strong>Grade:</strong>
				<div>
					<p class="<?= $srv["grade"] === 'F' ? "text-error" : color_score_text($srv["total_score"]) ?>" style="font-size: 1000%; line-height: 100px;"><?= $srv["grade"] ?></p>
				</div>
			</div>
		</div>


<?php
	if ($srv["certificate_score"] == 0) {
?>
				<div class="alert alert-block alert-error">
						Certificate is <strong>not trusted</strong>, grade capped to <strong>F</strong>. Ignoring trust: <strong><?= $srv["sslv2"] === 't' ? "F" : grade($srv["total_score"]) ?></strong>.
				</div>
<?php
	}

	$i = $i + 1;
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn" href="#">Show all <?= count($srvs) ?> SRV targets &raquo;</a></p>
		</div>
<?php
}
?>

		<h2 class="page-header">DNS</h2>
		<h3>SRV records</h3>
		<h5>_xmpp-<?= $result->type ?>._tcp.<?= idn_to_ascii($result->server_name) ?> <span class="label<?= $result->srv_dnssec_good === 't' ? " label-success" : ($result->srv_dnssec_bogus === 't' ? " label-warning" : "")?>"><?= $result->srv_dnssec_good === 't' ? "" : ($result->srv_dnssec_bogus === 't' ? "BOGUS " : "NO ")?>DNSSEC</span></h5>
		<div class="row">
			<div class="span5">
				<table class="table table-bordered table-striped">

					<tr>
						<th>Priority</th>
						<th>Weight</th>
						<th>Port</th>
						<th>Server</th>
					</tr>
<?php

foreach ($srvs as $srv) {
?>
					<tr>
						<td><?= $srv["priority"] ?></td>
						<td><?= $srv["weight"] ?></td>
						<td><?= $srv["port"] ?></td>
						<td title="<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>"><?= htmlspecialchars($srv["target"]) ?></td>
					</tr>
<?php
}
?>
				</table>
			</div>
		</div>
	
		<h3>TLSA records</h3>
<?php

$i = 0;

foreach ($srvs as $srv) {

		if ($i === 1) {
?>
		<div class="collapse-group">
				<div class="collapse">
<?php
		}
?>
		<h3 class="page-header">_<?= $srv["port"] ?>._tcp.<?= htmlspecialchars($srv["target"]) ?> <span class="label<?= $srv["tlsa_dnssec_good"] === 't' ? " label-success" : ($srv["tlsa_dnssec_bogus"] === 't' ? " label-warning" : "")?>"><?= $srv["tlsa_dnssec_good"] === 't' ? "" : ($srv["tlsa_dnssec_bogus"] === 't' ? "BOGUS " : "NO ")?>DNSSEC</span></h3>

		<table class="span10 table table-bordered table-striped">
			<tr>
				<th>Verified</th>
				<th>Usage</th>
				<th>Selector</th>
				<th>Match</th>
				<th>Data</th>
			</tr>
<?php

	$res = pg_execute($dbconn, "find_tlsas", array($srv["srv_result_id"]));

	$tlsas = pg_fetch_all($res);

	if ($tlsas) foreach ($tlsas as $tlsa) {
?>
		<tr>
			<td><span class="label label-<?= $tlsa["verified"] === 't' ? "success" : "warning" ?>"><?= $tlsa["verified"] === 't' ? "Yes" : "No" ?></span></td>
			<td><?= tlsa_usage($tlsa["usage"]) ?></td>
			<td><?= tlsa_selector($tlsa["selector"]) ?></td>
			<td><?= tlsa_match($tlsa["match"]) ?></td>
<?php
		if ($tlsa["match"] == 0) {
?>
			<td>
				<div class="collapse-group">
					<pre class="collapse" style="height: 100px"><?= pg_unescape_bytea($tlsa["data"]) ?></pre>
				<p><a class="btn" href="#">Show full &raquo;</a></p>
			</div></td>
<?
		} else {
?>
						<td><pre><?= fp(pg_unescape_bytea($tlsa["data"])) ?></pre></td>
<?
		}
?>
		</tr>
<?php
	}
?>
		</table>
<?php
	$i = $i + 1;
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn" href="#">Show all <?= count($srvs) ?> SRV targets &raquo;</a></p>
		</div>
<?php
}
?>
		
		<h2 class="page-header">TLS</h2>

<?php

$i = 0;
$j = 0;

foreach ($srvs as $srv) {
	if ($j === 1) {
?>
		<div class="collapse-group">
			<div class="collapse">
<?php
	}
?>		
		<h3 class="page-header"><?= htmlspecialchars($srv["target"]) ?>:<?= $srv["port"] ?></h3>

		<h3>Certificates</h3>
<?php

	$res = pg_execute($dbconn, "find_certs", array($srv["srv_result_id"]));

	$certs = pg_fetch_all($res);

	$cert = $certs[0];

	$prev_signed_by_id = NULL;

	while (true) {

		$index = NULL;

		foreach ($certs as $k => $v) {
			if ($v["digest_sha512"] == $cert["digest_sha512"]) {
				$index = $k;
				break;
			}
		}

		$res = pg_execute($dbconn, "find_errors", array($cert["srv_certificates_id"]));

		$errors = pg_fetch_all($res);

		show_cert($dbconn, $cert, $errors ? $errors : array(), $prev_signed_by_id, $result->server_name, $srv, $i);

		$prev_signed_by_id = $cert["signed_by_id"];

		if ($prev_signed_by_id == NULL) {
			break;
		}

		if ($cert["signed_by_id"] == $cert["certificate_id"]) {
			break;
		}

		$cert = NULL;

		foreach ($certs as $k => $v) {
			if ($v["certificate_id"] == $prev_signed_by_id) {
				$cert = $v;
				break;
			}
		}

		if (!$cert) {
			$res = pg_execute($dbconn, "find_cert", array($prev_signed_by_id));
		
			$cert = pg_fetch_assoc($res);
		}

		if (!$cert) {
			break;
		}

		$i = $i + 1;
	}
?>

		<h3>Protocols</h3>

		<div class="row">
			<div class="span4">
				<table class="table table-bordered table-striped">
					<tr>
						<td><abbr class="my-popover" title="" data-content="SSLv2 is old, obsolete and insecure. Servers <strong>must not</strong> allow it to be used." data-toggle="popover" data-original-title="SSLv2">SSLv2</abbr></td>
						<td><span class="label label-<?= $srv["sslv2"] === 't' ? "important" : "success"?>"><?= $srv["sslv2"] === 't' ? "Yes" : "No" ?></span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="SSLv3 is old and not recommended. Servers <strong>should not</strong> allow it to be used." data-toggle="popover" data-original-title="SSLv3">SSLv3</abbr></td>
						<td><span class="label label-<?= $srv["sslv3"] === 't' ? "important" : "success"?>"><?= $srv["sslv3"] === 't' ? "Yes" : "No" ?></span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="Although replaced by TLSv1.1 and TLSv1.2, it is <strong>recommended</strong> to support TLSv1 for compatibility with older clients. There are no known security issues with TLSv1.0 for XMPP." data-toggle="popover" data-original-title="TLSv1">TLSv1</abbr></td>
						<td><span class="label"><?= $srv["tlsv1"] === 't' ? "Yes" : "No" ?></span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="There are no known security issues with TLSv1.1." data-toggle="popover" data-original-title="TLSv1.1">TLSv1.1</abbr></td>
						<td><span class="label label-<?= $srv["tlsv1_1"] === 't' ? "success" : "important"?>"><?= $srv["tlsv1_1"] === 't' ? "Yes" : "No" ?></span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="TLSv1.2 is the latest version and it is <strong>strongly recommended</strong> that servers support it as it adds a number of newer cipher suites." data-toggle="popover" data-original-title="TLSv1.2">TLSv1.2</abbr></td>
						<td><span class="label label-<?= $srv["tlsv1_2"] === 't' ? "success" : "important"?>"><?= $srv["tlsv1_2"] === 't' ? "Yes" : "No" ?></span></td>
					</tr>
				</table>
			</div>
		</div>

		<h3>Ciphers</h3>

		<p>Server does <?= $srv["reorders_ciphers"] === 't' ? "<strong>not</strong> " : "" ?>respect the client's cipher ordering.</p>

		<div class="row">
			<div class="span7">
				<table class="table table-bordered table-striped">
					<tr><th>Cipher suite</th><th>Bitsize</th><th>Forward secrecy</th></tr>
<?php
	$res = pg_execute($dbconn, "find_ciphers", array($srv["srv_result_id"]));

	$ciphers = pg_fetch_all($res);

	foreach($ciphers as $cipher) {
?>
					<tr><td><abbr class="my-popover" title="" data-content="<strong><?= ($cipher["authentication"] !== $cipher["key_exchange"] ? $cipher["key_exchange"] . "-" : "") . $cipher["authentication"] ?></strong>: ...<br><strong><?= $cipher["symmetric_alg"] ?></strong>: ...<br><strong><?= $cipher["hash_alg"] ?></strong>: ..." data-toggle="popover" data-original-title="<?= $cipher["openssl_name"] ?>"><?= $cipher["openssl_name"] ?></abbr> <span class="muted">(0x<?= dechex($cipher["cipher_id"]) ?>)</span><?= $cipher["export"] === 't' ? " <span class=\"label label-important\">VERY WEAK</span>" : ($cipher["bitsize"] < 128 ? " <span class=\"label label-warning\">WEAK</span>" : "") ?></td><td><span class="badge <?= color_bitsize($cipher["bitsize"]) ?>"><?= $cipher["bitsize"] ?></span><td><span class="label label-<?= $cipher["forward_secret"] === 't' ? "success" : "important" ?>"><?= $cipher["forward_secret"] === 't' ? "Yes" : "No" ?></span></td></tr>
<?php
	}
?>
				</table>
			</div>
		</div>
<?php
	$j = $j + 1;
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn" href="#">Show all <?= count($srvs) ?> SRV targets &raquo;</a></p>
		</div>
<?php
}

}
?>

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
