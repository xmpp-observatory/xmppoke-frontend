<?php

include("common.php");

// Different policy for the charts API
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://www.google.com https://ajax.googleapis.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com");

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
        <a class="navbar-brand" href="index.php">IM Observatory</a>
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



    <div class="col-md-9">
      <h1>Stats for November 2013</h1>

      <p>Statistics for reports generated during november 2013. Only the last test during november per server counts.</p>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart1" class="chart"></div>
        <p class="text-muted">Untrusted certificates and servers supporting SSL 2 are not capped to F here.</p>
      </div>
      <div class="col-md-6">
        <div id="chart2" class="chart"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart3" class="chart"></div>
      </div>
      <div class="col-md-6">
        <div id="chart4" class="chart"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart5" class="chart"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
      <div class="col-md-6">
        <div id="chart6" class="chart"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart7" class="chart"></div>
      </div>
      <div class="col-md-6">
        <div id="chart8" class="chart"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart9" class="chart"></div>
      </div>
      <div class="col-md-6">
        <div id="chart10" class="chart"></div>
      </div>
    </div>

    <div class="row">
        <div id="chart11" ></div>
        <div id="chart12" class="large-chart"></div>
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
  <script src="./js/bootstrap-sortable.js"></script>

  <script src="./js/main.js"></script>


  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
          ['Grade', 'Percentage', { role: 'style' }, { role: 'annotation' }],
          ['A', 64.5, 'green', 1116],
          ['B', 18.4, 'orange', 318],
          ['C', 17.2, 'red', 297],
          ['D', 0, 'red', 0],
          ['E', 0, 'red', 0],
          ['F', 0, 'red', 0]
        ]);

        var options = {
                title: 'Grade',
                legend: { position: "none" },
        };

        new google.visualization.ColumnChart(document.getElementById('chart1')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
          ['Protocol', 'Percentage', { role: 'style' }, { role: 'annotation' }],
          ['SSL 2', 9.3, 'red', 161],
          ['SSL 3', 87.4, 'orange', 1513],
          ['TLS 1.0', 99.7, 'green', 1725],
          ['TLS 1.1', 57.4, 'green', 993],
          ['TLS 1.2', 57.5, 'green', 995]
        ]);

        var options = {
                title: 'Protocol',
                legend: { position: "none" },
        };

        new google.visualization.ColumnChart(document.getElementById('chart2')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['RSA size', 'Count'],
                ['511', 1],
                ['1024', 314],
                ['2048', 960],
                ['2432', 5],
                ['3072', 2],
                ['3248', 7],
                ['3981', 2],
                ['4096', 448],
                ['8192', 3]
        ]);

        var options = {
                title: 'RSA key size',
                slices: {
                  1: { color: 'red' },
                  2: { color: 'orange' },
                  7: { offset: 0.2, color: 'green' }
                }
        };

        new google.visualization.PieChart(document.getElementById('chart3')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Forward secret', 'Count'],
                ['Yes', 692],
                ['No', 1731 - 692],
        ]);

        var options = {
                title: 'Forward secret (any cipher)',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'green'},
                           1: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart4')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['RC4', 'Count'],
                ['Yes', 1315],
                ['No', 1731 - 1315],
        ]);

        var options = {
                title: 'RC4 (any cipher)',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'red'},
                           1: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart5')).
                            draw(data, options);
      });

      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Weak cipher', 'Count'],
                ['Yes', 1554],
                ['No', 1731 - 1554],
        ]);

        var options = {
                title: 'Weak cipher (<128 bits)',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'red'},
                           1: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart6')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['c2s StartTLS', 'Count'],
                ['Required', 375],
                ['Allowed', 765],
        ]);

        var options = {
                title: 'c2s StartTLS',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'green'},
                           1: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart7')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['s2s StartTLS', 'Count'],
                ['Required', 42],
                ['Allowed', 549],
        ]);

        var options = {
                title: 's2s StartTLS',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'green'},
                           1: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart8')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Trust', 'Count'],
                ['Trusted, Valid', 950],
                ['Trusted, Invalid', 197],
                ['Untrusted, Valid', 302],
                ['Untrusted, Invalid', 368],
        ]);

        var options = {
                title: 'Trust',
                legend: { position: "none" },
                slices: {  0: {offset: 0.2, color: 'green'},
                           1: {color: 'grey'},
                           2: {color: 'grey'},
                           3: {color: 'grey'} }
        };

        new google.visualization.PieChart(document.getElementById('chart9')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Exact version', 'Count', { role: 'annotation' }],
                ['(Undisclosed)', 422, '24.4%'],
                ['ejabberd 2.1.10', 270, '15.6%'],
                ['Prosody 0.9.1', 212, '12.2%'],
                ['ejabberd 2.1.13', 132, '7.6%'],
                ['Prosody 0.8.2', 96, '5.5%'],
                ['ejabberd 2.1.5', 90, '5.2%'],
                ['ejabberd 2.1.11', 67, '3.9%'],
                ['Openfire 3.8.2', 61, '3.5%'],
                ['ejabberd 2.1.12', 20, '1.2%'],
                ['Prosody unknown', 18, '1.0%'],
                ['Prosody 0.9 nightly build 165 (2013-10-31, 1b0ac7950129)', 18, '1.0%'],
                ['ejabberd 2.1.10 Jabbim I need Holidays Edition', 14, '0.8%'],
                ['Openfire 3.7.1', 11, '0.6%'],
                ['Prosody 0.9.0', 10, '0.6%'],
                ['ejabberd 2.0.5', 10, '0.6%'],
                ['ejabberd 2.1.x-mh', 10, '0.6%'],
                ['Prosody 0.9 nightly build 155 (2013-08-09, 6ef79af0c445)', 10, '0.6%'],
                ['ejabberd 2.1.2', 10, '0.6%'],
        ]);

        var options = {
                title: 'Exact version',
                legend: { position: "none" },
        };

        new google.visualization.ColumnChart(document.getElementById('chart11')).
                            draw(data, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Version', 'Count', { role: 'annotation' }],
                ['ejabberd', 675, '39.0%'],
                ['Prosody', 457, '26.5%'],
                ['(Undisclosed)', 422, '24.4%'],
                ['Openfire', 90, '5.2%'],
                ['jabberd', 44, '2.5%'],
                ['Metronome', 19, '1.1%'],
                ['Tigase', 11, '0.6%'],
                ['Isode M-Link', 5, '0.3%'],
                ['PSYC', 3, '0.2%'],
                ['yabberd', 3, '0.2%'],
                ['ESTOS UCServer', 1, '0.1%'],
                ['Spectrum', 1, '0.1%']
        ]);

        var options = {
                title: 'Version',
                legend: { position: "none" },
        };

        new google.visualization.ColumnChart(document.getElementById('chart12')).
                            draw(data, options);
      });
    </script>

  </body>
</html>