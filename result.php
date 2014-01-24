<?php

include("common.php");

$result_id = $_GET['id'];
$result_domain = idn_to_utf8(strtolower(idn_to_ascii($_GET['domain'], "utf8")));
$result_type = $_GET['type'];

if (isset($result_id) || (isset($result_domain) && isset($result_type))) {
	
	if (isset($result_id)) {	
		pg_prepare($dbconn, "find_result", "SELECT * FROM test_results WHERE test_id = $1");
	
		$res = pg_execute($dbconn, "find_result", array($result_id));

        $result = pg_fetch_object($res);

        $result_domain = $result->server_name;
        $result_type = $result->type;
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

	pg_prepare($dbconn, "find_and_sort_ciphers", "SELECT * FROM srv_ciphers, ciphers WHERE srv_ciphers.srv_result_id = $1 AND srv_ciphers.cipher_id = ciphers.cipher_id ORDER BY bitsize DESC, forward_secret DESC, export DESC, ciphers.cipher_id DESC;");

	pg_prepare($dbconn, "find_certs", "SELECT * FROM srv_certificates, certificates WHERE srv_certificates.certificate_id = certificates.certificate_id AND srv_certificates.srv_result_id = $1 ORDER BY chain_index;");

	pg_prepare($dbconn, "find_cert", "SELECT * FROM certificates WHERE certificates.certificate_id = $1;");

	pg_prepare($dbconn, "find_root", "SELECT * FROM certificates WHERE trusted_root = 't' AND subject_key_info = (SELECT subject_key_info FROM certificates WHERE certificates.certificate_id = $1);");

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

function color_score_bar($score) {
	if ($score >= 80) {
		return "progress-bar-success";
	} elseif ($score >= 40) {
		return "progress-bar-warning";
	}
	return "progress-bar-danger";
}

function color_bitsize($size) {
	if ($size >= 256) {
		return "label-success";
	} elseif ($size >= 128) {
		return "label-default";
	} elseif ($size >= 64) {
		return "label-warning";
	}
	return "label-danger";
}

function grade($score) {
	if ($score >= 80) return "A";
	if ($score >= 65) return "B";
	if ($score >= 50) return "C";
	if ($score >= 35) return "D";
	if ($score >= 20) return "E";
	return "F";
}

function show_bool($b) {
	if ($b === 't') {
		return "Yes";
	} else if ($b === 'f') {
		return "No";
	}
	return "?";
}

function help($str) {
	switch($str) {
		case "AES":
			return "Advanced Encryption Standard is a symmetric key encryption algorithm using 128 or 256 bit keys. This is currently the recommended encryption algorithm.";
		case "DES":
			return "The Data Encryption Standard is a symmetric key encryption algorithm using 56 bit keys. This encryption algorithm is known to be easy to break and must not be used.";
		case "3DES":
			return "The Triple Data Encryption Algorithm is a symmetric key encryption algorithm which applies the DES algorithm 3 times. No practical attacks on 3DES exist, but it is recommended to use the faster AES instead.";
		case "RC4":
			return "Rivest Cipher 4 is a fast stream cipher using 128 bit keys. Due to known biases in the output it is no longer safe to use RC4.";
		case "CAMELLIA":
			return "Camellia is a symmetric key encryption algorithm using 128 or 256 bit keys.";
		case "SEED":
			return "SEED is a symmetric key encryption algorithm using 128 bit keys.";
		case "RC2":
			return "Rivest Cipher 2 is a symmetric key encryption algorithm using 128 bit keys.";
		case "MD5":
			return "MD5 is a cryptographic hash function producing a 16 byte hash. Due to problems with collision resistance it is no longer safe to use.";
		case "RSA":
			return "RSA is a public-key encryption algorithm. To securely use RSA, it is recommended use a key of at least 2048 bits.";
		case "SHA-1":
			return "Secure Hash Algorithm 1 is a cryptographic hash function producing a 20 byte hash. A number of weaknesses in SHA-1 are known and it is no longer recommended.";
		case "SHA-2":
			return "Secure Hash Algorithm 2 is a set of 4 cryptographic hash functions, SHA-224, SHA-256, SHA-384 and SHA-512, producing respectively 28, 32, 48 or 64 byte hashes. There are no known practical weakneses in SHA-2.";
		case "SHA-256":
			return help("SHA-2");
		case "SHA-384":
			return help("SHA-2");
		case "SHA-512":
			return help("SHA-2");
		case "DHE-RSA":
			return "Ephemeral Diffie-Hellman is a key exchange algorithm with forward secrecy. The security depends on the Diffie-Hellman parameters used by the server.";
		case "ECDHE-RSA":
			return "Ephemeral Elliptic Curve Diffie-Hellman is the elliptic curve variant of the Diffie-Hellman key exchange. This algorithm supports forward secrecy. The security depends on the curve chosen by the server.";
		case "AESGCM":
			return "Advanced Encryption Standard using Galois/Counter Mode is an authenticated symmetric key encryption algorithm using 128 or 256 bit keys. This is more efficient and faster compared to normal AES, which uses Cipher-block chaining (CBC).";
		case "AEAD":
			return "Authenticated Encryption with Associated Data algorithms do not require a separate hash function.";
		case "None":
			return "This cipher suite uses no encryption. Server's MUST NOT allow this cipher to be used.";
		default:
			return "...";
	}

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
		<h4 class="page-header"><?= $cert["chain_index"] !== NULL ? "#" . $cert["chain_index"] : "" ?> <?= htmlspecialchars($name) ?></h4>

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
if ($cert["trusted_root"] === 't' && $cert["chain_index"] !== NULL) {
?>
		<div class="alert alert-block alert-warning">
			<strong>Warning:</strong> Trusted root certificate is included in the chain.
		</div>
<?php
}
if ($cert["trusted_root"] === 'f' && $cert["chain_index"] === NULL && $cert["signed_by_id"] !== $cert["certificate_id"]) {
?>
		<div class="alert alert-block alert-danger">
			<strong>Error:</strong> Intermediate certificate was not included in the chain.
		</div>
<?php
}
if ($prev_signed_by_id !== $cert["certificate_id"] && $cert["chain_index"] !== '0') {
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
		<div class="alert alert-block alert-danger">
			<strong>Error:</strong> <?= $error["message"] ?>.
		</div>

<?php
}

if ($cert["private_key"] !== NULL) {
?>
			<div class="alert alert-block alert-danger">
				<strong>Error:</strong> This certificate’s private key is publicly available. The server can be impersonated and traffic can be decrypted when forward-secrecy is not used.
			</div>
<?php
}
?>
		<dl class="dl-horizontal">
			<dt>Signature algorithm</dt>
			<dd><?= $cert["sign_algorithm"] ?><?= $cert["sign_algorithm"] === "md5WithRSAEncryption" && $cert["trusted_root"] !== 't' ? " <span class='label label-danger'>INSECURE</span>" : "" ?></dd>
			<dt>Key size</dt>
			<dd><?= $cert["rsa_bitsize"] ?><?= $cert["rsa_bitsize"] < 2048 ? " <span class='label label-warning'>WEAK</span>" : "" ?></dd>
			<dt>Valid from</dt>
			<dd><?= $cert["notbefore"] ?> UTC <time class="<?= strtotime($cert["notbefore"]) > strtotime("now") ? "text-danger" : "text-muted" ?> timeago" datetime="<?= date("c", strtotime($cert["notbefore"])) ?>"></time></dd>
			<dt>Valid to</dt>
			<dd><?= $cert["notafter"] ?> UTC <time class="<?= strtotime($cert["notafter"]) < strtotime("now") ? "text-danger" : "text-muted" ?> timeago" datetime="<?= date("c", strtotime($cert["notafter"])) ?>"></time></dd>
<?php
if (isset($cert["crl_url"])) {
?>
			<dt>CRL</dt>
			<dd><a href="<?= urlencode($cert["crl_url"]) ?>"><?= htmlspecialchars($cert["crl_url"]) ?></a></dd>
<?php
}
?>
<?php
if (isset($cert["ocsp_url"])) {
?>
						<dt>OCSP</dt>
						<dd><a href="<?= urlencode($cert["ocsp_url"]) ?>"><?= htmlspecialchars($cert["ocsp_url"]) ?></a></dd>
<?php
}

if ($i === 0) {
?>
			<dt>Valid for <?= htmlspecialchars($server_name) ?></dt>
			<dd><span class="label <?= $srv["valid_identity"] === 't' ? "label-success" : "label-danger" ?>"><?= $srv["valid_identity"] === 't' ? "YES" : "NO" ?></span></dd>
<?php } ?>
			<dt>
				<select class="hash-select input-small">
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha1"]) ?>'>SHA-1 hash</option>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha256"]) ?>'>SHA-256 hash</option>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= fp($cert["digest_sha512"]) ?>'>SHA-512 hash</option>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= $cert["subject_key_info"] ?>'>Public key</option>
<?php
if ($cert["private_key"] !== NULL) {
?>
					<option data-hash-taget="#hashfield<?= $i ?>" data-hash='<?= $cert["private_key"] ?>'>Private key</option>
<?php
}
?>
				</select>
			</dt>
			<dd><pre type="text" id="hashfield<?= $i ?>"><?= fp($cert["digest_sha1"]) ?></pre></dd>
		</dl>


		<button class="btn btn-default pem" data-sha-digest="<?= $cert["digest_sha256"] ?>">Show PEM <span class="glyphicon glyphicon-new-window"></span></button>
	<?php
	return $prev_signed_by_id;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$done = TRUE;

foreach ($srvs as $srv) {
	if ($srv["done"] === 'f') {
		$done = FALSE;
		break;
	}
}

$refresh = NULL;

// In the first 30 seconds, always refresh every 5 seconds, as the SRV results still need to come in.
// In the first 15 minutes, refresh every 15 seconds when not yet done.
if (time() - strtotime($result->test_date) < 30) {
	$refresh = 5;
} else if (!$done && time() - strtotime($result->test_date) < 60 * 15) {
	$refresh = 15;
}

if ($refresh !== NULL) {
	common_header("<meta http-equiv='refresh' content='" . $refresh . "'>");
} else {
	common_header("");
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
					<li class="active"><a href="list.php">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
					<li><a href="about.php">About</a></li>
					<li><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">

			<div class="col-md-3">
				<div id="sidebar" class="side-bar" role="complementary">
					<ul class="nav">
						<li class="active"><a href="#score">Score</a></li>
						<li><a href="#general">General</a></li>
						<li>
							<a href="#dns">DNS</a>
							<ul class="nav">
								<li><a href="#srv">SRV</a></li>
								<li><a href="#tlsa">TLSA</a></li>
							</ul>
						</li>
						<li>
							<a href="#tls">TLS</a>
							<ul class="nav">
								<li><a href="#certificates">Certificates</a></li>
								<li><a href="#protocols">Protocols</a></li>
								<li><a href="#ciphers">Ciphers</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-md-9">
<?php
if (!$result) {

?>
		<h1>404</h1>
		<div class="alert alert-block alert-danger">
			Test result could not be found.
		</div>
<?php

} else {

?>

		<h1>IM Observatory <?= $result->type ?> report for <?= htmlspecialchars($result->server_name) ?></h1>
		<p>Test started <?= date('Y-m-d H:i:s T', strtotime($result->test_date)) ?> <span class="text-muted"><time class="timeago" datetime="<?= date("c", strtotime($result->test_date)) ?>"></time></span>.</p>

        <a href='result.php?domain=<?= urlencode($result_domain) ?>&amp;type=<?= $result_type === "client" ? "server" : "client" ?>'>Show <?= $result_type === "client" ? "server" : "client" ?> to server result</a> | <a href='result.php?id=<?= $result->test_id ?>'>Permalink to this report</a>

		<h2 class="page-header" id="score">Score</h2>
<?php

foreach ($srvs as $srv) {

	if (count($srvs) > 1 && $srv === $srvs[1]) {
?>
		<div class="collapse-group">
				<div class="collapse" id="collapse-scores">
<?php
	}
?>

		<h5 title="<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>:<?= $srv["port"] ?>"><?= htmlspecialchars($srv["target"]) ?>:<?= htmlspecialchars($srv["port"]) ?></h5>
<?php
	if ($srv["done"] === 'f') {
		if (time() - strtotime($result->test_date) < 60 * 30) {
?>
		<div class="alert alert-block alert-warning">
			<img src="img/ajax-loader.gif"> Test did not complete successfully or is still in progress. <a class="text-muted" href="about.php#slow">Why is this taking so long?</a>
		</div>
<?php
		} else {
?>
		<div class="alert alert-block alert-danger">
			<strong>Error:</strong> Test failed.
		</div>
<?php
		}
	}
?>
		<div class="row">
			<div class="col-md-10">
				<div class="row">
					<div class="col-md-3 text-right">
						<strong><a href="#certificates">Certificate score:<a/></strong>
					</div>
					<div class="col-md-6">
						<div class="progress">
							<div class="progress-bar <?= color_score_bar($srv["certificate_score"]) ?>" role="progressbar" aria-valuenow="<?= $srv["certificate_score"] ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $srv["certificate_score"] ?>%"></div>
						</div>
					</div>
					<div class="col-md-1">
						<strong class="<?= color_score_text($srv["certificate_score"]) ?>"><?= $srv["certificate_score"] ?></strong>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 text-right">
						<strong><a href="#certificates">Key exchange score:</a></strong>
					</div>
					<div class="col-md-6">
						<div class="progress">
							<div class="progress-bar <?= color_score_bar($srv["keysize_score"]) ?>" role="progressbar" aria-valuenow="<?= $srv["keysize_score"] ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $srv["keysize_score"]?>%"></div>
						</div>
					</div>
					<div class="col-md-1">
						<strong class="<?= color_score_text($srv["keysize_score"]) ?>"><?= $srv["keysize_score"]?></strong>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 text-right">
						<strong><a href="#protocols">Protocol score:</a></strong>
					</div>
					<div class="col-md-6">
						<div class="progress">
							<div class="progress-bar <?= color_score_bar($srv["protocol_score"]) ?>" role="progressbar" aria-valuenow="<?= $srv["protocol_score"] ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $srv["protocol_score"]?>%"></div>
						</div>
					</div>
					<div class="col-md-1">
						<strong class="<?= color_score_text($srv["protocol_score"]) ?>"><?= $srv["protocol_score"]?></strong>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 text-right">
						<strong><a href="#ciphers">Cipher score:</a></strong>
					</div>
					<div class="col-md-6">
						<div class="progress">
							<div class="progress-bar <?= color_score_bar($srv["cipher_score"]) ?>" role="progressbar" aria-valuenow="<?= $srv["protocol_score"] ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $srv["cipher_score"]?>%"></div>
						</div>
					</div>
					<div class="col-md-1">
						<strong class="<?= color_score_text($srv["cipher_score"]) ?>"><?= $srv["cipher_score"]?></strong>
					</div>
				</div>
			</div>

			<div class="col-md-2 text-center">
				<strong>Grade:</strong>
				<div>
					<p class="<?= color_text_score($srv["grade"]) ?>" style="font-size: 1000%; line-height: 100px;"><?= $srv["grade"] ?></p>
				</div>
			</div>
		</div>

<?php
	if ($srv["certificate_score"] === '0' && $srv["done"] === 't') {
?>
				<div class="alert alert-block alert-danger">
						Certificate is <strong>not trusted</strong>, grade capped to <strong>F</strong>. Ignoring trust: <strong><?= $srv["sslv2"] === 't' ? "F" : grade($srv["total_score"]) ?></strong>.
				</div>
<?php
	}
	if ($srv["sslv2"] === 't' && $srv["done"] === 't') {
?>
				<div class="alert alert-block alert-danger">
						Server allows SSLv2, which is obsolete and insecure. Grade capped to <strong>F</strong>.
				</div>
<?php
	}
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn btn-default" data-toggle="collapse" data-target="#collapse-scores">Show all <?= count($srvs) ?> SRV targets <span class="glyphicon glyphicon-collapse-down"></span></a></p>
		</div>
<?php
}
?>

		<h2 class="page-header" id="general">General</h2>
<?php
foreach ($srvs as $srv) {

	if (count($srvs) > 1 && $srv === $srvs[1]) {
?>
		<div class="collapse-group">
				<div class="collapse" id="collapse-general">
<?php
	}
?>

		<h5 title="<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>"><?= htmlspecialchars($srv["target"]) ?>:<?= $srv["port"] ?></h5>
		<dl class="dl-horizontal">
<?php
	if ($result->version) {
?>
			<dt>Version</dt>
			<dd>
				<span class="my-popover" title="" <?= released($result->version) === "" ? "" : "data-content='<strong>" . $result->version . "</strong> was released on " . released($result->version) . "'" ?> data-toggle="popover" data-original-title="<?= $result->version ?>">
					<?= htmlspecialchars($result->version) ?>
				</span>
			</dd>
<?php
	}
	if ($srv["requires_starttls"]) {
?>
			<dt>StartTLS</dt>
			<dd><?= $srv["requires_starttls"] === 't' ? "<span class='label label-success'>REQUIRED</span>" : "<span class='label label-warning'>ALLOWED</span>" ?></dd>
<?php
	}
	if ($srv["compression"]) {
?>
			<dt>TLS compression</dt>
			<dd><?= htmlspecialchars($srv["compression"]) ?></dd>
<?php
	}
	if ($srv["requires_peer_cert"] === 't') {
?>
			<dt>Peer certificate</dt>
			<dd>The server <strong>requires</strong> incoming s2s connections to present a peer certificate.</dd>
<?php
	}
?>
		</dl>
		<br>

		<?php
}
if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn btn-default" data-toggle="collapse" data-target="#collapse-general">Show all <?= count($srvs) ?> SRV targets <span class="glyphicon glyphicon-collapse-down"></span></a></p>
		</div>
<?php
}
?>


		<h2 class="page-header" id="dns">DNS</h2>
		<h3 id="srv">SRV records
			<small title="_xmpp-<?= $result->type ?>._tcp.<?= htmlspecialchars($result->server_name) ?>">_xmpp-<?= $result->type ?>._tcp.<?= idn_to_ascii($result->server_name, "UTF-8") ?>
				<span class="label <?= $result->srv_dnssec_good === 't' ? "label-success" : ($result->srv_dnssec_bogus === 't' ? "label-warning" : "label-default")?>"><?= $result->srv_dnssec_good === 't' ? "" : ($result->srv_dnssec_bogus === 't' ? "BOGUS " : "NO ")?>DNSSEC</span>
			</small>
		</h3>
		<div class="row">
			<div class="col-md-5">
				<table class="table table-bordered table-striped">

					<tr>
						<th>Priority</th>
						<th>Weight</th>
						<th>Port</th>
						<th>Server</th>
					</tr>
<?php

foreach ($srvs as $srv) {
	if ($srv["priority"] === NULL) continue;
?>
					<tr>
						<td><?= htmlspecialchars($srv["priority"]) ?></td>
						<td><?= htmlspecialchars($srv["weight"]) ?></td>
						<td><?= htmlspecialchars($srv["port"]) ?></td>
						<td title="<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>"><?= htmlspecialchars($srv["target"]) ?></td>
					</tr>
<?php
}
?>
				</table>
			</div>
		</div>
	
		<h3 id="tlsa">TLSA records</h3>
<?php

foreach ($srvs as $srv) {

		if (count($srvs) > 1 && $srv === $srvs[1]) {
?>
		<div class="collapse-group">
				<div class="collapse" id="collapse-tlsa">
<?php
		}
?>
		<h4 class="page-header" title="_<?= $srv["port"] ?>._tcp.<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>">_<?= $srv["port"] ?>._tcp.<?= htmlspecialchars($srv["target"]) ?> <span class="label <?= $srv["tlsa_dnssec_good"] === 't' ? "label-success" : ($srv["tlsa_dnssec_bogus"] === 't' ? "label-warning" : "label-default")?>"><?= $srv["tlsa_dnssec_good"] === 't' ? "" : ($srv["tlsa_dnssec_bogus"] === 't' ? "BOGUS " : "NO ")?>DNSSEC</span></h4>

		<table class="table table-bordered table-striped">
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
			<td><span class="label label-<?= $tlsa["verified"] === 't' ? "success" : "warning" ?>"><?= $tlsa["verified"] === 't' ? "YES" : "NO" ?></span></td>
			<td><?= tlsa_usage($tlsa["usage"]) ?></td>
			<td><?= tlsa_selector($tlsa["selector"]) ?></td>
			<td><?= tlsa_match($tlsa["match"]) ?></td>
<?php
		if ($tlsa["match"] == 0) {
?>
			<td>
				<div class="collapse-group">
					<pre class="collapse" id="collapse-tlsa-record-<?= $tlsa["tlsa_record_id"] ?>" style="height: 100px; overflow: hidden;"><?= pg_unescape_bytea($tlsa["data"]) ?></pre>
					<p><a class="btn btn-default" data-toggle="collapse" data-target="#collapse-tlsa-record-<?= $tlsa["tlsa_record_id"] ?>">Show full <span class="glyphicon glyphicon-collapse-down"></span></a></p>
				</div>
			</td>
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
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn btn-default" data-toggle="collapse" data-target="#collapse-tlsa">Show all <?= count($srvs) ?> SRV targets <span class="glyphicon glyphicon-collapse-down"></span></a></p>
		</div>
<?php
}
?>
		
		<h2 class="page-header" id="tls">TLS</h2>
<?php

$i = 0;

foreach ($srvs as $srv) {
	if (count($srvs) > 1 && $srv === $srvs[1]) {
?>
		<div class="collapse-group">
			<div class="collapse" id="collapse-certs">
<?php
	}
?>		
		<h3 class="page-header" title="<?= htmlspecialchars(idn_to_utf8($srv["target"])) ?>:<?= $srv["port"] ?>"><?= htmlspecialchars($srv["target"]) ?>:<?= htmlspecialchars($srv["port"]) ?></h3>

<?php

	$res = pg_execute($dbconn, "find_certs", array($srv["srv_result_id"]));

	$certs = pg_fetch_all($res);

	$cert = $certs[0];

	if (!$cert) {
?>
		<div class="alert alert-block alert-danger">
			<strong>Error:</strong> Connection failed.
		</div>
<?php
	} else {
?>
		<h3<?= $srv === $srvs[0] ? " id='certificates'" : "" ?>>Certificates</h3>
<?php
		$prev_signed_by_id = NULL;

		while (true) {

			$index = NULL;

			foreach ($certs as $k => $v) {
				if ($v["digest_sha512"] === $cert["digest_sha512"]) {
					$index = $k;
					break;
				}
			}

			$res = pg_execute($dbconn, "find_errors", array($cert["srv_certificates_id"]));

			$errors = pg_fetch_all($res);

			show_cert($dbconn, $cert, $errors ? $errors : array(), $prev_signed_by_id, $result->server_name, $srv, $i);

			$prev_signed_by_id = $cert["signed_by_id"];

			if ($prev_signed_by_id === NULL) {
				break;
			}

			if ($cert["signed_by_id"] === $cert["certificate_id"]) {
				break;
			}

			$cert = NULL;

			foreach ($certs as $k => $v) {
				if ($v["certificate_id"] === $prev_signed_by_id) {
					$cert = $v;
					break;
				}
			}

			if (!$cert) {
				// First, we look if the certificate was in our trust store, maybe under a different ID.
				$res = pg_execute($dbconn, "find_root", array($prev_signed_by_id));
			
				$cert = pg_fetch_assoc($res);

				// If not, just grab the certificate that we think signed it.
				if (!$cert) {
					$res = pg_execute($dbconn, "find_cert", array($prev_signed_by_id));
			
					$cert = pg_fetch_assoc($res);
				} else {
					$prev_signed_by_id = $cert["certificate_id"];
				}
			}

			if (!$cert) {
				break;
			}

			$i = $i + 1;

			if ($i > 10) {
				break;
			}
		}
?>

		<h3<?= $srv === $srvs[0] ? " id='protocols'" : "" ?>>Protocols</h3>

		<div class="row">
			<div class="col-md-4">
				<table class="table table-bordered table-striped">
					<tr>
						<td><abbr class="my-popover" title="" data-content="SSLv2 is old, obsolete and insecure. Servers <strong>must not</strong> allow it to be used." data-toggle="popover" data-original-title="SSLv2">SSLv2</abbr></td>
						<td><span class="label <?= $srv["sslv2"] === 't' ? "label-danger" : ($srv["sslv2"] === 'f' ? "label-success" : "label-default") ?>">
							<?= show_bool($srv["sslv2"]) ?>
						</span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="SSLv3 is old and not recommended. Servers <strong>should not</strong> allow it to be used." data-toggle="popover" data-original-title="SSLv3">SSLv3</abbr></td>
						<td><span class="label <?= $srv["sslv3"] === 't' ? "label-warning" : ($srv["sslv3"] === 'f' ? "label-success" : "label-default") ?>">
							<?= show_bool($srv["sslv3"]) ?>
						</span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="Although replaced by TLSv1.1 and TLSv1.2, it is <strong>recommended</strong> to support TLSv1 for compatibility with older clients. There are no known security issues with TLSv1.0 for XMPP." data-toggle="popover" data-original-title="TLSv1">TLSv1</abbr></td>
						<td><span class="label label-default">
							<?= show_bool($srv["tlsv1"]) ?>
						</span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="There are no known security issues with TLSv1.1." data-toggle="popover" data-original-title="TLSv1.1">TLSv1.1</abbr></td>
						<td><span class="label <?= $srv["tlsv1_1"] === 't' ? "label-success" : ($srv["sslv2"] === 'f' ? "label-danger" : "label-default") ?>">
							<?= show_bool($srv["tlsv1_1"]) ?>
						</span></td>
					</tr>
					<tr>
						<td><abbr class="my-popover" title="" data-content="TLSv1.2 is the latest version and it is <strong>strongly recommended</strong> that servers support it as it adds a number of newer cipher suites." data-toggle="popover" data-original-title="TLSv1.2">TLSv1.2</abbr></td>
						<td><span class="label <?= $srv["tlsv1_2"] === 't' ? "label-success" : ($srv["sslv2"] === 'f' ? "label-danger" : "label-default" ) ?>">
							<?= show_bool($srv["tlsv1_2"]) ?>
						</span></td>
					</tr>
				</table>
			</div>
		</div>

		<h3<?= $srv === $srvs[0] ? " id='ciphers'" : "" ?>>Ciphers</h3>

<?php
		if ($srv["reorders_ciphers"] !== NULL) {
?>

		<p>Server does <?= $srv["reorders_ciphers"] === 't' ? "<strong>not</strong> " : "" ?>respect the client's cipher ordering.</p>

		<div class="row">
			<div class="col-md-9">
				<table class="table table-bordered table-striped">
					<tr><th>Cipher suite</th><th>Bitsize</th><th>Forward secrecy</th></tr>
<?php
			if ($srv["reorders_ciphers"] === 't') {
				$res = pg_execute($dbconn, "find_ciphers", array($srv["srv_result_id"]));
			} else {
				$res = pg_execute($dbconn, "find_and_sort_ciphers", array($srv["srv_result_id"]));
			}

			$ciphers = pg_fetch_all($res);

			foreach($ciphers as $cipher) {
?>
					<tr>
						<td>
							<abbr class="my-popover" title="" data-content="<strong><?= ($cipher["authentication"] !== $cipher["key_exchange"] ? $cipher["key_exchange"] . "-" : "") . $cipher["authentication"] ?></strong>: <?= help(($cipher["authentication"] !== $cipher["key_exchange"] ? $cipher["key_exchange"] . "-" : "") . $cipher["authentication"]) ?><br><strong><?= $cipher["symmetric_alg"] ?></strong>: <?= help($cipher["symmetric_alg"]) ?><br><strong><?= $cipher["hash_alg"] ?></strong>: <?= help($cipher["hash_alg"]) ?>" data-toggle="popover" data-original-title="<?= $cipher["openssl_name"] ?>"><?= $cipher["openssl_name"] ?></abbr> <span class="text-muted">(0x<?= dechex($cipher["cipher_id"]) ?>)</span><?= $cipher["export"] === 't' || $cipher["bitsize"] < 64 ? " <span class=\"label label-danger\">VERY WEAK</span>" : ($cipher["bitsize"] < 128 ? " <span class=\"label label-warning\">WEAK</span>" : "") ?>
						</td>
						<td>
							<span class="label <?= color_bitsize($cipher["bitsize"]) ?>"><?= $cipher["bitsize"] ?></span>
						</td>
						<td>
							<span class="label label-<?= $cipher["forward_secret"] === 't' ? "success" : "danger" ?>"><?= $cipher["forward_secret"] === 't' ? "Yes" : "No" ?></span>
						</td>
					</tr>
<?php
			}
?>
				</table>
			</div>
		</div>
<?php
		} else {
?>
		<div class="alert alert-block alert-warning">
			<strong>Warning:</strong> Still in progress.
		</div>
<?php
		}
	}
}

if (count($srvs) > 1) {
?>
				</div>
			<p><a class="btn btn-default" data-toggle="collapse" data-target="#collapse-certs">Show all <?= count($srvs) ?> SRV targets <span class="glyphicon glyphicon-collapse-down"></span></a></p>
		</div>
<?php
}

}
?>
			</div>
		</div> <!-- /row -->

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
