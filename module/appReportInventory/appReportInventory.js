/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-02-04
 * Time: 12:21:37 AM
 * Filename: appReportInventory.js
 */
$(function () {
    var slug = "appReportInventory",
        root = $(".icms-container").find("#" + slug + ".tab-pane");

    root.find("#icms-table-" + slug).parents(".icms-widget").on({
        'reload.ace.widget': function (e) {
            e.stopPropagation();
            var $box = $(this);
            nativeTable(slug, false, true, function (x,data) {
                if(data.data!==null){$box.find(".report-download").show();}else{$box.find(".report-download").hide();}
                if (x) {
                    setTimeout(function () {
                        $box.trigger('reloaded.ace.widget');
                    }, parseInt(Math.random() * 500 + 500));
                }
            });
        }
    });

    root.on("nativeTable_first_loaded",function (e,data) {
        if(data.data!==null){$(this).find(".report-download").show();}else{$(this).find(".report-download").hide();}
    });

    root.find("#icms-table-appReportInventory").on("table_search_enter",function(e,data){
        if(data.data!==null){$(this).find(".report-download").show();}else{$(this).find(".report-download").hide();}
    });

    root.find(".report-download").click(function(){
        var table = $(this).parents("table"),
            param = {method:"actionPage",act:"downloadReport",slug:slug,app:"app",appslug:"pos_cafe"};
        param['search'] = table.find("input.search").val();
        $.icmsDownloader({url:"Services",data:param,async: true});
    });
});