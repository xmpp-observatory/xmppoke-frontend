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
				The test needs to make a large number of connections to the server to determine what it supports: one connection for every TLS version, one for every cipher it and some more for the other tests, like determining whether the server honors the the client's cipher order. Making 30 connections to the server is not uncommon.
			</p>
			<p>
				During development it was observed that some servers require very strict rate limiting. Only when waiting 15 seconds between connection attempts it was possible to stay under these strict limits. Therefore the test is expected to take around 8 minutes. This is repeated for every SRV record for the server.
			</p>

			<strong>How do I improve my Certificate score?</strong>
			<p>
				The certificate score is either 0, for untrusted or invalid certificates, or 100. Scoring a 0 means your grade is capped to “F”. To obtain 100, you need a certificate that is trusted and valid for your XMPP domain. See <a href="https://www.startssl.com/">StartCom</a> for free XMPP certificates.
			</p>

			<strong>How do I improve my Public key score?</strong>
			<p>
				The public key score depends on two factors: the size of your RSA key pair and whether any cipher suites are enabled that don't use this key.
			</p>
			<p>
				<table class="table">
					<tr>
						<th>RSA bitsize</th>
						<th>Score</th>
					</tr>
					<tr>
						<td>0</td>
						<td>0</td>
					</tr>
					<tr>
						<td>1 - 511</td>
						<td>20</td>
					</tr>
					<tr>
						<td>512 - 1023</td>
						<td>40</td>
					</tr>
					<tr>
						<td>1024 - 2047</td>
						<td>80</td>
					</tr>
					<tr>
						<td>2048 - 4095</td>
						<td>90</td>
					</tr>
					<tr>
						<td>≥ 4096</td>
						<td>100</td>
					</tr>
				</table>
			</p>
			<p>Enabling an anonymous DH cipher suite (ADH) caps your public key score to 0, as these do not use a public key for authentication. Enabling EXPORT cipher suites caps your score to 40, as these use an ephemeral 512-bit RSA key.</p>

			<p>RSA keys larger than 4096 bits have known compatibility problems, notably with OpenSSL.</p>

			<strong>How do I improve my Protocol score?</strong>
			<p>
				Your protocol score is the average of the score for the lowest and the highest protocol you support. This means you have two ways of increasing your score: disabling older protocols and adding new ones. Note that it is recommended to keep support for TLS 1.0 for compatibility.
			</p>
			<p>
				<table class="table">
					<tr>
						<th>Protocol</th>
						<th>Score</th>
					</tr>
					<tr>
						<td>SSL 2</td>
						<td>20</td>
					</tr>
					<tr>
						<td>SSL 3</td>
						<td>80</td>
					</tr>
					<tr>
						<td>TLS 1.0</td>
						<td>90</td>
					</tr>
					<tr>
						<td>TLS 1.1</td>
						<td>95</td>
					</tr>
					<tr>
						<td>TLS 1.2</td>
						<td>100</td>
					</tr>
				</table>
			</p>

			<strong>How do I improve my Cipher score?</strong>
			<p>
				You cipher score is the average of the score of the ciper suite with the smallest key and the cipher suite with the largest key. Note that it is recommended to keep support for 128 bit AES for compatibility.
			</p>
			<p>
				<table class="table">
					<tr>
						<th>Bitsize</th>
						<th>Score</th>
					</tr>
					<tr>
						<td>0</td>
						<td>0</td>
					</tr>
					<tr>
						<td>0 - 127</td>
						<td>20</td>
					</tr>
					<tr>
						<td>128 - 255</td>
						<td>80</td>
					</tr>
					<tr>
						<td>≥ 256</td>
						<td>100</td>
					</tr>
				</table>
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
