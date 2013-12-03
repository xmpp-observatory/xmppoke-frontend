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

    <div class="row">
      <p>Statistics for reports generated during november 2013. Only the last test during november per server counts.</p>
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
                legend: { position: "none" },
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
    </script>

  </body>
</html>