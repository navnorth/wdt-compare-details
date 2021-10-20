<!-- Panel Group -->
<div class="col-sm-6">

    <!-- Panel Heading -->
    <p class="wdt-activation-heading"><?php _e('Compare-Detail', 'wpdatatables'); ?></p>
    <!-- /Panel Heading -->

    <!-- Panel Body -->
    <div class="panel-body">

        <!-- TMS Store Purchase Code -->
        <div class="col-sm-10 wdt-purchase-code-compare-detail">

            <!-- TMS Store Purchase Code Heading-->
            <h4 class="c-black m-b-20">
                <?php _e('Purchase Code', 'wpdatatables'); ?>
                <i class="zmdi zmdi-help-outline" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Ignore this.', 'wpdatatables'); ?>"></i>
            </h4>
            <!-- /TMS Store Purchase Code Heading -->

            <!-- TMS Store Purchase Code Form -->
            <div class="form-group">
                <div class="row">

                    <!-- TMS Store Purchase Code Input -->
                    <div class="col-sm-11">
                        <div class="fg-line">
                            <input type="text" name="wdt-purchase-code-store-compare-detail"
                                   id="wdt-purchase-code-store-compare-detail"
                                   class="form-control input-sm"
                                   placeholder="<?php _e('No need for an activation code.', 'wpdatatables'); ?>"
                                   value=""
                            />
                        </div>
                    </div>
                    <!-- TMS Store Purchase Code Input -->

                    <!-- TMS Store Purchase Code Activate Button -->
                    <div class="col-sm-1">
                        <button class="btn btn-primary waves-effect" id="wdt-activate-plugin-compare-detail">
                            <?php _e('Activate ', 'wpdatatables'); ?>
                        </button>
                    </div>
                    <!-- /TMS Store Purchase Code Activate Button -->

                </div>
            </div>
            <!-- /TMS Store Purchase Code Form -->

        </div>
        <!-- /TMS Store Purchase Code -->

    </div>
    <!-- /Panel Body -->

</div>
<!-- /Panel Group -->

