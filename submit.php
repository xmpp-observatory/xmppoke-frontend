<?php

include("common.php");

$domain = $_POST["domain"];
$type = $_POST["mode"];

if(preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", idn_to_ascii($domain)) && ($type === "c2s" || $type === "s2s")) {

	if ($type === "c2s") {
		$type = "client";
	} else {
		$type = "server";
	}

	$out = array();

	exec("/opt/xmppoke/bin/luajit /opt/xmppoke/bin/xmppoke --cafile=/etc/ssl/certs/ca-certificates.crt --db_password='" . escapeshellarg($dbpass) . "' --mode=$type -d=10 '" . escapeshellarg($domain) . "'", &$out);

	print_r($out);

	// header("Location: result.php?domain=$domain&type=$type");

} else {
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
					<li class="active"><a href="list.php">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">

		<h1>Error</h1>

		<div class="alert alert-block alert-danger">
			"<?= htmlspecialchars($domain) ?>" is not a valid domain name.
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

	</body>
</html>

<?php
}