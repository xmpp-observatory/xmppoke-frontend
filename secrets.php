<?php
$dbport = $_ENV["POSTGRES_PORT"];
$dbhost = $_ENV["POSTGRES_HOST"];
$dbname = $_ENV["DB_NAME"];
$dbuser = $_ENV["DB_USER"];
$dbpass = $_ENV["DB_PASS"];
$queue_url = $_ENV["QUEUE_URL"] or "http://localhost:1337";