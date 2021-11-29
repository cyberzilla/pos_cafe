/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-30
 * Time: 2:16:05 PM
 * Filename: appReportCashier.js
 */
$(function () {
    var slug = "appReportCashier",
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

    root.find('.input-daterange').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayHighlight: true,
        language: "id"
    });

    root.find("#iform-appReportCashier").on("prevent-post",function(e,data){
        var table = root.find("#icms-table-appReportCashier");
        table.data("extra-param",data);
        nativeTable(slug, false,true,function (r,res) {
            if(res.data!==null){
                table.find(".report-download").show();
            }else{
                table.find(".report-download").hide();
            }
        });
    });

    root.find("#iform-appReportCashier").on("resetted",function(e){
        root.find("#icms-table-appReportCashier").data("extra-param","").find(".report-download").hide();
        nativeTable(slug, false,true);
    });

    root.find("#icms-table-appReportCashier").on("table_search_enter",function(e,data){
        if(data.data!==null){
            $(this).find(".report-download").show();
        }else{
            $(this).find(".report-download").hide();
        }
    });

    root.find(".report-download").click(function(){
        var table = $(this).parents("table"),
            param = table.data("extra-param");
        param['search'] = table.find("input.search").val();
        param['act'] = "downloadReport";
        $.icmsDownloader({url:"Services",data:param,async: true});
    });
});