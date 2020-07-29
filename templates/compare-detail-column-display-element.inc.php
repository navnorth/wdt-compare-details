<div class="col-sm-6 wdt-cd-column-block hidden">
    <h4 class="c-black m-b-20">
        <?php _e('Compare-detail column', 'wpdatatables'); ?>
        <i class="zmdi zmdi-help-outline" data-popover-content="#compare-detail-column"
           data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
    </h4>

    <!-- Hidden popover with image hint -->
    <div class="hidden" id="compare-detail-column">
        <div class="popover-heading">
            <?php _e('Add to the details section', 'wpdatatables'); ?>
        </div>

        <div class="popover-body">
            <?php _e('If you turn on this option, values from this column will appear in the Details section in the Compare-Detail popup or post/page', 'wpdatatables'); ?>
        </div>
    </div>
    <!-- /Hidden popover with image hint -->

    <div class="form-group">
        <div class="toggle-switch" data-ts-color="blue">
            <label for="wdt-cd-column"
                   class="ts-label"><?php _e('Add to the details section', 'wpdatatables'); ?></label>
            <input id="wdt-cd-column" type="checkbox" hidden="hidden">
            <label for="wdt-cd-column" class="ts-helper"></label>
        </div>
    </div>

</div>