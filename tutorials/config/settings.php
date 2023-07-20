<?php
/**
 * @desc Tutorials module settings
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 * Note that you are able to use any key that exists in
 * the global settings, and it will overwrite it
 *
 */
$swpg_module_settings = array(
  "module_name" => "Tutorials"
  ,"module_description" => "Tutorials for importing assay data into the Portal."
  ,"module_icon_css_classes" => "fa fa-fw fa-question-circle"
  ,"sort_order" => 20
  ,"menu_hidden" => isset($_SESSION[$final_global_template_vars["session_key"]]) && $_SESSION[$final_global_template_vars["session_key"]] ? false : true
  ,"navbar" => "/site/templates/admin_navbar_update.php"
  ,"layout_template_name" => "swpg_bootstrap_admin_non_responsive.twig"
  ,"pages" => array(
    array(
      "label" => "Quick Start Guide"
      ,"path" => "/"
      //,"display" => $apply_permissions("tutorials", "access_tutorials")
    )
  )
  , "remove_side_nav" => true
);
?>
