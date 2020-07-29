<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php /** @var WPDataTable $wpDataTable */ ?>
<div id="<?php echo $wpDataTable->getId() ?>_cd_dialog" style="display: none">

    <!-- .wdt-details-dialog-fields-block -->
    <div class="row wdt-details-dialog-fields-block">
        <?php
        /** @var WDTColumn $dataColumn */
        foreach( $wpDataTable->getColumnsByHeaders() as $dataColumn_key=>$dataColumn ) {
            ?>
            <!-- .form-group -->
            <div
                <?php
                if (($dataColumn_key == 'wdt_ID') || ($dataColumn_key == 'comparedetail') ||
                (isset($dataColumn->compareDetailColumnOption) && $dataColumn->compareDetailColumnOption !== 1) ||
                (($wpDataTable->getUserIdColumn() != '') && ($dataColumn_key == $wpDataTable->getUserIdColumn()))) { ?>
                    style="display: none"
                    <?php if ($dataColumn_key == $wpDataTable->getIdColumnKey()) { ?>
                        class="idRow"
                    <?php } ?>
                <?php } else { ?>
                    class="form-group col-xs-12">
                <?php } ?>

                <p  class="col-sm-3 <?php echo $wpDataTable->getId() ?>_<?php echo $dataColumn_key ?>">
                    <?php echo $dataColumn->getTitle(); ?>:<?php if ($dataColumn->isNotNull()) { ?> * <?php } ?>
                </p>
                <!-- .col-sm-9 -->
                <div class="col-sm-9">
                    <div class="fg-line">
                        <div id="<?php echo $wpDataTable->getId() ?>_<?php echo $dataColumn_key ?>_detials"
                             data-key="<?php echo $dataColumn_key ?>"
                             data-column_type="<?php echo $dataColumn->getDataType(); ?>"
                             data-column_header="<?php echo $dataColumn->getTitle(); ?>"
                             data-input_type="<?php echo $dataColumn->getInputType(); ?>"
                             style="<?php echo $dataColumn->getCSSStyle(); ?>"
                             class="detailColumn column-<?php echo strtolower(str_replace(' ', '-',$dataColumn->getOriginalHeader())) . " " . $dataColumn->getCSSClasses() ?>"
                        ></div>
                    </div>
                </div>
                <!-- .col-sm-9 -->
            </div>
            <!--/ .form-group -->
        <?php } ?>
        <?php if (isset($wpDataTable->compareDetail) && $wpDataTable->compareDetail &&
            isset($wpDataTable->compareDetailLogic) && $wpDataTable->compareDetailLogic =='row' &&
            isset($wpDataTable->compareDetailRender) &&
            ($wpDataTable->compareDetailRender =='wdtNewPage' || $wpDataTable->compareDetailRender =='wdtNewPost') &&
            (isset($wpDataTable->compareDetailRenderPage) || isset($wpDataTable->compareDetailRenderPost))) {
            $renderAction = $wpDataTable->compareDetailRender == 'wdtNewPage' ? $wpDataTable->compareDetailRenderPage : $wpDataTable->compareDetailRenderPost;?>
            <form  class='wdt_cd_form' method='post' target='_blank' action='<?php echo $renderAction; ?>'>
                <input class='wdt_cd_hidden_data' type='hidden' name='wdt_details_data' value=''>
                <input class='compare_detail_column_btn' type='submit' value='Submit'>
            </form>
        <?php } ?>
    </div>
    <!--/ .wdt-details-dialog-fields-block -->

</div>
