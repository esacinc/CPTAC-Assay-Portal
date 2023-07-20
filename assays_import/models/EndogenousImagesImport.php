<?php

/**
 * Validation Sample Images Import Class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

use core\models\Db\EndogenousImages;

class EndogenousImagesImport {
    private $session_key = "";
    public $db;

    /**
     * Constructor
     *
     * @param object $db_connection The database connection object
     * @param object $import_panorama_data The Import Panorama Data class
     * @param object $labkey The LabkeyApi class
     */

    public function __construct($db_connection = false, $import_panorama_data = false, $labkey = false) {

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


        // Set the $result to an empty array, by default.
        $result = array();
        $i = 0;


        foreach ($sequences as $sequence) {

            $error_data = [
                "import_log_id" => $lab_data["import_log_id"],
                "analyte_peptide_id" => $sequence['analyte_peptide_id'],
                "peptide_sequence" => $sequence['peptide_sequence'],
                "modified_peptide_sequence" => $sequence["peptide_modified_sequence"],
                "laboratory_name" => $sequence['laboratory_name'],
                "laboratory_abbreviation" => $sequence['laboratory_abbreviation'],
                //"error_response" => "Test error",
                //"panorama_url" => "http://cptac-proliant-linux.esacinc.com/labkey//query/CPTAC%20Assay%20Portal/CPTAC_TEST/CPTAC_Import_Test",
                "experiment_type" => "Endogenous",
                "data_type" => "Image"
            ];

            /*
             * First, delete any old data and images.
             */

            if ($delete_old_files_and_data) {

                write_log($lab_data["import_log_id"], 'Deleting any old data and images');

                // Get the file names.
                $file_names = $this->import_panorama_data->get_images_data(
                    'panorama_endogenous_images'
                    , $sequence['analyte_peptide_id']
                    , $sequence['laboratories_id']
                );

                // Loop through, delete the files, and delete the corresponding database records.
                foreach ($file_names as $file) {
                    // Files
                    if (file_exists($this->final_global_template_vars["panorama_images_storage_path"] . $file["file_name"])) {
                        unlink($this->final_global_template_vars["panorama_images_storage_path"] . $file["file_name"]);
                    }
                    // Data
                    $this->import_panorama_data->delete_data('panorama_endogenous_images', $file["endogenous_images_id"]);
                }

                write_log($lab_data["import_log_id"], 'Deleting complete');

            }

            // Query Panorama peptide table for the data (modified_sequence and protein) using the peptide sequence
            $peptide_data = $this->labkey->getPeptide(
                $panorama_authentication_cookie
                , "targetedms"
                , "Peptide"
                , $sequence['peptide_sequence']
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'Endogenous'
            );


            if (isset($peptide_data->response) && !empty($peptide_data->response)) {

                $peptide_data = json_decode($peptide_data->response, true);


                if ($peptide_data['rowCount'] > 0) {

                    $this_peptide = $peptide_data['rows'][0];

                    // Query Panorama precursor table for the "Charge" using the modified peptide id
                    $peptide_charge = $this->labkey->getPrecursorChargeData(
                        $panorama_authentication_cookie
                        , "targetedms"
                        , "Precursor"
                        , $this_peptide['Id']
                        , $sequence['laboratory_abbreviation']
                        , $sequence['celllysate_path']
                        , 'Endogenous'
                    );

                    if (isset($peptide_charge->response) && !empty($peptide_charge->response)) {

                        $import_log_id = $lab_data['import_log_id'];
                        $peptide_charge = json_decode($peptide_charge->response, true);
                        $modified_sequence = $this_peptide['PeptideModifiedSequence'];
                        $protein = $this_peptide['PeptideGroupId/Label'];
                        $charge = $peptide_charge['rows'][0]["Charge"];

                        // Query Panorama precursor table for the "curve_type" using the peptide modified sequence
                        $curve_type_data = $this->labkey->getCurveTypeData(
                            $panorama_authentication_cookie
                            , "targetedms"
                            , "Precursor"
                            , $modified_sequence
                            , $sequence['laboratory_abbreviation']
                            , $sequence['celllysate_path']
                            , 'Endogenous'
                        );

                        if (isset($curve_type_data->response) && !empty($curve_type_data->response)) {
                            $curve_type_data = json_decode($curve_type_data->response, true);
                            $curve_type_data = $curve_type_data['rows'];
                            $isotope_standard_status = false;
                            $isotope_standard_label = false;
                            foreach ($curve_type_data as $curve_type_value) {
                                $isotope_standard = $curve_type_value['IsotopeLabelId/Standard'];
                                $isotope_label = $curve_type_value['IsotopeLabelId/Name'];
                                if ($isotope_standard) {
                                    $isotope_standard_status = $isotope_standard;
                                    $isotope_standard_label = $isotope_label;
                                    break;
                                }
                            }
                            if ($isotope_standard_status) {
                                if ($isotope_standard_label == 'heavy') {
                                    $curve_type = 'forward';
                                } else {
                                    $curve_type = 'reverse';
                                }
                                // Query Panorama for validation sample image using the modified sequence, protein, charge and curve type
                                $endogenous_data = $this->labkey->getPeptideEndogenousImage(
                                    $panorama_authentication_cookie
                                    , $modified_sequence
                                    , $protein
                                    , $charge
                                    , $curve_type
                                    , $sequence['laboratory_abbreviation']
                                    , $sequence['celllysate_path']
                                    , 'Endogenous'
                                );

                                if (isset($endogenous_data->response) && !empty($endogenous_data->response)) {

                                    if (stristr($endogenous_data->response, 'Error executing command') == false) {

                                        $endogenous_image = explode('/', $endogenous_data->response);
                                        // Log an error or insert into the database
                                        if(isset($endogenous_image[6]) && !empty($endogenous_image[6])) {
                                            $data = [
                                                'file_name' => $endogenous_image[6],
                                                'peptide_sequence' => $sequence['peptide_sequence'],
                                                'analyte_peptide_id' => $sequence['analyte_peptide_id'],
                                                'laboratory_id' => $sequence['laboratory_id'],
                                                'import_log_id' => $import_log_id
                                            ];
                                            $endogenous_image = EndogenousImages::where([
                                                ['peptide_sequence', $sequence['peptide_sequence']],
                                                ['analyte_peptide_id', $sequence['analyte_peptide_id']],
                                                ['laboratory_id', $sequence['laboratory_id']],
                                                ['file_name', $endogenous_image[6]]
                                            ])->first();
                                            if (empty($endogenous_image)) {
                                                $endogenous_image = new EndogenousImages();
                                                $endogenous_image->fill($data);
                                                $endogenous_image->created_date = date("Y-m-d H:i:s");
                                            } else {
                                                $endogenous_image->fill($data);
                                            }
                                            $endogenous_image->save();
                                        } else {
                                            // Log the failed image retrieval in the database
                                            $this_response = json_decode($endogenous_data->response, true);
                                            if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {

                                                $result["errors"]["get_peptide_endogenous"][$i] = $sequence['peptide_modified_sequence'];
                                                // Insert into the database
                                                $this->import_panorama_data->panorama_endogenous_images_failed(
                                                    $lab_data["import_log_id"]
                                                    , $sequence['analyte_peptide_id']
                                                    , $sequence['peptide_sequence']
                                                    , $sequence["peptide_modified_sequence"]
                                                    , $sequence['laboratory_name']
                                                    , $sequence['laboratory_abbreviation']
                                                    , $this_response['html']
                                                    , $this_response["panoramaUrl"]
                                                );
                                            }
                                        }

                                    } else {

                                        $result["errors"]["get_peptide_endogenous"][$i] = $sequence['peptide_sequence'];

                                        // Error returned from Panorama.
                                        // Log the error to the 'panorama_endogenous_images_failed' table of the database.
                                        $this->import_panorama_data->panorama_endogenous_images_failed(
                                            $lab_data["import_log_id"]
                                            , $sequence['analyte_peptide_id']
                                            , $sequence['peptide_sequence']
                                            , $sequence["peptide_modified_sequence"]
                                            , $sequence['laboratory_name']
                                            , $sequence['laboratory_abbreviation']
                                            , $endogenous_data->response
                                            , $endogenous_data->panoramaUrl
                                        );
                                    }
                                }
                            } else {
                                $result["errors"]["get_peptide_endogenous_internal_standard"][$i] = $sequence['peptide_sequence'];
                                // Log the failed 'internal standard' retrieval in the database
                                $this->import_panorama_data->panorama_endogenous_images_failed(
                                    $lab_data["import_log_id"]
                                    , $sequence['analyte_peptide_id']
                                    , $sequence['peptide_sequence']
                                    , $sequence["peptide_modified_sequence"]
                                    , $sequence['laboratory_name']
                                    , $sequence['laboratory_abbreviation']
                                    , 'Failed to retrieve internal standard'
                                    , false
                                );
                            }
                        } else {
                            $result["errors"]["get_peptide_endogenous_curve_type"][$i] = $sequence['peptide_sequence'];
                            // Log the failed 'curve type' retrieval in the database
                            $this->import_panorama_data->panorama_endogenous_images_failed(
                                $lab_data["import_log_id"]
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , 'Failed to retrieve curve type'
                                , false
                            );
                        }
                    } else {

                        $result["errors"]["get_peptide_endogenous_charge"][$i] = $sequence['peptide_sequence'];

                        // Log the failed 'charge' retrieval in the database
                        $this->import_panorama_data->panorama_endogenous_images_failed(
                            $lab_data["import_log_id"]
                            , $sequence['analyte_peptide_id']
                            , $sequence['peptide_sequence']
                            , $sequence["peptide_modified_sequence"]
                            , $sequence['laboratory_name']
                            , $sequence['laboratory_abbreviation']
                            , 'Failed to retrieve charge'
                            , false
                        );
                    }
                } else {

                    $result["errors"]["get_peptide_rows"][$i] = $sequence['peptide_sequence'];
                    // Log the failed peptide retrieval in the database
                    $this->import_panorama_data->panorama_endogenous_images_failed(
                        $lab_data["import_log_id"]
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence["peptide_modified_sequence"]
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , 'Unable to get peptide data (no rows returned)'
                        , false
                    );
                }
            } else {

                $result["errors"]["get_peptide_response"][$i] = $sequence['peptide_sequence'];
                // Log the failed peptide retrieval in the database
                $this->import_panorama_data->panorama_endogenous_images_failed(
                    $lab_data["import_log_id"]
                    , $sequence['analyte_peptide_id']
                    , $sequence['peptide_sequence']
                    , $sequence["peptide_modified_sequence"]
                    , $sequence['laboratory_name']
                    , $sequence['laboratory_abbreviation']
                    , 'Unable to get peptide data (no response returned)'
                    , false
                );
            }

            // Make sure we give Panorama enough time
            usleep(500000);

            $i++;
            // if($i > 14) break;
        }

        return $result;
    }

}
