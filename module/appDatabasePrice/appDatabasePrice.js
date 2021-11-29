/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-29
 * Time: 1:29:01 PM
 * Filename: appDatabasePrice.js
 */
$(function () {
    var slug = "appDatabasePrice",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    root.find("#icms-table-" + slug).parents(".icms-widget").on({
        'reload.ace.widget': function (e) {
            e.stopPropagation();
            var $box = $(this);
            nativeTable(slug, false, true, function (x) {
                if (x) {
                    setTimeout(function () {
                        $box.trigger('reloaded.ace.widget');
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        }
    });
});