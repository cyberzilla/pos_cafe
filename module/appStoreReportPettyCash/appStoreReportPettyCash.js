/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-11-01
 * Time: 11:25:09 AM
 * Filename: appStoreReportPettyCash.js
 */
$(function () {
    var slug = "appStoreReportPettyCash",
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