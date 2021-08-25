var forcompare = [];
var tbl_cntr = 0;
var idtoinstance = [];
var globalresponse = 0;
var msg_timer = [];
var show_partial_percentage = .18;
var breakpoints = [4,3,2]
var wpdt_instance_cntr = 0; 
var prev_instance_id = '';
var active_tblno = 0; //works only after compare modal visible
var prev_scrollpos = 0;
var max_compare_len = [];
wpDataTablesHooks.onRenderDetails.push(function showDetailModalCompare(tableDescription) {
    (function ($) {
        if (tableDescription.compareDetail) { // compare enabled
            //var max_compare_len = (tableDescription.compareDetailMaxCompare)? tableDescription.compareDetailMaxCompare: 10;
           /**
           * Insert Compare and Clear Buttons
           */
            //var tableid = gettableid(jQuery('.wpDataTable.dataTable'));
            var tableid = tableDescription.tableWpId;
            var thebody = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >tbody');
            var theheadtr = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >thead tr');
            var firstheader = tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >thead>tr>th:first-child';
            var firstcolumn = tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >tbody>tr>td:first-child';
            max_compare_len[tableid] = (tableDescription.compareDetailMaxCompare)? tableDescription.compareDetailMaxCompare: 10;
            /* Fix to checkbox conflict with master detail row click */
            thebody.unbind();
            if(tableDescription.masterDetailLogic == 'row'){
              if(tableDescription.masterDetailRender === 'wdtNewPage' || tableDescription.masterDetailRender === 'wdtNewPost') {
                
                /**
                * From Master Detail wdt.md.frontend.js begin
                */
                $(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' tbody').on('mouseenter', 'tr', function () {

                    $(this).css("cursor", "pointer");

                }).on('click', 'tr', function (e) {
                    let elm = e.target.nodeName;
                    if(elm == 'TD'){
                      if ($(this).hasClass('row-detail')) {
                          rowData = $(this).closest('tr').prevAll('.detail-show');
                      } else {
                          rowData = $(this);
                      }

                      var row = rowData.get(0);

                      var data = wpDataTables[tableDescription.tableId].fnGetData(row);
                      var detailObject = {};
                      $(data).each(function (index, el) {
                          var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');

                          $columnValue = $columnValue[0].id.replace(tableDescription.tableId + "_", "");
                          if (el) {
                              var val = el.toString();
                          } else {
                              var val = '';
                          }
                          if ($columnValue != 'masterdetail_detials') {
                              $columnValue = $columnValue.replace('_detials', '');
                              detailObject[$columnValue] = val;
                          }

                      });
                      detailObject['wdt_md_id_table'] = tableDescription.dataTableParams.wpdatatable_id;
                      $inputValue = $('#' + tableDescription.tableId + '_md_dialog .wdt_md_hidden_data');
                      $submitButton = $('#' + tableDescription.tableId + '_md_dialog .master_detail_column_btn');
                      $inputValue[0].value = JSON.stringify(detailObject);
                      $submitButton.click();
                    }
                });
                /**
                * From Master Detail wdt.md.frontend.js end
                */
                
              }else{
                
                thebody.unbind();
                thebody.on('click', 'tr', function (e) {
                    if(e.target.nodeName == 'TD'){
                      showDetailsModal(this, tableDescription);
                    }  
                });
              }
            }
          
            jQuery('.master_detail_column_btn').attr('role','button');
            thebody.attr('logic',tableDescription.masterDetailLogic);

            var checkExist = setInterval(function() {
               if (jQuery('table.wpDataTable.dataTable').length) {
                  clearInterval(checkExist);
                  insertCompareButton(tableDescription.tableId, max_compare_len[tableid]);
               }
            }, 100); // check every 100ms

            var thebodytr = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' tbody tr td:nth-of-type(1)');          


            /**
            * Show details modal for all tables
            */
            function showDetailsModal(obj, tableDescription) {
                var modal = $('#wdt-md-modal');
                var modalTitle = tableDescription.masterDetailPopupTitle !== '' ? tableDescription.masterDetailPopupTitle : wdtMdTranslationStrings.modalTitle;

                if ($(obj).hasClass('disabled'))
                    return false;

                if (tableDescription.editable && tableDescription.popoverTools) {
                    $('.wpDataTablesPopover.editTools').hide();
                }

                modal.find('.modal-title').html(modalTitle);
                modal.find('.modal-body').html('');
                modal.find('.modal-footer').html('');
                var rowData;

                if ((tableDescription.masterDetailLogic === 'button' || tableDescription.masterDetailLogic === 'row') && $(obj).parents('.columnValue').length) {
                    rowData = $(obj).closest('tr').prevAll('.detail-show');
                } else if (tableDescription.editable && tableDescription.masterDetailLogic === 'row') {
                    rowData = $(tableDescription.selector + ' tr.selected');
                } else if (tableDescription.masterDetailLogic === 'button') {
                    rowData = $(obj).closest('tr');
                } else {
                    rowData = $(obj);
                }

                var row = rowData.get(0);

                var data = wpDataTables[tableDescription.tableId].fnGetData(row);

                $(data).each(function (index, el) {
                    var $columnValue = $('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');
                    if (el) {
                        var val = el.toString();
                    } else {
                        var val = '';
                    }

                    $columnValue.html(val);

                });
                modal.find('.modal-body').append($(tableDescription.selector + '_md_dialog').show());
                modal.modal('show');
            }

            
            /**
            * Handle compare checkbox click
            */      
            jQuery(document).on('click','table.wpDataTable tbody tr td input.wdt_compare_checkbox',function(e){
              
              let tblno = parseInt(jQuery(this).closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
              let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
              if(forcompare[tblno].length > (max_compare_len[tmp_tableid] - 1)){
                jQuery(this).prop("checked", false);
              }

              var rowindex = parseInt(getrowindex(jQuery(this)));
              addtomodcomparelist(jQuery(this), rowindex, tblno, max_compare_len[tmp_tableid],  function(){
                preventfurtherchecks(tblno,max_compare_len[tmp_tableid]);
              });
              e.stopImmediatePropagation();
            })

            /**
            * Handle enter key event on compare checkboxes
            */
            jQuery(document).on('keyup','table.wpDataTable tbody tr td input.wdt_compare_checkbox', function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);

              if(keyCode == 13){
                
                let tblno = parseInt(jQuery(this).closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
                let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
                if(forcompare[tblno].length > (max_compare_len[tmp_tableid] - 1)){
                  jQuery(this).prop("checked", false);
                }else{
                  if(jQuery(this).is(":checked")){
                    jQuery(this).prop("checked", false);
                    preventfurtherchecks(tblno,max_compare_len[tmp_tableid]);
                  }else{
                    jQuery(this).prop("checked", true);
                    preventfurtherchecks(tblno,max_compare_len[tmp_tableid]);
                  }
                }

                var rowindex = parseInt(getrowindex(jQuery(this)));
                addtomodcomparelist(jQuery(this), rowindex, tblno, max_compare_len[tmp_tableid], function(){
                  preventfurtherchecks(tblno,max_compare_len[tmp_tableid]);
                });
                e.stopImmediatePropagation();

              }
            })

            /**
            * Display Compare Modal on Compare button click
            */
            jQuery(document).on('click','.dataTables_compare_button_wrapper a.compare_button',function(e){
              let tmp_tableid = gettableid(jQuery(e.target).closest('.wpnn_wpdt_action_wrapper').siblings('.wdtResponsiveWrapper').find('table.wpDataTable.dataTable'));
              initiateModal(this,tableDescription,max_compare_len[tmp_tableid]);
              e.stopImmediatePropagation();
            })
            
            /**
            * Display Compare Modal on enter key press
            */
            jQuery(document).on('keyup','.dataTables_compare_button_wrapper a.compare_button',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
                initiateModal(this,tableDescription,max_compare_len[tmp_tableid]);
                e.stopImmediatePropagation();
              }
            })
            
            /**
            * Clear compare selections on Clear Compare Button click
            */
            jQuery(document).on('click','.dataTables_compare_button_wrapper a.clear_compare_button',function(e){
              let tblno = parseInt(jQuery(this).closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
              let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
              clearcomparison(tableid,tblno,max_compare_len[tmp_tableid]);
              e.stopImmediatePropagation();
            })
            
            /**
            * Clear compare selections on Clear Compare Button enter key press
            */
            jQuery(document).on('keyup','.dataTables_compare_button_wrapper a.clear_compare_button',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                let tblno = parseInt(jQuery(this).closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
                let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
                clearcomparison(tableid,tblno,max_compare_len[tmp_tableid]);
              }
              e.stopImmediatePropagation();
            })

            /**
            * Remove column in compare modal throgh x button click
            */
            jQuery(document).on('click','.wdt-remove-column',function(e){
              var tid = jQuery(this).closest('.wpdt_main_wrapper').find('.wdtResponsiveWrapper table.wpDataTable').attr('data-wpdatatable_id');
              let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
              deletecolumn(jQuery(this),tid,max_compare_len[tmp_tableid],function(){
                jQuery('.wdt-cd-modal').focus();
                adjusmodalcolumnwidth();
                //setCompareTableWidth();
              });
            })
            
            /**
            * Remove column in compare modal throgh x button enter key press
            */
            jQuery(document).on('keyup','.wdt-remove-column',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                var tid = jQuery(this).closest('.wpdt_main_wrapper').find('.wdtResponsiveWrapper table.wpDataTable').attr('data-wpdatatable_id');
                let tmp_tableid = gettableid(jQuery(e.target).closest('table.wpDataTable.dataTable'));
                deletecolumn(jQuery(this),tid,max_compare_len[tmp_tableid],function(){
                  jQuery('#wdt-cd-modal').focus();
                  //setCompareTableWidth();
                });
              }
            })

            /**
            * Set focus back to last active element before opening the  compare modal.
            */
            jQuery('.wdt-cd-modal').on('hidden.bs.modal', function () {
              jQuery('.wdt-compare-preloader-wrapper').hide(300,function(){
                setTimeout(function(){
                  jQuery(window).scrollTop(prev_scrollpos);
                }, 1);
              });
              jQuery(this).closest('.wpdt_main_wrapper').find('.dataTables_compare_button_wrapper a.compare_button').focus();
              //jQuery('.dataTables_compare_button_wrapper .compare_button').focus();
            });
            
            /**
            * Clear Modal contents and set modal aria-hidden attribute to true on close.
            */
            jQuery("#wdt-cd-modal").on('hide.bs.modal', function(){
              jQuery('#wdt-cd-modal').find('.wdt-compare-modal-body-content').html('');
              jQuery('#wdt-cd-modal').attr('aria-hidden','true');
              jQuery('.wdt-compare-modal-body-content').removeClass('enola');
              jQuery('.wdt-compare-modal-body-content').removeAttr('style');
            });
            
            /**
            * Set aria-hidden attribute to false on Modal open.
            */
            jQuery("#wdt-cd-modal").on('show.bs.modal', function(){
              jQuery('#wdt-cd-modal').attr('aria-hidden','false');
            });
            
            
            jQuery(document).ready(function(){
              jQuery('.wpdt-c').each(function(i, obj) {
                  if(jQuery(obj).find('table.wpDataTable').length){
                    var active_tbl_id = jQuery(obj).attr('id');
                  }
              });
            });

            jQuery.each(wpDataTables, function(index, item) {
              
              if(prev_instance_id != index){
                item.addOnDrawCallback( function(wpdt_instance_cntr){
                  synccomparechecks(idtoinstance[index],function(){
                    let tmp_tableid = item[0]['dataset']['wpdatatable_id'];
                    preventfurtherchecks(idtoinstance[index],max_compare_len[tmp_tableid]);
                  });
                })
                idtoinstance[index] = wpdt_instance_cntr;
                forcompare[wpdt_instance_cntr] = [];
                wpdt_instance_cntr++;
              }
              prev_instance_id = index;
            });
            
            
            
            if(jQuery('.column-settings-overlay').length){
              var target = document.querySelector('.column-settings-overlay');
              observer.observe(target, {
                attributes: true
              });
            }

            
        }

    })(jQuery);

});

/**
 *  Create visibility toggle variable on column settings save
 */
jQuery('#wdt-columns-list-modal #wdt-apply-columns-list').click(function (e) {
  jQuery.ajax({type:'POST',url: wdt_ajax_object.ajaxurl,async: true,data: {'action':'setVisibilitySession'},success:function(response){}});
});

/**
 *  Create visibility toggle variable on save
 */
jQuery('#wdt-column-settings-buttons .wdt-column-apply').click(function (e) {
  jQuery.ajax({type:'POST',url: wdt_ajax_object.ajaxurl,async: true,data: {'action':'setVisibilitySession'},success:function(response){}});
});

/**
 *  Observe opening of column settings modal and set which tabs are visible for compare addon.
 */
 var observer = new MutationObserver(function(mutations) {
    hidecolumnsettingstab();
});

function hidecolumnsettingstab(){
  try {
    if(wpdatatable_config.currentOpenColumn.orig_header != null){
      let curOpen = wpdatatable_config.currentOpenColumn.orig_header;
      if(curOpen == 'Compare'){
        jQuery('li.column-filtering-settings-tab').hide();
        jQuery('li.column-sorting-settings-tab').hide();
        jQuery('li.column-conditional-formatting-settings-tab').hide();
      }
    }
  }catch(err) {
    //document.getElementById("demo").innerHTML = err.message;
  }
}

// Function for inserting compare button
function insertCompareButton(inst, maxcomp){
  
    if(!jQuery('.wpDataTablesWrapper #'+inst+'_filter dataTables_compare_button_wrapper').length){
    var html = '<div class="dataTables_compare_button_wrapper">';
        html += '<a class="compare_button" role="button" aria-label="Please select up to '+maxcomp+' school(s) to compare" title="Compare" tabindex="0" >Compare</a>';
        html += '<a class="clear_compare_button" role="button" aria-label="Clear Compare Data" title="Clear Comparison" tabindex="0">Clear</a>';
        html += '</div>';
    jQuery( html).insertBefore( '.wpDataTablesWrapper #'+inst+'_filter label');

    html = '<div class="dataTables_compare_message" style="display:none;"><span class="dashicons dashicons-warning"></span><span class="cmpr_content"></span></div>';
    if (!jQuery('.dataTables_compare_message').length) {
      jQuery( html).insertAfter( '.wpDataTablesWrapper .dataTables_filter');
    }
    
    jQuery('.wpDataTablesWrapper .wpDataTable').addClass('fixed_headers');
    jQuery('#'+inst+' th.column-compare').attr('tabindex','0');
    
  }
}


/**
* Hide Compare Modal Column Settngs
*/
jQuery("#wdt-columns-list-modal").on('show.bs.modal', function(){
  var tableDescription = getTblDesc();
  if(tableDescription.compareDetail){
    jQuery('div[data-orig_header="Compare"]').show();
  }else{
    jQuery('div[data-orig_header="Compare"]').hide();
  }
});

/**
* Retrieve Table Advanced Settings
*/
function getTblDesc(){
  return jQuery.parseJSON(jQuery('input#table_1_desc').val());
}

wdtNotify = wdtFunctionExtend(wdtNotify,function(){
  synccomparechecks();
});


function clearcomparison(tableid,tblno,maxcomp){
  compare_message(tblno);
  //jQuery('table[data-wpdatatable_id="'+tableid+'"] td input[type="checkbox"]').prop("checked", false);
  jQuery('#wpdt_main_wrapper_'+tblno).find('.wdtResponsiveWrapper table td input[type="checkbox"]').prop("checked", false);
  forcompare[tblno] = [];
  preventfurtherchecks(tblno,maxcomp);
}

function initiateModal(obj,tableDescription,maxcomp){
  let tblno = parseInt(jQuery(obj).closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
  prev_scrollpos = jQuery(window).scrollTop();
  compare_message(tblno);
  if(forcompare[tblno].length > 0){
    jQuery('.wdt-compare-preloader-wrapper').show();
    retrieveCompareData(jQuery(obj),tblno,tableDescription,function(){
      setTimeout(function(){
        adjusmodalcolumnwidth(function(){
          adjustrowheight(function(){
            jQuery('.wdt-compare-preloader-wrapper').hide(300);
            //setTimeout(function(){ adjusmodalcolumnwidth(); }, 100000);
          });
        });
      }, 1000);
    });
    showCompareModal(obj,tblno,tableDescription, function(){                  
    });

    
    
    
  }else{
    compare_message(tblno,'Please select up to '+maxcomp+' school(s).');
  }
}

function deletecolumn(target,tableid,maxcomp,callback){
  var dataid = target.attr('fcmp');
  var colno = target.attr('col');
  let tblno = parseInt(target.closest('.wpdt_main_wrapper').attr('id').replace('wpdt_main_wrapper_',''));
  target.closest('table').find('td.wdtcomparecol-'+colno).hide();
  target.closest('table').find('th.wdtcomparecol-'+colno).hide();
  
  //jQuery(this).parent('.wdt-compare-block-wrapper').remove();
  //jQuery('#table_'+tableid+'_row_'+dataid+' td:first-child input[type="checkbox"]').prop("checked", false);
  //jQuery('#wpdt_main_wrapper_'+tblno).find('#table_'+tableid+'_row_'+dataid+' td input.wdt_compare_checkbox').prop("checked", false);
  jQuery('#wpdt_main_wrapper_'+tblno).find('#table_'+tableid+'_row_'+dataid+' td input.wdt_compare_checkbox').prop("checked", false);
  
  removefrommodecompatelist(dataid, tblno, function(){
    preventfurtherchecks(tblno,maxcomp);
    if(forcompare[tblno].length == 0){
      jQuery('.wdt-compare-modal-body-content').addClass('enola');
    }else{
      jQuery('.wdt-compare-modal-body-content').removeClass('enola');
      jQuery('.wdt-compare-modal-body-content').removeAttr('style');
    }
    //adjusmodalcolumnwidth();
  });
  if (callback && typeof(callback) === "function") {
      callback();
  }
}
function wdtFunctionExtend(func, callback) {
    return function () {
        callback.apply();
        return func.apply(this, arguments);
    }
}


// Show Compare modal for all tables
function showCompareModal(obj,tblno,tableDescription,callback) {
    var modal = jQuery('#wpdt_main_wrapper_'+tblno).find('#wdt-cd-modal');
    var modalTitle = tableDescription.compareDetailPopupTitle !== '' ? tableDescription.compareDetailPopupTitle : wdtMdTranslationStrings.modalTitle;

    if (jQuery(obj).hasClass('disabled'))
        return false;

    if (tableDescription.editable && tableDescription.popoverTools) {
        $('.wpDataTablesPopover.editTools').hide();
    }

    modal.find('.modal-title').html(modalTitle);
    //modal.find('.wdt-compare-modal-body-content').html('');
    modal.find('.modal-footer').html('');
    var rowData;

    if ((tableDescription.compareDetailLogic === 'button' || tableDescription.compareDetailLogic === 'row') && jQuery(obj).parents('.columnValue').length) {
        rowData = jQuery(obj).closest('tr').prevAll('.detail-show');
    } else if (tableDescription.editable && tableDescription.compareDetailLogic === 'row') {
        rowData = jQuery(tableDescription.selector + ' tr.selected');
    } else if (tableDescription.compareDetailLogic === 'button') {
        rowData = jQuery(obj).closest('tr');
    } else {
        rowData = jQuery(obj);
    }

    var row = rowData.get(0);

    var data = wpDataTables[tableDescription.tableId].fnGetData(row);

    jQuery(data).each(function (index, el) {
        var $columnValue = jQuery('#' + tableDescription.tableId + '_md_dialog .detailColumn:eq(' + index + ')');
        if (el) {
            var val = el.toString();
        } else {
            var val = '';
        }
        $columnValue.html(val);
    });
    jQuery('#wpdt_main_wrapper_'+tblno).find('#wdt-cd-modal .modal-dialog').addClass('compare');
    //modal.find('.wdt-compare-modal-body-content').append($(tableDescription.selector + '_md_dialog').show());
    modal.modal('show');
    
    if (callback && typeof(callback) === "function") {
        callback();
    }
    
}

function handleprefixsuffix(tblno,callback){
  jQuery('#wpdt_main_wrapper_'+tblno).find('.wpDataTablesWrapper table.wpDataTable >tbody >tr >td:first-child').each(function() {
    var id= jQuery(this).parent('tr').attr('ID');
    var pfx = window.getComputedStyle(this, ':before').content;
    pfx = (pfx != 'none')?pfx:'';
    pfx = pfx.replace(/['"]+/g, '');
    var sfx = window.getComputedStyle(this, ':after').content;
    sfx = (sfx != 'none')?sfx:'';
    sfx = sfx.replace(/['"]+/g, '');
    var target = jQuery("#"+id+' tr td.scnd:not(.pfxd)');
    target.prepend(pfx);
    target.append(sfx);
    target.addClass('pfxd');
    jQuery("#"+id+' td:first-child').addClass('pfxgone');
  });
  if (callback && typeof(callback) === "function") {
      callback();
  }
}

function synccomparechecks(tblno, callback){
  
  jQuery('#wpdt_main_wrapper_'+tblno).find('table.wpDataTable tr td input.wdt_compare_checkbox').each(function (i, obj) {
    var rowid = getrowindex(jQuery(this));
    var idxof = forcompare[tblno].indexOf(parseInt(rowid))
    if(idxof == -1){
      jQuery(this).prop('checked', false);
    }else{
      jQuery(this).prop('checked', true);
    }
  });
  if (callback && typeof(callback) === "function") {
      callback();
  }
}

function compare_message(tblno, msg){
  if(msg_timer[tblno]){
  clearTimeout(msg_timer[tblno]);
}
  if(msg !== undefined){
    jQuery('#wpdt_main_wrapper_'+tblno).find('.wpDataTablesWrapper .dataTables_compare_message span.cmpr_content').html(msg);
    jQuery('#wpdt_main_wrapper_'+tblno).find('.wpDataTablesWrapper .dataTables_compare_message').show(300);
  }else{
    jQuery('#wpdt_main_wrapper_'+tblno).find('.wpDataTablesWrapper .dataTables_compare_message').hide(300);
  }

  msg_timer[tblno] = setTimeout(function(){
    compare_message(tblno);
  },5000);
}

function addtomodcomparelist(target,dataid,tblno,maxcomp,callback){
  
    if(target.prop("checked")){
      forcompare[tblno].push(dataid);
    }else{
      removefrommodecompatelist(dataid,tblno,function(){
        if(forcompare[tblno].length > (maxcomp - 1)){
          compare_message(tblno,'Maximum of '+maxcomp+' school(s) can be compared.');
        }
      });
      target.addClass('checked');
    }
    if (callback && typeof(callback) === "function") {
        callback();
    }
}

function preventfurtherchecks(tblno,maxcomp){
  if(forcompare[tblno].length > (maxcomp - 1)){
    jQuery('#wpdt_main_wrapper_'+tblno).find('table.wpDataTable tr td:first-child input[type="checkbox"]').each(function (i, obj) {
      if(jQuery(this).prop("checked")){
        jQuery(this).prop('disabled', false);
      }else{
        jQuery(this).prop('disabled', true);
      }
    });
  }else{
    jQuery('#wpdt_main_wrapper_'+tblno).find('table.wpDataTable tr td input[type="checkbox"]').each(function (i, obj) {
        jQuery(this).prop('disabled', false);
    });
  }
  
  if(forcompare[tblno].length == 0){
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .clear_compare_button').removeClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').removeClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Please select up to '+maxcomp+' school(s) to compare');
  }else if(forcompare[tblno].length == 1){
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .clear_compare_button').addClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').removeClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Compare');
  }else if(forcompare[tblno].length > 1) {
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .clear_compare_button').addClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').addClass('selected');
    jQuery('#wpdt_main_wrapper_'+tblno).find('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Compare');
  }

}

function removefrommodecompatelist(dataid, tblno, callback){
  const idx = forcompare[tblno].indexOf(parseInt(dataid));
  if (idx > -1) {
    forcompare[tblno].splice(idx, 1);
  }
  if (callback && typeof(callback) === "function") {
      callback();
  }
}

function gettableid(obj){
  return obj.closest('table.wpDataTable').attr('data-wpdatatable_id');
}

function getrowindex(obj){
  var str = obj.closest('tr').attr('id');
  var n = str.lastIndexOf('_');
  return str.substring(n + 1);
}


function retrieveCompareData(obj,tblno,tableDescription,callback){
    var tableid = jQuery('#wpdt_main_wrapper_'+tblno).find('table.wpDataTable').attr('data-wpdatatable_id');
    globalresponse = 0;
    var table_name = (tableDescription.compareDetailPopupTitle == "")? "Compare Details": tableDescription.compareDetailPopupTitle;
    jQuery.ajax({
				type:'POST',
				url: wdt_ajax_object.ajaxurl,
        async: true,
				data: {'action':'extendTableObjectCompareAjax','table_id': tableid},
				success:function(response){
					globalresponse = JSON.parse(response);
          var alldata = [];

          var chtml = '<table arial-label="'+table_name+'">';
          chtml += '<tr>';
          chtml += '<td class="wdtcomparerow wdtcomparerow-0 wdtcomparecol-0 hdr" tabindex="-1" hdr></td>';
          for (var w = 0; w < forcompare[tblno].length; w++) {
              colno = w + 1;
              var fcmp = forcompare[tblno][w];
              chtml += '<td class="wdtcomparerow wdtcomparerow-0 wdtcomparecol-'+colno+' dtl" tabindex="-1">';
                chtml += '<a class="wdt-remove-column" role="button" tabindex="0" aria-label="Remove column '+colno+' from comparison" fcmp="'+fcmp+'" col="'+colno+'">';
                  chtml += '<span class="dashicons dashicons-dismiss" tabindex="-1"></span>';
                chtml += '</a>';
              chtml += '</td>';
          }
          chtml += '</tr>';


          //column
          var colrw = 1;
          colArrayLength = globalresponse['column'].length;
          for (var x = 0; x < colArrayLength; x++) {

              var vis = globalresponse['column'][x]['compareDetailColumnOption'];


              if(vis > 0){
                chtml += '<tr>';
                var left_header = globalresponse['column'][x]['orig_header'];

                if(left_header != 'Compare'){
                  
                  var left_display_header = globalresponse['column'][x]['display_header'];
                  chtml += '<th class="wdtcomparerow wdtcomparerow-'+colrw+' wdtcomparecol-0 hdr" scope="row" >';
                  chtml += '<span>';
                  chtml += left_display_header;
                  chtml += '</span>';
                  chtml += '</th>';

                  var forcomparelength = forcompare[tblno].length;
                  var colno = 1;
                  for (var y = 0; y < forcomparelength; y++) {
                    var fcmp = forcompare[tblno][y];


                    for (var q = 0; q < colArrayLength; q++) {
                      var vis = globalresponse['column'][q]['compareDetailColumnOption'];
                      var dtp = globalresponse['column'][q]['type'];
                      var tgt = globalresponse['column'][q]['linkTargetAttribute']
                      var btn = globalresponse['column'][q]['linkButtonAttribute']
                      var pfx = globalresponse['column'][q]['text_before'];
                      var sfx = globalresponse['column'][q]['text_after'];
                      var dec = globalresponse['column'][q]['decimalPlaces'];
                      var lnklabel = globalresponse['column'][q]['linkButtonLabel'];
                      lnklabel = (lnklabel.trim() == '')? 'View':lnklabel;
                      if(vis > 0){
                        var col = globalresponse['column'][q]['orig_header'];
                        var dsp = globalresponse['column'][q]['display_header'];
                        if(left_header == col){

                          var dta = globalresponse['data'][fcmp][col];
                          if(dta !== null){                  
                            if(dtp == 'float'){
                              if(!isNaN(dta)){
                                dta = (dec)? thousands_separators(addZeroes(parseFloat(dta).toFixed(2))) : thousands_separators(parseFloat(dta));
                                dta = pfx+dta+sfx;
                              }else{
                                dta = '';
                              }
                            }          
                          }else{
                            dta = '';
                          }

                          if(colrw == 1){
                            chtml += '<th class="wdtcomparerow wdtcomparerow-'+colrw+' wdtcomparecol-'+colno+' dtl"  scope="col">';
                            chtml += '<span>';
                            chtml += dta;
                            chtml += '</span>';
                            //chtml += '<div class="wdt-remove-column" tabindex="0" role="button" aria-label="Remove column '+colno+': '+dsp+' '+dta+' from comparison" fcmp="'+fcmp+'" col="'+colno+'"><span class="dashicons dashicons-dismiss"></span><div class="wdt-compare-tooltip"><span class="wdt-compare-tooltiptext">Remove</span></div></div>';
                            chtml += '</th>';
                          }else{
                            chtml += '<td class="wdtcomparerow wdtcomparerow-'+colrw+' wdtcomparecol-'+colno+' dtl" >';
                            chtml += '<span>';
                            if(dtp == 'link'){
                                if(btn){
                                  chtml += '<a href="'+dta+'" typ="btn" target="'+tgt+'"><button class="">'+lnklabel+'</button></a>';
                                }else{
                                  var inc = dta.includes('||', 0);
                                  var lbl = (inc)? dta.split('||')[1]: dta;
                                  chtml += '<a href="'+dta+'" typ="url" target="'+tgt+'">'+lbl+'</a>';
                                }
                            }else{
                                chtml += dta;
                            }
                            chtml += '</span>';
                            chtml += '</td>';
                          }

                          colno++;
                        }
                      }
                    }

                  }
                  chtml += '</tr>';
                  colrw++;
                } //if(left_header != 'Compare'){


              }

          }
          chtml += '</table>';
          
          
          
          jQuery('#wpdt_main_wrapper_'+tblno).find('#wdt-cd-modal .wdt-compare-modal-body-content').html(chtml).show('slow', function(){
            active_tblno = tblno;
            if (callback && typeof(callback) === "function") {
                callback();
            }
          });
          
          
          
            

				},
				error: function(xhr, textStatus, errorThrown) {
           var errorMessage = xhr.status + ': ' + xhr.statusText
				   alert(errorMessage);
				}
		});


}


function isOdd(num) { return num % 2;}
/*
function adjusmodalcolumnwidth(callback){
  var forcomparelength = forcompare.length;
  var colwidth = 100/(parseInt(forcomparelength) + 1);
  jQuery('.wdtcomparerow.hdr').css('width',colwidth+'%');
  if (callback && typeof(callback) === "function") {
      callback();
  }
}
*/

function adjusmodalcolumnwidth(callback){
  var tblno = active_tblno;
  var forcomparelength = forcompare[tblno].length;
  var parentwrap = jQuery('#wpdt_main_wrapper_'+tblno);
  if(forcomparelength < 4 ){
    var hdrwidth = parentwrap.find('.wdtcomparerow.hdr').outerWidth();
    var containerwidth = parentwrap.find('.wdt-compare-modal-body-content').outerWidth();
    var winwidth = parentwrap.find(window).width();
    var divisor = forcomparelength + 1;
    if(winwidth < 1024){divisor =  breakpoints[2]}
    var showpartial =  containerwidth * show_partial_percentage;
    var colwidth =  (containerwidth - showpartial) / divisor;
    parentwrap.find('.wdtcomparerow.hdr').css('width',colwidth+'px');
    parentwrap.find('.wdtcomparerow.dtl').css('width',colwidth+'px');
    
    if(forcomparelength > 1){
      //parentwrap.find('.wdt-compare-modal-body-content').css('padding-left',colwidth+'px');
      parentwrap.find('.wdt-compare-modal-body-content').removeClass('enola');
    }else{
      parentwrap.find('.wdt-compare-modal-body-content').addClass('enola');
    }
  }else{
    var hdrwidth = parentwrap.find('.wdtcomparerow.hdr').outerWidth();
    var containerwidth = parentwrap.find('.wdt-compare-modal-body-content').outerWidth();
    var winwidth = jQuery(window).width();
    var divisor = breakpoints[0];
    if(winwidth < 1024){divisor =  breakpoints[1]}
    if(winwidth < 841){divisor =  breakpoints[2]}
    var showpartial =  (containerwidth / divisor) * show_partial_percentage;
    var colwidth =  (containerwidth - showpartial) / divisor;
    parentwrap.find('.wdtcomparerow.hdr').css('width',colwidth+'px');
    parentwrap.find('.wdtcomparerow.dtl').css('width',colwidth+'px');
    //parentwrap.find('.wdt-compare-modal-body-content').css('padding-left',colwidth+'px');
  }
  
  /*parentwrap.find('.wdt-compare-modal-body-content').scrollLeft(showpartial);*/
  
  if (callback && typeof(callback) === "function") {
      callback();
  }
}





function adjustrowheight(callback){
  var tblno = active_tblno;
  var parentwrap = jQuery('#wpdt_main_wrapper_'+tblno);
  parentwrap.find(".wdtcomparerow").css('height','auto');
  parentwrap.find(".wdt-compare-modal-body-content table tr").each(function(i, l_row){
    var maxheight = 0;
    parentwrap.find(".wdtcomparerow-"+i).each(function(j, l_col){
      var curheight = jQuery(l_col).height();
      maxheight = (curheight > maxheight)? curheight: maxheight;
      parentwrap.find(".wdtcomparerow-"+i).height(maxheight);
    });
  });
  
  if (callback && typeof(callback) === "function") {
      callback();
  }

}


var a;
jQuery(window).resize(function(){
  if(jQuery('#wpdt_main_wrapper_'+active_tblno).find('#wdt-cd-modal').is(":visible")){
    clearTimeout(a);
    a = setTimeout(function(){
      adjusmodalcolumnwidth(function(){
        adjustrowheight();
      });
    },300);
  }
});


function thousands_separators(num){
  var num_parts = num.toString().split(".");
  num_parts[0] = num_parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return num_parts.join(".");
}
function addZeroes(num) {
  const dec = num.split('.')[1]
  const len = dec && dec.length > 2 ? dec.length : 2
  return Number(num).toFixed(len)
}