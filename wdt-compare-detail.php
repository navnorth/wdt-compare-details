<?php
namespace WDTCompareDetail;
session_start();
/**
 * @package Compare-Details for wpDataTables
 * @version 1.2.1
 */
/*
Plugin Name: Compare-Details for wpDataTables
Plugin URI: https://wpdatatables.com/documentation/addons/compare-detail/
Description: A wpDataTables addon which allows comparing details for selected rows in a popup.
Version: 1.2.1
Author: Navigation North
Author URI: https://www.navigationnorth.com
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
define('WDT_CD_VERSION', '1.2.1');
// Required wpDataTables version
define('WDT_CD_VERSION_TO_CHECK', '3.2');
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

        // Add JS For Compare Plugin
        add_action('wpdatatables_after_table', array('WDTCompareDetail\Plugin', 'wdtCompareDetailEnqueueBackendafterrender'), 10, 4);

        //Add Compare Column while CSV data loads from DB
        add_action('wpdatatables_filter_excel_array', array('WDTCompareDetail\Plugin', 'wdtInsertColumn'), 10, 4);

        //Hide Compare Column on very first load
        add_action('wpdatatables_before_get_columns_metadata', array('WDTCompareDetail\Plugin', 'extendCompareTableMetadata'), 10, 1);


        require_once(WDT_CD_ROOT_PATH . 'controllers/wdt_ajax_compare_actions.php');


        // Check if wpDataTables required version is installed
        if (version_compare(WDT_CURRENT_VERSION, WDT_CD_VERSION_TO_CHECK) < 0) {
            // Show message if required wpDataTables version is not installed
            add_action('admin_notices', array('WDTCompareDetail\Plugin', 'wdtRequiredVersionMissing'));
            return false;
        }

        return self::$initialized = true;

    }

    /**
     *  Make sure settings persists on page refresh
     */
    public static function extendCompareTableMetadata($tableId){
      global $wpdb;

      $_result = $wpdb->get_results("SELECT advanced_settings FROM ".$wpdb->prefix."wpdatatables WHERE id = ".$tableId, ARRAY_A);
      $_tmpadvset = json_decode($_result[0]['advanced_settings']);
      $_tmpadvset->compareDetailColumnOption = 0;
      $_tmpadvset->masterDetailColumnOption = 0;
      $_tmpadvset->sorting = 0;
      $_tmpadvset = json_encode($_tmpadvset);
      $wpdb->update($wpdb->prefix."wpdatatables_columns", array('advanced_settings'=>$_tmpadvset), array('table_id' => $tableId, 'orig_header' => 'Compare'));
      $wpdb->update($wpdb->prefix."wpdatatables_columns", array('filter_type'=>'none'), array('table_id' => $tableId, 'orig_header' => 'Compare'));


      $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
      if($pageWasRefreshed) {
        
      } else {
        $_result = $wpdb->get_results("SELECT advanced_settings FROM ".$wpdb->prefix."wpdatatables WHERE id = ".$tableId, ARRAY_A);
        $advancedSettingsTable = json_decode($_result[0]['advanced_settings']);

          if(isset($_SESSION["wdtvisibilitytoggleclicked"])){  //session used
            if($_SESSION["wdtvisibilitytoggleclicked"] == 'wdt-apply-columns-list'){  //tooggled visibility
              if($_SESSION["wdtvisibilitytoggleloopcount"] == 0){
                
              }
            }else{
              $_upd = $wpdb->update($wpdb->prefix."wpdatatables_columns", array('visible'=> $advancedSettingsTable->compareDetail), array('table_id' => $tableId, 'orig_header' => 'Compare'));
            }
          }else{
            $_upd = $wpdb->update($wpdb->prefix."wpdatatables_columns", array('visible'=> $advancedSettingsTable->compareDetail), array('table_id' => $tableId, 'orig_header' => 'Compare'));
          }

          if(isset($_SESSION["wdtvisibilitytoggleloopcount"])){
            if($_SESSION["wdtvisibilitytoggleloopcount"] > 0 ){
              unset($_SESSION["wdtvisibilitytoggleloopcount"]);
              unset($_SESSION["wdtvisibilitytoggleclicked"]);
            }else{
              $_SESSION["wdtvisibilitytoggleloopcount"]++;
            }
          }

      }

    }

    public static function wdtlog($txt){
      $_log = fopen(WDT_CD_ROOT_PATH."log.txt", "a");
      fwrite($_log, "\n".$txt);
      fclose($_log);
    }

    public static function wdtInsertColumn($namedDataArray, $wpid, $xls_url){
      foreach($namedDataArray as $i => $item) {
          $namedDataArray[$i] = array('Compare'=>'<input type="checkbox" aria-label="Compare Column Header" class="wdt_compare_checkbox" tabindex="0"/>') + $namedDataArray[$i];
      }
      return $namedDataArray;
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
     *  Extend datacolumn object
     * @param $dataColumn
     * @param $dataColumnProperties
     * @return mixed
     */
    public static function extendDataColumnObject($dataColumn,$dataColumnProperties){
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
     * Insert Modal templates
     * @param $wpDataTable \WPDataTable
     */
    public static function insertModal($wpDataTable)
    {
        include WDT_CD_TEMPLATE_PATH . 'compare_detail_modal.inc.php';
        include WDT_CD_TEMPLATE_PATH . 'style_compareblock.inc.php';
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
            wp_dequeue_script( 'wdt-column-config' );
            wp_deregister_script( 'wdt-column-config' );
            wp_enqueue_script('wdt-column-config', WDT_CD_ROOT_URL . 'assets/js/column_config_object.js', array(), WDT_CD_VERSION, true);
            //wp_enqueue_script('wdt-column-config', WDT_JS_PATH . 'wpdatatables/adm/table-settings/column_config_object.js', array(), WDT_CD_VERSION, true);
            
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
            wp_enqueue_script('wdt-cd-backend-afterrender', WDT_CD_ROOT_URL . 'assets/js/wdt.cd.backend.afterrender.js', array('wdt-md-frontend'), WDT_CD_VERSION, true);
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

            wp_enqueue_style(
                'wdt-cd-stylesheet',
                WDT_CD_ROOT_URL . 'assets/css/wdt.cd.css',
                array(),
                WDT_CD_VERSION
            );

             wp_enqueue_script('wdt-cd-frontend-afterrender', WDT_CD_ROOT_URL . 'assets/js/wdt.cd.backend.afterrender.js', array('wdt-md-frontend'), WDT_CD_VERSION, true);

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
        if (isset($table->compareDetailMaxCompare)) $advancedSettings->compareDetailMaxCompare = $table->compareDetailMaxCompare;
        
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
            
            if (isset($advancedSettings->compareDetailMaxCompare)) {
                $wpDataTable->compareDetailMaxCompare = $advancedSettings->compareDetailMaxCompare;
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
        
        if (isset($wpDataTable->compareDetailMaxCompare)) {
            $tableDescription->compareDetailMaxCompare = $wpDataTable->compareDetailMaxCompare;
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
     * Update wpdatatables table in database after deactivate/uninstall Compare-Detail add-on
     * @param $advancedSettingsFromAllTables
     * @param $action
     * @return array
     */
    public static function updateWpDataTableInDatabaseCompare($advancedSettingsFromAllTables, $action){
        global $wpdb;
        $allTablesIDWithCD = [];
        foreach ($advancedSettingsFromAllTables as $advancedSetting) {
            $tempTableID = (int)$advancedSetting['id'];
            $tempAdvancedSettings = json_decode($advancedSetting['advanced_settings']);
            if ( $action == 'deactivate'){

                $_tmpAdvSet = isset($tempAdvancedSettings->compareDetail);
                if (isset($tempAdvancedSettings->compareDetail) && $tempAdvancedSettings->compareDetail == 1 &&
                    isset($tempAdvancedSettings->compareDetailLogic) && $tempAdvancedSettings->compareDetailLogic == 'button'){

                    $tempAdvancedSettings->compareDetail = 0;
                    $tempAdvancedSettings->compareDetailLogic = 'row';
                    $tempAdvancedSettings = json_encode($tempAdvancedSettings);

                    $wpdb->update(
                        $wpdb->prefix . 'wpdatatables',
                        array( 'advanced_settings' => $tempAdvancedSettings),
                        array('id'=> $tempTableID)
                    );

                    $allTablesIDWithCD[] = $tempTableID;
                }


                if ($_tmpAdvSet){
                    $rows =  $wpdb->get_results( 'SELECT id, orig_header FROM '.$wpdb->prefix .'wpdatatables_columns WHERE table_id = '.$tempTableID.' ORDER BY pos ASC' , ARRAY_A);
                    $cnt = 0;
                    foreach($rows as $row){
                        if($row['orig_header'] == 'Compare'){

                            $wpdb->delete( $wpdb->prefix . 'wpdatatables_columns', array( 'id' => $row['id']));

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
                            }


                        }else{
                          $wpdb->update(
                              $wpdb->prefix . 'wpdatatables_columns',
                              array( 'pos' => $cnt),
                              array('id'=> $row['id'])
                          );
                          $cnt++;
                        }
                    }
                }



            } else if ( $action == 'uninstall') {
                if (isset($tempAdvancedSettings->compareDetail)){
                    unset($tempAdvancedSettings->compareDetail);
                    unset($tempAdvancedSettings->compareDetailLogic);
                    unset($tempAdvancedSettings->compareDetailRender);
                    unset($tempAdvancedSettings->compareDetailRenderPage);
                    unset($tempAdvancedSettings->compareDetailRenderPost);
                    unset($tempAdvancedSettings->compareDetailPopupTitle);
                    unset($tempAdvancedSettings->compareDetailMaxCompare);
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
    public static function updateWpDataTableColumnsInDatabaseCompare($allTablesIDWithCD){
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

        $allTablesIDWithCD = self::updateWpDataTableInDatabaseCompare($advancedSettingsFromAllTables, $action);

        if (!empty($allTablesIDWithCD)){
            //self::updateWpDataTableColumnsInDatabaseCompare($allTablesIDWithCD);
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

        $allTablesIDWithCD = self::updateWpDataTableInDatabaseCompare($advancedSettingsFromAllTables, $action);

        if (!empty($allTablesIDWithCD)){
            //self::updateWpDataTableColumnsInDatabaseCompare($allTablesIDWithCD);
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

} //end of class
