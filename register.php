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
			<h2>Register</h2>

<p>If you run a public XMPP service and would like to add your service to the <a href='/'>xmpp.net directory</a>, please follow the instructions below.</p>
<p>Note: The xmpp.net directory is planning to migrate to the process defined in <a href='http://xmpp.org/extensions/xep-0309.html'>XEP-0309: Service Directories</a>. Once we do so, server admins won't need to provide the following information, since it will be retrieved automatically.</p>
<ol start='1'>
<li>
<p>Subscribe to the operators@xmpp.org email list by visiting <a href='http://mail.jabber.org/mailman/listinfo/operators'>http://mail.jabber.org/mailman/listinfo/operators</a> or sending an email message to <a href='mailto:operators-subscribe@xmpp.org'>operators-subscribe@xmpp.org</a>. You <strong>must subscribe to the list</strong> in order to post, but don&#8217;t worry: this is a low-volume list and a good place to share experience with other operators of public XMPP services.</li>
<li>
<p>Send an email message to the list with a subject of &#8220;public XMPP service: [yourdomain.tld]&#8220;. Please provide the following information:</p>
<ul>
<li>The <strong>domain name</strong> of the XMPP service.</li>
<li>The <strong>website</strong> where users can find more information.</li>
<li>The <strong>year</strong> when the service was launched.</li>
<li>The <strong>country</strong> of the physical machine that hosts the service.</li>
<li>The approximate <strong>latitude</strong> and <strong>longitude</strong> of the physical machine that hosts the service.</li>
<li>The <strong>CA</strong> that issued the security certificate you use for encryption.</li>
<li>The <strong>server software</strong> used to run the service.</li>
<li>The <strong>name</strong> and <strong>JabberID</strong> of the primary admin.</li>
<li>A brief <strong>description</strong> of the service.</li>
</ul>
</ol>
<p>To make your life easier, a template for your message is provided below, using the information from the jabber.org service (replace data as necessary!).</p>
<blockquote class='box'>
<p>Please add my public XMPP service to the list at xmpp.net. The information is as follows:</p>
<ul>
<li><strong>domain</strong>: [jabber.org]</li>
<li><strong>website</strong>: [http://www.jabber.org]</li>
<li><strong>year launched</strong>: [1999]</li>
<li><strong>country</strong>: [USA]</li>
<li><strong>latitude</strong>: [42.2]</li>
<li><strong>longitude</strong>: [-91.2]</li>
<li><strong>CA</strong>: [StartSSL]</li>
<li><strong>server software</strong>: [ejabberd]</li>
<li><strong>admin name</strong>: [Peter Saint-Andre]</li>
<li><strong>admin JID</strong>: [stpeter@jabber.org]</li>
<li><strong>description</strong>: [the original Jabber service]</li>
</ul>
</blockquote>
<p>After you contact us, we will work to <a href='verify.php'>verify</a> your request, so expect further communication from us!</p>
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