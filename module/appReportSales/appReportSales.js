/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-20
 * Time: 10:08:08 AM
 * Filename: appReportSales.js
 */
$(function () {
    var slug = "appReportSales",
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

    root.find("#iform-appReportSales").on("prevent-post",function(e,data){
        var table = root.find("#icms-table-appReportSales");
        table.data("extra-param",data);
        nativeTable(slug, false,true,function (r,res) {
            if(res.data!==null){
                table.find(".report-download").show();
                var prices=0;
                if(res.custom!==null){
                    $.each(res.custom,function(i,item){
                        if(item.orderStatus==="process"){
                            prices += parseFloat(item.orderDownPayment);
                        }else if(item.orderStatus==="success"){
                            prices += parseFloat(item.orderTotalPrice);
                        }else{
                            prices += 0;
                        }
                    });
                }else{
                    prices = 0;
                }
                root.find("#reportTotalPrice").autoNumeric("set",isNaN(prices)?0:prices);
            }else{
                table.find(".report-download").hide();
                root.find("#reportTotalPrice").autoNumeric("set",0);
            }
        });
    });

    root.find("#iform-appReportSales").on("resetted",function(e){
        root.find("#icms-table-appReportSales").data("extra-param","").find(".report-download").hide();
        nativeTable(slug, false,true);
        root.find("#reportTotalPrice").autoNumeric("set",0);
    });

    root.find("#icms-table-appReportSales").on("table_search_enter",function(e,data){
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

    root.find("#icms-table-appReportSales").on("tb_act_custom",function (e,data) {
        var invoice = data.selector.parents("tr").children()[2].firstChild.textContent,param;
        switch(data.extra){
            case "info":
                param = "method=actionPage&slug=appReportSales&app=app&appslug=pos_cafe&act=showOrderDetail&orderInvoice="+invoice;
                $.post("Services",param,function (res) {
                    var tbRow = function (rows) {
                            var tr="";
                            $.each(rows,function(i,row){
                                tr += '<tr><td class="text-center">'+(i+1)+'.</td><td>'+row.productCode+'</td><td>'+row.productName+'</td><td class="text-center">'+row.orderdetailQuantity+" "+row.productUnitName+'</td><td class="text-right">'+(parseFloat(row.orderdetailSubPrice)).toLocaleString("id-ID")+'</td></tr>'
                            });
                            return tr;
                        },
                        tbDetail = '<table class=""><tr><th>Invoice</th><td>&nbsp;: '+res.order.orderInvoice+'</td></tr><tr><th>Tipe</th><td>&nbsp;: '+res.order.orderType+'</td></tr><tr><th>Total Harga</th><td>&nbsp;: '+(parseFloat(res.order.orderTotalPrice)).toLocaleString("id-ID")+'</td></tr><tr><th>Status</th><td>&nbsp;: '+res.order.orderStatus+'</td></tr></table><table class="table table-hover table-bordered table-striped"><tr><th class="text-center">No.</th><th>Kode</th><th>Nama Produk</th><th class="text-center">Qty</th><th class="text-right">Sub Total</th></tr>'+tbRow(res.data)+'</table>';
                    $.alerts.showIcon=false;
                    jAlert(tbDetail,"Detail Order Invoice "+invoice);
                    $.alerts.showIcon=true;
                },'json');
                break;
            case "download":
                param = "method=actionPage&slug=appReportSales&app=app&appslug=pos_cafe&act=downloadOrderDetail&orderInvoice="+invoice;
                $.icmsDownloader({url:"Services",data:$.deparam(param),async: true});
                break;
        }
    });
});