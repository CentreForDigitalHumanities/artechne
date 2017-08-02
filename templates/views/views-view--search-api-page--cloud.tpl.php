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
    <div id="cloud"><svg /></div>
    <input type="checkbox" id="normalize" name="normalize">
    <labeL for="normalize">Normalize using inverse document frequency</label><br>
    <labeL for="minchars">Mininum characters</label>
    <input type="number" id="minchars" name="minchars" value="4"><br>
    <textarea id="csv" style="display: none;"></textarea><a id="download">Download .csv</a>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/1.2.4/d3.layout.cloud.min.js"></script>
    <script>
      // Converts a JSON object to something comma-separated
      function convert2csv(objArray) {
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        var str = 'word,current,total' + '\r\n';

        for (var i = 0; i < array.length; i++) {
          var line = array[i]['token'] + ',' + array[i]['tf'] + ',' + array[i]['df'];
          str += line + '\r\n';
        }
        return str;
      }

      // Retrieve the words from the .alert-block (yes, very hacky)
      jQuery('.alert-block').hide();
      var words = JSON.parse(jQuery('.alert-block').contents().filter(function(){ return this.nodeType == 3; }).text());

      // Add content to the csv textarea, download on click
      jQuery('#csv').val(convert2csv(words));
      jQuery('#download').on("click", function() {
        this.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(jQuery('#csv').val());
        this.download = 'export.txt';
      });

      // Initialize normalize/minchars options, add reDraw functions on change
      var normalize = false;
      var minchars = 4;

      jQuery("input[name=normalize]").change(function() {
        normalize = this.checked;
        reDraw(normalize, minchars);
      });

      jQuery("input[name=minchars]").change(function() {
        minchars = jQuery(this).val();
        reDraw(normalize, minchars);
      });

      // Draws the word cloud
      function reDraw(normalize, minchars) {
        function getSize(w) {
          if (normalize) {
            return w.tf / w.df * 150;
          }
          else {
            return w.tf * 15;
          }
        }

        var fill = d3.scale.category20();

        var layout = d3.layout.cloud()
          .size([800, 500])
          .words(words.map(function(w) {
            return {text: w.token, size: getSize(w), color: "#000000" };
          }).filter(function(w) {
            return w.text.length >= minchars;
          }))
          .padding(5)
          .rotate(function() { return 0; })
          .font("Impact")
          .fontSize(function(d) { return d.size; })
          .on("end", draw);

        layout.start();

        function draw(words) {
          d3.selectAll("svg > *").remove();
          d3.select("#cloud svg")
              .attr("width", layout.size()[0])
              .attr("height", layout.size()[1])
            .append("g")
              .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
            .selectAll("text")
              .data(words)
            .enter().append("text")
              .style("font-size", function(d) { return d.size + "px"; })
              .style("font-family", "Impact")
              .style("fill", function(d, i) { return d.color; })
              .attr("text-anchor", "middle")
              .attr("transform", function(d) {
                return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
              })
              .text(function(d) { return d.text; });
        }
      }

      // Draw the word cloud on page load
      reDraw(normalize, minchars);
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
