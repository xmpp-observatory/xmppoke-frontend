<?php

$digest = $_GET['sha256'];

if (isset($digest)) {
	$dbconn = pg_connect("port=5433 host=localhost dbname=xmppoke user=xmppoke password=xmppoke") or die('Could not connect: ' . pg_last_error());

	pg_prepare($dbconn, "find_cert", "SELECT pem FROM certificates WHERE digest_sha256 = $1 LIMIT 1;");

	$res = pg_execute($dbconn, "find_cert", array($digest));

	$pem = pg_fetch_assoc($res);

	header('Content-Type: text/plain');

	echo $pem["pem"];
}