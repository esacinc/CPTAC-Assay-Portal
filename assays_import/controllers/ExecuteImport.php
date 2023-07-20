<?php
/**
 * @desc Import Assays: controller for inserting and updating data
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
namespace assays_import\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use \GuzzleHttp\Client;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays_import\models\UserAccountImport;
use assays_import\models\ImportPanoramaData;
use assays\models\Assay;


class ExecuteImport extends Controller {

    function execute_import(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource);
        $import = new AssaysImport($db_resource, $final_global_template_vars["session_key"]);
        $import_panorama_data = new ImportPanoramaData($db_resource);
        $user_account_import = new UserAccountImport($db_resource, $final_global_template_vars["session_key"]);
        $import_panorama_data = new ImportPanoramaData($db_resource);
        $user = new \user_account\models\UserAccountDao($db_resource, $final_global_template_vars["session_key"]);

        $data = array();
        $laboratory_data = array();
        $get = $request->getParams();
        $post = $request->getParams();

        $data["import_executed_status"] = (isset($get["import_executed_status"]) && ($get["import_executed_status"] == "true")) ? true : false;
        $data["session"] = $_SESSION[$final_global_template_vars["session_key"]];

        $user_laboratory_ids = $data["session"]["associated_groups"];


        // Get the user's roles.
        $user_role_ids = isset($data["session"]["user_role_list"])
            ? $data["session"]["user_role_list"] : array();

        if ($request->isGet()) {
            ///@@@CAP-46
            $this->logger->info("get import details");
            // Get the laboratory metadata via the import_log_id GET variable.
            $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id($get["import_log_id"]);

            $this->logger->info("laboratory_data " . var_export($data['laboratory_data'], true));

            // If get_laboratories() returns false, throw a 404.
            if (!$data["laboratory_data"]) throw new \Slim\Exception\NotFoundException($request, $response);

            // If user is not a superadmin or not in a laboratory (group), throw a 404.
            if (!in_array(4, $user_role_ids) && !in_array($data["laboratory_data"]["laboratory_id"], $user_laboratory_ids)) {
                throw new \Slim\Exception\NotFoundException($request, $response);
            }

            // Get all executed imports data.
            $data["executed_imports"] = $import->get_executed_imports($data["laboratory_data"]["import_log_id"]);

            $this->logger->info("executed imports " . var_export($data["executed_imports"], true));

            // Get all sequences for a lab, to pass it to the next method, check_for_missed_images().
            $all_sequences = $assay->getPeptideSequences($data["laboratory_data"]["import_log_id"]);

            // Get all of the problematic images.
            $data["missed_images"] = $import_panorama_data->check_for_missed_images(
                $data["laboratory_data"]["laboratory_id"]
                , $data["laboratory_data"]["import_log_id"]
                , $all_sequences
            );

            // Get all of the problematic images data.
            $data["missed_images_data"] = $import_panorama_data->check_for_missed_images_data(
                $data["laboratory_data"]["laboratory_id"]
                , $data["laboratory_data"]["import_log_id"]
                , $all_sequences
            );

            $data["deleted"] = (isset($get["deleted"]) && ($get["deleted"] == "true")) ? true : false;
            $data["reset"] = (isset($get["reset"]) && ($get["reset"] == "true")) ? true : false;


            // look for import logs
            $data['path_to_log'] = false;
            $folder_path = $_SERVER['DOCUMENT_ROOT'] . $final_global_template_vars['import_log_location'];
            $folders = scandir($folder_path, 2);
            $ignore = array('.', '..');
            foreach ($folders as $key => $value) {
                if (!in_array($value, $ignore)) {

                    $log_file = $folder_path . '/' . $value . '/' . $data["laboratory_data"]["import_log_id"] . '.txt';
                    if (is_file($log_file)) {
                        $data['path_to_log'] = $final_global_template_vars['import_log_location'] . '/' . $value . '/' . $data["laboratory_data"]["import_log_id"] . '.txt';
                        break;
                    }
                }
            }


        }

        /*
         * Import From Panorama Into Portal Tables:
         *
         * protein
         * analyte_peptide
         * uniprot_splice_junctions
         * uniprot_snps
         * uniprot_isoforms
         *
         */
        if($request->isPost()) {

            $this->logger->info("executed post " . $post['import_log_id']);

            clear_log($post["import_log_id"]);
            write_log($post["import_log_id"],'Import Started. ID:' . $post['import_log_id']);

            // Get the laboratory metadata via the import_log_id POST variable.
            $data["laboratory_data"] = $assay->get_laboratory_by_import_log_id( $post["import_log_id"] );

            $this->logger->info("laboratory data: " . var_export($data['laboratory_data'], true));
            // If get_laboratory_by_import_log_id() returns false, throw a 404.
            if(!$data["laboratory_data"]) throw new \Slim\Exception\NotFoundException($request, $response);
            // Set the run_missed_images variable.
            $test_import = (isset($post["test_import"]) && ($post["test_import"] == "true"))
                ? "&test_import=1" : false;
            // Set the run_missed_images variable.
            $run_missed_images = (isset($post["run_missed_images"]) && ($post["run_missed_images"] == "true"))
                ? "&run_missed_images=true" : false;

            if( !$run_missed_images ) {

                // Execute the full import script.


                $import_type = ($test_import) ? "Test" : "Full";
                write_log($post["import_log_id"],"Execute the {$import_type} import script");

                $log_data["import_log_id"] = $get["import_log_id"];
                $log_data["laboratory_id"] = $data["laboratory_data"]["laboratory_id"];
                $log_data["executed_by_user_id"] = $data["session"]["account_id"];
                $log_data["import_executed_status"] = true;
                $imports_executed_log_id = $import_panorama_data->insert_executed_imports($log_data);

                write_log($get["import_log_id"], 'Import logs updated');

                $url = "https://".$_SERVER["SERVER_NAME"].$final_global_template_vars["path_to_this_module"]
                    . "/import_panorama_protein_peptide/?import_log_id=".$data["laboratory_data"]["import_log_id"]
                    . "&imports_executed_log_id=" . $imports_executed_log_id
                    . "&account_id=" . $data["session"]["account_id"]
                    . $test_import
                    . "&uniquehash=".uniqid();

                $this->logger->info("executed url " . $url);

                //die($url);

/*                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                $result = curl_exec( $ch );
                curl_close( $ch );*/

                $client = new \GuzzleHttp\Client([
                    'base_uri' => "https://".$_SERVER["SERVER_NAME"]. $final_global_template_vars["path_to_this_module"]
                    .  "/import_panorama_protein_peptide", // Base URI is used with relative requests
                    //'timeout' => 20.0, // You can set any number of default request options.
                    'verify' => false
                ]);

                $promise = $client->getAsync($url);
                $promise->then(function ($response) { echo $response->getBody() . PHP_EOL; });

                $result = $promise->wait();

                $this->logger->info("curl result: " . var_dump($result));

                if ($import_type == "Full") {
                    $user_account_import->add_user_account_import($data["laboratory_data"]["import_log_id"]);
                }

                /*
 * Check for missing UniProt data and attempt to import again.
 */
                //@@@CAP-61 - fix assay import blockers
                //write_log($get["import_log_id"], 'Running Uniprot fix');

                //$url = "https://" . $_SERVER["SERVER_NAME"] . "/assays_import/fix_uniprot_import/" . $get["import_log_id"];


                //$this->logger->info("uniprot fix " . $url);

/*                $ch = curl_init($url);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_exec($ch);
                curl_close($ch);*/

                //$promise = $client->getAsync($url);
                //$promise->then(function ($response) { echo $response->getBody() . PHP_EOL; });

                //$result = $promise->wait();


                $url = "https://" . $_SERVER["SERVER_NAME"] . "/assays_import/import_panorama_data/?import_log_id=" . $get["import_log_id"]
                    . "&imports_executed_log_id=" . $imports_executed_log_id
                    . "&account_id=" . $get["account_id"]
                    . $test_import
                    . "&uniquehash=" . uniqid();

                $this->logger->info("import panorama data " . $url);

/*                $ch = curl_init($url);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_exec($ch);
                curl_close($ch);*/


                $promise = $client->getAsync($url);
                $promise->then(function ($response) { echo $response->getBody() . PHP_EOL; });

                $result = $promise->wait();

                return $response->withJson(['import_in_progress' => true]);

            } else {
                // Execute the import for missed images script.
                $url = "https://".$_SERVER["SERVER_NAME"].$final_global_template_vars["path_to_this_module"]
                    ."/import_panorama_data/?import_log_id=".$data["laboratory_data"]["import_log_id"]
                    ."&imports_executed_log_id=".$post["imports_executed_log_id"]
                    .$run_missed_images."&account_id=".$post["account_id"]."&uniquehash=".uniqid();

                $this->logger->info("executed url " . $url);

                write_log($post["import_log_id"],'Execute the import for missed images script');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_exec( $ch );
                curl_close( $ch );

            }

            $data["import_executed_status"] = true;
        }



        // Get the panorama errors email recipients data.
        foreach ($final_global_template_vars["panorama_errors_email_recipients_ids"] as $account_id) {
            $admins[] = $user->get_user_account_info($account_id);
        }
        foreach ($admins as $admin) {
            // Names array.
            $data["panorama_errors_email_recipients"]["names"][] = $admin["given_name"] . " " . $admin["sn"];
        }

        // Get the laboratory name for the page title (superadmin only).
        $laboratory_name = in_array(4, $user_role_ids) ? ": " . $data["laboratory_data"]["laboratory_name"] : "";

        // Render
        $view = $this->container->get('view');
        $view->render($response,
            "execute_import.twig"
            , array(
                "page_title" => "Execute and Manage Import" . $laboratory_name
            , "hide_side_nav" => true
            , "data" => $data
            , "show_log" => $data["import_executed_status"]
            , "log_cache_id" => uniqid()
            )
        );
    }

}
