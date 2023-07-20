<?php
namespace assays\models;
use GuzzleHttp\Cookie\FileCookieJar;

/**
 * @desc LabKey/Panorama API for importing data into the CPTAC Assay Portal database
 *
 * @author ESAC's CPTAC Team
 * @version 1.0
 * @package cptac
 *
 */
class ResponseCurveV2LabkeyApi extends LabkeyApi {

    /*
     * Get Peptide Response Curve Image
     */

    public function getPeptideResponseCurveImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $plot_type = false
        , $unit = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptideType = false) {


        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , array(
                'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveQueryV2'
            , 'reportName' => 'ResponseCurveV2'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'plotType' => $plot_type
            , 'unit' => $unit
            , 'showSection' => 'response_curve_png'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , $plot_type
            , false // protein
            , false // csv
            , $peptideType
        );

        $report_name = "response_curve";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , $plot_type
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get LOD/LOQ Data
     */
    public function getLodLoqData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {
        write_log($this->import_log_id, "inside getLodLoqData cookie: " . $panorama_authentication_cookie);
        write_log($this->import_log_id, 'inside getLodLoqData celllysate: ' . $celllysate_path);
        write_log($this->import_log_id, 'inside getLodLoqData library ' . $library);
        write_log($this->import_log_id, 'inside getLodLoqData peptide sequence' . $peptide_sequence);
        write_log($this->import_log_id, 'inside getLodLoqData lab_abbreviation ' . $laboratory_abbreviation);

        $parameters = [
            'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveAnalysisV2'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'reportName' => 'ResponseCurveAnalysisV2'
            , 'showSection' => 'LODCTable.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;

    }

    /*
     * Get Curve Fit Data
     */
    public function getCurveFitData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
            'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveAnalysisV2'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'reportName' => 'ResponseCurveAnalysisV2'
            , 'showSection' => 'fitTable.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }


}
