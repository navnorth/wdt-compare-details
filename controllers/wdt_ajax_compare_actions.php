<?php

defined('ABSPATH') or die('Access denied.');

add_action('plugins_loaded', array('WDTCompareDetail\Plugin', 'init'), 10);
class WPDataTableCompare extends WPDataTable {

  function extendTableObjectCompareAjax(){
      $tableId = (int)$_POST['table_id']; 
      echo json_encode(WPDataTableCompare::getcsvdata($tableId));
      die();
  }
  
  function getcsvdata($tableId){
    
    $tableId = (int)$_POST['table_id']; 
    $tableData = WDTConfigController::loadTableFromDB($tableId);
    if ($tableData) {$tableData->disable_limit = $disableLimit;}
    $wpDataTable = $tableView === 'excel' ? new WPExcelDataTable($tableData->connection) : new self($tableData->connection);
    $wpDataTable->setWpId($tableId);
    $columnDataPrepared = $wpDataTable->prepareColumnData($tableData);
    $wpDataTable->fillFromData($tableData, $columnDataPrepared);
    
    $xls_url = $tableData->content;
    $columns = $tableData->columns;
    $the_big_array = [];

    $the_big_array['column'] = $columns;
    $the_big_array['data'] = $wpDataTable->getdataRows();
    
    return $the_big_array;
  }

  /**
   *  Create visibility toggle variable on column settings apply and table save
   */
  function setVisibilitySession(){
    $_SESSION["wdtvisibilitytoggleloopcount"] = 0;
    $_SESSION["wdtvisibilitytoggleclicked"] = "wdt-apply-columns-list";
    echo 'COLUMN';
    die();
  }

  
}
add_action('wp_ajax_extendTableObjectCompareAjax', array( 'WPDataTableCompare', 'extendTableObjectCompareAjax' ));
add_action('wp_ajax_nopriv_extendTableObjectCompareAjax', array( 'WPDataTableCompare', 'extendTableObjectCompareAjax' ));  

add_action('wp_ajax_setSaveSession', array( 'WPDataTableCompare', 'setSaveSession' ));
add_action('wp_ajax_nopriv_setSaveSession', array( 'WPDataTableCompare', 'setSaveSession' ));  

add_action('wp_ajax_setVisibilitySession', array( 'WPDataTableCompare', 'setVisibilitySession' ));
add_action('wp_ajax_nopriv_setVisibilitySession', array( 'WPDataTableCompare', 'setVisibilitySession' )); 

add_action('wp_ajax_setCompareEnableToggleSession', array( 'WPDataTableCompare', 'setCompareEnableToggleSession' ));
add_action('wp_ajax_nopriv_setCompareEnableToggleSession', array( 'WPDataTableCompare', 'setCompareEnableToggleSession' )); 


?>