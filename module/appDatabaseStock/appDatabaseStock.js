/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 1:24:18 AM
 * Filename: appDatabaseStock.js
 */
$(function () {
    var slug = "appDatabaseStock",
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

    root.find("button.browseFile").click(function(e) {
        var fileName = root.find("#stockFileName"),
            file = root.find("#stockFile");
        file.click().on("change", function(e) {
            var itemsFile = this.files[0];
            fileName.val(itemsFile.name).focus();
        });
    });

    root.find("#iform-appDatabaseStock-import").on("submitted",function(e){
        root.find("#icms-table-" + slug).parents(".icms-widget").trigger("reload.ace.widget");
    });
});