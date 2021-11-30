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

    root.find("#icms-table-appDatabasePrice").on("tb_act_update",function (e,data) {
        root.find("input[name=priceCapitalPrice]").val(data.data.priceCapitalPrice);
        root.find("input[name=priceSellingPrice]").val(data.data.priceSellingPrice);
    });

    root.find("#icms-table-appDatabasePrice").on("tb_act_custom",function (e,data) {
        switch (data.extra) {
            case "history":
                param = "method=actionPage&slug=appDatabasePrice&app=app&appslug=pos_cafe&act=showHistory&id="+data.id;
                $.post("Services",param,function (res) {
                    if(res.data!==null){
                        var tbRow = function (rows) {
                                var tr="";
                                $.each(rows,function(i,row){
                                    tr += '<tr><td class="text-center">'+(i+1)+'.</td><td>'+(new Date(row.price_logDateTime)).toLocaleDateString("id", {year: 'numeric', month: 'short', day: "numeric"})+'</td><td class="text-right">Rp. '+(parseFloat(row.price_logCapitalPrice)).toLocaleString("id-ID")+'</td><td class="text-right">Rp. '+(parseFloat(row.price_logSellingPrice)).toLocaleString("id-ID")+'</td></tr>'
                                });
                                return tr;
                            },
                            tbDetail = '<table class="table table-hover table-bordered table-striped"><tr><th class="text-center">No.</th><th class="text-center">Update</th><th>Harga Modal</th><th class="text-center">Harga Jual</th>'+tbRow(res.data)+'</table>';
                        $.alerts.showIcon=false;
                        jAlert(tbDetail,"History Harga");
                        $.alerts.showIcon=true;
                    }else{
                        jAlert("Harga Belum di set","History Harga");
                    }
                },'json');
                break;
        }
    });
});