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

<p>When a server administrator <a href='register.php'>registers</a> a <a href='/'>public XMPP service</a> with xmpp.net, here is how we verify the registration:</p>
<ol>
<li>
<p>Ensure that the request is approved by one of the official representatives for the <em>root</em> domain by forwarding the message sent on the <a href='http://mail.jabber.org/mailman/listinfo/operators'>operators@xmpp.org list</a> to (1) the email address(es) listed in the whois record for the root domain and (2) the hostmaster/postmaster/webmaster email addresses for the root domain. (By "root domain" we mean the lowest-level domain that can be looked up in whois &mdash; e.g., if the XMPP service is running at im.example.com then we contact the owners and admins for example.com.)</p>
<p>Typically the message we send says something like this:</p>
<blockquote><p>"Please affirm that you approve of this request to add im.example.com to the list at http://xmpp.net/ by replying to this message."</p>
</blockquote>
</li>
<li>
<p>Check for appropriate DNS SRV records using the dig command, such as:</p>
<blockquote><p>dig +short -t SRV _xmpp-client._tcp.im.example.com</p>
<p>dig +short -t SRV _xmpp-server._tcp.im.example.com</p>
</blockquote>
</li>
<li>
<p>Verify that there is indeed an XMPP service running at the domain for server-to-server and client-to-server communications using telnet to the ports discovered via SRV lookups, such as:</p>
<blockquote><p>telnet im.example.com 5222</p>
<p>telnet im.example.com 5269</p>
</blockquote>
<li>
<p>Validate the certificate against the root cert of the security provider to make sure that secure connections can be established without errors. We do this by checking STARTTLS on port 5222 or SSL on port 5223 using the OpenSSL s_client feature, such as:</p>
<blockquote><p>openssl s_client -connect im.example.com:5222 -starttls xmpp -CAfile startcom.crt</p></blockquote>
<blockquote><p>openssl s_client -connect im.example.com:5223 -CAfile startcom.crt</p></blockquote>
</li>
<li>
<p>Visit the website of the service to make sure that it is accurate, provides contact information, etc.</p>
</li>
<li>
<p>Communicate with the service administrator via XMPP.</p>
</li>
</ol>

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