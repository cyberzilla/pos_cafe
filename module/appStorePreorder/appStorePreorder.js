/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-25
 * Time: 4:24:16 AM
 * Filename: appStorePreorder.js
 */
$(function () {
    var slug = "appStorePreorder",
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

    root.find("#icms-table-appStorePreorder").on("tb_act_update",function (e,data) {
        root.find(".preorderInvoice").html("Invoice: "+data.data.orderInvoice);
        nativeSelect(slug);
    });

    root.find("#icms-table-appStorePreorder").on("tb_act_custom",function (e,data) {
        var invoice = data.selector.parents("tr").children()[2].firstChild.textContent,param;
        switch(data.extra){
            case "info":
                    param = "method=actionPage&slug=appStorePreorder&app=app&appslug=pos_cafe&act=showOrderDetail&orderInvoice="+invoice;
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
        }
    });

    function priceback(price){
        var sisa = root.find("#orderTotalPrice").autoNumeric("get"),
            back = parseFloat(price)-parseFloat(sisa);
        root.find("#orderPriceBack").autoNumeric('set',isNaN(back)?0:(back<0?0:back));
        if(back<0){
            root.find("#btnPricePayment").prop("disabled",true);
        }else{
            root.find("#btnPricePayment").prop("disabled",false);
        }
    }

    root.find("#orderPricePayment").on("autoNumeric_press",function(e,data){
        priceback(data);
    });


    root.find("#iform-appStorePreorder").parents(".icms-widget").on({
        'reloaded.ace.widget': function (e) {
            var check = getData({key:"icms-cashier"});
            if(check===null){
                $(e.currentTarget).find("button[type='reset']").click();
                jCustomConfirm("Silahkan buka kasir terlebih dahulu!","Buka Kasir","Buka Kasir","Batal",function(r){
                    if(r){
                        $('.addMainTab[data-slug="appStoreCashier"]').click();
                    }
                });
            }
        }
    });

    root.find("#iform-appStorePreorder").on("resetted",function (e,res) {
        root.find(".preorderInvoice").html("Pembayaran");
    });

    root.find("#orderType").change(function (e) {
        var total = root.find("#orderTotalPrice").autoNumeric("get");
        switch (e.target.value) {
            case "cash":
                root.find("#orderPricePayment").val("").focus();
                break;

            case "card":
                root.find("#orderPricePayment").autoNumeric('set',total);
                root.find("input[name=orderPricePayment]").val(total);
                break;

            default:
                root.find("#orderPricePayment").val("").focus();
                break;
        }
    });

    root.find("#iform-appStorePreorder").on("submitted",function (e,res) {
            var check = getData({key:"icms-cashier"}),mergeObj,content,
                readyPrint = {
                    "storeName": check.receiptStoreName,
                    "storeAddress": check.receiptStoreAddress,
                    "storePhone":check.receiptStorePhone,
                    "storeCashier":"KASIR: "+check.cashierName,
                    "storeInvoice":"INVOICE: "+res.data.data[0].orderInvoice,
                    "storeFooter":[check.receiptStoreFoot1,check.receiptStoreFoot2]
                };
            readyPrint['storePrintType'] = "Store";
            mergeObj = Object.assign(readyPrint, res.content);
            content = JSON.stringify({"content":mergeObj});
            if(res.print==="on"){
                location.href='jprint:'+btoa(content);
            }
    });
});