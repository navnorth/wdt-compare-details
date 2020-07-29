(function ($) {
  $(function () {

    // Handle Activation Settings
    handleActivationSettings();


    // Add event on "Activate"/"Deactivate" button
    $('#wdt-activate-plugin-compare-detail').on('click', function () {
      if (typeof wdt_current_config.wdtActivatedCompareDetail === 'undefined' || wdt_current_config.wdtActivatedCompareDetail == 0 || wdt_current_config.wdtActivatedCompareDetail == '') {
        activatePlugin()
      } else {
        deactivatePlugin()
      }
    });

    // Activate plugin
    function activatePlugin() {
      $('#wdt-activate-plugin-compare-detail').html('Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;
      
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wpdatatables_activate_plugin',
          purchaseCodeStore: $('#wdt-purchase-code-store-compare-detail').val(),
          wdtNonce: $('#wdtNonce').val(),
          slug: 'wdt-compare-detail',
          domain: domain,
          subdomain: subdomain
        },
        success: function (response) {
          let valid = JSON.parse(response).valid;
          let domainRegistered = JSON.parse(response).domainRegistered;

          if (valid === true && domainRegistered === true) {
            wdt_current_config.wdtActivatedCompareDetail = 1;
            wdt_current_config.wdtPurchaseCodeStoreCompareDetail = $('#wdt-purchase-code-store-compare-detail').val();
            wdtNotify('Success!', 'Plugin has been activated', 'success');
            $('#wdt-purchase-code-store-compare-detail').prop('disabled', 'disabled');
            $('#wdt-activate-plugin-compare-detail').removeClass('btn-primary').addClass('btn-danger').html('Deactivate');
          } else if (valid === false) {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.purchaseCodeInvalid, 'danger');
            $('#wdt-activate-plugin-compare-detail').html('Activate');
          } else {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
            jQuery('#wdt-activate-plugin-compare-detail').html('Activate');
          }
        },
        error: function () {
          wdt_current_config.wdtActivatedCompareDetail = 0;
          wdtNotify('Error!', 'Unable to activate the plugin. Please try again.', 'danger');
          $('#wdt-activate-plugin-compare-detail').html('Activate');
        }
      });
    }

    // Deactivate plugin
    function deactivatePlugin() {
      $('#wdt-activate-plugin-compare-detail').html('Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;
      let params = {
        action: 'wpdatatables_deactivate_plugin',
        wdtNonce: $('#wdtNonce').val(),
        domain: domain,
        subdomain: subdomain,
        slug: 'wdt-compare-detail',
      };

      if (wdt_current_config.wdtPurchaseCodeStoreCompareDetail) {
        params.type = 'code';
        params.purchaseCodeStore = wdt_current_config.wdtPurchaseCodeStoreCompareDetail;
      }

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: params,
        success: function (response) {
          var parsedResponse = JSON.parse(response);
          if (parsedResponse.deactivated === true) {
            wdt_current_config.wdtPurchaseCodeStoreCompareDetail = '';
            wdt_current_config.wdtActivatedCompareDetail = 0;
            $('#wdt-purchase-code-store-compare-detail').prop('disabled', '').val('');
            $('#wdt-activate-plugin-compare-detail').removeClass('btn-danger').addClass('btn-primary').html('Activate');
            $('.wdt-preload-layer').animateFadeOut();
            $('.wdt-purchase-code-compare-detail').show();
          } else {
            wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.unable_to_deactivate_plugin, 'danger');
            $('#wdt-activate-plugin-compare-detail').html('Deactivate');
          }
        }
      });
    }


    function handleActivationSettings() {
      if (wdt_current_config.wdtActivatedCompareDetail == 1) {

        // Fill the purchase code input on settings load
        $('#wdt-purchase-code-store-compare-detail').val(wdt_current_config.wdtPurchaseCodeStoreCompareDetail);

        // Change the "Activate"/"Deactivate" button if plugin is activated/deactivated
        $('#wdt-purchase-code-store-compare-detail').prop('disabled', 'disabled');
        $('#wdt-activate-plugin-compare-detail').removeClass('btn-primary').addClass('btn-danger').html('Deactivate');

      } else {
        $('#wdt-purchase-code-store-compare-detail').prop('disabled', '');
        $('#wdt-activate-plugin-compare-detail').removeClass('btn-danger').addClass('btn-primary').html('Activate');
      }
    }
  });
})(jQuery);
