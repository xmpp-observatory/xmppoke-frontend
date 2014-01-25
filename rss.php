<?php

include("common.php");

header("Content-Type: application/xml; charset=UTF-8");

pg_prepare($dbconn, "find_news", "SELECT * FROM news_posts ORDER BY post_date DESC;");

$res = pg_execute($dbconn, "find_news", array());

$news = pg_fetch_all($res);

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0">
<channel>
	<title>IM Observatory news</title>
	<description>News about the IM observatory at xmpp.net</description>
	<link>https://xmpp.net</link>
	<pubDate>Sat, 25 Jan 2014 14:31:38 +0100 </pubDate>
	<ttl>1800</ttl>

<?php

foreach ($news as $new) {
?>
<item>
		<title><?= $new["title"] ?></title>
		<description><![CDATA[[[<?= $new["message"] ?>]]></description>
		<link>https://xmpp.net/</link>
		<guid>https://xmpp.net/news.php?id=<?= $new["post_id"] ?></guid>
		<pubDate><?= date("D, d M Y H:i:s O", strtotime($new["post_date"])) ?></pubDate>
	</item>
<?php
}
?>

</channel>
</rss>
