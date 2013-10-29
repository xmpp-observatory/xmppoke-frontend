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
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div>
			<h2>About</h2>
			<p>
				This service enables XMPP users and server administrators to inspect the security of their servers. It can test the TLS configuration and the DNSSEC deployment of XMPP servers, give warnings about issues with certificate chains, show the list of ciphersuites used by a server and their strength, check DANE records, and many more.
			</p>
			<p>
				Every server is given a grade from A to F, both for their client-to-server and server-to-server TLS configuration. The grades are based on the same principles as the tests of SSL Labs, <a href="https://www.ssllabs.com/projects/rating-guide/index.html">https://www.ssllabs.com/projects/rating-guide/index.html</a> for details. Scoring 100 on every test is not the goal: this will lead to incompatibility with many XMPP clients. Scoring an A, on the other hand, does not mean that security cannot be improved. For instance: mandatory channel encryption, forward secrecy, and DNSSEC do not (yet) count toward the grade.
			</p>
			
			<h3>XMPPoke</h3>
			<p>
				The backend of this service is provided by XMPPoke, which can be found on <a href="https://bitbucket.org/xnyhps/xmppoke">https://bitbucket.org/xnyhps/xmppoke</a>.
			</p>

			<h3>Frequently Asked Questions</h3>
			<strong id="slow">Why is the test so slow?</strong>
			<p>
				The test needs to make a large number of connections to the server to determine what it supports: one connection for every protocol and one for every cipher it supports. Other tests, like determining the cipher order require some extra connections. Making 30 connections to the server is not uncommon.
			</p>
			<p>
				During development it was observed that some servers require very strict rate limiting. Only when waiting 15 seconds between connection attempts it was possible to stay under the strict limits. Therefore the test is expected to take around 8 minutes per SRV record.
			</p>
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
	<script src="./js/bootstrap.js"></script>

	<script src="./js/main.js"></script>

	</body>
</html>
