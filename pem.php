<?php

$digest = $_GET['sha256'];

include("common.php");

if (isset($digest)) {
	pg_prepare($dbconn, "find_cert", "SELECT pem FROM certificates WHERE digest_sha256 = $1 LIMIT 1;");

	$res = pg_execute($dbconn, "find_cert", array($digest));

	$pem = pg_fetch_assoc($res);

	header('Content-Type: text/plain');

	echo $pem["pem"];
}