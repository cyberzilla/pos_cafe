/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-05
 * Time: 1:09:43 AM
 * Filename: appConfig.js
 */
$(function () {
    var slug = "appDatabaseConfig",
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