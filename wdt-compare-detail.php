<?php
namespace WDTCompareDetail;

/**
 * @package Compare-Detail Tables for wpDataTables
 * @version 1.1.1
 */
/*
Plugin Name: Compare-Detail Tables for wpDataTables
Plugin URI: https://wpdatatables.com/documentation/addons/compare-detail/
Description: A wpDataTables addon which allows showing additional details for a specific row in a popup or a separate page or post. Handy when you would like to keep fewer columns in the table, while allowing user to access full details of particular entries.
Version: 1.1.1
Author: TMS-Plugins
Author URI: http://tms-plugins.com
Text Domain: wpdatatables
Domain Path: /languages
*/

use Exception;
//use CompareDetailWDTColumn;
use WDTConfigController;
use WDTColumn;
use WDTTools;
use WP_Error;
use WPDataTable;


defined('ABSPATH') or die('Access denied');
// Full path to the WDT Compare-detail root directory
define('WDT_CD_ROOT_PATH', plugin_dir_path(__FILE__));
// URL of WDT Compare-detail plugin
define('WDT_CD_ROOT_URL', plugin_dir_url(__FILE__));
// Current version of WDT Compare-detail plugin
define('WDT_CD_VERSION', '1.1.1');
// Required wpDataTables version
define('WDT_CD_VERSION_TO_CHECK', '2.8.2');
// Path to Compare-detail templates
define('WDT_CD_TEMPLATE_PATH', WDT_CD_ROOT_PATH . 'templates/');


// Init Compare-detail for wpDataTables add-on
add_action('plugins_loaded', array('WDTCompareDetail\Plugin', 'init'), 10);

register_deactivation_hook(__FILE__,  array('WDTCompareDetail\Plugin', 'deactivateCompareDetail'));
register_uninstall_hook(__FILE__, array('WDTCompareDetail\Plugin', 'uninstallCompareDetail'));


/**
 * Class Plugin
 * Main entry point of the wpDataTables Compare-detail add-on
 * @package WDTCompareDetail
 */

class Plugin
{

    public static $initialized = false;

    /**
     * Instantiates the class
     * @return bool
     */
    public static function init()
    {
        // Check if wpDataTables is installed
        if (!defined('WDT_ROOT_PATH')) {
            add_action('admin_notices', array('WDTCompareDetail\Plugin', 'wdtNotInstalled'));
            return false;
        }
        
        // Add JS and CSS for editable tables on backend
        add_action('wdt_enqueue_on_edit_page', array('WDTCompareDetail\Plugin', 'wdtCompareDetailEnqueueBackendCompare'));

        // Add JS and CSS for editable tables on frontend
        add_action('wdt_enqueue_on_frontend', array('wdtCompareDetail\Plugin', 'wdtCompareDetailEnqueueFrontendCompare'));


        
        // Add "Compare-Detail" tab on table configuration page
        add_action('wdt_add_table_configuration_tab', array('WDTCompareDetail\Plugin', 'addCompareDetailSettingsTabCompare'));

        // Add tab panel for "Compare-detail" tab on table configuration page
        add_action('wdt_add_table_configuration_tabpanel', array('WDTCompareDetail\Plugin', 'addCompareDetailSettingsTabPanelCompare'));

        // Add element in Display column settings for "Compare-detail" table
        add_action('wdt_add_column_display_settings_element', array('WDTCompareDetail\Plugin', 'addCompareDetailColumnSettingsElementCompare'));

        // Add new column type option
        //add_action('wpdatatables_add_custom_column_type_option', array('WDTCompareDetail\Plugin', 'addCompareDetailColumnTypeOptionCompare'));

        // Extend table config before saving table to DB
        add_filter('wpdatatables_filter_insert_table_array', array('wdtCompareDetail\Plugin', 'extendTableConfigCompare'), 10, 1);

        // Extend WPDataTable Object with new properties
        add_action('wdt_extend_wpdatatable_object', array('wdtCompareDetail\Plugin', 'extendTableObjectCompare'), 10, 2);
        


        
        // Extend table description before returning it to the front-end
        add_filter('wpdatatables_filter_table_description', array('wdtCompareDetail\Plugin', 'extendJSONDescription'), 10, 3);
        
        // Add custom modal in DOM
        add_action('wpdatatables_add_custom_modal', array('wdtCompareDetail\Plugin', 'insertModal'), 10, 1);
        
        // Add custom modal in DOM
        //add_action('wpdatatables_add_custom_template_modal', array('wdtCompareDetail\Plugin', 'insertTemplateModal'), 10, 1);
        
        

        // Prepare column data
        //add_filter('wpdatatables_prepare_column_data', array('wdtCompareDetail\Plugin', 'prepareColumnDataCompare'), 10, 2);

        // Custom populate cells
        //add_action('wpdatatables_custom_populate_cells', array('wdtCompareDetail\Plugin', 'fillCellsCompareDetailCompare'), 10, 2);
        
        // Custom prepare output data
        //add_filter('wpdatatables_custom_prepare_output_data', array('WDTCompareDetail\Plugin', 'prepareOutputDataCompareDetailsCompare'), 10, 5);
        
        
        
        
/*        
        // Disable wpdatatables features fro new column(sorting,searching and filtering)
        add_action('wpdatatables_columns_from_arr', array('wdtCompareDetail\Plugin', 'setColumnDetailsCompare'), 10, 4);
*/
        // Include file that contaions CompareDetailWDTColumn class from CD in wpdt
        add_filter('wpdatatables_column_formatter_file_name', array('wdtCompareDetail\Plugin', 'columnFormatterFileNameCompare'), 10, 2);
/*
        // Filtering column types array
        add_filter('wpdatatables_columns_types_array', array('wdtCompareDetail\Plugin', 'columnsTypesArrayCompare'), 10, 3);

        // Add and save custom column
        add_action('wpdatatables_add_and_save_custom_column', array('wdtCompareDetail\Plugin', 'saveColumnsCompare'), 10, 4);

        // Removing columns that that are not in source
        add_filter('wpdatatables_columns_not_in_source', array('wdtCompareDetail\Plugin', 'removeColumnsNotInSourceCompare'), 10, 4);

        // Filter the content with detail placehodlers
        add_filter( 'the_content', array('wdtCompareDetail\Plugin', 'filterTheContentCompare'));
*/        


        
/*
        // Filter column JSON definition
        add_filter('wpdatatables_extend_column_js_definition', array('wdtCompareDetail\Plugin', 'extendColumnJSONDefinition'), 10, 2);
        
        // Filter data column properties
        add_filter('wpdt_filter_data_column_properties', array('wdtCompareDetail\Plugin', 'extendDataColumnProperties'), 10, 3);
             
        // Filter column params
        add_filter('wpdt_filter_column_params', array('wdtCompareDetail\Plugin', 'extendColumnParams'), 10, 2);

        // Filter column options
        add_filter('wpdt_filter_column_options', array('wdtCompareDetail\Plugin', 'extendColumnOptions'), 10, 2);

        // Filter supplementary array column object
        add_filter('wpdt_filter_supplementary_array_column_object', array('wdtCompareDetail\Plugin', 'extendSupplementaryArrayColumnObject'), 10, 3 );

*/

        // Extend column config object
        add_filter('wpdt_filter_column_config_object', array('wdtCompareDetail\Plugin', 'extendColumnConfigObject'), 10, 2 );

        // Extend column description object
        add_filter('wpdt_filter_column_description_object', array('wdtCompareDetail\Plugin', 'extendColumnDescriptionObject'), 10, 3 );



        
        // Extend datacolumn object
        add_filter('wpdatatables_extend_datacolumn_object', array('wdtCompareDetail\Plugin', 'extendDataColumnObject'), 10, 2);
        
        // Extend small column block
        add_action('wpdt_add_small_column_block', array('WDTCompareDetail\Plugin', 'addCompareDetailSmallBlack'));

        // Add Compare-Detail activation setting
        add_action('wdt_add_activation', array('WDTCompareDetail\Plugin', 'addCompareDetailActivation'));
        
        
        
/*
        // Enqueue Compare-Detail add-on files on back-end settings page
        add_action('wdt_enqueue_on_settings_page', array('WDTCompareDetail\Plugin', 'wdtCompareDetailEnqueueBackendSettings'));
        
        // Check auto update
        add_filter('pre_set_site_transient_update_plugins', array('WDTCompareDetail\Plugin', 'wdtCheckUpdateCompareDetail'));

        // Check plugin info
        add_filter('plugins_api', array('WDTCompareDetail\Plugin', 'wdtCheckInfoCompareDetail'), 10, 3);

        // Add a message for unavailable auto update if plugin is not activated
        add_action('in_plugin_update_message-' . plugin_basename(__FILE__), array('WDTCompareDetail\Plugin', 'addMessageOnPluginsPageCompareDetail'));

        // Add error message on plugin update if plugin is not activated
        add_filter('upgrader_pre_download', array('WDTCompareDetail\Plugin', 'addMessageOnUpdateCompareDetail'), 10, 4);

        // Filter Columns CSS
        add_filter('wpdt_filter_columns_css', array('WDTCompareDetail\Plugin', 'wpdtFilterColumnsCss'), 10, 4);
*/


        // Add JS For Compare Plugin
        add_action('wpdatatables_after_table', array('WDTCompareDetail\Plugin', 'wdtCompareDetailEnqueueBackendafterrender'), 10, 4);
        
        
        //textStatus
        add_action('wpdatatables_filter_excel_array', array('WDTCompareDetail\Plugin', 'wdtInsertColumn'), 10, 4);
        
        
        
        
        require_once(WDT_CD_ROOT_PATH . 'controllers/wdt_ajax_compare_actions.php');
        
        

        // Check if wpDataTables required version is installed
        if (version_compare(WDT_CURRENT_VERSION, WDT_CD_VERSION_TO_CHECK) < 0) {
            // Show message if required wpDataTables version is not installed
            add_action('admin_notices', array('WDTCompareDetail\Plugin', 'wdtRequiredVersionMissing'));
            return false;
        }
        
        self::createNewColumnTypeCompare();

        return self::$initialized = true;
        
    }
    
    
    public static function wdtInsertColumn($namedDataArray, $wpid, $xls_url){
      
      $tableData = WDTConfigController::loadTableFromDB($wpid);
      $advancedSettingsTable = json_decode($tableData->advanced_settings);

      foreach($namedDataArray as $i => $item) {
          $namedDataArray[$i] = array('Compare'=>'<input type="checkbox" aria-label="Compare Column Header" tabindex="0"/>') + $namedDataArray[$i];   
      }
      
      return $namedDataArray;    
    }
    
    /**
     *  Filter Columns CSS
     * @param $columnsCSS
     * @param $columnObj
     * @param $tableID
     * @param $cssColumnHeader
     * @return string
     */
    public static function wpdtFilterColumnsCss( $columnsCSS, $columnObj, $tableID, $cssColumnHeader )
    {
        if ($columnObj->text_before != '') {
            $columnsCSS .= "\n#wdt-cd-modal div.{$cssColumnHeader}:not(:empty):before{ content: '{$columnObj->text_before}' }";
        }
        if ($columnObj->text_after != '') {
            $columnsCSS .= "\n#wdt-cd-modal div.{$cssColumnHeader}:not(:empty):after { content: '{$columnObj->text_after}' }";
        }
        if ($columnObj->color != '') {
            $columnsCSS .= "#wdt-cd-modal div.{$cssColumnHeader}{ background-color: {$columnObj->color} !important; }";
        }

        return $columnsCSS;
    }
    /**
     *  Extend small column block
     * @param $tableData
     */
    public static function addCompareDetailSmallBlack( $tableData )
    {
        $advancedSettingsTable = json_decode($tableData->table->advanced_settings);
        if (isset($advancedSettingsTable->compareDetail) && $advancedSettingsTable->compareDetail != 0) {
            ob_start();
            include WDT_CD_ROOT_PATH . 'templates/compare_detail_small_block.inc.php';
            $compareDetailSmallBlock = ob_get_contents();
            ob_end_clean();

            echo $compareDetailSmallBlock;
        }
    }

    /**
     *  Create new column type in database - comparedetail
     */
    public static function createNewColumnTypeCompare( )
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "wpdatatables_columns MODIFY COLUMN column_type ENUM('autodetect','string','int','float','date','link','email','image','formula','datetime','time','mydetail','comparedetail')");
        $wpdb->update( $wpdb->prefix . "wpdatatables_columns", array( 'column_type' => 'comparedetail' ), array( 'orig_header' => 'comparedetail' ));
    }

    /**
     *  Extend datacolumn object
     * @param $dataColumn
     * @param $dataColumnProperties
     * @return mixed
     */
    public static function extendDataColumnObject($dataColumn,$dataColumnProperties){
        //print_r('->'.$dataColumnProperties['compareDetailColumnOption']);
        if (isset($dataColumnProperties['compareDetailColumnOption'])){
            $dataColumn->compareDetailColumnOption = $dataColumnProperties['compareDetailColumnOption'];
        }else {
            $dataColumn->compareDetailColumnOption = 1;
        }
        return $dataColumn;
    }

    /**
     *  Extend column description object
     * @param $feColumn
     * @param $dbColumn
     * @param $advancedSettings
     * @return mixed
     */
    public static function extendColumnDescriptionObject( $feColumn, $dbColumn, $advancedSettings)
    {
        if (isset($advancedSettings->compareDetailColumnOption)){
            $feColumn->compareDetailColumnOption = $advancedSettings->compareDetailColumnOption;
        } else {
            $feColumn->compareDetailColumnOption = 1;
        }
        return $feColumn;
    }

    /**
     *  Extend column config object
     * @param $columnConfig
     * @param $feColumn
     * @return mixed
     */
    public static function extendColumnConfigObject($columnConfig, $feColumn)
    {
        $columnAdvancedSettings = json_decode($columnConfig['advanced_settings']);
        if (isset($feColumn->compareDetailColumnOption)) {
            $columnAdvancedSettings->compareDetailColumnOption = $feColumn->compareDetailColumnOption;
        } else {
            $columnAdvancedSettings->compareDetailColumnOption = 1;
        }

        $columnConfig['advanced_settings'] = json_encode($columnAdvancedSettings);

        return $columnConfig;
    }

    /**
     *  Extend supplementary array column object
     * @param $colObjOptions
     * @param $wdtParameters
     * @param $dataColumn_key
     * @return mixed
     */
    public static function extendSupplementaryArrayColumnObject($colObjOptions, $wdtParameters, $dataColumn_key)
    {
        if (isset($wdtParameters['compareDetailColumnOption'])) {
            $colObjOptions['compareDetailColumnOption'] = $wdtParameters['compareDetailColumnOption'][$dataColumn_key];
        } else {
            $colObjOptions['compareDetailColumnOption'] = true;
        }

        return $colObjOptions;
    }

    /**
     *  Extend column options
     * @param $columnOptions
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnOptions($columnOptions, $columnData)
    {
        
        foreach ($columnData as $column) {
            $advancedSettings = json_decode($column->advanced_settings);

            if (isset($advancedSettings->compareDetailColumnOption) && $advancedSettings->compareDetailColumnOption == 1 ) {
                $compareDetailColumnOption[$column->orig_header] = $advancedSettings->compareDetailColumnOption;
            } else {
                $compareDetailColumnOption[$column->orig_header] = null;
            }
            $columnOptions['compareDetailColumnOption'] = $compareDetailColumnOption;
        }

        return $columnOptions;
    }

    /**
     *  Extend column params
     * @param $params
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnParams($params, $columnData)
    {
        if (isset($columnData['compareDetailColumnOption'])) {
            $params['compareDetailColumnOption'] = $columnData['compareDetailColumnOption'];
        } else {
            $params['compareDetailColumnOption'] = 1;
        }
        return $params;
    }

    /**
     *  Extend data column properties
     * @param $dataColumnProperties
     * @param $wdtParameters
     * @param $key
     * @return mixed
     */
    public static function extendDataColumnProperties($dataColumnProperties, $wdtParameters, $key)
    {
        if (isset($wdtParameters['compareDetailColumnOption'])) {
            $dataColumnProperties['compareDetailColumnOption'] =  $wdtParameters['compareDetailColumnOption'][$key];
        } else {
            $dataColumnProperties['compareDetailColumnOption'] = 1;
        }
        return $dataColumnProperties;
    }

    /**
     *  Extend column JSON definition
     * @param $colJsDefinition
     * @param $title
     * @return mixed
     */
    public static function extendColumnJSONDefinition($colJsDefinition, $wpdatatable)
    {
        if (isset($wpdatatable->compareDetailColumnOption)) {
            $colJsDefinition->compareDetailColumnOption = $wpdatatable->compareDetailColumnOption;
        } else {
            $colJsDefinition->compareDetailColumnOption = 1;
        }
        return $colJsDefinition;
    }

    /**
     *  Removing columns that that are not in source
     * @param $columnsNotInSource
     * @param $table
     * @param $tableId
     * @param $frontendColumns
     * @return array
     */
    public static function removeColumnsNotInSourceCompare($columnsNotInSource, $table, $tableId, $frontendColumns)
    {
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in comparedetail columns in this loop
                if ($feColumn->type != 'comparedetail') {
                    continue;
                }
                // Removing this column from the array of marked for deletiong
                $columnsNotInSource = array_diff($columnsNotInSource, array($feColumn->orig_header));

            }
            return $columnsNotInSource;
        }
        return array();
    }

    /**
     * Add and save custom column
     * @param $table \WPDataTable
     * @param $tableId
     * @param $frontendColumns
     * @throws Exception
     */
    public static function saveColumnsCompare($table, $tableId, $frontendColumns)
    {
        global $wpdb;
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in comparedetail column in this loop
                if ($feColumn->type != 'comparedetail') {
                    continue;
                }
                $wdtColumn = WDTColumn::generateColumn(
                    'comparedetail',
                    array(
                        'orig_header' => $feColumn->orig_header,
                        'display_header' => $feColumn->display_header,
                        'decimalPlaces' => $feColumn->decimalPlaces
                    )
                );
                $existingPositionQuery = $wpdb->prepare(
                    "SELECT pos
                FROM " . $wpdb->prefix . "wpdatatables_columns
                WHERE table_id = %d",
                    $tableId
                );

                $columnsPositionInSource = $wpdb->get_col($existingPositionQuery);
                $columnsPositionInSourceCounts = array_count_values($columnsPositionInSource);

                $tempCompareDetailPosition = $feColumn->pos;
                $checkDuplicatePosition= $columnsPositionInSourceCounts[$tempCompareDetailPosition];

                /** @var CompareDetailWDTColumn $wdtColumn */
                $columnConfig = WDTConfigController::prepareDBColumnConfig($wdtColumn, $frontendColumns, $tableId);
                $columnConfig['filter_type'] = 'none';

                if ((in_array($tempCompareDetailPosition, $columnsPositionInSource) && $checkDuplicatePosition > 1)
                    || in_array($tempCompareDetailPosition, $columnsPositionInSource) && $tempCompareDetailPosition >= count($columnsPositionInSource) ) {
                    $dataSourceColumns = $table->getColumns();
                    $columnConfig['pos'] = count($dataSourceColumns);
                } else {
                    $columnConfig['pos'] = $tempCompareDetailPosition;
                }

                WDTConfigController::saveSingleColumn($columnConfig);
            }
        }
    }

    /**
     * Filtering column types array
     * @param $columnsTypesArray
     * @param $columnsNotInSource
     * @param $columnsTypes
     * @return array
     */
    public static function columnsTypesArrayCompare($columnsTypesArray, $columnsNotInSource, $columnsTypes)
    {
        $columnsTypesArray = array_diff(array_combine($columnsNotInSource, $columnsTypes), ['comparedetail', 'formula']);
        return $columnsTypesArray;
    }

    /**
     * Format file that contain column class
     * @param $columnFormatterFileName
     * @param $wdtColumnType
     * @return string
     */
    public static function columnFormatterFileNameCompare($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'comparedetail') {
            $columnFormatterFileName = WDT_CD_ROOT_PATH . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }

    /**
     * Disable sorting and searching for Compare-detail column
     * @param $obj \WPDataTable
     * @param $dataColumn
     * @param $wdtColumnTypes
     * @param $key
     * @throws \WDTException
     */
    public static function setColumnDetailsCompare($obj, $dataColumn, $wdtColumnTypes, $key)
    {
        if (isset($wdtColumnTypes[$key])) {
            if ($wdtColumnTypes[$key] === 'comparedetail') {
                /** @var CompareDetailWDTColumn $dataColumn */
                if ($obj->serverSide()) {
                    $dataColumn->setSorting(false);
                    $dataColumn->setSearchable(false);
                    $dataColumn->setFilterType('none');
                }
            }
        }
    }


    /**
     * Filter columns_from_arr
     * @param $obj \WPDataTable
     * @param $wdtColumnTypes
     */
    public static function fillCellsCompareDetailCompare($obj, $wdtColumnTypes)
    {
        if (in_array('comparedetail', $wdtColumnTypes)) {
            self::populateDetailsCellsCompare($obj);
        }
    }

    /**
     * Fill cell with predefined values
     * @param $obj \WPDataTable
     */
    public static function populateDetailsCellsCompare($obj)
    {
        foreach (array_keys($obj->getWdtColumnTypes(), 'comparedetail') as $column_key) {
            
            $allDataRows = $obj->getDataRows();
            foreach ($allDataRows as &$row) {
                try {
                    $row[$column_key] = 'More details';
                } catch (Exception $e) {
                    $row[$column_key] = '';
                }
            }
            $obj->setDataRows($allDataRows);

        }
    }

    /**
     * Insert Modal templates
     * @param $output
     * @param $obj \WPDataTable
     * @param $main_res_dataRows
     * @param $wdtParameters
     * @param $colObjs
     * @return array
     * @throws \WDTException
     */
    public static function prepareOutputDataCompareDetailsCompare($output, $obj, $main_res_dataRows, $wdtParameters, $colObjs)
    {
        $output = [];
        
        if (!empty($main_res_dataRows)) {
            foreach ($wdtParameters['foreignKeyRule'] as $columnKey => $foreignKeyRule) {
                if ($foreignKeyRule != null) {
                    $foreignKeyData = $obj->joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $main_res_dataRows);
                    $main_res_dataRows = $foreignKeyData['dataRows'];
                }
            }

            foreach ($main_res_dataRows as $res_row) {
                $row = array();
                foreach ($wdtParameters['columnOrder'] as $dataColumn_key) {
                    if ($wdtParameters['data_types'][$dataColumn_key] == 'comparedetail') {
                        try {
                            $detailsValue = 'More Details';
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($detailsValue),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = '';
                        }
                    } else if ($wdtParameters['data_types'][$dataColumn_key] == 'formula') {
                        try {
                            $headers = array();
                            $headersInFormula = $obj->detectHeadersInFormula($wdtParameters['columnFormulas'][$dataColumn_key], array_keys($wdtParameters['data_types']));
                            $headers = WDTTools::sanitizeHeaders($headersInFormula);
                            $formulaVal =
                                $obj::solveFormula(
                                    $wdtParameters['columnFormulas'][$dataColumn_key],
                                    $headers,
                                    $res_row
                                );
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($formulaVal),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = 0;
                        }
                    } else {

                        $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue($res_row[$dataColumn_key]), $obj->getWpId(), $dataColumn_key);
                    }
                }
                $output[] = self::formatAjaxQueryResultRow($row, $obj);
            }
        }
        return $output;
    }

    /**
     * Formatting row data structure for ajax display table
     * @param $row - key => value pairs as column name and cell value of a row
     * @param $obj WPDataTable/WPExcelDataTable object
     * @return array
     */
    public static function formatAjaxQueryResultRow($row, $obj)
    {
        if (is_a($obj, 'WPExcelDataTable')) {
            return $row;
        } else{
            return array_values($row);
        }
    }

    /**
     * Prepare column data
     * @param $returnArray
     * @param $column
     * @return mixed
     */
    public static function prepareColumnDataCompare($returnArray, $column)
    {
        
      
        
        if ($column->type === 'comparedetail') {
            $returnArray['columnTypes'][$column->orig_header] = $column->type;
        }

        if (isset($column->compareDetailColumnOption)){
            $returnArray['compareDetailColumnOption'][$column->orig_header] = isset($column->compareDetailColumnOption) ? $column->compareDetailColumnOption : null;
        }
        return $returnArray;
    }

    /**
     * Insert Modal templates
     * @param $wpDataTable \WPDataTable
     */
    public static function insertModal($wpDataTable)
    {
        
        if (isset($wpDataTable->compareDetail) && $wpDataTable->compareDetail && is_admin()) {
            //include WDT_CD_TEMPLATE_PATH . 'modal.inc.php';
            //include WDT_CD_TEMPLATE_PATH . 'cd_modal.inc.php';
        } else if (isset($wpDataTable->compareDetail) && $wpDataTable->compareDetail){
            //include WDT_CD_TEMPLATE_PATH . 'cd_modal.inc.php';
        }
        
        
      
        include WDT_CD_TEMPLATE_PATH . 'compare_detail_modal.inc.php';
        include WDT_CD_TEMPLATE_PATH . 'style_compareblock.inc.php';
        
    }

    /**
     * Insert Template Modal
     */
    public static function insertTemplateModal()
    {
        include WDT_CD_TEMPLATE_PATH . 'modal.inc.php';
    }


    /**
     * Show message if wpDataTables is not installed
     */
    public static function wdtNotInstalled()
    {
        $message = __('Compare-detail Tables for wpDataTables is an add-on - please install and activate wpDataTables to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Show message if required wpDataTables version is not installed
     */
    public static function wdtRequiredVersionMissing()
    {
        $message = __('Compare-Detail Tables add-on for wpDataTables requires wpDataTables version ' . WDT_CD_VERSION_TO_CHECK . '. Please update wpDataTables plugin to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }
    
    
    /**
     * Enqueue Compare-detail JS
     */
    public static function wdtCompareDetailEnqueueBackendafterrender()
    {
        if (self::$initialized) {
            wp_enqueue_script('wdt-cd-backend-afterrender', WDT_CD_ROOT_URL . 'assets/js/wdt.cd.backend.afterrender.js', array(), WDT_CD_VERSION, true);
            wp_localize_script( 'wdt-cd-backend-afterrender', 'wdt_ajax_compare', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

            wp_dequeue_script( 'wdt-column-config' );
            wp_deregister_script( 'wdt-column-config' );
            wp_enqueue_script('wdt-column-config', WDT_CD_ROOT_URL . 'assets/js/column_config_object.js', array(), WDT_CD_VERSION, true);
            
        }
    }
    
    /**
     * Enqueue Compare-detail add-on files on back-end
     */
    public static function wdtCompareDetailEnqueueBackendCompare()
    {
        if (self::$initialized) {
            wp_enqueue_style(
                'wdt-cd-stylesheet',
                WDT_CD_ROOT_URL . 'assets/css/wdt.cd.css',
                array(),
                WDT_CD_VERSION
            );
            wp_enqueue_script(
                'wdt-cd-backend',
                WDT_CD_ROOT_URL . 'assets/js/wdt.cd.backend.js',
                array(),
                WDT_CD_VERSION,
                true
            );
            wp_enqueue_script(
                'wdt-cd-frontend',
                WDT_CD_ROOT_URL . 'assets/js/wdt.cd.frontend.js',
                array(),
                WDT_CD_VERSION,
                true
            );
            wp_localize_script( 'wdt-cd-backend', 'wdt_ajax_compare_backend', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

            \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
            \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStrings());
            
            function is_admin() {
              echo '<script>';
                echo 'var page_is_admin = true;';
              echo '</script>';
            }
            
        }
    }

    /**
     * Enqueue Compare-Detail add-on files on front-end
     */
    public static function wdtCompareDetailEnqueueFrontendCompare()
    {
      
        if (self::$initialized) {
            
            wp_enqueue_script(
                'wdt-cd-frontend',
                WDT_CD_ROOT_URL . 'assets/js/wdt.cd.frontend.js',
                array(),
                WDT_CD_VERSION,
                true
            );

            wp_enqueue_style(
                'wdt-cd-stylesheet',
                WDT_CD_ROOT_URL . 'assets/css/wdt.cd.css',
                array(),
                WDT_CD_VERSION
            );

            \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
            \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStrings());
            
            
            
        }
    }

    /**
     * Function that extend table config before saving table to the database
     * @param $tableConfig - array that contains table configuration
     * @return mixed
     */
    public static function extendTableConfigCompare($tableConfig)
    {
        $table = apply_filters(
            'wpdatatables_before_save_table',
            json_decode(
                stripslashes_deep($_POST['table'])
            )
        );

        $advancedSettings = json_decode($tableConfig['advanced_settings']);
        if (isset($table->compareDetail)) $advancedSettings->compareDetail = $table->compareDetail;
        if (isset($table->compareDetailLogic)) $advancedSettings->compareDetailLogic = $table->compareDetailLogic;
        if (isset($table->compareDetailRender)) $advancedSettings->compareDetailRender = $table->compareDetailRender;
        if (isset($table->compareDetailRenderPage)) $advancedSettings->compareDetailRenderPage = $table->compareDetailRenderPage;
        if (isset($table->compareDetailRenderPost)) $advancedSettings->compareDetailRenderPost = $table->compareDetailRenderPost;
        if (isset($table->compareDetailPopupTitle)) $advancedSettings->compareDetailPopupTitle = $table->compareDetailPopupTitle;

        $tableConfig['advanced_settings'] = json_encode($advancedSettings);

        return $tableConfig;
    }

    /**
     * Function that extend $wpDataTable object with new properties
     * @param $wpDataTable \WPDataTable
     * @param $tableData \stdClass
     */
    public static function extendTableObjectCompare($wpDataTable, $tableData)
    {  
      
        if (!empty($tableData->advanced_settings)) {
            $advancedSettings = json_decode($tableData->advanced_settings);

            if (isset($advancedSettings->compareDetail)) {
                $wpDataTable->compareDetail = $advancedSettings->compareDetail;
            }

            if (isset($advancedSettings->compareDetailLogic)) {
                $wpDataTable->compareDetailLogic = $advancedSettings->compareDetailLogic;
            }

            if (isset($advancedSettings->compareDetailRender)) {
                $wpDataTable->compareDetailRender = $advancedSettings->compareDetailRender;
            }

            if (isset($advancedSettings->compareDetailRenderPage)) {
                $wpDataTable->compareDetailRenderPage = $advancedSettings->compareDetailRenderPage;
            }

            if (isset($advancedSettings->compareDetailRenderPost)) {
                $wpDataTable->compareDetailRenderPost = $advancedSettings->compareDetailRenderPost;
            }

            if (isset($advancedSettings->compareDetailPopupTitle)) {
                $wpDataTable->compareDetailPopupTitle = $advancedSettings->compareDetailPopupTitle;
            }

        }
        
        
    }

    /**
     * Function that extend table description before returning it to the front-end
     *
     * @param $tableDescription \stdClass
     * @param $wpDataTable \WPDataTable
     * @return mixed
     */
    public static function extendJSONDescription($tableDescription, $tableId, $wpDataTable)
    {

        
        if (isset($wpDataTable->compareDetail)) {
            $tableDescription->compareDetail = $wpDataTable->compareDetail;
        }

        if (isset($wpDataTable->compareDetailLogic)) {
            $tableDescription->compareDetailLogic = $wpDataTable->compareDetailLogic;
        }

        if (isset($wpDataTable->compareDetailRender)) {
            $tableDescription->compareDetailRender = $wpDataTable->compareDetailRender;
        }

        if (isset($wpDataTable->compareDetailRenderPage)) {
            $tableDescription->compareDetailRenderPage = $wpDataTable->compareDetailRenderPage;
        }

        if (isset($wpDataTable->compareDetailRenderPost)) {
            $tableDescription->compareDetailRenderPost = $wpDataTable->compareDetailRenderPost;
        }

        if (isset($wpDataTable->compareDetailPopupTitle)) {
            $tableDescription->compareDetailPopupTitle = $wpDataTable->compareDetailPopupTitle;
        }

        if (isset($wpDataTable->compareDetail) && $wpDataTable->compareDetail &&
            isset($wpDataTable->compareDetailLogic) && $wpDataTable->isEditable() && $wpDataTable->serverSide()) {
            (!isset($tableDescription->dataTableParams->buttons)) ? $tableDescription->dataTableParams->buttons = array() : '';
            array_push(
                $tableDescription->dataTableParams->buttons,

                array(
                    'text' => __('Details', 'wpdatatables'),
                    'className' => 'compare_detail DTTT_button DTTT_button_cd'
                )
            );
        }

        return $tableDescription;
    }


    /**
     * Add Compare-Detail Settings tab on table configuration page
     */
    public static function addCompareDetailSettingsTabCompare()
    {
        ob_start();
        include WDT_CD_ROOT_PATH . 'templates/compare_detail_settings_tab.inc.php';
        $compareDetailSettingsTab = ob_get_contents();
        ob_end_clean();

        echo $compareDetailSettingsTab;
    }

    /**
     * Add tablpanel for Compare-Detail Settings tab on table configuration page
     */
    public static function addCompareDetailSettingsTabPanelCompare()
    {
        ob_start();
        include WDT_CD_ROOT_PATH . 'templates/compare_detail_settings_tabpanel.inc.php';
        $compareDetailSettingsTabPanel = ob_get_contents();
        ob_end_clean();

        echo $compareDetailSettingsTabPanel;
    }

    /**
     * Add element in column settings for Compare-Detail table
     */
    public static function addCompareDetailColumnSettingsElementCompare()
    {
        ob_start();
        include WDT_CD_ROOT_PATH . 'templates/compare-detail-column-display-element.inc.php';
        $compareDetailColumnSettingsElement = ob_get_contents();
        ob_end_clean();

        echo $compareDetailColumnSettingsElement;
    }

    /**
     * Add new option for column type
     */
    public static function addCompareDetailColumnTypeOptionCompare()
    {
        ob_start();
        include WDT_CD_ROOT_PATH . 'templates/compare-detail-column-type-option.inc.php';
        $compareDetailColumnTypeOption = ob_get_contents();
        ob_end_clean();

        echo $compareDetailColumnTypeOption;
    }

    /**
     * Get all pages from database
     */
    public static function getAllPages() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'page' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPages = $wpdb->get_results($query, ARRAY_A);
        return $allPages;
    }

    /**
     * Get all posts from database
     */
    public static function getAllPosts() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'post' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPosts = $wpdb->get_results($query, ARRAY_A);
        return $allPosts;
    }

    /**
     * Get compareDetailRenderPage and compareDetailRenderPost values from database
     */
    public static function removePlaceholdersFromContent($currentPostLink, $content) {
        global $wpdb;
        $finalPageIDs = [];

        $query = "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE {$wpdb->prefix}wpdatatables.id > 0";

        $advancedSettingsFromAllTables= $wpdb->get_results($query, ARRAY_A);
        foreach ($advancedSettingsFromAllTables as $advancedSetting) {
            $tempID = $advancedSetting['id'];
            $tempAdvancedSettings = json_decode($advancedSetting['advanced_settings']);
            if (isset($tempAdvancedSettings->compareDetailRenderPage) && $tempAdvancedSettings->compareDetailRenderPage != '' ){
                if ($tempAdvancedSettings->compareDetailRenderPage == $currentPostLink)
                    $finalPageIDs[] = $tempID;
            }
            if (empty($finalPageID)){
                if (isset($tempAdvancedSettings->compareDetailRenderPost) && $tempAdvancedSettings->compareDetailRenderPost != '' ){
                    if ($tempAdvancedSettings->compareDetailRenderPost == $currentPostLink)
                        $finalPageIDs[] =$tempID;
                }
            }
        }
        if (empty($finalPageID)){
            foreach ($finalPageIDs as $finalPageID){
                $columnsData = WDTConfigController::loadColumnsFromDB($finalPageID);
                $origHeaders= [];
                foreach ($columnsData as $columnData){
                    $origHeaders[] = $columnData->orig_header;
                }
                foreach ($origHeaders as $origHeader) {
                    $content = str_replace("%" . $origHeader . "%", "", $content);
                }
            }
        }

        return $content;
    }

    /**
     * Replace Compare-detail placeholders in content(page or post)
     */
    public static function filterTheContentCompare( $content ) {
        if (isset($_POST['wdt_details_data'])){
            $detailsData= json_decode(stripslashes($_POST['wdt_details_data']), true);
            $columnsData = WDTConfigController::loadColumnsFromDB($detailsData['wdt_cd_id_table']);
            $origHeaders= [];
            $removeOrigHeaders= [];
            foreach ($columnsData as $columnData){
                $advancedColumnSettings = json_decode($columnData->advanced_settings);
                if(isset($advancedColumnSettings->compareDetailColumnOption) &&
                    $advancedColumnSettings->compareDetailColumnOption == 1 ){
                    $origHeaders[] = $columnData->orig_header;
                } else if (isset($advancedColumnSettings->compareDetailColumnOption) &&
                    $advancedColumnSettings->compareDetailColumnOption == 0){
                    $removeOrigHeaders[]= $columnData->orig_header;
                }
            }
            foreach ($origHeaders as $origHeader) {
                if (isset($detailsData[$origHeader])) {
                    $content = str_replace("%" . $origHeader . "%", $detailsData[$origHeader], $content);
                }
            }
            if (isset($removeOrigHeaders)){
                foreach ($removeOrigHeaders as $removeOrigHeader) {
                    $content = str_replace("%" . $removeOrigHeader . "%", '', $content);
                }
            }
        } else {
            $currentPostLink = get_permalink(get_the_ID());
            $content = self::removePlaceholdersFromContent($currentPostLink, $content);
        }

        return $content;
    }

    /**
     * Update wpdatatables table in database after deactivate/uninstall Compare-Detail add-on
     * @param $advancedSettingsFromAllTables
     * @param $action
     * @return array
     */
    public static function updateWpDataTableInDatabase($advancedSettingsFromAllTables, $action){
        global $wpdb;
        $allTablesIDWithCD = [];
        foreach ($advancedSettingsFromAllTables as $advancedSetting) {
            $tempTableID = (int)$advancedSetting['id'];
            $tempAdvancedSettings = json_decode($advancedSetting['advanced_settings']);
            if ( $action == 'deactivate'){
                if (isset($tempAdvancedSettings->compareDetail) && $tempAdvancedSettings->compareDetail == 1 &&
                    isset($tempAdvancedSettings->compareDetailLogic) && $tempAdvancedSettings->compareDetailLogic == 'button'){

                    $tempAdvancedSettings->compareDetailLogic = 'row';
                    $tempAdvancedSettings = json_encode($tempAdvancedSettings);

                    $wpdb->update(
                        $wpdb->prefix . 'wpdatatables',
                        array( 'advanced_settings' => $tempAdvancedSettings),
                        array('id'=> $tempTableID)
                    );

                    $allTablesIDWithCD[] = $tempTableID;
                }
            } else if ( $action == 'uninstall') {
                if (isset($tempAdvancedSettings->compareDetail)){
                    unset($tempAdvancedSettings->compareDetail);
                    unset($tempAdvancedSettings->compareDetailLogic);
                    unset($tempAdvancedSettings->compareDetailRender);
                    unset($tempAdvancedSettings->compareDetailRenderPage);
                    unset($tempAdvancedSettings->compareDetailRenderPost);
                    unset($tempAdvancedSettings->compareDetailPopupTitle);
                    $tempAdvancedSettings = json_encode($tempAdvancedSettings);

                    $wpdb->update(
                        $wpdb->prefix . 'wpdatatables',
                        array( 'advanced_settings' => $tempAdvancedSettings),
                        array('id'=> $tempTableID)
                    );

                    $allTablesIDWithCD[] = $tempTableID;
                }
            }
        }
        return $allTablesIDWithCD;
    }

    /**
     * Update wpdatatables_columns table in database after deactivate/uninstall Compare-Detail add-on
     * @param $allTablesIDWithCD
     */
    public static function updateWpDataTableColumnsInDatabase($allTablesIDWithCD){
        global $wpdb;
        $columnPosition   = '';
        $tableID          = '';
        foreach ($allTablesIDWithCD as $tableIDWithCD) {
            $query = "SELECT id, table_id, orig_header, pos 
                          FROM {$wpdb->prefix}wpdatatables_columns 
                          WHERE {$wpdb->prefix}wpdatatables_columns.table_id = {$tableIDWithCD}";

            $tempColumnFields= $wpdb->get_results($query, ARRAY_A);
            foreach ($tempColumnFields as $tempColumnField){
                if ($tempColumnField['orig_header']== 'comparedetail'){
                    $wpdb->delete(
                        $wpdb->prefix . "wpdatatables_columns",
                        array(
                            'orig_header' => $tempColumnField['orig_header'],
                            'table_id' => $tempColumnField['table_id'],
                            'id' => $tempColumnField['id']
                        )
                    );
                    $columnPosition   = (int)$tempColumnField['pos'];
                    $tableID          = (int)$tempColumnField['table_id'];
                }
            }
            $wpdb->query(
                "UPDATE {$wpdb->prefix}wpdatatables_columns
                           SET {$wpdb->prefix}wpdatatables_columns.pos = {$wpdb->prefix}wpdatatables_columns.pos-1
                           WHERE {$wpdb->prefix}wpdatatables_columns.table_id = {$tableID}
                           AND {$wpdb->prefix}wpdatatables_columns.pos > {$columnPosition}"
            );
        }
    }

    /**
     * Deactivate of Compare-Detail add-on
     */
    public static function deactivateCompareDetail(){
        global $wpdb;
        $action = 'deactivate';

        $query = "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE {$wpdb->prefix}wpdatatables.id > 0";

        $advancedSettingsFromAllTables= $wpdb->get_results($query, ARRAY_A);

        $allTablesIDWithCD = self::updateWpDataTableInDatabase($advancedSettingsFromAllTables, $action);

        if (!empty($allTablesIDWithCD)){
            self::updateWpDataTableColumnsInDatabase($allTablesIDWithCD);
        }
    }

    /**
     * Uninstall Compare-Detail add-on
     */
    public static function uninstallCompareDetail(){
        global $wpdb;
        $action = 'uninstall';

        $query = "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE {$wpdb->prefix}wpdatatables.id > 0";

        $advancedSettingsFromAllTables= $wpdb->get_results($query, ARRAY_A);

        $allTablesIDWithCD = self::updateWpDataTableInDatabase($advancedSettingsFromAllTables, $action);

        if (!empty($allTablesIDWithCD)){
            self::updateWpDataTableColumnsInDatabase($allTablesIDWithCD);
        }
    }

    /**
     * Add Compare-Detail activation on wpDataTables settings page
     */
    public static function addCompareDetailActivation()
    {
        ob_start();
        include WDT_CD_ROOT_PATH . 'templates/activation.inc.php';
        $activation = ob_get_contents();
        ob_end_clean();

        echo $activation;
    }

    /**
     * Enqueue Compare-Detail add-on files on back-end settings page
     */
    public static function wdtCompareDetailEnqueueBackendSettings()
    {
        if (self::$initialized) {
            wp_enqueue_script(
                'wdt-cd-settings',
                WDT_CD_ROOT_URL . 'assets/js/wdt.cd.admin.settings.js',
                array(),
                WDT_CD_VERSION,
                true
            );
        }
    }

    /**
     * @param $transient
     *
     * @return mixed
     */
    public static function wdtCheckUpdateCompareDetail($transient)
    {

        if (class_exists('WDTTools')) {
            $pluginSlug = plugin_basename(__FILE__);

            if (empty($transient->checked)) {
                return $transient;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreCompareDetail');

            $envatoTokenEmail = '';

            // Get the remote info
            $remoteInformation = WDTTools::getRemoteInformation('wdt-compare-detail', $purchaseCode, $envatoTokenEmail);

            // If a newer version is available, add the update
            if ($remoteInformation && version_compare(WDT_CD_VERSION, $remoteInformation->new_version, '<')) {
                $remoteInformation->package = $remoteInformation->download_link;
                $transient->response[$pluginSlug] = $remoteInformation;
            }
        }

        return $transient;
    }

    /**
     * @param $response
     * @param $action
     * @param $args
     *
     * @return bool|mixed
     */
    public static function wdtCheckInfoCompareDetail($response, $action, $args)
    {

        if (class_exists('WDTTools')) {

            $pluginSlug = plugin_basename(__FILE__);

            if ('plugin_information' !== $action) {
                return $response;
            }

            if (empty($args->slug)) {
                return $response;
            }

            $purchaseCode = get_option('wdtPurchaseCodeStoreCompareDetail');

            $envatoTokenEmail = '';

            if ($args->slug === $pluginSlug) {
                return WDTTools::getRemoteInformation('wdt-compare-detail', $purchaseCode, $envatoTokenEmail);
            }
        }

        return $response;
    }


    public static function addMessageOnPluginsPageCompareDetail()
    {
        /** @var bool $activated */
        $activated = get_option('wdtActivatedCompareDetail');

        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

        if (!$activated) {
            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Compare-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect);
        }
    }

    public static function addMessageOnUpdateCompareDetail($reply, $package, $updater)
    {
        if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === get_plugin_data( __FILE__ )['Name']) {
            /** @var string $url */
            $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

            /** @var string $redirect */
            $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

            if (!$package) {
                return new WP_Error(
                    'wpdatatables_compare_detail_not_activated',
                    sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Compare-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect)
                );
            }

            return $reply;
        }

        return $reply;
    }
  
}
