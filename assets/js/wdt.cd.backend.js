(function ($) {
    $(function () {


        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {

            compareDetail: 0,
            compareDetailLogic: '',
            compareDetailRender: '',
            compareDetailRenderPage: '',
            compareDetailRenderPost: '',
            compareDetailPopupTitle: 10,
            compareDetailMaxCompare: '',
            setCompareDetail: function (compareDetail) {



                let state = false;
                let compareColumn;
                wpdatatable_config.compareDetail = compareDetail;
                $('#wdt-cd-toggle-compare-detail').prop('checked', compareDetail);
                if (compareDetail == 1) {
                    jQuery('.wdt-cd-column-block').removeClass('hidden');
                    jQuery('.wdt-cd-click-event-logic-block').animateFadeIn();
                    jQuery('.wdt-cd-render-data-in-block').animateFadeIn();
                    jQuery('.wdt-cd-popup-title-block').animateFadeIn();
                    jQuery('.wdt-cd-click-event-logic-block').show();
                    jQuery('.wdt-cd-render-data-in-block').show();
                    jQuery('.wdt-cd-popup-title-block').show();
                    jQuery('#wdt-cd-click-event-logic').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-cd-render-data-in').selectpicker('refresh').trigger('change');
                    var cnt = 0;
                    for (let column of wpdatatable_config.columns) {
                        
                        if (column.orig_header === 'Compare') {
                            wpdatatable_config.columns[cnt].filter_type = "none";
                            wpdatatable_config.columns[cnt].masterDetailColumnOption = 0;
                            wpdatatable_config.columns[cnt].compareDetailColumnOption = 0;
                        }
                        cnt++;
                    }

                } else {
                    jQuery('.wdt-cd-click-event-logic-block').hide();
                    jQuery('.wdt-cd-render-data-in-block').hide();
                    jQuery('.wdt-cd-popup-title-block').hide();
                    jQuery('.wdt-cd-render-post-block').hide();
                    jQuery('.wdt-cd-render-page-block').hide();
                    jQuery('.wdt-cd-column-block').addClass('hidden');
                    wpdatatable_config.setCompareDetailPopupTitle('');
                    wpdatatable_config.setCompareDetailMaxCompare(10);
                    wpdatatable_config.setCompareDetailLogic('row');
                    wpdatatable_config.setCompareDetailRender('popup');
                        
                    var cnt = 0;
                    for (let column of wpdatatable_config.columns) {
                        if (column.orig_header === 'Compare') {
                            wpdatatable_config.columns[cnt].filter_type = "none";
                            wpdatatable_config.columns[cnt].masterDetailColumnOption = 0;
                            wpdatatable_config.columns[cnt].compareDetailColumnOption = 0;
                        }
                        cnt++;
                    }
                    
                    for (let column of wpdatatable_config.columns) {
                        if (column.orig_header === 'comparedetail') {
                            state = true;
                            compareColumn = column;
                        }
                    }
                    if (state) {
                        //fix column positions after deleting comparedetail column
                        for (var i = compareColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }

                        //remove comparedetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, compareColumn.orig_header);

                        //remove comparedetail column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == compareColumn.orig_header;
                            });
                    }

                }
            },
            setCompareDetailLogic: function (compareDetailLogic) {
                wpdatatable_config.compareDetailLogic = compareDetailLogic;
                let state = false;
                let compareColumn;
                for (let column of wpdatatable_config.columns) {
                    if (column.orig_header === 'comparedetail') {
                        state = true;
                        compareColumn = column;
                    }
                }
                if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.compareDetailLogic === 'row') {

                    if (state) {
                        //fix column positions after deleting comparedetail column
                        for (var i = compareColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }
                        //remove comparedetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, compareColumn.orig_header);

                        //remove comparedetaisl column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == compareColumn.orig_header;
                            });
                    }

                } else if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.compareDetailLogic === 'button') {

                    if (!state) {
                        //Adding a new Compare-detail column
                        wpdatatable_config.addColumn(
                            new WDTColumn(
                                {
                                    type: 'comparedetail',
                                    orig_header: 'comparedetail',
                                    display_header: 'comparedetail',
                                    pos: wpdatatable_config.columns.length,
                                    details: 'comparedetail',
                                    parent_table: wpdatatable_config
                                }
                            )
                        );
                    }
                }
                $('#wdt-cd-click-event-logic')
                    .val( compareDetailLogic )
                    .selectpicker('refresh');
            },
            setCompareDetailRender: function (compareDetailRender) {
                wpdatatable_config.compareDetailRender = compareDetailRender;
                $('#wdt-cd-render-data-in').selectpicker('val', compareDetailRender);
                if ( wpdatatable_config.compareDetailRender == 'wdtNewPage'){
                    jQuery('.wdt-cd-render-post-block').hide();
                    jQuery('.wdt-cd-popup-title-block').hide();
                    jQuery('.wdt-cd-render-page-block').animateFadeIn();
                    jQuery('#wdt-cd-render-page-block').selectpicker('refresh').trigger('change');
                }else if ( wpdatatable_config.compareDetailRender == 'wdtNewPost'){
                    jQuery('.wdt-cd-render-page-block').hide();
                    jQuery('.wdt-cd-popup-title-block').hide();
                    jQuery('.wdt-cd-render-post-block').animateFadeIn();
                    jQuery('#wdt-cd-render-post-block').selectpicker('refresh').trigger('change');
                } else if ( wpdatatable_config.compareDetailRender == 'popup' && wpdatatable_config.compareDetail){
                    jQuery('.wdt-cd-render-post-block').hide();
                    jQuery('.wdt-cd-render-page-block').hide();
                    jQuery('.wdt-cd-popup-title-block').animateFadeIn();
                }
            },
            setCompareDetailRenderPage: function (compareDetailRenderPage) {
                wpdatatable_config.compareDetailRenderPage = compareDetailRenderPage;
                $('#wdt-cd-render-page').selectpicker('val', compareDetailRenderPage);
            },
            setCompareDetailRenderPost: function (compareDetailRenderPost) {
                wpdatatable_config.compareDetailRenderPost = compareDetailRenderPost;
                $('#wdt-cd-render-post').selectpicker('val', compareDetailRenderPost);
            },
            setCompareDetailPopupTitle: function (compareDetailPopupTitle) {
                wpdatatable_config.compareDetailPopupTitle = compareDetailPopupTitle;
                jQuery( '#wdt-cd-popup-title' ).val( compareDetailPopupTitle );
            },
            setCompareDetailMaxCompare: function (compareDetailMaxCompare) {
                wpdatatable_config.compareDetailMaxCompare = compareDetailMaxCompare;
                jQuery( '#wdt-cd-max-compare' ).val( compareDetailMaxCompare );
            },
            
            

        });


        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.advanced_settings !== '') {

            var advancedSettings = JSON.parse(wpdatatable_init_config.advanced_settings);

            if (advancedSettings !== null) {

                var compareDetail = advancedSettings.compareDetail;
                var compareDetailLogic = advancedSettings.compareDetailLogic;
                var compareDetailRender = advancedSettings.compareDetailRender;
                var compareDetailRenderPage = advancedSettings.compareDetailRenderPage;
                var compareDetailRenderPost = advancedSettings.compareDetailRenderPost;
                var compareDetailPopupTitle = advancedSettings.compareDetailPopupTitle;
                var compareDetailMaxCompare = advancedSettings.compareDetailMaxCompare;
                
                if (typeof compareDetail !== 'undefined') {
                    wpdatatable_config.setCompareDetail(compareDetail);
                }

                if (typeof compareDetailLogic !== 'undefined') {
                    wpdatatable_config.setCompareDetailLogic(compareDetailLogic);
                }

                if (typeof compareDetailRender !== 'undefined') {
                    wpdatatable_config.setCompareDetailRender(compareDetailRender);
                }

                if (typeof compareDetailRenderPage !== 'undefined') {
                    wpdatatable_config.setCompareDetailRenderPage(compareDetailRenderPage);
                }

                if (typeof compareDetailRenderPost !== 'undefined') {
                    wpdatatable_config.setCompareDetailRenderPost(compareDetailRenderPost);
                }

                if (typeof compareDetailPopupTitle !== 'undefined') {
                    wpdatatable_config.setCompareDetailPopupTitle(compareDetailPopupTitle);
                }
                
                if (typeof compareDetailMaxCompare !== 'undefined') {
                    wpdatatable_config.setCompareDetailMaxCompare(compareDetailMaxCompare);
                }

            }

        }

        /**
         * Toggle "Compare-detail" option
         */
        $('#wdt-cd-toggle-compare-detail').change(function () {
            wpdatatable_config.setCompareDetail($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Select "Compare-detail" logic
         */
        $('#wdt-cd-click-event-logic').change(function () {
            wpdatatable_config.setCompareDetailLogic($(this).val());
        });

        /**
         * Select "Compare-detail" render option
         */
        $('#wdt-cd-render-data-in').change(function () {
            wpdatatable_config.setCompareDetailRender($(this).val());
        });

        /**
         * Select "Compare-detail" render page
         */
        $('#wdt-cd-render-page').change(function () {
            wpdatatable_config.setCompareDetailRenderPage($(this).val());
        });

        /**
         * Select "Compare-detail" render post
         */
        $('#wdt-cd-render-post').change(function () {
            wpdatatable_config.setCompareDetailRenderPost($(this).val());
        });

        /**
         * Set "Compare-detail" Popup Title
         */
        $('#wdt-cd-popup-title').change(function (e) {
            wpdatatable_config.setCompareDetailPopupTitle($(this).val());
        });
        
        /**
         * Set "Compare-detail" max Compare
         */
        $('#wdt-cd-max-compare').change(function (e) {
            wpdatatable_config.setCompareDetailMaxCompare($(this).val());
        });

        /**
         * Show Compare-detail settings tab
         */
        if (!jQuery('.compare-detail-settings-tab').is(':visible')) {
            jQuery('.compare-detail-settings-tab').animateFadeIn();
        }

    });

})(jQuery);


/**
 * Initialize new property in object
 */
function callbackExtendColumnObjectCompare(column,obj) {

    var newOptionName = 'compareDetailColumnOption';
    if (typeof obj.compareDetailColumnOption == 'undefined'){
        obj.setAdditionalParam(newOptionName, column.compareDetailColumnOption);
    } else {
        obj.setAdditionalParam(newOptionName, 1);
    }
}

/**
 * Extend column settings and return it in an object format
 */
function callbackExtendOptionInObjectFormatCompare(allColumnSettings, obj) {
    if (wpdatatable_config.compareDetail == 1){
        allColumnSettings.compareDetailColumnOption = obj.compareDetailColumnOption;
        return allColumnSettings;
    }
}

/**
 * Extend a small block with new column option in the list
 */
function callbackExtendSmallBlockCompare($columnBlock, column) {
    $columnBlock.find('i.wdt-toggle-show-compare').click(function (e) {
        e.preventDefault();
        if (!column.compareDetailColumnOption) {
            column.compareDetailColumnOption = 1;
            jQuery(this)
              .removeClass('inactive')
        } else {
            column.compareDetailColumnOption = 0;
            jQuery(this)
              .addClass('inactive')
        }
    });

    if (!column.compareDetailColumnOption) {
        $columnBlock.find('i.wdt-toggle-show-compare')
          .addClass('inactive')
    }
}

/**
 * Fill in the visible inputs with data
 */
function callbackFillAdditinalOptionWithDataCompare(obj) {
    jQuery('#wdt-cd-column').prop('checked',obj.compareDetailColumnOption).change();
}

/**
 * Hide tabs and options from Compare-detail column
 */
function callbackHideColumnOptionsCompare(obj) {
    if (obj.type == 'comparedetail') {
        jQuery('li.column-filtering-settings-tab').hide();
        jQuery('li.column-editing-settings-tab').hide();
        jQuery('li.column-sorting-settings-tab').hide();
        jQuery('li.column-conditional-formatting-settings-tab').hide();
        jQuery('#wdt-column-type option[value="comparedetail"]').prop('disabled', '');
        jQuery('#wdt-column-type').prop('disabled', 'disabled').hide();
        jQuery('#column-data-settings .row:first-child').hide();
        jQuery('div.wdt-possible-values-type-block').hide();
        jQuery('div.wdt-possible-values-options-block').hide();
        jQuery('div.wdt-formula-column-block').hide();
        jQuery('div.wdt-skip-thousands-separator-block').hide();
        jQuery('div.wdt-numeric-column-block').hide();
        jQuery('div.wdt-float-column-block').hide();
        jQuery('div.wdt-date-input-format-block').hide();
        jQuery('div.wdt-group-column-block').hide();
        jQuery('div.wdt-link-target-attribute-block').hide();
        if (jQuery('#wdt-link-button-attribute').is(':checked')) {
            jQuery('div.wdt-link-button-label-block').show();
            jQuery('div.wdt-link-button-class-block').show();
        }
        jQuery('div.wdt-link-button-attribute-block').show();
        jQuery('div.wdt-cd-column-block').hide();
    } else {
        jQuery('li.column-conditional-formatting-settings-tab').show();
        jQuery('#wdt-column-type option[value="comparedetail"]').prop('disabled', 'disabled');
        jQuery('#wdt-column-type').prop('disabled', '');
        jQuery('#column-data-settings .row:first-child').show();
    }

}

/**
 * Apply changes from UI to the object for new column option
 */
function callbackApplyUIChangesForNewColumnOptionCompare(obj) {
    obj.compareDetailColumnOption = jQuery('#wdt-cd-column').is(':checked') ? 1 : 0;
}
