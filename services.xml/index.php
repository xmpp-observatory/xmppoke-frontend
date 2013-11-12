<?php
echo "<?xml version='1.0'?>";
?>
<query xmlns:reg="urn:xmpp:vcard:registration:1">
<?php

include("../common.php");

pg_prepare($dbconn, "list_server", "SELECT server_name FROM public_servers ORDER BY server_name;");

$res = pg_execute($dbconn, "list_server", array());

$list = pg_fetch_all($res);

foreach ($list as $result) {
?>
	<item jid='<?= $res["server_name"] ?>'/>
<?php
}

?>
</query>