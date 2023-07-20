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
class EndogenousLabkeyApi extends LabkeyApi {

    /*
 * Get Peptide Endogenous Image
 */
    public function getPeptideEndogenousImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $curve_type = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , array(
                'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'Endogenous'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => $curve_type
            , 'reportName' => 'Endogenous'
            , 'showSection' => 'QC_plot_png'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , false // csv
        );

        $report_name = "endogenous";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , false
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Endogenous Data
     */

    public function getPeptideEndogenousData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $curve_type = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
            'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'Endogenous'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => $curve_type
            , 'reportName' => 'Endogenous'
            , 'showSection' => 'CV_results.csv'
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
            , $protein
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
