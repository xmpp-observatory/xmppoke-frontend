<?php

include("common.php");

common_header();

?>
	<body>

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
					<li class="active"><a href="#">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
					<li><a href="about.php">About</a></li>
					<li><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">

		<h1>How To Run Your Own XMPP Server</h1>

			<p>Because XMPP is a decentralized technology, anyone can run their own server and join the open XMPP network. The most active, popular server software projects are probably <a href='http://www.process-one.net/en/ejabberd/'>ejabberd</a>, <a href='http://www.igniterealtime.org/projects/openfire/index.jsp'>Openfire</a>, and <a href='https://prosody.im/'>Prosody</a> (a full list is at <a href="http://xmpp.org/xmpp-software/servers/">http://xmpp.org/xmpp-software/servers/</a>). Simply download the software of your choice, follow the installation instructions provided with your software, obtain a digital certificate from a certification authority (<a href='http://startssl.com/'>StartSSL</a> is a popular choice), and make sure to configure your software to allow server-to-server connections. If you have any questions, ask the providers of your server software or join the <a href='http://mail.jabber.org/mailman/listinfo/operators'>operators@xmpp.org</a> discussion list.</p>

		<div class="footer">
			<p>Some rights reserved.</p>
		</div>
	</div> <!-- /container -->

	</body>
</html>
