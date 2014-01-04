<?php

include("common.php");
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
      <h1>Stats for December 2013</h1>

      <p>Statistics for reports generated during december 2013. Only the last test during december per server counts.</p>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart1" style="width: 500px; height: 300px;"></div>
        <p class="text-muted">Untrusted certificates and servers supporting SSL 2 are not capped to F here.</p>
      </div>
      <div class="col-md-6">
        <div id="chart2" style="width: 500px; height: 300px;"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart3" style="width: 500px; height: 300px;"></div>
      </div>
      <div class="col-md-6">
        <div id="chart4" style="width: 500px; height: 300px;"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart5" style="width: 500px; height: 300px;"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
      <div class="col-md-6">
        <div id="chart6" style="width: 500px; height: 300px;"></div>
        <p class="text-muted">The cipher may not be selected by all clients, or even any at all.</p>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart7" style="width: 500px; height: 300px;"></div>
      </div>
      <div class="col-md-6">
        <div id="chart8" style="width: 500px; height: 300px;"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div id="chart9" style="width: 500px; height: 300px;"></div>
      </div>
      <div class="col-md-6">
        <div id="chart10" style="width: 500px; height: 300px;"></div>
      </div>
    </div>

    <div class="row">
        <div id="chart11" style="width: 100%; height: 450px;"></div>
        <div id="chart12" style="width: 100%; height: 450px;"></div>
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
  <script src="./js/bootstrap-sortable.js"></script>

  <script src="./js/main.js"></script>


  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
          ['Grade', 'Percentage'],
          ['A', 65.2],
          ['B', 13.1],
          ['C', 21.7],
          ['D', 0],
          ['E', 0],
          ['F', 0]
        ]);

        var old_data = google.visualization.arrayToDataTable([
          ['Grade', 'Percentage'],
          ['A', 64.5],
          ['B', 18.4],
          ['C', 17.2],
          ['D', 0],
          ['E', 0],
          ['F', 0]
        ]);

        var options = {
                title: 'Grade',
                legend: { position: "none" }
        };

        var chartDiff = new google.visualization.ColumnChart(document.getElementById('chart1'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
          ['Protocol', 'Percentage'],
          ['SSL 2', 9.4],
          ['SSL 3', 85.8],
          ['TLS 1.0', 99.4],
          ['TLS 1.1', 59.7],
          ['TLS 1.2', 60.2]
        ]);

        var old_data = google.visualization.arrayToDataTable([
          ['Protocol', 'Percentage'],
          ['SSL 2', 9.3],
          ['SSL 3', 87.4],
          ['TLS 1.0', 99.7],
          ['TLS 1.1', 57.4],
          ['TLS 1.2', 57.5]
        ]);

        var options = {
                title: 'Protocol',
                legend: { position: "none" },
        };

        var chartDiff = new google.visualization.ColumnChart(document.getElementById('chart2'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['RSA size', 'Count'],
                ['511', 1],
                ['1024', 140],
                ['2048', 491],
                ['2432', 0],
                ['3072', 0],
                ['3248', 2],
                ['3981', 0],
                ['4096', 189],
                ['8192', 1]
        ]);

        var old_data = google.visualization.arrayToDataTable([
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
                  7: { color: 'green' },
                  10: { color: 'red' },
                  11: { color: 'orange' },
                  16: { color: 'green' }
                }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart3'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Forward secret', 'Count'],
                ['Yes', 364],
                ['No', 776 - 364],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['Forward secret', 'Count'],
                ['Yes', 692],
                ['No', 1731 - 692],
        ]);

        var options = {
                title: 'Forward secret (any cipher)',
                legend: { position: "none" },
                slices: {  0: {color: 'green'},
                           1: {color: 'grey'},
                           2: {color: 'green'},
                           3: {color: 'grey'} }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart4'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['RC4', 'Count'],
                ['Yes', 584],
                ['No', 776 - 584],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['RC4', 'Count'],
                ['Yes', 1315],
                ['No', 1731 - 1315],
        ]);

        var options = {
                title: 'RC4 (any suite)',
                legend: { position: "none" },
                slices: {  0: {color: 'red'},
                           1: {color: 'grey'},
                           2: {color: 'red'},
                           3: {color: 'grey'} }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart5'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });

      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Weak cipher', 'Count'],
                ['Yes', 687],
                ['No', 776 - 687],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['Weak cipher', 'Count'],
                ['Yes', 1554],
                ['No', 1731 - 1554],
        ]);

        var options = {
                title: 'Weak cipher (<128 bits)',
                legend: { position: "none" },
                slices: {  0: {color: 'red'},
                           1: {color: 'grey'},
                           2: {color: 'red'},
                           3: {color: 'grey'} }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart6'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['c2s StartTLS', 'Count'],
                ['Required', 190],
                ['Allowed', 378],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['c2s StartTLS', 'Count'],
                ['Required', 375],
                ['Allowed', 765],
        ]);

        var options = {
                title: 'c2s StartTLS',
                legend: { position: "none" },
                slices: {  0: {color: 'green'},
                           1: {color: 'grey'},
                           2: {color: 'green'},
                           3: {color: 'grey'} }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart7'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['s2s StartTLS', 'Count'],
                ['Required', 28],
                ['Allowed', 180],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['s2s StartTLS', 'Count'],
                ['Required', 42],
                ['Allowed', 549],
        ]);

        var options = {
                title: 's2s StartTLS',
                legend: { position: "none" },
                slices: {  0: {color: 'green'},
                           1: {color: 'grey'},
                           2: {color: 'green'},
                           3: {color: 'grey'} }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart8'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Trust', 'Count'],
                ['Trusted, Valid', 414],
                ['Trusted, Invalid', 56],
                ['Untrusted, Valid', 127],
                ['Untrusted, Invalid', 179],
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['Trust', 'Count'],
                ['Trusted, Valid', 950],
                ['Trusted, Invalid', 197],
                ['Untrusted, Valid', 302],
                ['Untrusted, Invalid', 368],
        ]);

        var options = {
                title: 'Trust',
                legend: { position: "none" },
                slices: {  0: {color: 'green'},
                           1: {color: 'grey'},
                           2: {color: 'grey'},
                           3: {color: 'grey'},
                           4: {color: 'green'},
                           5: {color: 'grey'},
                           6: {color: 'grey'},
                           7: {color: 'grey'}, }
        };

        var chartDiff = new google.visualization.PieChart(document.getElementById('chart9'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Exact version', 'Percentage'],
                ['(Undisclosed)', 27.7],
                ['ejabberd 2.1.10', 14.0],
                ['Prosody 0.9.1', 10.7],
                ['ejabberd 2.1.13', 8.4],
                ['ejabberd 2.1.5', 5.2],
                ['Prosody 0.8.2', 4.8],
                ['Openfire 3.8.2', 3.7],
                ['ejabberd 2.1.11', 3.6],
                ['ejabberd community', 2.7],
                ['Prosody 0.9.0', 1.2],
                ['ejabberd 2.1.10 Jabbim I need Holidays Edition', 0.9],
                ['Openfire 3.7.1', 0.8],
                ['ejabberd 2.1.12', 0.8],
                ['jabberd 7.6.2.14683', 0.6],
                ['Prosody 0.9 nightly build 155 (2013-08-09, 6ef79af0c445)', 0.6]
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['Exact version', 'Count'],
                ['(Undisclosed)', 24.4],
                ['ejabberd 2.1.10', 15.6],
                ['Prosody 0.9.1', 12.2],
                ['ejabberd 2.1.13', 7.6],
                ['ejabberd 2.1.5', 5.2],
                ['Prosody 0.8.2', 5.5],
                ['Openfire 3.8.2', 3.5],
                ['ejabberd 2.1.11', 3.9],
                ['ejabberd community', 0.6],
                ['Prosody 0.9.0', 0.6],
                ['ejabberd 2.1.10 Jabbim I need Holidays Edition', 0.8],
                ['Openfire 3.7.1', 0.6],
                ['ejabberd 2.1.12', 1.2],
                ['jabberd 7.6.2.14683', 0.2],
                ['Prosody 0.9 nightly build 155 (2013-08-09, 6ef79af0c445)', 0.6]
        ]);

        var options = {
                title: 'Exact version',
                legend: { position: "none" },
        };

        var chartDiff = new google.visualization.ColumnChart(document.getElementById('chart11'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
      google.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
                ['Version', 'Percentage'],
                ['ejabberd', 39.4],
                ['(Undisclosed)', 27.7],
                ['Prosody', 22.7],
                ['Openfire', 5.8],
                ['jabberd', 1.8],
                ['Metronome', 0.8],
                ['Tigase', 0.8],
                ['Isode M-Link', 0.5],
                ['yabberd', 0.3],
                ['ESTOS UCServer', 0.1],
                ['MU-Conference', 0.1],
                ['Spectrum', 0.0],
                ['PSYC', 0.0]
        ]);

        var old_data = google.visualization.arrayToDataTable([
                ['Version', 'Percentage'],
                ['ejabberd', 39.0],
                ['(Undisclosed)', 24.4],
                ['Prosody', 26.5],
                ['Openfire', 5.2],
                ['jabberd', 2.5],
                ['Metronome', 1.1],
                ['Tigase', 0.6],
                ['Isode M-Link', 0.3],
                ['yabberd', 0.2],
                ['ESTOS UCServer', 0.1],
                ['MU-Conference', 0.0],
                ['Spectrum', 0.1],
                ['PSYC', 0.2]
        ]);

        var options = {
                title: 'Version',
                legend: { position: "none" },
        };

        var chartDiff = new google.visualization.ColumnChart(document.getElementById('chart11'));

        var diffData = chartDiff.computeDiff(old_data, data);

        chartDiff.draw(diffData, options);
      });
    </script>

  </body>
</html>