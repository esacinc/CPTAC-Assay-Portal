<?php
/**
 * Response Curve Images Import Class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class ResponseCurveImagesImport {
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


    public function get_peptide_type($import_log_id) {
        $sql = "SELECT peptide_standard_purity_types.type
            FROM assay_parameters_new
            LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
            WHERE assay_parameters_new.import_log_id = :import_log_id";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result['type'] != 'Crude') $result['type'] = 'purified';

        return $result['type'];
    }

    public function get_unit($import_log_id) {
        $sql = "SELECT assay_parameters_new.quantification_units
             FROM assay_parameters_new
             WHERE assay_parameters_new.import_log_id = :import_log_id";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result['quantification_units'];
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
        $response_curve = array();
        $i = 0;
        foreach ($sequences as $sequence) {

            /*
             * First, delete any old data and images.
             */

            if ($delete_old_files_and_data) {

                // Get the file names.
                $file_names = $this->import_panorama_data->get_images_data(
                    'panorama_response_curve_images'
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
                    $this->import_panorama_data->delete_data('panorama_response_curve_images', $file["response_curve_images_id"]);
                }

            }

            /*
             * Process the import logic.
             */

            // the peptideType
            $peptideType = $this->get_peptide_type($lab_data["import_log_id"]);
            // the unit
            $unit = $this->get_unit($lab_data["import_log_id"]);

            $response_curve["response_curve_image_linear"] = false;
            $response_curve["response_curve_image_log"] = false;
            $response_curve["response_curve_image_residual"] = false;
            $response_curve["import_log_id"] = $lab_data["import_log_id"];
            $response_curve["peptide_sequence"] = $sequence["peptide_sequence"];
            $response_curve["analyte_peptide_id"] = $sequence['analyte_peptide_id'];
            $response_curve["laboratory_id"] = $sequence['laboratories_id'];

            /*
             * Query Panorama for the linear response curve.
             */
            $response_data_linear = $this->labkey->getPeptideResponseCurveImage(
                $panorama_authentication_cookie
                , $sequence["peptide_modified_sequence"]
                , 'linear'
                , $unit
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ResponseCurve'
                , $peptideType

            );

            $image_test_data = isset($response_data_linear) ? json_encode($response_data_linear) : 'FALSE';


            if (isset($response_data_linear->response) && !empty($response_data_linear->response)) {

                if (stristr($response_data_linear->response, 'Error executing command') == false) {

                    $response_curve_image_linear = explode('/', $response_data_linear->response);

                    if (!isset($response_curve_image_linear[6])) {
                        // Log the failed image retreival in the database
                        $this_response = json_decode($response_data_linear->response, true);
                        if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {
                            // Insert into the database
                            $this->import_panorama_data->panorama_response_curve_images_failed(
                                $lab_data["import_log_id"]
                                , 'linear'
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , $this_response['html']
                                , $this_response["panoramaUrl"]
                            );
                        }
                        $response_curve["response_curve_image_linear"] = false;
                    } else {
                        // Add image data to the array used for inserting into the database.
                        $response_curve["response_curve_image_linear"] = $response_curve_image_linear[6];
                    }

                } else {

                    $result["errors"]["linear_response_curve"][$i] = $sequence['peptide_sequence'];

                    // Error returned from Panorama.
                    // Log the error to the 'panorama_response_curve_images_failed' table of the database.
                    $this->import_panorama_data->panorama_response_curve_images_failed(
                        $lab_data["import_log_id"]
                        , 'linear'
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence["peptide_modified_sequence"]
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , $response_data_linear->response
                        , $response_data_linear->panoramaUrl
                    );
                    $response_curve["response_curve_image_linear"] = false;
                }
            }

            // Make sure we give Panorama enough time.
            //usleep(500000);
            usleep(10000000);

            /*
             * The log response curve.
             */
            $response_data_log = $this->labkey->getPeptideResponseCurveImage(
                $panorama_authentication_cookie
                , $sequence["peptide_modified_sequence"]
                , 'log'
                , $unit
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ResponseCurve'
                , $peptideType
            );

            if (isset($response_data_log->response) && !empty($response_data_log->response)) {

                if (stristr($response_data_log->response, 'Error executing command') == false) {

                    $response_curve_image_log = explode('/', $response_data_log->response);

                    $response_curve["response_curve_image_log"] = false;

                    if (!isset($response_curve_image_log[6])) {

                        $result["errors"]["log_response_curve"][$i] = $sequence['peptide_sequence'];

                        // Log the failed image retreival in the database
                        $this_response = json_decode($response_data_log->response, true);
                        if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {
                            // Insert into the database
                            $this->import_panorama_data->panorama_response_curve_images_failed(
                                $lab_data["import_log_id"]
                                , 'log'
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , $this_response['html']
                                , $this_response["panoramaUrl"]
                            );
                        }
                        $response_curve["response_curve_image_log"] = false;
                    } else {
                        // Add image data to the array used for inserting into the database.
                        $response_curve["response_curve_image_log"] = $response_curve_image_log[6];
                    }

                } else {

                    $result["errors"]["log_response_curve"][$i] = $sequence['peptide_sequence'];

                    // Error returned from Panorama.
                    // Log the error to the 'panorama_response_curve_images_failed' table of the database.
                    $this->import_panorama_data->panorama_response_curve_images_failed(
                        $lab_data["import_log_id"]
                        , 'log'
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence["peptide_modified_sequence"]
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , $response_data_log->response
                        , $response_data_log->panoramaUrl
                    );
                    $response_curve["response_curve_image_log"] = false;
                }
            }

            // Make sure we give Panorama enough time.
            //usleep(500000);
            usleep(10000000);

            /*
             * The residual response curve.
             */
            $response_data_residual = $this->labkey->getPeptideResponseCurveImage(
                $panorama_authentication_cookie
                , $sequence["peptide_modified_sequence"]
                , 'residual'
                , $unit
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ResponseCurve'
                , $peptideType
            );


            if (isset($response_data_residual->response) && !empty($response_data_residual->response)) {

                if (stristr($response_data_residual->response, 'Error executing command') == false) {

                    $response_curve_image_residual = explode('/', $response_data_residual->response);

                    if (!isset($response_curve_image_residual[6])) {

                        $result["errors"]["residual_response_curve"][$i] = $sequence['peptide_sequence'];

                        // Log the failed image retreival in the database
                        $this_response = json_decode($response_data_residual->response, true);
                        if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {
                            // Insert into the database
                            $this->import_panorama_data->panorama_response_curve_images_failed(
                                $lab_data["import_log_id"]
                                , 'residual'
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , $sequence['celllysate_path']
                                , $this_response['html']
                                , $this_response["panoramaUrl"]
                            );
                        }
                        $response_curve["response_curve_image_residual"] = false;
                    } else {
                        // Add image data to the array used for inserting into the database.
                        $response_curve["response_curve_image_residual"] = $response_curve_image_residual[6];
                    }

                } else {

                    $result["errors"]["residual_response_curve"][$i] = $sequence['peptide_sequence'];

                    // Error returned from Panorama.
                    // Log the error to the 'panorama_response_curve_images_failed' table of the database.
                    $this->import_panorama_data->panorama_response_curve_images_failed(
                        $lab_data["import_log_id"]
                        , 'residual'
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence["peptide_modified_sequence"]
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , $response_data_residual->response
                        , $response_data_residual->panoramaUrl
                    );
                    $response_curve["response_curve_image_residual"] = false;
                }
            }

            // Make sure we give Panorama enough time.
            usleep(10000000);

            // Insert files data into the database
            $this->import_panorama_data->import_response_curve_images($response_curve);

            $i++;
            // if($i > 15) break;
        }

        return $result;

    }

}
