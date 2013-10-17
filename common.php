<?php

include("secrets.php");

date_default_timezone_set('UTC');
setlocale(LC_CTYPE, "UTF8", "en_US.UTF-8");

$dbconn = pg_connect("port=$dbport host=$dbhost dbname=$dbname user=$dbuser password=$dbpass") or die('Could not connect: ' . pg_last_error());

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

		<?= $head ? $head : "" ?>

	</head>

<?php

}