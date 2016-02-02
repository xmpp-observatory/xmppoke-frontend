<?php

include("secrets.php");

header("Cache-Control: max-age=10");

date_default_timezone_set('UTC');
setlocale(LC_CTYPE, "UTF8", "en_US.UTF-8");

$dbconn = pg_connect("port=$dbport host=$dbhost dbname=$dbname user=$dbuser password=$dbpass") or die('Could not connect: ' . pg_last_error());

pg_prepare($dbconn, "timezone", "SET timezone = 'UTC'");

pg_execute($dbconn, "timezone", array());

function fp($x) {
	return strtoupper(join(':', str_split($x, 2)));
}

function grade($srv) {
	if ($srv === NULL || $srv["done"] !== "t" || $srv["error"] !== NULL) {
		return NULL;
	}

	$grade = $srv["grade"];

	if ($srv["warn_rc4_tls11"] === 't' && $grade === "A") {
		$grade = "A⁻";
	}
	if ($srv["warn_no_fs"] === 't' && $grade === "A") {
		$grade = "A⁻";
	}

	if ($srv["certificate_score"] == 0 && $grade === "A") {
		$grade = "T";
	} elseif ($srv["certificate_score"] == 0) {
		$grade = "F";
	}

	return $grade;
}

function color_label_text_grade($score) {
	switch ($score[0]) {
		case 'A':
			return "label-success";
		case 'B':
		case 'C':
		case 'T':
			return "label-warning";
		case NULL:
			return "label-default";
		default:
			return "label-danger";
	}
}

function color_text_grade($score) {
	switch ($score[0]) {
		case 'A':
			return "text-success";
		case 'B':
		case 'C':
		case 'T':
			return "text-warning";
		case NULL:
			return "";
		default:
			return "text-danger";
	}
}

function color_score_text($score) {
	if ($score >= 80) {
		return " text-success";
	} else if ($score >= 40) {
		return " text-warning";
	}
	return " text-danger";
}

function released($software) {
		switch ($software) {
		case "Isode M-Link 16.0v4":
			return "2013/06/24";
		
		case "Metronome 2.9":
			return "2013/10/02";
		case "Metronome 2.9.16":
			return "2013/10/16";
		case "Metronome 2.9.27":
			return "2013/10/27";
		case "Metronome 3.0":
			return "2013/10/29";
		case "Metronome 3.0.6":
			return "2013/11/05";
		case "Metronome 3.3.3":
			return "2014/01/14";
		case "Metronome 3.7":
			return "2015/03/09";
		
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
		case "Openfire 3.9.0":
			return "2014/02/05";
		case "Openfire 3.9.3":
			return "2014/05/06";
		case "Openfire 3.10.3":
			return "2015/11/18";
		case "Openfire 4.0.0":
			return "2016/01/11";
		case "Openfire 4.0.1":
			return "2016/01/26";

		case "ejabberd 2.0.5":
			return "2009/04/03";
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
		case "ejabberd 13.03-beta2":
			return "2013/03/29";
		case "ejabberd 14.07":
			return "2014/07/23";
		case "ejabberd 14.12":
			return "2014/12/17";
		case "ejabberd 15.02":
			return "2015/02/17";
		case "ejabberd 15.03":
			return "2015/03/20";
		case "ejabberd 15.04":
			return "2015/04/27";
		case "ejabberd 15.06":
			return "2015/06/25";
		case "ejabberd 15.07":
			return "2015/07/29";
		case "ejabberd 15.09":
			return "2015/09/30";
		case "ejabberd 15.10":
			return "2015/10/29";
		case "ejabberd 15.11":
			return "2015/11/30";
		case "ejabberd 16.01":
			return "2016/01/13";
		
		case "jabberd2 2.2.1":
			return "2008/07/30";
		case "jabberd2 2.2.8":
			return "2009/04/24";
		case "jabberd 2.2.13":
			return "2011/02/23";
		case "jabberd 2.2.14":
			return "2011/05/31";
		case "jabberd 2.2.16":
			return "2012/05/04";
		case "jabberd 2.2.17":
		case "jabberd 2.2.17-399":
			return "2012/08/26";
		case "jabberd 2.3.2":
			return "2014/02/24";
		case "jabberd 2.3.4":
			return "2015/10/30";

		
		case "Prosody 0.7.0rc1":
			return "2010/03/02";
		case "Prosody 0.8.0":
			return "2011/04/07";
		case "Prosody 0.8.2":
			return "2011/06/20";
		case "Prosody hg:6f4c8af128e2":
			return "2013/09/06";
		case "Prosody 0.9.0":
			return "2013/08/20";
		case "Prosody 0.9.1":
			return "2013/09/10";
		case "Prosody 0.9.2":
			return "2014/01/07";
		case "Prosody 0.9.3":
			return "2014/02/10";
		case "Prosody 0.9.4":
			return "2014/04/02";
		case "Prosody 0.9.5":
			return "2014/10/09";
		case "Prosody 0.9.6":
			return "2014/11/15";
		case "Prosody 0.9.7":
			return "2014/11/24";
		case "Prosody 0.9.8":
			return "2015/03/26";
		case "Prosody 0.9.9":
			return "2016/01/08";
		case "Prosody 0.9.10":
			return "2016/01/27";
		
		case "Tigase 5.1.4-b3001":
			return "2013/01/14";
	}

	if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $software, $match)) {
		return $match[1] . "/" . $match[2] . "/" . $match[3];
	}

	return "";
}

function common_header($head) {
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>IM Observatory</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="css/bootstrap-sortable.css" rel="stylesheet" media="screen">

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="css/main.css">

		<link rel="shortcut icon" href="./ico/favicon.png">

		<link rel="alternate" type="application/rss+xml" title="RSS" href="https://xmpp.net/rss.php">

		<?= $head ? $head : "" ?>

	</head>

<?php

}
