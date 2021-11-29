$(function () {
    var slug = "appHome",
        root = $(".icms-container").find("#" + slug + ".tab-pane"),
        year = new Date().getFullYear();

    root.find("#widget-chart.icms-widget").on({
        'reload.ace.widget': function (e) {
            e.stopPropagation();
            var $box = $(this),
                tahun = root.find("#btnChart").data("value");
            loadChart(tahun);
            setTimeout(function () {
                $box.trigger('reloaded.ace.widget');
            }, parseInt(Math.random() * 500 + 500));
        }
    });

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

    function renderDropDownYear(){
        var yearUl = root.find(".chartYearList"),list="",
            start = year-3;
        for(i=start;i<=year;i++){
            if(i===year){
                list+='<li class="active"><a class="btnChart blue" data-value="'+i+'"><i class="ace-icon fa fa-caret-right bigger-110">&nbsp;</i>Tahun '+i+'</a></li>';
            }else{
                list+='<li><a class="btnChart" data-value="'+i+'"><i class="ace-icon fa fa-caret-right bigger-110 invisible">&nbsp;</i>Tahun '+i+'</a></li>';
            }
        }
        root.find("#btnChart").data("value",year).html("Tahun "+year+' <i class="ace-icon fa fa-angle-down icon-on-right bigger-110"></i>');
        yearUl.html(list);
        root.find(".btnChart").click(function (e) {
            var $btnChart = root.find("#btnChart"),
                btnChart = root.find(".btnChart"),
                btnChartParent = btnChart.parents("li"),
                value = $(this).data("value");
            btnChartParent.removeClass("active");
            btnChart.removeClass("blue");
            btnChart.find("i").addClass("invisible");
            $(this).parents("li").addClass("active");
            $(this).addClass("blue");
            $(this).find("i").removeClass("invisible");
            $btnChart.data("value",value).html("Tahun "+value+' <i class="ace-icon fa fa-angle-down icon-on-right bigger-110"></i>');
            root.find("#widget-chart.icms-widget").find('[data-action=reload]').click();
        });
    }

    function renderChart(selector,column,header){
        var randomColor = function(str) {
            var hash = 0,color = '#';if (str.length === 0) return hash;
            for (var i = 0; i < str.length; i++) {hash = str.charCodeAt(i) + ((hash << 5) - hash);hash = hash & hash;}
            for (var j = 0; j < 3; j++) {var value = (hash >> (j * 8)) & 255;color += ('00' + (value+Math.floor(Math.random()*16777215)).toString(16)).substr(-2);}return color;}, colors={};
            $.each(column,function (i,col) {if(col[0]!=="kolom"){colors[col[0]]=randomColor(i);}});
            header = header!==undefined?{text: header, position: 'outer-middle'}:false;
        var c3chart = c3.generate({
            bindto: selector,
            data: {x: 'kolom', columns: column, type: 'area-spline', labels: false, colors: colors, selection: {enabled: true}},
            axis: {x: {type: 'categorized',}, y : {label: header,tick:{format: function (d) {var array = ['','Ribu','Juta','M','T'];var i=0;while (d > 999) {i++;d = d/1000;}d = d+' '+array[i];return d;}}}},
            bar: {width: {ratio: 0.3,}},
            grid: {x: {show: true}, y: {show: true}}
        });
    }

    function loadChart(tahun){
        var param = "method=actionPage&app=app&slug="+slug+"&appslug=pos_cafe&act=loadChart&year="+tahun;
        $.post("Services",param,function(res){
            renderChart("#chart",res.chart,"Grafik Tahun "+tahun);
            root.find(".purchaseNow").html(res.purchaseNow===null?0:res.purchaseNow);
            root.find(".orderNow").html(res.orderNow===null?0:res.orderNow);
            root.find(".incomeNow").html("Rp. "+(isNaN(parseFloat(res.incomeNow))?0:parseFloat(res.incomeNow)).toLocaleString("id-ID"));
            root.find(".spendNow").html("Rp. "+(isNaN(parseFloat(res.spendNow))?0:parseFloat(res.spendNow)).toLocaleString("id-ID"));
        },'json');
    }

    //Run First
    loadChart(year);renderDropDownYear();
});