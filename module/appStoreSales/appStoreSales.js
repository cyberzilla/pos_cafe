/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-30
 * Time: 11:07:34 AM
 * Filename: appStoreSales.js
 */
$(function () {
    var slug = "appStoreSales",
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

    root.find("#icms-table-appStoreSales").on("tb_act_custom",function (e,data) {
        var invoice = data.selector.parents("tr").children()[2].firstChild.textContent,param,
            id = ((data.id).toString()).indexOf(":")!==-1?(data.id).split(":")[0]:data.id;
        switch(data.extra){
            case "info":
                param = "method=actionPage&slug=appStoreSales&app=app&appslug=pos_cafe&act=showOrderDetail&orderInvoice="+invoice;
                $.post("Services",param,function (res) {
                    var tbRow = function (rows) {
                            var tr="";
                            $.each(rows,function(i,row){
                                tr += '<tr><td class="text-center">'+(i+1)+'.</td><td>'+row.productCode+'</td><td>'+row.productName+'</td><td class="text-center">'+row.orderdetailQuantity+" "+row.productUnitName+'</td><td class="text-right">'+(parseFloat(row.orderdetailSubPrice)).toLocaleString("id-ID")+'</td></tr>'
                            });
                            return tr;
                        },
                        tbDetail = '<table class=""><tr><th>Invoice</th><td>&nbsp;: '+res.order.orderInvoice+'</td></tr><tr><th>Tipe</th><td>&nbsp;: '+res.order.orderType+'</td></tr><tr><th>Total Harga</th><td>&nbsp;: '+(parseFloat(res.order.orderTotalPrice)).toLocaleString("id-ID")+'</td></tr><tr><th>Status</th><td>&nbsp;: '+res.order.orderStatus+'</td></tr></table><table class="table table-hover table-bordered table-striped" style="margin-bottom: 5px;"><tr><th class="text-center">No.</th><th>Kode</th><th>Nama Menu</th><th class="text-center">Qty</th><th class="text-right">Sub Total</th></tr>'+tbRow(res.data)+'</table>'+'<table><tr><th>Ket</th><td>:</td><td>&nbsp;<i>'+res.order.orderTable+'</i></td></tr></table>';
                    $.alerts.showIcon=false;
                    jAlert(tbDetail,"Detail Order Invoice "+invoice);
                    $.alerts.showIcon=true;
                },'json');
                break;
            case "print":
                param = "method=actionPage&slug=appStoreSales&app=app&appslug=pos_cafe&act=rePrint&id="+id;
                $.post("Services",param,function (res) {
                    var check = getData({key:"icms-cashier"});
                        if(check===null){
                            $(e.currentTarget).find("button[type='reset']").click();
                            jCustomConfirm("Silahkan buka kasir terlebih dahulu!","Buka Kasir","Buka Kasir","Batal",function(r){
                                if(r){
                                    $('.addMainTab[data-slug="appStoreCashier"]').click();
                                }
                            });
                        }else{
                         var readyPrint = {
                                "storeName": check.receiptStoreName,
                                "storeAddress": check.receiptStoreAddress,
                                "storePhone":check.receiptStorePhone,
                                "storeCashier":"KASIR: "+check.cashierName,
                                "storeInvoice":"INVOICE: "+res.invoice,
                                "storeFooter":[check.receiptStoreFoot1,check.receiptStoreFoot2]
                                };
                            $.alerts.closeButton =true;
                            var template = '<form id="frmCetakStruk" name="frmCetakStruk" role="form" style="width: 100%">\
													<div class="form-group has-info">\
													    <div class="row no-margin" style="padding-bottom: 5px">\
                                                            <div class="col-md-4"><div class="pretty p-jelly p-round p-icon"><input name="storePrint" type="checkbox" checked><div class="state p-primary"><i class="icon fa fa-check" style="top: 0.1px;left: 1px;"></i><label>Kasir</label></div></div></div>\
                                                            <div class="col-md-8"><input class="form-control input-sm" placeholder="Jumlah Print" value="1" type="number" name="storePrintCount" min="1"></div>\
													    </div>\
													    <div class="row no-margin">\
                                                            <div class="col-md-4"><div class="pretty p-jelly p-round p-icon"><input name="kitchenPrint" type="checkbox" checked><div class="state p-primary"><i class="icon fa fa-check" style="top: 0.1px;left: 1px;"></i><label>Dapur</label></div></div></div>\
                                                            <div class="col-md-8"><input class="form-control input-sm" placeholder="Jumlah Print" value="1" type="number" name="kitchenPrintCount" min="1"></div>\
													    </div>\
											</form>';
                            jCustomPopup(template,"Print Struk","Cetak","Batal",function(r,data){
                                var param2 = $.deparam(data),noprint=false;
                                if(param2.storePrint==="on" && param2.kitchenPrint==="on"){
                                    readyPrint['storePrintType'] = "Both";
                                    readyPrint['storePrintCount'] = param2.storePrintCount;
                                    readyPrint['kitchenPrintCount'] = param2.kitchenPrintCount;
                                }else{
                                    if(param2.storePrint==="on"){
                                        readyPrint['storePrintType'] = "Store";
                                        readyPrint['storePrintCount'] = param2.storePrintCount;
                                    }else if(param2.kitchenPrint==="on"){
                                        readyPrint['storePrintType'] = "Kitchen";
                                        readyPrint['kitchenPrintCount'] = param2.kitchenPrintCount;
                                    }else{
                                        noprint = true;
                                    }
                                }

                                if(noprint===false){
                                    var mergeObj = Object.assign(readyPrint, res.content),
                                        content = JSON.stringify({"content":mergeObj});
                                    location.href='jprint:'+btoa(content);
                                }
                            });
                            $.alerts.closeButton =false;
                        }
                },'json');
                break;
        }
    });

    root.find("select[name=orderStatus]").change(function (e){
        var reason = root.find(".cancelOrderContainer");
        switch (e.target.value){
            case "cancel":
                reason.show().find("input").show();
                break;
            default:
                reason.hide().find("input").hide();
                break;
        }
    });
});