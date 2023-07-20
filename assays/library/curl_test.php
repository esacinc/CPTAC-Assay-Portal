<?php
/*
 * @desc For testing cURL calls to Panorama.
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

// test

$file = file_get_contents("/swpg_files/cptac/panorama_test/SGNYPSSLSNETDR_validation_sample_SNU_KIST_Kim_FHCRC_Paulovich.png");
$file_to_overwrite = "/swpg_files/cptac/panorama_test/ILTFDQLALDSPK_peptide_chrom_id_977051_Broad_Carr_FHCRC_Paulovich.png";
$writeable = is_writable($file_to_overwrite);
if($writeable) {
  echo "The filesystem is writable.<br>";
} else {
  echo "The filesystem is NOT writable.<br>";
}

die('Done!');

$writable = ( is_writable($file_to_overwrite) ) ? TRUE : chmod($file_to_overwrite, 0755);
if ( $writable ) {
    file_put_contents( $file_to_overwrite , $file );
} else {
    echo "failed\n\n";
}

die("done!!!!");

$ch = curl_init("https://panoramaweb.org/labkey/query/CPTAC%20Assay%20Portal/FHCRC_Paulovich/CellLysate_5500QTRAP_directMRM/ResponseCurve/selectRows.api?schemaName=targetedms&query.queryName=Peptide&query.Sequence~eq=TITVALADGGRPDNTGR");
$fp = fopen("output.txt", "w");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

curl_exec($ch);

$curlVersion = curl_version();
extract(curl_getinfo($ch));
$metrics = <<<EOD
URL....: $url
Code...: $http_code ($redirect_count redirect(s) in $redirect_time secs)
Content: $content_type Size: $download_content_length (Own: $size_download) Filetime: $filetime
Time...: $total_time Start @ $starttransfer_time (DNS: $namelookup_time Connect: $connect_time Request: $pretransfer_time)
Speed..: Down: $speed_download (avg.) Up: $speed_upload (avg.)
Curl...: v{$curlVersion['version']}
EOD;

echo "<pre>";
echo $metrics;
echo "</pre>";

curl_close($ch);
fclose($fp);
echo 'Done! Here\'s a random number as proof: '.rand(1,10000);
?>
