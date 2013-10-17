<?php

include("common.php");

pg_prepare($dbconn, "list_server", "SELECT * FROM public_servers ORDER BY server_name;");

pg_prepare($dbconn, "find_s2s", "SELECT * FROM test_results WHERE server_name = $1 AND type = 'server' ORDER BY test_date DESC LIMIT 1;");

pg_prepare($dbconn, "find_c2s", "SELECT * FROM test_results WHERE server_name = $1 AND type = 'client' ORDER BY test_date DESC LIMIT 1;");

pg_prepare($dbconn, "find_srvs", "SELECT * FROM srv_results WHERE test_id = $1 ORDER BY priority ASC;");

pg_prepare($dbconn, "find_cert", "SELECT * FROM srv_certificates, certificates WHERE srv_certificates.certificate_id = certificates.certificate_id AND srv_certificates.srv_result_id = $1;");

pg_prepare($dbconn, "find_cn", "SELECT * FROM certificate_subjects WHERE certificate_subjects.certificate_id = $1 AND (certificate_subjects.name = 'commonName' OR certificate_subjects.name = 'organizationName') ORDER BY certificate_subjects.name LIMIT 1;");

pg_prepare($dbconn, "find_score", "SELECT DISTINCT ON (grade) grade, total_score FROM srv_results WHERE test_id = $1;");

$res = pg_execute($dbconn, "list_server", array());

$list = pg_fetch_all($res);

common_header();

// Maybe this should be in a db. Or a file.
function released($software) {
        switch ($software) {
        case "Isode M-Link 16.0v4":
            return "2013/06/24";
        
        case "Metronome 2.9":
            return "2013/10/02";
        case "Metronome 2.9.16":
        	return "2013/10/16";
        
        case "Openfire 3.6.4":
        	return "2009/05/01";
        case "Openfire 3.7.1":
            return "2011/10/01";
        case "Openfire 3.8.0":
            return "2013/02/06";
        case "Openfire 3.8.1":
            return "2013/03/03";
        case "Openfire 3.8.2":
            return "2013/05/28";

        case "ejabberd 2.1.2":
            return "2010/01/18";
        case "ejabberd 2.1.5":
            return "2010/08/03";
        case "ejabberd 2.1.9":
            return "2011/10/3";
        case "ejabberd 2.1.10":
        case "ejabberd 2.1.10 Jabbim I need Holidays Edition":
			return "2011/12/24";
		case "ejabberd 2.1.11":
			return "2012/05/04";
		case "ejabberd 2.1.12":
			return "2013/02/05";
		case "ejabberd 2.1.13":
            return "2013/06/28";
        
        case "jabberd 2.2.14":
            return "2011/05/31";
		case "jabberd 2.2.16":
            return "2012/05/04";
        case "jabberd 2.2.17":
        	return "2012/08/26";

        
        case "Prosody 0.8.0":
            return "2011/04/07";
        case "Prosody hg:6f4c8af128e2":
            return "2013/09/06";
		case "Prosody 0.9.1":
			return "2013/09/10";
		
		case "Tigase 5.1.4-b3001":
			return "2013/01/14";
	}

	if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $software, $match)) {
		return $match[1] . "/" . $match[2] . "/" . $match[3];
	}

	return "";
}

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
					<li class="active"><a href="#">Public server directory</a></li>
					<li><a href="about.php">About</a></li>
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

		<h1>Public XMPP Server Directory</h1>

		<table class="table table-bordered table-striped sortable">
			<thead>
				<tr>
					<th data-defaultsort="asc">Domain</th>
					<th>Founded</th>
					<th>Country</th>
					<th>CA</th>
					<th>Software</th>
					<th>Grade c2s</th>
					<th>Grade s2s</th>
				</tr>
			</thead>
			<tbody>
<?php

foreach ($list as $result) {

	$issuer = NULL;

	$res = pg_execute($dbconn, "find_s2s", array($result["server_name"]));

	$s2s = pg_fetch_assoc($res);

	$res = pg_execute($dbconn, "find_score", array($s2s["test_id"]));

	$s2s_scores = pg_fetch_all($res);

	$res = pg_execute($dbconn, "find_c2s", array($result["server_name"]));

	$c2s = pg_fetch_assoc($res);

	$res = pg_execute($dbconn, "find_score", array($c2s["test_id"]));

	$c2s_scores = pg_fetch_all($res);

	$res = pg_execute($dbconn, "find_srvs", array($c2s["test_id"]));

	$srvs = pg_fetch_all($res);

	if (pg_num_rows($res) > 0) {
		$res = pg_execute($dbconn, "find_cert", array($srvs[0]["srv_result_id"]));

		$cert = pg_fetch_assoc($res);

		if ($cert["signed_by_id"] !== NULL) {
			$res = pg_execute($dbconn, "find_cn", array($cert["signed_by_id"]));

			$issuer = pg_fetch_assoc($res);
		} else {
			$res = pg_execute($dbconn, "find_cn", array($cert["certificate_id"]));

			$issuer = pg_fetch_assoc($res);
		}
	}

	$c2s_final_score = NULL;
	$s2s_final_score = NULL;

	foreach ($c2s_scores as $score) {
		if ($score["grade"] && (!$c2s_final_score || $score["grade"] < $c2s_final_score)) {
			$c2s_final_score = $score["grade"];
		}
	}
	foreach ($s2s_scores as $score) {
		if ($score["grade"] && (!$s2s_final_score || $score["grade"] < $s2s_final_score)) {
			$s2s_final_score = $score["grade"];
		}
	}
?>
			<tr>
				<td>
					<a class="my-popover" data-content="<?= $result["description"] ?>" data-toggle="popover" data-original-title="<?= htmlspecialchars($result["server_name"]) ?>" href="<?= $result["url"] ?>">
						<?= htmlspecialchars($result["server_name"]) ?> <span class="glyphicon glyphicon-link"></span>
					</a>
				</td>
				<td>
					<?= $result["founded"] ?>
				</td>
				<td>
					<?= $result["country"] ?>
				</td>
				<td>
					<span<?= $srvs[0]["certificate_score"] !== '100' ? " class='text-danger'" : ""?>><?= htmlspecialchars($issuer["value"]) ?></span>
				</td>
				<td data-value="<?= released($c2s["version"]) ?>">
					<span class="my-popover" title="" <?= released($c2s["version"]) === "" ? "" : "data-content='<strong>" . $c2s["version"] . "</strong> was released on " . released($c2s["version"]) . "'" ?> data-toggle="popover" data-original-title="<?= $c2s["version"] ?>">
						<?= htmlspecialchars($c2s["version"]) ?>
					</span>
				</td>
				<td data-value="<?= $c2s_final_score ? $c2s_final_score : "G" ?>">
					<a class="label <?= color_label_text_grade($c2s_final_score) ?>" href="result.php?domain=<?= $result["server_name"] ?>&amp;type=client">
						<?= $c2s_final_score ? $c2s_final_score : "?" ?>
					</a>
				</td>
				<td data-value="<?= $s2s_final_score ? $s2s_final_score : "G" ?>">
					<a class="label <?= color_label_text_grade($s2s_final_score) ?>" href="result.php?domain=<?= $result["server_name"] ?>&amp;type=server">
						<?= $s2s_final_score ? $s2s_final_score : "?" ?>
					</a>
				</td>
			</tr>
<?php
}
?>
		</tbody>
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
	<script src="./js/bootstrap.js"></script>
	<script src="./js/bootstrap-sortable.js"></script>

	<script src="./js/main.js"></script>

	</body>
</html>
