<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php $wdtFontColorSettings = get_option('wdtFontColorSettings'); ?>
<style>
  .dataTables_compare_button_wrapper a.compare_button.selected,
  .dataTables_compare_button_wrapper a.clear_compare_button.selected {
    
    background-color: <?php echo $wdtFontColorSettings['wdtHeaderBaseColor'] ?> !important;
    color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
    
  }
</style>