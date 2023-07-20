<?php
function delete_interlab_data() {
  $app = \Slim\Slim::getInstance();
  $env = $app->environment();
  global $final_global_template_vars;
  require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";

  $db_conn = new \swpg\models\db( $final_global_template_vars["db_connection"] );
  $db_resource = $db_conn->get_resource();
  $result = false;
  $assay = new Assay( $db_resource );

  // Select which scripts to execute (boolean)
  $execute['delete_interlab_data'] = false;

  // Filesystem array
  $csv_base_directory = '/mnt/webrepo/fr-s-swpg-cpt-d/csv';
  $lod_loq_comparisons_directory_name = 'lod_loq_comparisons';
  $response_curves_directory_name = 'response_curves';

  // $hutch_sequences = $assay->get_hutch_interlab_sequences( $csv_base_directory, $lod_loq_comparisons_directory_name );
  

  $csv_laboratories_directories = $assay->get_directory_listing_of_directories( $csv_base_directory, 'FHCRC_Paulovich' );

  // Delete Interlab data
  if($execute['delete_interlab_data']) {
    foreach( $csv_laboratories_directories as $csv_laboratories_directory ) {

      // Get the laboratory_id by the abbreviation (directory)
      $laboratory_id = $assay->get_laboratory_id_by_abbreviation( $csv_laboratories_directory );

      $csv_files = $assay->get_directory_listing_of_interlab_files( $csv_base_directory.'/'.$csv_laboratories_directory.'/'.$lod_loq_comparisons_directory_name );

     
      foreach( $csv_files as $csv_file ) {
        $rows = new SplFileObject( $csv_base_directory.'/'.$csv_laboratories_directory.'/'.$lod_loq_comparisons_directory_name.'/'.$csv_file );
        $rows->setFlags(SplFileObject::READ_CSV);

        $i=0;
        foreach( $rows as $row )
        {
          // Skip the first line, which are the column names
          if($i > 0) {

            // Skip empty lines
            if($row[0] != NULL) {

              // Strip the modified sequence of the string --> [+57]
              $replacements = array();
              $patterns = array();
              $replacements[0] = '';
              $patterns[0] = '/[\[\+\d+\]]/';
              $peptide_sequences_stripped[] = preg_replace($patterns, $replacements, $row[1]);

            }
          }
          $i++;
          // if($i > 4) break;
        }
      }

      foreach( array_unique($peptide_sequences_stripped) as $peptide_sequence ) {

        // Get the analyte peptide ids and protein ids
        $analyte_and_protein_ids = $assay->get_analyte_and_protein_ids( $peptide_sequence, $laboratory_id );
  
        // Loop through and delete crap
        if(!empty($analyte_and_protein_ids)) {

          foreach( $analyte_and_protein_ids as $ids ) {

            // Method: delete_data_from_tables()
            // Params: $table_name, $field_name, $id

            // Delete from analytical_validation_of_assay table
            $assay->delete_data_from_tables( 'analytical_validation_of_assay', 'analyte_peptide_id', (int)$ids['analyte_peptide_id'] );
            // Delete from assay_parameters table
            $assay->delete_data_from_tables( 'assay_parameters', 'analyte_peptide_id', (int)$ids['analyte_peptide_id'] );
            // Delete from analyte_peptide table
            $assay->delete_data_from_tables( 'analyte_peptide', 'protein_id', (int)$ids['protein_id'] );
            // Delete from protein table
            $assay->delete_data_from_tables( 'protein', 'protein_id', (int)$ids['protein_id'] );
            // Delete from protein_species_join table
            $assay->delete_data_from_tables( 'protein_species_join', 'protein_id', (int)$ids['protein_id'] );
            // Delete from matrix_join table
            $assay->delete_data_from_tables( 'matrix_join', 'analytical_validation_of_assay_id', (int)$ids['protein_id'] );
            // Delete from matrix_amount_units_join table
            $assay->delete_data_from_tables( 'matrix_amount_units_join', 'analytical_validation_of_assay_id', (int)$ids['protein_id'] );
            // Delete from lod_loq_method_type_join table
            $assay->delete_data_from_tables( 'lod_loq_method_type_join', 'analytical_validation_of_assay_id', (int)$ids['protein_id'] );
            // Delete from curve_type_join table
            $assay->delete_data_from_tables( 'curve_type_join', 'analytical_validation_of_assay_id', (int)$ids['protein_id'] );
            // Delete from lod_loq_comparison table
            $assay->delete_data_from_tables( 'lod_loq_comparison', 'analyte_peptide_id', (int)$ids['analyte_peptide_id'] );
            // Delete from response_curves_data table
            $assay->delete_data_from_tables( 'response_curves_data', 'analyte_peptide_id', (int)$ids['analyte_peptide_id'] );
          }

        } else {
          // $empty_results[] = array('laboratory' => $csv_laboratories_directory, 'peptide_sequence' => $peptide_sequence);
          // echo "<br>".$csv_laboratories_directory."<br>";
          // echo $peptide_sequence."<br>";
        }

      }

    }
  }

  // Set the message
  if( $execute['delete_interlab_data'] ) {
    $message = 'Interlab data deleted successfully.';
  } else {
    $message = 'Nothing executed. Enable a script block to execute.';
  }

  $app->render(
    'delete_interlab_data.php'
    ,array(
      "page_title" => "Delete Interlab Data"
      ,"hide_side_nav" => true
      ,"menu" => $menu
      ,"message" => $message
    )
  );
}
?>