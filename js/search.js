(function($) {
    $(document).ready(function(){
        // Add query parameters to the block menu, so that searches are saved when switching views
        $("#block-menu-menu-advanced-search .nav a").each(function(i, e) {
            $(this).attr("href", e.href + window.location.search);
        });
    });
})(jQuery);
