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
    <div id="collocations">
      <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6"></div>
      </div>
      <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6"></div>
      </div>
    </div>
    <script>
      // Retrieve the words from the .alert-block (yes, very hacky)
      jQuery('.alert-block').hide();
      var words = JSON.parse(jQuery('.alert-block').contents().filter(function(){ return this.nodeType == 3; }).text());

      var COLLOCATIONS_NUMBER = 4;  // Note that this has to be changed at the back-end as well

      for (var i = 1; i <= COLLOCATIONS_NUMBER; i++) {
        var div = "#collocations .row:nth-child(" + Math.ceil(i / 2) + ") .col-md-6:nth-child(" + (i % 2 == 1 ? 1 : 2) + ")";
        jQuery(div).append(
          "<h3>Co-occurrences of search terms (d=" + i + ")</h3>" +
          "<table id=\"words" + i + "\" class=\"table table-striped\">" +
          "<thead><tr><th>Word</th><th>Frequency</th></tr></thead>" +
          "<tbody></tbody></table>");

        jQuery.each(words[i], function(word, count) {
          var tr = "<tr><td>" + word + "</td><td>" + count + "</td></tr>";
          jQuery("#words" + i + " tbody").append(tr);
        });
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
