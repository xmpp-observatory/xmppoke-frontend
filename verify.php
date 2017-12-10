<?php

include("common.php");

common_header("");

?>
	<body data-spy="scroll" data-target="#sidebar">

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">IM Observatory</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li><a href="list.php">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
					<li class="active"><a href="#">About</a></li>
					<li><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div>
			<h2>Verification</h2>

<p>When a server administrator <a href='register.php'>registers</a> a <a href='directory.php'>public XMPP service</a> with xmpp.net, we verify the registration by sending email to the the hostmaster/postmaster/webmaster email addresses for the root domain. (By "root domain" we mean the lowest-level domain that can be looked up in whois &mdash; e.g., if the XMPP service is running at im.example.com then we contact the owners and admins for example.com.) We also check the server's test results on xmpp.net and visit the website of the service to make sure that it is accurate, provides contact information, etc.</p>
		</div>

		<div class="footer">
			<p>Some rights reserved.</p>
		</div>
	</div> <!-- /container -->

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="./js/jquery.js"></script>
	<script src="./js/jquery.timeago.js"></script>
	<script src="./js/bootstrap.min.js"></script>

	<script src="./js/main.js"></script>

	</body>
</html>
