<?php

include("common.php");

pg_prepare($dbconn, "list_results", "SELECT * FROM (SELECT DISTINCT ON (server_name, type) * FROM test_results ORDER BY server_name, type, test_date DESC) AS results ORDER BY test_date DESC LIMIT 10;");

pg_prepare($dbconn, "find_score", "SELECT grade, total_score, certificate_score, done, error FROM srv_results WHERE test_id = $1;");

pg_prepare($dbconn, "find_news", "SELECT * FROM news_posts ORDER BY post_date DESC LIMIT 1;");

$res = pg_execute($dbconn, "list_results", array());

$list = pg_fetch_all($res);

$res = pg_execute($dbconn, "find_news", array());

$news = pg_fetch_object($res);

common_header("");

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
					<li><a href="about.php">About</a></li>
					<li><a href="reports.php">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="jumbotron">
		  <div class="container">
			<h1>IM Observatory</h1>
			<p>Testing the security of the Jabber/XMPP network.</p>
		  </div>
		</div>

		<div class="row">
			<div class="col-lg-6">
				<h3>Test a server</h3>
				<form id="test-server" action="submit.php" method="post">
					<div class="input-group">
							<input type="text" class="form-control" name="domain" placeholder="jabber.org">
							<input type="hidden" name="mode" id="mode" form="test-server" value="c2s">
							<div class="input-group-btn">
								<button type="submit" class="btn btn-default">Check!</button>
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="type" value="client">c2s <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" id="type-select">
									<li class="active"><a href="#" data-type="c2s">c2s</a></li>
									<li><a href="#" onclick='' data-type="s2s">s2s</a></li>
								</ul>
							</div><!-- /btn-group -->
					</div><!-- /input-group -->
				</form>
				<br>
				<small class="text-muted">Submit a publicly accessible XMPP server for testing. This test will make a large number of connections to the server and will take around 8-15 minutes. You can test either the client-to-server encryption or the server-to-server encryption.</small>

				<br>

				<h3>Recent results</h3>
					<table class="table" style="width: 80%;">
<?php

foreach ($list as $result) {
	$res = pg_execute($dbconn, "find_score", array($result["test_id"]));

	$scores = pg_fetch_all($res);
?>
						<tr>
							<td><a href="result.php?domain=<?= $result["server_name"] ?>&amp;type=<?= $result["type"] ?>"><?= $result["server_name"] ?></a> <span class="text-muted"><?= $result["type"] ?></span></td>
<?php
	$final_score = NULL;

	foreach ($scores as $score) {
		if (grade($score) && (!$final_score || grade($score) < $final_score)) {
			$final_score = grade($score);
		}
	}
?>
							<td><span class="<?= color_label_text_grade($final_score) ?> label"><?= $final_score === NULL ? "?" : $final_score ?></span><?= count($scores) > 1 ? "*" : "" ?></td>
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

				<h3>Join the network</h3>
				<p>Interested in setting up your own XMPP server? Our <a href="howto.php">HOWTO page</a> gets you started.</p>

				<br>
				<h3>Learn about XMPP</h3>
				<p>XMPP is an open standard for instant messaging and real-time communication. Visit <a href="http://xmpp.org/">xmpp.org</a> for all the details.</p>

				<br>
				<h3>Latest news</h3>
				<time class="text-muted timeago" datetime="<?= date("c", strtotime($news->post_date)) ?>"></time>
				<?= $news->message ?>
				<a href="/rss.php">RSS feed</a>
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
