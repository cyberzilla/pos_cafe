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

    function controlOrder(type,opts){
        var target = root.find("select[name=orderTable]"),optId;
        $.each(target.find("option"),function (i,opt) {
            optId = ($(opt).data("cdata"))!==undefined?($(opt).data("cdata")).tableroot:""
            if(optId!=="" && optId!==type){
                target.val("").find('option[value=""]').attr('selected',true);
                $(opt).hide();
            }else{
                target.val("").find('option[value=""]').attr('selected',true);
                $(opt).show();
            }
        });

        if(optId===""){
            target.val("").find('option[value=""]').attr('selected',true);
        }
    }

    root.find("#orderRequestType").change(function(e){
        controlOrder(this.value);
    });

    root.find("#icms-table-appStorePreorder").on("tb_act_custom",function (e,data) {
        var invoice = data.selector.parents("tr").children()[2].firstChild.textContent,
            customer = data.selector.parents("tr").children()[3].firstChild.textContent,
            table = data.selector.parents("tr").children()[4].firstChild.textContent,
            param;
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
                        tbDetail = '<table class=""><tr><th>Invoice</th><td>&nbsp;: '+res.order.orderInvoice+'</td></tr><tr><th>Tipe</th><td>&nbsp;: '+res.order.orderType+'</td></tr><tr><th>Total Harga</th><td>&nbsp;: '+(parseFloat(res.order.orderTotalPrice)).toLocaleString("id-ID")+'</td></tr><tr><th>Status</th><td>&nbsp;: '+res.order.orderStatus+'</td></tr></table><div class="tableDetailContainer"><table class="table table-hover table-bordered table-striped" style="margin-bottom: 5px;"><tr><th class="text-center">No.</th><th>Kode</th><th>Nama Produk</th><th class="text-center">Qty</th><th class="text-right">Sub Total</th></tr>'+tbRow(res.data)+'</table></div><table><tr><th>Ket</th><td>:</td><td>&nbsp;<i>'+res.order.orderTable+'</i></td></tr></table>';
                    $.alerts.showIcon=false;
                    jAlert(tbDetail,"Detail Order Invoice "+invoice);
                    $("#popup_container").find('.tableDetailContainer').ace_scroll('modify', {
                        size: 200,
                        observeContent: true,
                        touchDrag: true,
                        touchSwipe: true,
                        styleClass: 'scroll-margin scroll-dark'
                    });
                    $.alerts.showIcon=true;
                },'json');
            break;

            case "merge":
                var mergeinv = {invoice:invoice, customer:customer, table:table,request:"dinein"}
                jConfirm("Apakah anda akan menambahkan order baru pada invoice ini?","Gabung Order",function(r){
                    if(r){
                        pushData({key: "icms-invoice-merge", data: mergeinv, replace: true});
                        var getInv = getData({key:"icms-invoice-merge"});
                        $('.addMainTab[data-slug="appStoreCashier"]').click();
                        $(".icms-container").find("#appStoreCashier.tab-pane").find("#cancelOrClear").html('<i class="fa fa-times"></i> Batal Gabung');
                    }
                });
                break;

            case "cancel":
                var template='<form class="icms-form"><div class="form-group"><label for="orderAdditionalInfo">Masukkan alasan pembatalan order:</label><textarea name="orderAdditionalInfo" id="orderAdditionalInfo" class="form-control input-sm" rows="3" data-placement="top-r" data-msg="Masukkan Alasan Pembatalan" required></textarea></div></form>';
                jCustomPopup(template,"Pembatalan Order "+invoice,"Simpan","Batal",function(r,frm){
                    if(r){
                        var reason = $.deparam(frm);
                            param = {method:"actionPage",slug:slug,app:"app",appslug:"pos_cafe",act:"cancelOrder",mainId:data.id,orderAdditionalInfo:reason.orderAdditionalInfo,orderInvoice:invoice,orderStatus:"cancel",orderTable:table};
                        $.post("Services",param,function(res){
                            if(res.status==="success"){
                                $.gritter.add({
                                    title: 'Berhasil',
                                    text: res.messageSuccess,
                                    class_name: 'gritter-success'
                                });
                                nativeTable(slug, false, true);
                            }
                        },'json');
                    }
                });
                break;
        }
    });

    root.find("#icms-table-appStorePreorder").on("tb_act_update",function (e,data) {
        var orderTable = root.find(".orderTableContainer");
        if(data.data.orderType==="preorder"){
            orderTable.hide();
            orderTable.find("select[name=orderTable]").hide();
            root.find("#requestType").val(data.data.orderType);
        }else if(data.data.orderType==="orderweb"){
            orderTable.show();
            orderTable.find("select[name=orderTable]").show();
            root.find("#requestType").val(data.data.orderType);
        }
    });

    function priceback(price){
        var sisa = root.find("#orderTotalPrice").autoNumeric("get"),
            back = parseFloat(price)-parseFloat(sisa);
        root.find("#orderPriceBack").autoNumeric('set',isNaN(back)?0:(back<0?0:back));
        if(back<0){
            root.find("#btnPricePayment").prop("disabled",true);
        }else{
            if(root.find("#orderPricePayment").is(':disabled') && price!==0){
                root.find("#btnPricePayment").prop("disabled",true);
            }else{
                root.find("#btnPricePayment").prop("disabled",false);
            }
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

            if(res.request==="orderweb"){
                readyPrint['storePrintType'] = "Both";
            }else{
                readyPrint['storePrintType'] = "Store";
            }
            mergeObj = Object.assign(readyPrint, res.content);
            content = JSON.stringify({"content":mergeObj});
            if(res.print==="on"){
                location.href='jprint:'+btoa(content);
            }
    });
});