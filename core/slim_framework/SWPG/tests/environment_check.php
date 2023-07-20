<?php
$host_port = array(
  'mailfwd.nih.gov' => array(25) //outbound mail
  ,'uniprot.org' => array(80) //outbound http
  ,'google.com' => array(443) //outbound https
  ,$_SERVER["HTTP_HOST"] => array(80, 443) //your server - inbound
);

$needed_ext = array("soap"
  ,"PDO"
  ,"pdo_mysql"
  ,"mysql"
  ,"curl"
  ,"gd"
  ,"mcrypt"
  ,"ldap"
  ,"imagick"
  ,"json"
  ,"fileinfo"
);

$needed_apached_modules = array(
  "mod_alias"
  ,"mod_auth_basic"
  ,"mod_authn_file"
  ,"mod_authz_default"
  ,"mod_authz_groupfile"
  ,"mod_authz_host"
  ,"mod_authz_user"
  ,"mod_autoindex"
  ,"mod_cgi"
  ,"mod_deflate"
  ,"mod_dir"
  ,"mod_env"
  ,"mod_headers"
  ,"util_ldap"
  ,"mod_mime"
  ,"mod_mime_magic"
  ,"mod_negotiation"
  ,"mod_php5"
  ,"mod_reqtimeout"
  ,"mod_rewrite"
  ,"mod_setenvif"
  ,"mod_ssl"
  ,"mod_status"
);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Environment check for the Scientific Web Programming Group's web server setup.">
    <meta name="author" content="ABCC's Scientific Web Programming Group (SWPG)">

    <title>Web Server Environment Check: <?php echo $_SERVER["SERVER_NAME"].'  ('.$_SERVER["SERVER_ADDR"].')'; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <style type="text/css">
      body {
        padding-top: 70px;
        padding-bottom: 30px;
      }
      .theme-dropdown .dropdown-menu {
        position: static;
        display: block;
        margin-bottom: 20px;
      }
      .theme-showcase > p > .btn {
        margin: 5px 0;
      }
      .theme-showcase .navbar .container {
        width: auto;
      }
    </style>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="https://ncifrederick.cancer.gov/isp/abcc/" target="_blank">ABCC</a></li>
            <li><a href="https://ncifrederick.cancer.gov/isp/abcc/abcc-groups/swpg/" target="_blank">Scientific Web Programming Group (SWPG)</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container theme-showcase" role="main">

      <!-- Main jumbotron for a primary marketing message or call to action -->
      <div class="jumbotron">
        <h1>Web Server Environment Check</h1>
        <h2>Server: <?php echo $_SERVER["SERVER_NAME"].'  ('.$_SERVER["SERVER_ADDR"].')'; ?></h2>
        <p>This script checks for open ports and the presence of required PHP extensions and Apache modules.</p>
      </div>

      <!-- <div class="page-header">
        <h1>System Checks</h1>
      </div> -->
      <div class="row">
        <div class="col-sm-4">
          <h2>Server Ports</h2>
          <ul class="list-group">
            <?php
              foreach($host_port as $host => $ports){
                foreach($ports as $port){
                  $connection = @fsockopen($host, $port);
                    if (is_resource($connection)){
                        echo '<li class="list-group-item"><span class="label label-success">Open</span> <strong>' . $host . ':' . $port . ' ' . '(' . getservbyport($port, 'tcp') . ')</strong>';

                        fclose($connection);
                    }else{
                        echo '<li class="list-group-item"><span class="label label-danger">No response</span> <strong>' . $host . ':' . $port . '</strong>';
                    }
                }
              }
            ?>
          </ul>
        </div><!-- /.col-sm-4 -->
        <div class="col-sm-4">
          <h2>PHP Extensions</h2>
          <ul class="list-group">
            <?php
              $loaded_extensions = get_loaded_extensions();
              foreach($needed_ext as $single_ext){
                if(!in_array($single_ext,$loaded_extensions)){
                  echo '<li class="list-group-item"><span class="label label-danger">Missing</span> <strong>' . $single_ext . '</strong></li>';
                }else{
                  echo '<li class="list-group-item"><span class="label label-success">Present</span> <strong>' . $single_ext . '</strong></li>';
                }
              }
            ?>
          </ul>
        </div><!-- /.col-sm-4 -->
        <div class="col-sm-4">
          <h2>Apache Modules</h2>
          <ul class="list-group">
            <?php
              $loaded_apache_modules = apache_get_modules();
              foreach($needed_apached_modules as $single_apache_mod){
                if(!in_array($single_apache_mod,$loaded_apache_modules)){
                  echo '<li class="list-group-item"><span class="label label-danger">Missing</span> <strong>' . $single_apache_mod . '</strong></li>';
                }else{
                  echo '<li class="list-group-item"><span class="label label-success">Present</span> <strong>' . $single_apache_mod . '</strong></li>';
                }
              }
            ?>
          </ul>
        </div><!-- /.col-sm-4 -->
      </div>



    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <!-- <script src="../../assets/js/docs.min.js"></script> -->
  </body>
</html>
