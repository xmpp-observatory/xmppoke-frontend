<?php

$dbconn = pg_connect("port=5433 host=localhost dbname=xmppoke user=xmppoke password=xmppoke") or die('Could not connect: ' . pg_last_error());

function color_label_text_grade($score) {
	switch ($score) {
		case 'A':
			return "label-success";
		case 'B':
		case 'C':
			return "label-warning";
		case NULL:
			return "label-default";
		default:
			return "label-danger";
	}
}

function color_text_grade($score) {
	switch ($score) {
		case 'A':
			return "text-success";
		case 'B':
		case 'C':
			return "text-warning";
		case NULL:
			return "";
		default:
			return "text-danger";
	}
}

function color_text_score($score) {
	switch ($score) {
		case 'A':
			return "text-success";
		case 'B':
		case 'C':
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

function common_header() {
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>XMPPoke results</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="css/bootstrap-sortable.css" rel="stylesheet" media="screen">

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<style type="text/css">
		body {
			padding-top: 50px;
		}

		td .label, dd .label {
			font-size: 90%;
		}

		h2[id], h3[id] {
			margin-top: -45px;
			padding-top: 80px;
		}

		@media screen and (min-width: 992px) {
			.side-bar {
				position: fixed;
				top: 80px;
				width: 200px;
			}
		}

		.side-bar {
			border-radius: 5px 5px 5px 5px;
		}

		.side-bar .active {
			background-color: #f8f8f8;
		}

		.side-bar .nav .nav {
			display: none;
		}

		.side-bar .nav > .active > ul {
			display: block;
		}

		.side-bar .nav > .active > ul > li > a {
			padding-left: 30px;
			font-size: smaller;
		}
		</style>

		<link rel="shortcut icon" href="./ico/favicon.png">
	</head>

<?php

}