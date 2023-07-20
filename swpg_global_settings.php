<?php
  /*
 * Env Settings
 */

error_reporting(false);

switch($_SERVER["SERVER_NAME"]) {
#switch domain name 
  default:
  $db_conn = array(
     "name" => "{{ db_name }}"
    ,"host" => "{{ db_server_name }}"
    ,"user" => "{{ mysql_user }}"
    ,"password" => "{{ mysql_password }}"
    ,"die_on_connection_failure" => true
    ,"connection_error_message" => "The system is currently not accessible."
    ,"email_on_connection_failure" => false
    ,"admin_emails" => "{{ admin_emails }}"
  );
  break;
}

/**
 * Base Application Settings
 */

$multipart_separator = '-----'.md5(time()).'-----';

$panorama_paths = array(
  "server_raw" => "https://panoramaweb.org"
  ,"server" => "https://panoramaweb.org/labkey"
  ,"query_path" => "/query/CPTAC%20Assay%20Portal/"
  ,"targetedms_query_path" => "/targetedms/CPTAC%20Assay%20Portal/"
  ,"project_query_path" => "/project/CPTAC%20Assay%20Portal/"
);

$config = [
    'settings' => [
        'displayErrorDetails' => true
        
        ,'addContentLengthHeader' => false // Allow the web server to send the content-length header
        ,"mode" => "development"
        ,"debug" => true
        ,'cookies.domain' => $_SERVER["SERVER_NAME"]
        ,'cookies.httponly' => true
        ,'cookies.secure' => (!empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] !== "off"))
        
        ,'db' => [
            'driver' => 'mysql',
            'host' => '{{ db_server_name }}',
            'database' => '{{ db_name }}',
            'username' => '{{ mysql_user }}',
            'password' => '{{ mysql_password }}',
            'charset'  => 'utf8',
            'strict'   => true
        ]
        
        ,'logger' => [
            'name' => 'cptac-assay-logger',
            'level' => Monolog\Logger::INFO,
            //'path' => 'php://stdout',
            'path' => $_SERVER["PATH_TO_LOG"] . '/cptac_assay_{{ server_name }}.log'
        ],
    ],
];

$guzzle_config = [
    'base_uri' => "https://".$_SERVER["SERVER_NAME"]
    , 'verify' => false
    , 'cookies' => true
];

$mail_config = [
    'host' => '{{ mail_host }}',
    'port' => '{{ mail_port }}',
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    'username' => '{{ mail_username }}',
    'password' => '{{ mail_password }}'
];

$swpg_global_settings = array(
   "app_config" => $config
  ,"mail_config" => $mail_config
  ,"site_name" => "CPTAC Assay Portal"
  ,"superadmin_email_address" => "{{ admin_emails }}"
  ,"force_ssl" => ( ($_SERVER["SCRIPT_NAME"] == '/tests/index.php') || ($_SERVER["SCRIPT_NAME"] == '/assays/index.php') || ($_SERVER["REQUEST_URI"] == '/') || ( stristr($_SERVER["REQUEST_URI"], 'CPTAC') ) ) ? false : true
  ,"site_logo" => "/site/library/images/CPTAC_certified_seal_101x101.gif"
  ,"core_type" => $_SERVER["CORE_TYPE"]
  ,"session_key" => "cptac_session_key"
  ,"logout_url" => "/authenticate/logout"
  ,"login_url" => "/authenticate"
  ,"access_denied_url" => "/authenticate/access_denied"
  ,"log_page_load" => true
  ,"landing_page" => "/modules/"
  ,"public_upload" => "/public_upload/"
  ,"redirect_cookie_key" => "swpg_redirect"
  ,"hide_public_site" => true
  ,"google_analytics_key_new" => $google_analytics_key
  ,"google_analytics_key_new_sitename" => $google_analytics_key_sitename
  ,"module_icon_path" => "/modules/library/images/module_default_icon.png"
  ,"db_connection" => {{ db_connection | default('$db_conn') }}
  ,"user_account_db_connection" => {{ user_account_db_connection | default('$db_conn') }}
  ,"public_upload_db_connection" => {{ public_upload_db_connection | default('$db_conn') }}
  ,"db_connection_antibodies" => false
  ,"core_templates" => $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/templates/swpg_bootstrap"
  ,"menu_template_name" => "swpg_bootstrap_side_nav.html"
  ,"layout_template_name" => "swpg_bootstrap_admin.html"
  ,"site_templates" => $_SERVER["DOCUMENT_ROOT"] . "/site/templates"
  ,"mail_templates" => $_SERVER["PATH_TO_CORE"] . "/templates"
  ,"default_site_module" => $_SERVER["DOCUMENT_ROOT"] . "/site"
  ,"site_footer" => "/assays/templates/footer.php"
  ,"public_site_footer" => "/assays/templates/public_footer.php"              
  ,"support_form_url" => "/support/"
  ,"import_log_location" => "/assays_import/library/import_logs"
  ,"debug_log_location" => "/assays/library/debug_logs"
  ,"error_log" => $_SERVER["PATH_TO_LOG"] . "/cptac_assay.log"
  // ,"additional_header_links" => array(
  //  "/faq" => array(
  //    "text" => "FAQ"
  //  )
  // )
  ,"current_user_roles_session_key" => "user_role_list" //a list of all the roles a user is assigned to.  This does not take into account groups.  If a user is an admin for two groups, and an author for another, this will be ['admin','author'].  This will be used for displayed pages on the side nav
  ,"default_role_id" => 5 //when a user logs in for the first time, the default role they will be given is this
  //make sure that this is associative, so when module includes are merged, it will cascade properly
  ,"js_includes" => array(
    'admin_js' => '/site/library/js/javascript.js'
  )
  ,"css_includes" => array(
  )
  ,"server_name" => $_SERVER["SERVER_NAME"]
  ,"request_uri" => $_SERVER["REQUEST_URI"]
  ,"upload_directory"=> $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/sop_files/"
  ,"sop_file_upload_directory" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/sop_files/"
  // Google Oauth Keys
  ,"google_client_id" => "{{ google_client_id }}"
  ,"google_client_secret" => "{{ google_client_secret }}"
  , "google_recaptcha_site_key" => "{{ google_recaptcha_site_key }}"
  , "google_recaptcha_secret" => "{{ google_recaptcha_secret }}"


  // For importing data from UniProt
  ,"uniprot_regions_array" => array(
    'topological domain'
    ,'transmembrane region'
    ,'intramembrane region'
    ,'domain'
    ,'repeat'
    ,'calcium binding'
    ,'zinc finger'
    ,'dna binding'
    ,'nucleotide phosphate-binding region'
    ,'region of interest'
    ,'coiled coil'
    ,'motif'
    ,'compositional bias'
  )
  ,"files_directory"=> "/assays/library/images/"
  ,"panorama_images_storage_path"=> $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/panorama/"
  ,"panorama_images_path"=> "/swpg_files/cptac/panorama/"
  ,"phosphosite_images_storage_path"=> $_SERVER['PATH_TO_DATA']."/assay_portal/phosphosite"
  ,"sop_files_path" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/sop_files/"
  ,"database_backup_path" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/database_backups/"
  ,"temp_directory_path" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/temp/"
  ,"temp_directory_path_via_http" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/temp/"
  ,"disqus_shortname" => ($_SERVER["SERVER_NAME"] == 'assaysdev.cancer.gov') ? 'cptacdevswpg' : 'cptacswpg'
  // Panorama / Labkey settings.
  ,"labkey_config" => array(
    "email" => "{{ labkey_config.email }}"
    ,"password" => "{{ labkey_config.password }}"
    ,"server_raw" => $panorama_paths["server_raw"]
    ,"server" => $panorama_paths["server"]
    ,"query_endpoint_live" => $panorama_paths["server"].$panorama_paths["query_path"]
    ,"targetedms_query_path" => $panorama_paths["server"].$panorama_paths["targetedms_query_path"]
    ,"project_endpoint_live" => $panorama_paths["server"].$panorama_paths["project_query_path"]
  )
  // Panorama Import error report email recipients.
  ,"panorama_errors_email_recipients_ids" => array(
     "1" // Michael Loss
    //,"65169" // Jeffrey Whiteaker
  )
  ,"google_analytics_config" => array(
    "id" => "ga:78611517"
    ,"path_to_key_file" => $_SERVER['DOCUMENT_ROOT']."/swpg_files/cptac/ga_key_file/74f850cf5d875f69aa9732e5b11acf7e58bfd9be-privatekey.p12"
    ,"email_address" => "685467335384-goaoe9n23ufd0l3u1tpbsbtm2ahjmmhv@developer.gserviceaccount.com"
    ,"client_id" => "685467335384-g4g9sfr6ij8btag15tkdigu67u9jg5rs.apps.googleusercontent.com"
    ,"start_date" => "2013-11-01"
    ,"end_date" => date('Y-m-d') // Today
  )
  ,"uniprot_protein_api_url" => "https://www.uniprot.org/uniprot/"
  ,"entrez_api_url" => "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/"
  ,"panorama_url" => "https://panoramaweb.org/"
  ,"biodbnet_api_url" => "http://biodbnet.abcc.ncifcrf.gov/webServices/rest.php/biodbnetRestApi.json"
  ,"linkout_assays_ftp" => array(
      "host" => "ftp-private.ncbi.nlm.nih.gov"
    , "username" => "assaysnci"
    , "password" => "dbs55GaQ"
    , "linkout_provider_id" => "8855"
    , "target_url" => "https://assays.cancer.gov"
  )
  ,"linkout_antibodies_ftp" => array(
      "host" => "ftp-private.ncbi.nlm.nih.gov"
    , "username" => "antibodynci"
    , "password" => "sn79aRuf"
    , "linkout_provider_id" => "8467"
    , "target_url" => "http://antibodies.cancer.gov"
  )
  /**** role based PERMISSIONS *****/
  ,"role_permissions" => array(
    //groups related permissions
    "group" => array(
      "browse_access" => array(4)
      ,"manage_access" => array(4)
    )
    //user account related permissions
    ,"user_account" => array(
      "browse_access" => array(3,4)
      ,"manage_access" => array(3,4)
      ,"delete" => array(4)
      ,"modify_own_account" => array(4)
      ,"assign_to_any_group" => array(4)
    )
    //support related permissions
    ,"support" => array(
      "browse_access" => array(3,4)
      ,"manage_access" => array(3,4)
      ,"settings_access" => array(3,4)
    )
    // Assay Approval permissions
    ,"assay_approval" => array(
      "manage_access" => array(1,4)
    )
    // Assay Preview permissions
    ,"assay_preview" => array(
      "browse_access" => array(1,2,4,6)
    )
    // Assay Import permissions
    ,"import" => array(
      "submit_access" => array(1,2,4,7),
      "submit_import" => array(1,2,4)
    )
    // Tutorials
    ,"tutorials" => array(
       "access_tutorials" => array(1,2,3,4,5)
    )
    ,"admin" => array(
        "execute_script" => array(4)
    )
    , "public_upload" => array(
        "upload" => array(8)
    )
  )
  // Email template parts
  ,"message_parts" => array(
    'headers' => 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n",
    // 'multipart_separator' => $multipart_separator,
    // 'headers' => 'Content-type: multipart/alternative; boundary="'.$multipart_separator.'"' . "\r\n"
    // . $multipart_separator . "\r\n" . 'Content-Type: text/html; charset=iso-8859-1;' . "\r\n" . 'Content-Transfer-Encoding: 7bit' . "\r\n",
    'body_header' => '
    <html>
    <head>
      <title>New Comment</title>
    <style>
    #wrapper {
      width: 100%;
    }
    body {
      background-color: #FFFFFF;
      color: #444444;
      font: 12px/1.7em "Open Sans","trebuchet ms",arial,sans-serif;
    }
    hr {
      margin: 20px 0;
      border: 0;
      border-top: 1px dotted #CCCCCC;
      border-bottom: 1px dotted #ffffff;
    }
    #content {
      background-color: #FFFFFF;
      margin-left: 20px;
      margin-right: 20px;
      padding: 0 16px 16px 16px;
      position: relative;
      top: 10px;
      width: auto;
      z-index: 15;
    }
    #contentHeader {
      border-bottom: 1px dotted #CCCCCC;
      margin-bottom: 10px;
      padding: 20px 0 20px 0;
      position: relative;
      width: auto;
    }
    #contentHeader h1 {
      color: #333333;
      font-size: 28px;
      font-weight: normal;
      line-height: 34px;
      position: relative;
      top: 20px;
    }
    .container:after {
      clear: both;
    }
    .container:after {
      content: "";
      display: table;
    }
    .container {
      margin: 0 auto;
      position: relative;
      width: 100%;
    }
    .container p {
      font-size: 1.2em;
    }
    .container .grid-24 {
      width: auto;
    }
    .container [class^="grid-"] {
      margin-bottom: 2em;
      margin-left: 20px;
      margin-right: 20px;
    }
    #logo {
      clear: both;
      width: 111px;
      height: 101px;
      margin: 0 0 20px 0;
    }
    .view-post {
      font-size: 1.2em;
    }
    #footer {
      clear: both;
      background-color: #FFFFFF;
      border-top: 1px dotted #CCCCCC;
      color: #999999;
      padding: 12px 0 0 0;
      text-align: center;
      width: auto;
    }
    #footer p {
      font-size: 1em;
    }
    </style>
    </head>
    <body>
      <div id="wrapper">
        <div id="content">
          <div id="contentHeader">
            <div id="logo">
              <a href="http://'.$_SERVER["SERVER_NAME"].'/"><img src="http://assays.cancer.gov/site/library/images/CPTAC_certified_seal_101x101.gif" width="101" height="101" alt="CPTAC Qualified Assay seal"></a>
            </div>
    ',
    'body_connector' => '
            </div>
            <div class="container">
              <div class="grid-24">
    ',
    'body_footer' => '
            </div>
            <div id="footer">
              <p><em>This is an operational email from the CPTAC Assay Portal sent by the <a href="http://'.$_SERVER["SERVER_NAME"].'/">CPTAC Assay Portal web server</a>. Please do not reply to this email.</em></p>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>
    '
  )
);

if (!function_exists('http_parse_headers'))
{
    function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else
                {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else
            {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);
            }
        }

        return $headers;
    }
}

?>
