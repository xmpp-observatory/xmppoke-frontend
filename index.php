<?php

include("common.php");

pg_prepare($dbconn, "list_results", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results ORDER BY test_date DESC LIMIT 10;");

pg_prepare($dbconn, "find_score", "SELECT DISTINCT ON (grade) grade, total_score FROM srv_results WHERE test_id = $1;");

$res = pg_execute($dbconn, "list_results", array());

$list = pg_fetch_all($res);

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
				<a class="navbar-brand" href="#">IM Observatory</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li><a href="list.php">Test results</a></li>
					<li><a href="directory.php">Public server directory</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="jumbotron">
		  <div class="container">
			<h1>IM Observatory</h1>
			<p>Testing the security of XMPP TLS connections.</p>
		  </div>
		</div>

		<div class="row">
			<div class="col-lg-6">
				<h3>Test a server</h3>
				<div class="input-group">
					<input type="text" class="form-control" id="server-name" placeholder="jabber.org">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button">Check!</button>
					</span>
				</div><!-- /input-group -->

				<br>

				<h3>Recent results</h3>
					<table class="table" style="width: 80%;">
<?php

foreach ($list as $result) {
	$res = pg_execute($dbconn, "find_score", array($result["test_id"]));

	$scores = pg_fetch_all($res);
?>
						<tr>
							<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a></td>
<?php
	if (count($scores) > 1) {
?>
							<td><span class="muted">Multiple</span></td>
<?php
	} else {
?>
							<td><span class="<?= color_label_text_grade($scores[0]["grade"]) ?> label"><?= $scores[0]["grade"] === NULL ? "?" : $scores[0]["grade"] ?></span></td>
<?php
	}
?>
						</tr>
<?php
}
?>
					</table>
					<a href="list.php" class="btn btn-default">See more <span class="glyphicon glyphicon-chevron-right"></span></a>
			</div> <!-- /.col-lg-6 -->

			<div class="col-lg-6">
				<h3>Find a public server</h3>
				<p>Looking for a server to register an account? Check the list of <a href="directory.php">free, public and secure XMPP servers</a>.</p>

				<br>

				<h3>Setting up your own server</h3>
				<p>Interested in setting up your own XMPP server? See <a href="http://xmpp.org/xmpp-software/servers/">http://xmpp.org/xmpp-software/servers/</a>.</p>
			</div> <!-- /.col-lg-6 -->
		</div> <!-- /.row -->

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