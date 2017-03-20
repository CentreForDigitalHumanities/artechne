<?php

/**
 * @file
 * Main view template.
 *
 * Variables available:
 * - $classes_array: An array of classes determined in
 *   template_preprocess_views_view(). Default classes are:
 *     .view
 *     .view-[css_name]
 *     .view-id-[view_name]
 *     .view-display-id-[display_name]
 *     .view-dom-id-[dom_id]
 * - $classes: A string version of $classes_array for use in the class attribute
 * - $css_name: A css-safe version of the view name.
 * - $css_class: The user-specified classes names, if any
 * - $header: The view header
 * - $footer: The view footer
 * - $rows: The results of the view query, if any
 * - $empty: The empty text to display if the view is empty
 * - $pager: The pager next/prev links to display, if any
 * - $exposed: Exposed widget form/info to display
 * - $feed_icon: Feed icon to display, if any
 * - $more: A link to view more, if any
 *
 * @ingroup views_templates
 */
?>
<div class="<?php print $classes; ?>">
  <?php print render($title_prefix); ?>
  <?php if ($title): ?>
    <?php print $title; ?>
  <?php endif; ?>
  <?php print render($title_suffix); ?>
  <?php if ($header): ?>
    <div class="view-header">
      <?php print $header; ?>
    </div>
  <?php endif; ?>

  <?php if ($exposed): ?>
    <div class="view-filters">
      <?php print $exposed; ?>
    </div>
  <?php endif; ?>

  <?php if ($attachment_before): ?>
    <div class="attachment attachment-before">
      <?php print $attachment_before; ?>
    </div>
  <?php endif; ?>

  <?php if ($rows): ?>
    <div align=center>
      <div id="mapContainerDiv" style="position:relative;"></div>
      <div id="plotContainerDiv" style="position:relative;margin-top:20px;"></div>
      <div id="tableContainerDiv" style="position:relative;margin-top:20px;"></div>
    </div>
    <link rel="stylesheet" href="sites/all/libraries/GeoTemCo/css/geotemco.css" type="text/css" />
    <script src="sites/all/libraries/GeoTemCo/geotemco.js"></script>
    <script>
      var datasets = [];
      var mapDiv = document.getElementById("mapContainerDiv");
      var map = new WidgetWrapper();
      var mapWidget = new MapWidget(map,mapDiv,{
        mapTitle: "Library location"
      });
      var timeDiv = document.getElementById("plotContainerDiv");
      var time = new WidgetWrapper();
      var timeWidget = new TimeWidget(time,timeDiv,{
        timeTitle: "Date"
      });
      var tableDiv = document.getElementById("tableContainerDiv");
      var table = new WidgetWrapper();
      var tableWidget = new TableWidget(table,tableDiv);

      // Retrieve the JSON files from the advanced-search-json page, while keeping the GET filters
      var jsonUrl = window.location.href.replace("advanced-search-map", "advanced-search-json");
      var jsonFile = GeoTemConfig.getJson(jsonUrl);
      datasets.push(new Dataset(GeoTemConfig.loadJson(jsonFile), "Results"));

      map.display(datasets);
      time.display(datasets);
      table.display(datasets);
    </script>
  <?php elseif ($empty): ?>
    <div class="view-empty">
      <?php print $empty; ?>
    </div>
  <?php endif; ?>

  <?php if ($pager): ?>
    <?php print $pager; ?>
  <?php endif; ?>

  <?php if ($attachment_after): ?>
    <div class="attachment attachment-after">
      <?php print $attachment_after; ?>
    </div>
  <?php endif; ?>

  <?php if ($more): ?>
    <?php print $more; ?>
  <?php endif; ?>

  <?php if ($footer): ?>
    <div class="view-footer">
      <?php print $footer; ?>
    </div>
  <?php endif; ?>

  <?php if ($feed_icon): ?>
    <div class="feed-icon">
      <?php print $feed_icon; ?>
    </div>
  <?php endif; ?>

</div><?php /* class view */ ?>
