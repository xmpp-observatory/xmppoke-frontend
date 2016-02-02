<?php

include("common.php");

header("Content-Type: text/xml");
header("Cache-Control: max-age=1800");

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

echo "<?xml version='1.0'?>\n";
?>
<query xmlns:reg="urn:xmpp:vcard:registration:1">
<?php

pg_prepare($dbconn, "list_server", "SELECT server_name FROM public_servers ORDER BY server_name;");

$res = pg_execute($dbconn, "list_server", array());

$list = pg_fetch_all($res);

foreach ($list as $result) {
?>
<item jid='<?= preg_replace('/\s+/', '', $result["server_name"]) ?>'/>
<?php
}

?></query>