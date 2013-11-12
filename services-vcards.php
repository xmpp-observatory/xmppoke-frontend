<?php
header("Content-Type: text/xml");
?>
<vcards xmlns='urn:ietf:params:xml:ns:vcard-4.0'>
<?php

include("common.php");

pg_prepare($dbconn, "list_server", "SELECT * FROM public_servers ORDER BY server_name;");

$res = pg_execute($dbconn, "list_server", array());

$list = pg_fetch_all($res);

pg_prepare($dbconn, "find_cn", "SELECT value FROM srv_results, srv_certificates, certificate_subjects WHERE test_id = (SELECT test_id FROM test_results WHERE server_name = $1 ORDER BY test_date DESC LIMIT 1) AND srv_results.srv_result_id = srv_certificates.srv_result_id AND srv_certificates.certificate_id = certificate_subjects.certificate_id AND chain_index = $2 AND certificate_subjects.name = 'commonName';");

pg_prepare($dbconn, "find_version", "SELECT version FROM test_results WHERE server_name = $1 ORDER BY test_date DESC LIMIT 1;");

foreach ($list as $result) {
	$res = pg_execute($dbconn, "find_cn", array($result["server_name"], 1));

	$cn = pg_fetch_assoc($res);

	if ($cn === NULL) {
		$res = pg_execute($dbconn, "find_cn", array($result["server_name"], 0));

		$cn = pg_fetch_assoc($res);
	}

	$res = pg_execute($dbconn, "find_version", array($result["server_name"]));

	$version = pg_fetch_assoc($res);
?>
  <vcard>
  <fn>
    <text><?= $result["server_name"] ?></text>
  </fn>
  <kind>
    <text>application</text>
  </kind>
  <url>
    <uri><?= $result["url"] ?></uri>
  </url>
  <note>
    <text><?= $result["description"] ?></text>
  </note>
  <bday>
    <date><?= $result["founded"] ?></date>
  </bday>
  <adr>
    <country><?= $result["country"] ?></country>
  </adr>
  <ca xmlns="urn:xmpp:vcard:ca:0">
    <name><?= $cn["value"] ?></name>
  </ca>
  <name xmlns="jabber:iq:version"><?= $version["version"] ?></name>
  <impp>
    <uri><?= $result["admin"] ?></uri>
  </impp>
</vcard>
<?php
}

?>
</vcards>