var forcompare = [];
var globalresponse = 0;
var msg_timer;

wpDataTablesHooks.onRenderDetails.push(function showDetailModalCompare(tableDescription) {
    (function ($) {
        if (tableDescription.compareDetail) { // compare enabled

           /**
           * Insert Compare and Clear Buttons
           */
            var tableid = gettableid(jQuery('.wpDataTable.dataTable'));
            var thebody = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >tbody');
            var theheadtr = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >thead tr');
            var firstheader = tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >thead>tr>th:first-child';
            var firstcolumn = tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' >tbody>tr>td:first-child';

            jQuery('.master_detail_column_btn').attr('role','button');
            thebody.attr('logic',tableDescription.masterDetailLogic);

            var checkExist = setInterval(function() {
               if (jQuery('table.wpDataTable.dataTable').length) {
                  clearInterval(checkExist);
                  insertCompareButton();
               }
            }, 100); // check every 100ms

            var thebodytr = jQuery(tableDescription.selector + '_wrapper table#' + tableDescription.tableId + ' tbody tr td:nth-of-type(1)');
            function insertCompareButton(){
              var html = '<div class="dataTables_compare_button_wrapper">';
                  html += '<a class="compare_button" role="button" aria-label="Please select up to 3 schools to compare" title="Compare" tabindex="0">Compare</a>';
                  html += '<a class="clear_compare_button" role="button" aria-label="Clear Compare Data" title="Clear Comparison" tabindex="0">Clear</a>';
                  html += '</div>';
              jQuery( html).insertBefore( '.wpDataTablesWrapper .dataTables_filter label');

              html = '<div class="dataTables_compare_message" style="display:none;"><span class="dashicons dashicons-warning"></span><span class="cmpr_content"></span></div>';
              jQuery( html).insertAfter( '.wpDataTablesWrapper .dataTables_filter');
            }


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
                jQuery('#wdt-md-modal .modal-dialog').removeClass('compare');
                modal.find('.modal-body').append($(tableDescription.selector + '_md_dialog').show());
                modal.modal('show');
            }

            
            /**
            * Handle compare checkbox click
            */      
            jQuery(document).on('click','table.wpDataTable tbody tr td input.wdt_compare_checkbox',function(e){
              if(forcompare.length > 2){
                jQuery(this).prop("checked", false);
              }

              var rowindex = parseInt(getrowindex(jQuery(this)));
              addtomodcomparelist(jQuery(this), rowindex, function(){
                preventfurtherchecks();
              });
              e.stopImmediatePropagation();
            })

            /**
            * Handle enter key event on compare checkboxes
            */
            jQuery(document).on('keyup','table.wpDataTable tbody tr td input.wdt_compare_checkbox', function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){

                if(forcompare.length > 2){
                  jQuery(this).prop("checked", false);
                }else{
                  if(jQuery(this).is(":checked")){
                    jQuery(this).prop("checked", false);
                    preventfurtherchecks();
                  }else{
                    jQuery(this).prop("checked", true);
                    preventfurtherchecks();
                  }
                }

                var rowindex = parseInt(getrowindex(jQuery(this)));
                addtomodcomparelist(jQuery(this), rowindex, function(){
                  preventfurtherchecks();
                });
                e.stopImmediatePropagation();

              }
            })

            /**
            * Display Compare Modal on Compare button click
            */
            jQuery(document).on('click','.dataTables_compare_button_wrapper a.compare_button',function(e){
              initiateModal(this,tableDescription);
              e.stopImmediatePropagation();
            })
            
            /**
            * Display Compare Modal on enter key press
            */
            jQuery(document).on('keyup','.dataTables_compare_button_wrapper a.compare_button',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                initiateModal(this,tableDescription);
                e.stopImmediatePropagation();
              }
            })
            
            /**
            * Clear compare selections on Clear Compare Button click
            */
            jQuery(document).on('click','.dataTables_compare_button_wrapper a.clear_compare_button',function(e){
              clearcomparison(tableid);
              e.stopImmediatePropagation();
            })
            
            /**
            * Clear compare selections on Clear Compare Button enter key press
            */
            jQuery(document).on('keyup','.dataTables_compare_button_wrapper a.clear_compare_button',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                clearcomparison(tableid);
              }
              e.stopImmediatePropagation();
            })

            /**
            * Remove column in compare modal throgh x button click
            */
            jQuery(document).on('click','.wdt-remove-column',function(e){
              deletecolumn(jQuery(this),tableid,function(){
                jQuery('.wdt-cd-modal').focus();
                //setCompareTableWidth();
              });
            })
            
            /**
            * Remove column in compare modal throgh x button enter key press
            */
            jQuery(document).on('keyup','.wdt-remove-column',function(e){
              var keyCode = (e.keyCode ? e.keyCode : e.which);
              if(keyCode == 13){
                deletecolumn(jQuery(this),tableid,function(){
                  jQuery('#wdt-cd-modal').focus();
                  //setCompareTableWidth();
                });
              }
            })

            /**
            * Set focus back to last active element before opening the  compare modal.
            */
            jQuery('#wdt-cd-modal').on('hidden.bs.modal', function () {
              jQuery('.wdt-compare-preloader-wrapper').hide(300);
              jQuery('.dataTables_compare_button_wrapper .compare_button').focus();
            });

            /**
            * Make sure previously selected rows are still checked when navigating through pagination.
            */
            wpDataTables.table_1.addOnDrawCallback(
            function(){
              synccomparechecks(function(){
                preventfurtherchecks();
              });
            })

            /**
            * Clear Modal contents and set modal aria-hidden attribute to true on close.
            */
            jQuery("#wdt-cd-modal").on('hide.bs.modal', function(){
              jQuery('#wdt-cd-modal').find('.wdt-compare-modal-body-content').html('');
              jQuery('#wdt-cd-modal').attr('aria-hidden','true');
            });
            
            /**
            * Set aria-hidden attribute to false on Modal open.
            */
            jQuery("#wdt-cd-modal").on('show.bs.modal', function(){
              jQuery('#wdt-cd-modal').attr('aria-hidden','false');
            });
            
            jQuery("#wdt-columns-list-modal").on('show.bs.modal', function(){
              jQuery('div[data-orig_header="Compare"]').show();
            });

        }else{ // compare disabled
          jQuery("#wdt-columns-list-modal").on('show.bs.modal', function(){
            jQuery('div[data-orig_header="Compare"]').hide();
          });
        }

    })(jQuery);



});

wdtNotify = wdtFunctionExtend(wdtNotify,function(){
  synccomparechecks();
});

function clearcomparison(tableid){
  compare_message();
  jQuery('table[data-wpdatatable_id="'+tableid+'"] td input[type="checkbox"]').prop("checked", false);
  forcompare = [];
  preventfurtherchecks();
}

function initiateModal(obj,tableDescription){
  compare_message();
  if(forcompare.length > 0){
    jQuery('.wdt-compare-preloader-wrapper').show();
    retrieveCompareData(jQuery(obj),tableDescription);
    showCompareModal(obj, tableDescription);
  }else{
    compare_message('Please select up to 3 schools.');
  }
}

function deletecolumn(target,tableid,callback){
  var dataid = target.attr('fcmp');
  var colno = target.attr('col');

  target.closest('table').find('td.wdtcomparecol-'+colno).hide();
  target.closest('table').find('th.wdtcomparecol-'+colno).hide();

  //jQuery(this).parent('.wdt-compare-block-wrapper').remove();
  //jQuery('#table_'+tableid+'_row_'+dataid+' td:first-child input[type="checkbox"]').prop("checked", false);
  jQuery('#table_'+tableid+'_row_'+dataid+' td input.wdt_compare_checkbox').prop("checked", false);
  removefrommodecompatelist(dataid, function(){
    preventfurtherchecks();
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
function showCompareModal(obj, tableDescription) {
    var modal = jQuery('#wdt-cd-modal');
    var modalTitle = tableDescription.compareDetailPopupTitle !== '' ? tableDescription.compareDetailPopupTitle : wdtMdTranslationStrings.modalTitle;

    if (jQuery(obj).hasClass('disabled'))
        return false;

    if (tableDescription.editable && tableDescription.popoverTools) {
        $('.wpDataTablesPopover.editTools').hide();
    }

    modal.find('.modal-title').html(modalTitle);
    modal.find('.wdt-compare-modal-body-content').html('');
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
    jQuery('#wdt-cd-modal .modal-dialog').addClass('compare');
    //modal.find('.wdt-compare-modal-body-content').append($(tableDescription.selector + '_md_dialog').show());
    modal.modal('show');
}

function handleprefixsuffix(callback){
  jQuery('.wpDataTablesWrapper table.wpDataTable >tbody >tr >td:first-child').each(function() {
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

function synccomparechecks(callback){
  jQuery('table.wpDataTable tr td input.wdt_compare_checkbox').each(function (i, obj) {
    var rowid = getrowindex(jQuery(this));
    var idxof = forcompare.indexOf(parseInt(rowid))
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

function compare_message( msg){
  clearTimeout(msg_timer);

  if(msg !== undefined){
    jQuery('.wpDataTablesWrapper .dataTables_compare_message span.cmpr_content').html(msg);
    jQuery('.wpDataTablesWrapper .dataTables_compare_message').show(300);
  }else{
    jQuery('.wpDataTablesWrapper .dataTables_compare_message').hide(300);
  }

  msg_timer = setTimeout(function(){
    compare_message();
  },10000);
}

function addtomodcomparelist(target,dataid,callback){

    if(target.prop("checked")){
      forcompare.push(dataid);
    }else{
      removefrommodecompatelist(dataid,function(){
        if(forcompare.length > 2){
          compare_message('Maximum of 3 schools can be compared.');
        }
      });
      target.addClass('checked');
    }
    if (callback && typeof(callback) === "function") {
        callback();
    }
}

function preventfurtherchecks(){

  if(forcompare.length > 2){
    jQuery('table.wpDataTable tr td:first-child input[type="checkbox"]').each(function (i, obj) {
      if(jQuery(this).prop("checked")){
        jQuery(this).prop('disabled', false);
      }else{
        jQuery(this).prop('disabled', true);
      }
    });
  }else{

    if(forcompare.length == 0){
      jQuery('.dataTables_compare_button_wrapper .clear_compare_button').removeClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').removeClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Please select up to 3 schools to compare');
    }else if(forcompare.length == 1){
      jQuery('.dataTables_compare_button_wrapper .clear_compare_button').addClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').removeClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Compare');
    }else if(forcompare.length > 1) {
      jQuery('.dataTables_compare_button_wrapper .clear_compare_button').addClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').addClass('selected');
      jQuery('.dataTables_compare_button_wrapper .compare_button').attr('aria-label','Compare');
    }


    jQuery('table.wpDataTable tr td input[type="checkbox"]').each(function (i, obj) {
        jQuery(this).prop('disabled', false);
    });

  }


}

function removefrommodecompatelist(dataid, callback){
  const idx = forcompare.indexOf(parseInt(dataid));
  if (idx > -1) {
    forcompare.splice(idx, 1);
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


function retrieveCompareData(obj,tableDescription){
    var tableid = obj.closest('.wpDataTablesWrapper').find('table.wpDataTable').attr('data-wpdatatable_id');
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
          chtml += '<td class="wdtcomparerow wdtcomparecol-0 hdr" tabindex="-1"></td>';
          for (var w = 0; w < forcompare.length; w++) {
              colno = w + 1;
              var fcmp = forcompare[w];
              chtml += '<td class="wdtcomparerow wdtcomparecol-'+colno+' hdr" tabindex="-1">';
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
                  chtml += '<th class="wdtcomparerow wdtcomparerow-'+0+'" scope="row" >';
                  chtml += '<span>';
                  chtml += left_display_header;
                  chtml += '</span>';
                  chtml += '</th>';

                  var forcomparelength = forcompare.length;
                  var colno = 1;
                  for (var y = 0; y < forcomparelength; y++) {
                    var fcmp = forcompare[y];


                    for (var q = 0; q < colArrayLength; q++) {
                      var vis = globalresponse['column'][q]['compareDetailColumnOption'];
                      var dtp = globalresponse['column'][q]['type'];
                      var tgt = globalresponse['column'][q]['linkTargetAttribute']
                      var btn = globalresponse['column'][q]['linkButtonAttribute']
                      var pfx = globalresponse['column'][q]['text_before'];
                      var sfx = globalresponse['column'][q]['text_after'];
                      var dec = globalresponse['column'][q]['decimalPlaces'];
                      var lnklabel = globalresponse['column'][q]['linkButtonLabel'];

                      if(vis > 0){
                        var col = globalresponse['column'][q]['orig_header'];
                        var dsp = globalresponse['column'][q]['display_header'];
                        if(left_header == col){

                          var dta = globalresponse['data'][fcmp][col];
                          if(dta !== null){
                            if(dtp == 'float'){

                              if(dec){
                                dta = thousands_separators(addZeroes(parseFloat(dta).toFixed(2)));
                              }else{
                                dta = thousands_separators(parseFloat(dta));
                              }
                            }
                            dta = pfx+dta+sfx;
                          }else{
                            dta = '';
                          }

                          if(colrw == 1){
                            chtml += '<th class="wdtcomparerow wdtcomparerow-'+colrw+' wdtcomparecol-'+colno+'"  scope="col">';
                            chtml += '<span>';
                            chtml += dta;
                            chtml += '</span>';
                            //chtml += '<div class="wdt-remove-column" tabindex="0" role="button" aria-label="Remove column '+colno+': '+dsp+' '+dta+' from comparison" fcmp="'+fcmp+'" col="'+colno+'"><span class="dashicons dashicons-dismiss"></span><div class="wdt-compare-tooltip"><span class="wdt-compare-tooltiptext">Remove</span></div></div>';
                            chtml += '</th>';
                          }else{
                            chtml += '<td class="wdtcomparerow wdtcomparerow-'+colrw+' wdtcomparecol-'+colno+'" >';
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

          jQuery('#wdt-cd-modal').find('.wdt-compare-modal-body-content').append(chtml).show('slow')
          jQuery('.wdt-compare-preloader-wrapper').hide(300);


				},
				error: function(xhr, textStatus, errorThrown) {
           var errorMessage = xhr.status + ': ' + xhr.statusText
				   alert(errorMessage);
				}
		});


}

/*
function retrieveCompareData(obj,tableDescription){
    var tableid = obj.closest('.wpDataTablesWrapper').children('table.wpDataTable').attr('data-wpdatatable_id');
    globalresponse = 0;
    jQuery.ajax({
				type:'POST',
				url: wdt_ajax_compare.ajaxurl,
        async: true,
				data: {'action':'extendTableObjectCompareAjax','table_id': tableid},
				success:function(response){
					globalresponse = JSON.parse(response);
          var alldata = [];

          //column
          var cdcolhtml = '';
          var colrw = 1;
          colArrayLength = globalresponse['column'].length;
          for (var x = 0; x < colArrayLength; x++) {
              var vis = globalresponse['column'][x]['compareDetailColumnOption']
              if(vis > 0){
                var col = globalresponse['column'][x]['display_header'];
                var cls = (isOdd(colrw))?'odd':'even';
                cdcolhtml += '<tr><td class="wdtcomparerow wdtcomparerow-'+colrw+' '+cls+'"><strong>'+col+'</strong></td></tr>';
                colrw++;
              }

          }

          alldata[0] = '<div class="wdt-compare-block column"><table>'+cdcolhtml+'</table></div>';

          //Data
          var cddetailhtml = []; cnt = 0;
          var forcomparelength = forcompare.length;
          for (var y = 0; y < forcomparelength; y++) {
              var fcmp = forcompare[y];
              var tmpHtml = '';

              var datarw = 1;
              for (var q = 0; q < colArrayLength; q++) {
                  var vis = globalresponse['column'][q]['compareDetailColumnOption'];
                  var pfx = globalresponse['column'][q]['text_before'];

                  var sfx = globalresponse['column'][q]['text_after'];
                  if(vis > 0){
                    var col = globalresponse['column'][q]['orig_header'];
                    var dta = (globalresponse['data'][fcmp][col] === null)? '': globalresponse['data'][fcmp][col];
                    var cls = (isOdd(datarw))?'odd':'even';
                    tmpHtml += '<tr><td class="wdtcomparerow wdtcomparerow-'+datarw+' '+cls+'">'+pfx+dta+sfx+'</td></tr>';
                    datarw++;
                  }
              }


              cddetailhtml[cnt]  = '<div class="wdt-compare-block data"><table>'+tmpHtml+'</table></div>';
              cddetailhtml[cnt] += '<div class="wdt-remove-column" fcmp="'+fcmp+'">';
                cddetailhtml[cnt] += '<span class="dashicons dashicons-dismiss"></span>';
                cddetailhtml[cnt] += '<div class="wdt-compare-tooltip">';
                  cddetailhtml[cnt] += '<span class="wdt-compare-tooltiptext">Remove</span>';
                cddetailhtml[cnt] += '</div>';
              cddetailhtml[cnt] += '</div>';



              cnt++;
          }


          alldata[1] = cddetailhtml;

          displayComparisonData(alldata,tableDescription, function(){
            synccomparechecks();
            setTimeout(function(){
              adjustrowheight();
              jQuery('.wdt-compare-preloader-wrapper').hide(300);
            }, 500);

          });


				},
				error: function(xhr, textStatus, errorThrown) {
           var errorMessage = xhr.status + ': ' + xhr.statusText
				   alert(errorMessage);
				}
		});


}
*/


function isOdd(num) { return num % 2;}

function adjusmodalcolumnwidth(){
  var forcomparelength = forcompare.length;
  var colwidth = 100/(parseInt(forcomparelength) + 1);
  jQuery('.wdtcomparerow.hdr').css('width',colwidth+'%');
}

function adjustrowheight(callback){

  if(typeof globalresponse['column'] !== 'undefined' && forcompare.length > 0){

    jQuery('.wdt-compare-block table tr td.wdtcomparerow').css({'height':''});
    var colArrayLength = globalresponse['column'].length;
    var rowcnt = 1;
    for (var e = 0; e < colArrayLength; e++) {
      var vis = globalresponse['column'][e]['compareDetailColumnOption']
      if(vis > 0){
        //console.log(".wdtcomparerow-"+rowcnt);
        var maxheight = 0;
        jQuery(".wdtcomparerow-"+rowcnt).each(function(){
            //console.log('rowcnt-'+rowcnt);
            var curheight = jQuery(this).height();
            maxheight = (curheight > maxheight)? curheight: maxheight;
            //console.log(rowcnt+'--'+curheight);
            jQuery(".wdtcomparerow-"+rowcnt).height(maxheight);
        });
        rowcnt++;
        //console.log('MAX:'+maxheight)
        //console.log('-------------------');
      }
    }

    var forcomparelength = forcompare.length;
    //console.log(forcomparelength);
    var colwidth = 100/(parseInt(forcomparelength) + 1);
    jQuery('.wdt-compare-block-wrapper').css('width',colwidth+'%');

  }else{
    jQuery('.wdt-compare-block-wrapper').css('width','100%');
  }

  if (callback && typeof(callback) === "function") {
      callback();
  }

}

/*
function displayComparisonData(alldata,tableDescription,callback){
  var modal = jQuery('#wdt-cd-modal');
  var columnhtml = '<div class="wdt-compare-block-wrapper">'+alldata[0]+'</div>';
  for (var i = 0; i < alldata[1].length; i++) {
      columnhtml += '<div class="wdt-compare-block-wrapper">'+alldata[1][i]+'</div>';
  }
  modal.find('.wdt-compare-modal-body-content').append(columnhtml).show('slow',function(){
    if (callback && typeof(callback) === "function") {
        callback();
    }
  });

}
*/




var a;
jQuery(window).resize(function(){
  clearTimeout(a);
  a = setTimeout(function(){
    adjustrowheight();
  },300);
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

