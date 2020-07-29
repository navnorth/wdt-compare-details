<?php

defined('ABSPATH') or die('Access denied.');

add_action('plugins_loaded', array('WDTCompareDetail\Plugin', 'init'), 10);
class WPDataTableCompare extends WPDataTable {

  function extendTableObjectCompareAjax(){

      $tableId = (int)$_POST['table_id']; 

      
      /*
      //$_eWPDataTable::excelBasedConstruct($tableData, WPDataTable::$params);
      //$_tbl = WPDataTable::loadWpDataTable($tableId);
      //echo json_encode($_tbl);
      
      //$wpDataTable = $tableView === 'excel' ? new WPExcelDataTable($tableData->connection) : new $tableData->connection;
      //$wpDataTable->setWpId($tableId);
      //$columnDataPrepared = $wpDataTable->prepareColumnData($tableData);
      //echo json_encode($this->excelBasedConstruct($tableData->content));
      
      // Defining column parameters if provided
      $params = array();
      if (isset($tableData->limit)) {
          $params['limit'] = $tableData->limit;
      }
      if (isset($tableData->table_type)) {
          $params['tableType'] = $tableData->table_type;
      }
      if (isset($columnData['columnTypes'])) {
          $params['data_types'] = $columnData['columnTypes'];
      }
      if (isset($columnData['columnTitles'])) {
          $params['columnTitles'] = $columnData['columnTitles'];
      }
      if (isset($columnData['columnFormulas'])) {
          $params['columnFormulas'] = $columnData['columnFormulas'];
      }
      if (isset($columnData['sorting'])) {
          $params['sorting'] = $columnData['sorting'];
      }
      if (isset($columnData['decimalPlaces'])) {
          $params['decimalPlaces'] = $columnData['decimalPlaces'];
      }
      if (isset($columnData['exactFiltering'])) {
          $params['exactFiltering'] = $columnData['exactFiltering'];
      }
      if (isset($columnData['rangeSlider'])) {
          $params['rangeSlider'] = $columnData['rangeSlider'];
      }
      if (isset($columnData['filterDefaultValue'])) {
          $params['filterDefaultValue'] = $columnData['filterDefaultValue'];
      }
      if (isset($columnData['filterLabel'])) {
          $params['filterLabel'] = $columnData['filterLabel'];
      }
      if (isset($columnData['checkboxesInModal'])) {
          $params['checkboxesInModal'] = $columnData['checkboxesInModal'];
      }
      if (isset($columnData['possibleValuesType'])) {
          $params['possibleValuesType'] = $columnData['possibleValuesType'];
      }
      if (isset($columnData['possibleValuesAddEmpty'])) {
          $params['possibleValuesAddEmpty'] = $columnData['possibleValuesAddEmpty'];
      }
      if (isset($columnData['possibleValuesAjax'])) {
          $params['possibleValuesAjax'] = $columnData['possibleValuesAjax'];
      }
      if (isset($columnData['foreignKeyRule'])) {
          $params['foreignKeyRule'] = $columnData['foreignKeyRule'];
      }
      if (isset($columnData['editingDefaultValue'])) {
          $params['editingDefaultValue'] = $columnData['editingDefaultValue'];
      }
      if (isset($columnData['dateInputFormat'])) {
          $params['dateInputFormat'] = $columnData['dateInputFormat'];
      }
      if (isset($columnData['linkTargetAttribute'])){
          $params['linkTargetAttribute'] = $columnData['linkTargetAttribute'];
      }
      if (isset($columnData['linkButtonAttribute'])){
          $params['linkButtonAttribute'] = $columnData['linkButtonAttribute'];
      }
      if (isset($columnData['linkButtonLabel'])){
          $params['linkButtonLabel'] = $columnData['linkButtonLabel'];
      }
      if (isset($columnData['linkButtonClass'])){
          $params['linkButtonClass'] = $columnData['linkButtonClass'];
      }

      $params = apply_filters('wpdt_filter_column_params', $params, $columnData);
      */
      
      
      //echo json_encode($columnData);
      //echo json_encode(getExcel($tableData->content,$params));
      //echo json_encode($tableData->content);
      
      //echo json_encode($wpDataTable->getdataRows());
      echo json_encode(WPDataTableCompare::getcsvdata($tableId));
      //echo json_encode($WPDataTable->fillFromData($tableData,$columnData));
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
  
  
  
  
}
add_action('wp_ajax_extendTableObjectCompareAjax', array( 'WPDataTableCompare', 'extendTableObjectCompareAjax' ));
add_action('wp_ajax_nopriv_extendTableObjectCompareAjax', array( 'WPDataTableCompare', 'extendTableObjectCompareAjax' ));  


?>