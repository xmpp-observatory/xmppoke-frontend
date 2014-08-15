<?php

include("common.php");

header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");

header("Content-Type: image/svg+xml");

$result_domain = idn_to_utf8(strtolower(idn_to_ascii($_GET['domain'])));

if (isset($result_domain)) {

	pg_prepare($dbconn, "find_result", "SELECT * FROM test_results WHERE server_name = $1 AND type = $2 AND EXISTS (SELECT 1 FROM srv_results WHERE srv_results.test_id = test_results.test_id AND done = 't' AND error IS NULL) ORDER BY test_date DESC LIMIT 1");

	$res = pg_execute($dbconn, "find_result", array($result_domain, "client"));

	$result_c2s = pg_fetch_object($res);

	$res = pg_execute($dbconn, "find_result", array($result_domain, "server"));

	$result_s2s = pg_fetch_object($res);

	pg_prepare($dbconn, "find_srvs", "SELECT * FROM srv_results WHERE test_id = $1");

	$res = pg_execute($dbconn, "find_srvs", array($result_c2s->test_id));

	$c2s_srvs = pg_fetch_all($res);

	$res = pg_execute($dbconn, "find_srvs", array($result_s2s->test_id));

	$s2s_srvs = pg_fetch_all($res);

	$c2s_final_score = NULL;
	$s2s_final_score = NULL;

	foreach ($c2s_srvs as $score) {
		if (grade($score) && (!$c2s_final_score || grade($score) < $c2s_final_score)) {
			$c2s_final_score = grade($score);
		}
	}
	foreach ($s2s_srvs as $score) {
		if (grade($score) && (!$s2s_final_score || grade($score) < $s2s_final_score)) {
			$s2s_final_score = grade($score);
		}
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function svg_color($score) {
	switch ($score[0]) {
		case 'A': return "#5CB85C";
		case 'B':
		case 'C':
		case 'T':
		case 'D': return "#EC971F";
		case 'E':
		case 'F':
		default: return "#C9302C";
	}
}

echo '<?xml version="1.0" standalone="no"?>';

?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" width="201" height="18" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1">
	<linearGradient id="a" x2="0" y2="100%">
		<stop offset="0" stop-color="#fff" stop-opacity=".7"/>
		<stop offset=".1" stop-color="#aaa" stop-opacity=".1"/>
		<stop offset=".9" stop-opacity=".3"/>
		<stop offset="1" stop-opacity=".5"/>
	</linearGradient>
	<rect rx="4" width="201" height="18" fill="#555"/>
	<rect x="99" width="51" height="18" fill="<?= svg_color($c2s_final_score) ?>"/>
	<rect rx="4" x="150" width="51" height="18" fill="<?= svg_color($s2s_final_score) ?>"/>
	<path fill="<?= svg_color($s2s_final_score) ?>" d="M150 0h4v18h-4z"/>
	<rect rx="4" width="201" height="18" fill="url(#a)"/>
	<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
		<a xlink:href="https://xmpp.net/result.php?domain=<?= urlencode($result_domain) ?>&amp;type=client">
			<text x="50.5" y="14" fill="#010101" fill-opacity=".3">xmpp.net score</text>
			<text x="50.5" y="13">xmpp.net score</text>
			<text x="123.5" y="14" fill="#010101" fill-opacity=".3">c2s: <?= $c2s_final_score ? $c2s_final_score : "-"  ?></text>
			<text x="123.5" y="13">c2s: <?= $c2s_final_score ? $c2s_final_score : "-"  ?></text>
		</a>
		<a xlink:href="https://xmpp.net/result.php?domain=<?= urlencode($result_domain) ?>&amp;type=server">
			<text x="174.5" y="14" fill="#010101" fill-opacity=".3">s2s: <?= $s2s_final_score ? $s2s_final_score : "-"  ?></text>
			<text x="174.5" y="13">s2s: <?= $s2s_final_score ? $s2s_final_score : "-"  ?></text>
		</a>
	</g>
</svg>