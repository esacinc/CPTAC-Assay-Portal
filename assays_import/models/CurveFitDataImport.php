<?php
/**
 * LOD LOQ Data Import Class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class CurveFitDataImport {
    private $session_key = "";
    public $db;

    /**
     * Constructor
     *
     * @param object $db_connection The database connection object
     * @param object $import_panorama_data The Import Panorama Data class
     * @param object $labkey The LabkeyApi class
     * @param object $plots The importPlotsData class
     */

    public function __construct($db_connection = false, $import_panorama_data = false, $labkey = false, $plots = false) {

        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }

        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;

        if ($import_panorama_data && is_object($import_panorama_data)) {
            $this->import_panorama_data = $import_panorama_data;
        }

        if ($labkey && is_object($labkey)) {
            $this->labkey = $labkey;
        }

        if ($plots && is_object($plots)) {
            $this->plots = $plots;
        }

    }

    /**
     * Run Import
     *
     * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
     * @param array $sequences The sequences array
     * @param string $panorama_authentication_cookie The Panorama authentication cookie value
     * @param bool $delete_old_files_and_data Whether to delete old files and data
     * @param array $lab_data The laboratory data array
     * @return array
     */

    public function run_import(
        $sequences = array()
        , $panorama_authentication_cookie = false
        , $delete_old_files_and_data = false
        , $lab_data = false) {


        write_log($lab_data["import_log_id"], 'Running Curve Fit Data Import');

        // Set the $result to an empty array, by default.
        $result = array();
        $i = 0;
        foreach ($sequences as $sequence) {


            if (!isset($sequence['peptide_modified_sequence']) && isset($sequence['modified_peptide_sequence'])) {
                $sequence['peptide_modified_sequence'] = $sequence['modified_peptide_sequence'];
            }

            write_log($lab_data["import_log_id"], 'Curve Fit record:' . $sequence['peptide_modified_sequence']);


            // Query Panorama for Curve Fit data using the modified sequence
            $curve_fit_data = $this->labkey->getCurveFitData(
                $panorama_authentication_cookie
                , $sequence['peptide_modified_sequence']
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ResponseCurve'
            );
            //give Panorama time to respond
            usleep(5000000);
            write_log($lab_data["import_log_id"], 'Result of curve fit query:' . print_r(var_dump($curve_fit_data)));

            if (isset($curve_fit_data->response) && !empty($curve_fit_data->response)) {

                if (stristr($curve_fit_data->response, 'Error executing command') == false) {

                    $this_response = json_decode($curve_fit_data->response, true);

                    if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {

                        $result["errors"]["get_curve_fit_data"][$i] = $sequence['peptide_sequence'];

                        write_log($lab_data["import_log_id"], 'Curve Fit <span class="import-error">ERROR</span>: ' . $this_response['html']);

                        // Log the failed image retreival in the database
                        $this->import_panorama_data->curve_fit_data_failed(
                            $lab_data["import_log_id"]
                            , $sequence['analyte_peptide_id']
                            , $sequence['peptide_sequence']
                            , $sequence['peptide_modified_sequence']
                            , $sequence['laboratory_name']
                            , $sequence['laboratory_abbreviation']
                            , $this_response['html']
                            , $this_response["panoramaUrl"]
                        );
                    } else {
                        // Create the array for the database insert
                        $file_arrays = array();
                        $file = explode("\n", $curve_fit_data->response);
                        array_pop($file);
                        $a = 0;

                        foreach ($file as $file_value) {
                            // Skip the first line, which are the column names
                            if ($a > 0) {
                                $file_array = explode(',', $file_value);
                                array_push($file_array, $sequence['peptide_sequence']);
                                array_push($file_array, $sequence['analyte_peptide_id']);
                                array_push($file_array, $sequence['laboratories_id']);
                                array_push($file_array, $lab_data["import_log_id"]);
                                $file_arrays[] = $file_array;
                            }
                            $a++;
                        }

                        // Insert file data into the database.
                        foreach ($file_arrays as $file_array) {
                            $this->plots->import_curve_fit_data($file_array);
                            write_log($lab_data["import_log_id"], 'Saving data: ' . $sequence['peptide_sequence']);

                        }

                    }

                } else {

                    $result["errors"]["get_curve_fit_data"][$i] = $sequence['peptide_sequence'];

                    write_log($lab_data["import_log_id"], 'Curve Fit <span class="import-error">ERROR</span>: ' . $curve_fit_data->response);
                    write_log($lab_data["import_log_id"], 'Curve Fit <span class="import-error">ERROR URL</span>: ' . $curve_fit_data->panoramaUrl);

                    // Error returned from Panorama.
                    // Log the error to the 'curve_fit_data_failed' table of the database.
                    $this->import_panorama_data->curve_fit_data_failed(
                        $lab_data["import_log_id"]
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence['peptide_modified_sequence']
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , $curve_fit_data->response
                        , $curve_fit_data->panoramaUrl
                    );
                }

            }
            $i++;
            // if($i > 6) break;
        }

        return $result;
    }

}
