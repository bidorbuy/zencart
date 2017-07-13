jQuery(document).ready(function () {
    var select_text = "#tokenExportUrl, #tokenExportScript, #tokenDownloadUrl";
    jQuery('.copy-button').click(function () {
        jQuery(this).parent().prev().find('.bobsi-url').select();
    });

    jQuery('.loggingFormButton').addClass('btn btn-primary');
    jQuery('.loggingFormButton').attr('type', 'submit');

    //This is set in main script:        jQuery('#loggingForm').submit(); -but it works only once.
    //It submits the form normally:
    jQuery('.loggingFormButton').click(function () {
        document.getElementById('loggingForm').submit();
    });

    jQuery(".copy-button, " + select_text).click(function (evt) {
        jQuery("#ctrl-c-message").css({
            top: evt.pageY - 800,
            left: evt.pageX - 200
        }).show();
    });

//    Tooltips
    function simple_tooltip(target_items, name) {
        jQuery(target_items).each(function (i) {

            var tip_text = jQuery(this).next().find('.tip');
            jQuery("body").append("<div class='" + name + "' id='" + name + i + "'>" +
                "<span class='tooltip-arrow'>" +
                "</span>" + tip_text.html() + "</div>");

            var my_tooltip = jQuery("#" + name + i);

            tip_text.detach();
            jQuery(this).removeAttr("title").mouseover(function () {
                my_tooltip.css({opacity: 0.8, display: "none"}).fadeIn(1);
            }).mousemove(function (kmouse) {
                    my_tooltip.css({left: kmouse.pageX + 15, top: kmouse.pageY + 15});
                }).mouseout(function () {
                    my_tooltip.fadeOut(1);
                });
        });
    }

    simple_tooltip(".hastip", "tooltip");

    jQuery('.logfiles').before(jQuery('#bobsi-export-links'));
});
