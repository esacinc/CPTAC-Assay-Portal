<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 6/15/16
 * Time: 3:19 PM
 */

function curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function scrape_between($data, $start, $end) {
    $data = stristr($data, $start); // Stripping all data from before $start
    $data = substr($data, strlen($start));  // Stripping $start
    $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
    $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
    return $data;   // Returning the scraped data from the function
}

function local_phosphosite_request_callback($content, $url, $ch, $search) {
    global $final_global_template_vars;

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode !== 200) {
        write_log("", "Fetch error $httpcode for '$url'\n");
        return;
    }
    write_log("", $content);
}

function phosphosite_request_callback($content, $url, $ch, $search) {

    global $final_global_template_vars;

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode !== 200) {
        print "Fetch error $httpcode for '$url'\n";
        return;
    }

    $phosphosites_page = $content;

    $phosphosites_graph_object = scrape_between($phosphosites_page, '<object width="970"', '</object>');

    if (!$phosphosites_graph_object) {

        preg_match('/<a href="\/..\/proteinAction.action;jsessionid=\\w{32}\?id=\\d+&amp;showAllSites=true" class="link13HoverRed">human<\/a>/u', $phosphosites_page, $match);
        $result = implode($match);
        preg_match('/id=\\d+&amp;showAllSites=true/u', $result, $match);

        //$proteinId = $assay->scrape_between($phosphosites_page, "<a href=\"/../proteinAction.action?id=", "\" class=\"link13HoverRed\">human</a>");
        $proteinId = implode($match);

        $phosphosites_page = curl("http://www.phosphosite.org/proteinAction.action?" . $proteinId);
    }

    $phosphosites_graph = scrape_between($phosphosites_page, '<object width="970" height="300">', '</object>');

    if (!$phosphosites_graph) {
        $phosphosites_graph = scrape_between($phosphosites_page, '<object width="970" height="200">', '</object>');
        $phosphosites_graph = str_replace('ProteinViewer200.swf', '/assays/library/ProteinViewer200.swf', $phosphosites_graph);
    }

    if (!$phosphosites_graph) {
        $phosphosites_graph = scrape_between($phosphosites_page, '<object width="970" height="400">', '</object>');
        $phosphosites_graph = str_replace('ProteinViewer400.swf', '/assays/library/ProteinViewer400.swf', $phosphosites_graph);
    }

    $phosphosites_graph_file = $final_global_template_vars["phosphosite_images_storage_path"] . "/" . $search . "cache.txt";

    file_put_contents($phosphosites_graph_file, $phosphosites_graph);
}

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
