<!-- Compare-Detail settings -->
<div role="tabpanel" class="tab-pane" id="compare-detail-settings">
    <!-- .row -->
    <div class="row">
        <!-- Compare-detail checkbox-->
        <div class="col-sm-4 m-b-16 wdt-cd-toggle-compare-detail-block">
            <h4 class="c-title-color m-b-4">
                <?php _e('Compare-detail', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enable this to turn the compare-detail functionality on for this table.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-cd-toggle-compare-detail" type="checkbox" hidden="hidden">
                <label for="wdt-cd-toggle-compare-detail"
                       class="ts-label"><?php _e('Enable compare-detail functionality', 'wpdatatables'); ?></label>              
            </div>
        </div>
        <!-- /Compare-Detail checkbox -->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">
        
        <!-- Compare-Detail Popup Title -->

        <div class="col-sm-4 wdt-cd-popup-title-block hidden">
            <h4 class="c-title-color m-b-4">
                <?php _e('Popup Title', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter a title for the popup with row details. If you leave the field blank, the default title is “Row details”', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="wdt-cd-popup-title" id="wdt-cd-popup-title"
                                   class="form-control input-sm" placeholder="Enter a title for Popup modal"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- /Compare-Detail Popup Title -->
    </div>
    <!-- /.row -->
    
    
    <!-- .row -->
    <div class="row">
        
        <!-- Compare-Detail Max COmpare -->

        <div class="col-sm-4 wdt-cd-popup-title-block hidden">
            <h4 class="c-title-color m-b-4">
                <?php _e('Max. Comparison', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Enter the maximum number of selections for comparison. Defaults to 10 if not set.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="number" min="1" name="wdt-cd-max-compare" id="wdt-cd-max-compare"
                                   class="form-control input-sm"
                                   value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- /Compare-Detail Popup Title -->
    </div>
    <!-- /.row -->
    
    
</div>
<!-- /Compare-Detail settings -->
