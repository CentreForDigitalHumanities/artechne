(function($) {
    $(document).ready(function() {
        // Add query parameters to the block menu, so that searches are saved when switching views
        $("#block-menu-menu-advanced-search .nav a").each(function(i, e) {
            $(this).attr("href", e.href + window.location.search);
        });

        move_search_button();

        add_translation_switch();
    });

    // Hides the default submit button of the exposed forms below the facets,
    // creates a replacement button below
    function move_search_button() {
        var submit = $(".views-exposed-widget.views-submit-button button"),
            label = $("<label>Submit</label>");

        submit.parent().hide();
        label.attr("for", submit.attr("id"));
        label.addClass(submit.attr("class"));
        label.insertAfter(".block-facetapi:last");
    }

    // Adds a checkbox to switch between transcriptions only or transcription + translation
    function add_translation_switch() {
        var translation   = $("#edit-search-api-views-fulltext-translation-wrapper"),
            transcription = $("#edit-search-api-views-fulltext-transcription-wrapper"),
            toggle        = $("<input id='incl_trans' type='checkbox'> \
                <label for='incl_trans'>Include translations</label>");

        translation.after(toggle);

        // Check the query parameters to see which div should be hidden on load
        if (window.location.search.indexOf("transcription=&") !== -1) {
            transcription.hide();
            // Set the checkbox as checked
            toggle.prop("checked", true);
        }
        else {
            translation.hide();
        }

        // On change of the checkbox, move the input from translation to transcription and vice versa
        $("form[id^=views-exposed-form-search-api]").on("change", "#incl_trans", function() {
            var translation_input   = translation.find("input"),
                transcription_input = transcription.find("input");
            if (this.checked) {
                translation_input.val(transcription_input.val());
                translation.show();
                transcription_input.val('');
                transcription.hide();
            }
            else {
                transcription_input.val(translation_input.val());
                transcription.show();
                translation_input.val('');
                translation.hide();
            }
        });
    }
})(jQuery);
