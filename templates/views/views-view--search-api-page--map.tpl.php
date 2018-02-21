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
    <script src="sites/all/libraries/GeoTemCo/geotemco-min.js"></script>
    <script>
     var datasets = [];
      var datasetsWithLocation = [];
      // see below, entities without locations break the map.
      var searchResultsWithLocation = []

      var mapDiv = document.getElementById("mapContainerDiv");
      var map = new WidgetWrapper();
      var mapWidget = new MapWidget(map,mapDiv,{
        mapTitle: "Library location",
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

      prepareForVisualization(jsonFile)
      
      datasets.push(new Dataset(GeoTemConfig.loadJson(jsonFile), "Results"));
      datasetsWithLocation.push(new Dataset(GeoTemConfig.loadJson(searchResultsWithLocation), "Results"));
      
      map.display(datasetsWithLocation);
      time.display(datasets);
      table.display(datasetsWithLocation);

      // Filter out entities with location set (to global variable),
      // Normalize time notation (e.g. 'ca. 1450 - 1455' to 4 digits)
      // Add information for table and details in map 
      function prepareForVisualization(results) {
        for (i = 0; i < results.length; i++) {
          if (results[i].lon) {
            searchResultsWithLocation.push(results[i]);
          }

          if (results[i].time && results[i].time.match("\\d{4}")) {
            results[i].time = results[i].time.match("\\d{4}")[0];
          }

          results[i].description = "<![CDATA[<b>" + results[i].name + " (" + results[i].time + "). </b><br/><br/>Location: " + results[i].place,
          results[i].tableContent =  {'name': results[i].name, 'place': results[i].place, 'time': results[i].time }
        }
      }
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
