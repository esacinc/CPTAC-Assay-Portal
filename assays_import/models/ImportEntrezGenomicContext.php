<?php

namespace assays_import\models;
use \PDO;

class ImportEntrezGenomicContext {
  public $db;
  public function __construct($db_connection = false) {
    if ($db_connection && is_object ( $db_connection )) {
      $this->db = $db_connection;
    }
    global $final_global_template_vars;
    $this->final_global_template_vars = $final_global_template_vars;
  }
  //public function import_entrez_gene_data($gene_symbol, $entrez_api_url, $xml2array, $user_email = false) {
  public function import_entrez_gene_data($gene_symbol, $gene_id=false,$uniprot_species=false,$entrez_api_url, $xml2array, $import_log_id,$user_email = false) {
    // References
    // http://www.ncbi.nlm.nih.gov/gene/2064
    // http://www.ncbi.nlm.nih.gov/books/NBK25500/#chapter1.ESearch

    // Get the gene record
    // http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gene&term=ERBB2[gene]+AND+Homo+sapiens[Organism]

    // Use the returned id to query for the full info of the target gene
    // http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=gene&id=2064&retmode=xml

    $data = false;

    //if gene_id returned by uniprot query ncbi by id
    if ($gene_id) {
      $all_gene_data = file_get_contents($entrez_api_url . 'esummary.fcgi?db=gene&id=' . $gene_id . '&retmode=xml');

      if (!empty($all_gene_data)) {
        $gene_data_array = $xml2array->createArray($all_gene_data);

        if (isset($gene_data_array['eSummaryResult']['DocumentSummarySet']['DocumentSummary'])) {
          $gene_array = @$gene_data_array['eSummaryResult']['DocumentSummarySet']['DocumentSummary'];

          if ( isset($gene_array['GenomicInfo']['GenomicInfoType'])) {
            $data['chromosome_start'] = @$gene_array['GenomicInfo']['GenomicInfoType']['ChrStart'];
            $data['chromosome_stop'] = @$gene_array['GenomicInfo']['GenomicInfoType']['ChrStop'];
            $data['chromosome_number'] = @$gene_array['Chromosome'];
          }
        }
      }

      // Update the data in the database
      if ($data['chromosome_start'] && $data['chromosome_stop'] && $data['chromosome_number']) {
        $statement = $this->db->prepare("
            UPDATE protein
            SET chromosome_start = " . $data['chromosome_start'] . "
            ,chromosome_stop = " . $data['chromosome_stop'] . "
            ,chromosome_number = '" . $data['chromosome_number'] . "'
            WHERE gene_symbol = '" . $gene_symbol . "'
            and import_log_id = " . $import_log_id . "
          ");
        $statement->execute();
      }
    } else {
      //query ncbi with gene_symbol and formated species
      $formatted_species = str_replace(" ", "+", $uniprot_species);
      $entrez_url = $entrez_api_url.'esearch.fcgi?db=gene&term='.$gene_symbol.'[gene]+AND+'.$formatted_species.'[Organism]';
      $entrez_gene_query = file_get_contents( $entrez_url );

      if( !empty($entrez_gene_query) )
      {
        $entrez_gene_query_array = $xml2array->createArray($entrez_gene_query);
        // Added 2014-11-07 due to no result returned by Entrez Gene.
        if($entrez_gene_query_array["eSearchResult"]["Count"] > 0) {
          $entrez_gene_id_final = "";
          // If an array of "Id"s is returned, match the returned gene name with the intended local target gene name
          if( is_array($entrez_gene_query_array["eSearchResult"]["IdList"]["Id"]) )
          {
            foreach($entrez_gene_query_array["eSearchResult"]["IdList"]["Id"] as $entrez_gene_id)
            {
              $gene_data = file_get_contents($entrez_api_url.'esummary.fcgi?db=gene&id='.$entrez_gene_id.'&retmode=xml');
              $current_id = false;

              if( !empty($gene_data) )
              {
                $gene_data_array = $xml2array->createArray( $gene_data );

                if( $gene_data_array )
                {
                  if( is_array($gene_data_array["eSummaryResult"]["DocumentSummary"]['GenomicInfo']) )
                  {
                    $current_id = $gene_data_array["eSummaryResult"]["DocumentSummary"]["@attributes"]["uid"];
                  }
                  elseif( is_array($gene_data_array["eSummaryResult"]["DocumentSummarySet"]["DocumentSummary"]['GenomicInfo']) )
                  {
                    $current_id = $gene_data_array["eSummaryResult"]["DocumentSummarySet"]["DocumentSummary"]["@attributes"]["uid"];
                  }

                  //if( $returned_gene_symbol == $gene_symbol && $current_id == $gene_id)
                  if( $current_id == $entrez_gene_id  )
                  {
                    $entrez_gene_id_final = $current_id;
                  }
                }
              }
            }
          }
          else
          {
            // If an array of IDs is not returned, then just use the "Id"
            $entrez_gene_id_final = $entrez_gene_query_array["eSearchResult"]["IdList"]["Id"];
          }

          if($entrez_gene_id_final) {
            //query ncbi with the returned gene_id
            $all_gene_data = file_get_contents($entrez_api_url . 'esummary.fcgi?db=gene&id=' . $entrez_gene_id_final . '&retmode=xml');

            if (!empty($all_gene_data)) {
              $gene_data_array = $xml2array->createArray($all_gene_data);

              if (isset($gene_data_array['eSummaryResult']['DocumentSummarySet']['DocumentSummary'])) {

                $gene_array = @$gene_data_array['eSummaryResult']['DocumentSummarySet']['DocumentSummary'];
                if ( isset($gene_array['GenomicInfo']['GenomicInfoType'])) {
                  $data['chromosome_start'] = @$gene_array['GenomicInfo']['GenomicInfoType']['ChrStart'];
                  $data['chromosome_stop'] = @$gene_array['GenomicInfo']['GenomicInfoType']['ChrStop'];
                  $data['chromosome_number'] = @$gene_array['Chromosome'];
                }
              }
            }

            // Update the data in the database
            if ($data['chromosome_start'] && $data['chromosome_stop'] && $data['chromosome_number']) {
              $statement = $this->db->prepare("
                           UPDATE protein
                           SET chromosome_start = " . $data['chromosome_start'] . "
                           ,chromosome_stop = " . $data['chromosome_stop'] . "
                           ,chromosome_number = '" . $data['chromosome_number'] . "'
                           WHERE gene_symbol = '" . $gene_symbol . "'
                           and import_log_id = " . $import_log_id . "
                           ");
              $statement->execute();
            }
          }

        } else {
          // Send an email to the super admin if no gene symbol.
           mail(
               $this->final_global_template_vars["superadmin_email_address"].", ".$user_email
             , "CPTAC Import: Entrez Import Failed for gene symbol: ".$gene_symbol
             , "Entrez URL for gene symbol ".$gene_symbol.":\n\n".$entrez_url
           );
        }

      }
    }
    return $data;
  }
  public function get_entrez_genomic_context($entrez_gene_data_array) {
    $data ["chromosome_number"] = false;
    $data ["chromosome_start"] = $this->get_entrez_chromosomal_coordinates ( $entrez_gene_data_array, 'ChrStart' );
    $data ["chromosome_stop"] = $this->get_entrez_chromosomal_coordinates ( $entrez_gene_data_array, 'ChrStop' );

    for($i = 0; $i < count ( $entrez_gene_data_array ); $i ++) {
      $isarray1 = array_key_exists ( "DocumentSummary", $entrez_gene_data_array [$i] );
      if ($isarray1) {
        $isarray2 = array_key_exists ( "DocumentSummary", $entrez_gene_data_array [$i] ["DocumentSummary"] );
        if ($isarray2) {
          foreach ( $entrez_gene_data_array [$i] ["DocumentSummary"] ["DocumentSummary"] as $set ) {
            $isarray3 = array_key_exists ( "@attributes", $set );
            if ($isarray3) {
              $name_attribute = $this->search_array ( $set, 'Name', 'ChrLoc' );
              if ($name_attribute) {
                $data ["chromosome_number"] = ( int ) $set ["@value"];
              }
            }
          }
        }
      }
    }
    return $data;
  }
  public function get_entrez_chromosomal_coordinates($entrez_gene_data_array, $start_or_end) {
    $data = false;

    for($i = 0; $i < count ( $entrez_gene_data_array ); $i ++) {

      $isarray1 = array_key_exists ( "DocumentSummary", $entrez_gene_data_array [$i] );
      if ($isarray1) {
        $isarray2 = array_key_exists ( "DocumentSummary", $entrez_gene_data_array [$i] ["DocumentSummary"] );
        if ($isarray2) {
          foreach ( $entrez_gene_data_array [$i] ["DocumentSummary"] ["DocumentSummary"] as $set ) {
            $isarray3 = array_key_exists ( "@attributes", $set );
            if ($isarray3) {
              $name_attribute = $this->search_array ( $set, 'Name', $start_or_end );
              if ($name_attribute) {
                $data = ($set ["@value"] + 1);
              }
            }
          }
        }
      }
    }
    return $data;
  }
  public function search_array($array, $key, $value) {
    $results = array ();
    if (is_array ( $array )) {
      if (isset ( $array [$key] ) && $array [$key] == $value)
        $results [] = $array;
      foreach ( $array as $subarray )
        $results = array_merge ( $results, $this->search_array ( $subarray, $key, $value ) );
    }
    return $results;
  }
}
