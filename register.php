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

<p>If you run a public XMPP service and would like to add your service to the <a href='directory.php'>xmpp.net directory</a>, please send a pull request to <a href='https://github.com/stpeter/xmppdotnet'>https://github.com/stpeter/xmppdotnet</a> with a vCard XML file for your server, following the format of <a href='https://github.com/stpeter/xmppdotnet/tree/master/vcards'>existing files</a>. The information requested is as follows:</p>
<ul>
<li>The <strong>domain name</strong> of the XMPP service.</li>
<li>The <strong>website</strong> where users can find more information.</li>
<li>The <strong>year</strong> when the service was launched.</li>
<li>The <strong>country</strong> of the physical machine that hosts the service.</li>
<li>The approximate <strong>latitude</strong> and <strong>longitude</strong> of the physical machine that hosts the service.</li>
<li>The <strong>certification authority</strong> that issued the security certificate you use for encryption.</li>
<li>The <strong>server software</strong> used to run the service.</li>
<li>The <strong>name</strong> and <strong>JabberID</strong> of the primary administrator.</li>
<li>A brief <strong>description</strong> of the service.</li>
</ul>
<p>After you provide your pull request, we will work to <a href='verify.php'>verify</a> the information, so expect further communication!</p>
<p>If you have questions or comments, please subscribe to the operators@xmpp.org email list by visiting <a href='http://mail.jabber.org/mailman/listinfo/operators'>http://mail.jabber.org/mailman/listinfo/operators</a> or sending an email message to <a href='mailto:operators-subscribe@xmpp.org'>operators-subscribe@xmpp.org</a>. You <strong>must subscribe to the list</strong> in order to post, but don't worry: this is a low-volume list and a good place to share experiences with operators of other public XMPP services.</li>
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
