<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php /** @var WPDataTable $wpDataTable */ ?>
<?php $__tblid = $wpDataTable->getWpId(); ?>

<!-- .wpdt-c -->
<div class="wpdt-c">
    <!-- .wdt-frontend-modal -->
    <div id="wdt-cd-modal" class="modal fade wdt-cd-modal" style="display: none" data-backdrop="static"
         data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">

        <!-- .modal-dialog -->
        <div class="modal-dialog">

            <!-- .modal-content -->
            <div class="modal-content">

                <!-- .modal-header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <!--/ .modal-header -->

                <!-- .modal-body -->
                <div class="modal-body">
                  <div class="wdt-compare-modal-body-content"></div>
                </div>
                
                <!--/ .modal-body -->

                <!-- .modal-footer -->
                <div class="modal-footer">
                </div>
                <!--/ .modal-footer -->
                
                <!-- PRELOADER -->
                <div class="wdt-compare-preloader-wrapper" style="display:none;">
                  <table>
                    <tr>
                      <td>
                        <div class="wdt-compare-preloader"></div>
                      </td>
                    </tr>
                  </table>
                </div>
                <!--/ PRELOADER -->
            </div>
            <!--/ .modal-content -->
        </div>
        <!--/ .modal-dialog -->
    </div>
    <!--/ .wdt-frontend-modal -->
</div>
<!--/ .wpdt-c -->
