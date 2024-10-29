Array.prototype.indexOf = function (val) {
    for (let i = 0; i < this.length; i++) {
        if (this[i] == val) return i;
    }
    return -1;
};

Array.prototype.remove = function (val) {
    let index = this.indexOf(val);
    if (index > -1) {
        this.splice(index, 1);
    }
};

class myMap {
    constructor(arr = []) {
        this.items = {};
        this.size = 0;
        arr.forEach(item => {
            this.set(item[0], item[1]);
        });
    }

    has(val) {
        return this.items.hasOwnProperty(val);
    }

    set(key, val) {
        if (this.has(key)) {
            this.items[key] = val;
        } else {
            this.items[key] = val;
            this.size++;
        }
    }

    get(key) {
        return this.has(key) ? this.items[key] : undefined;
    }

    delete(key) {
        if (this.has(key)) {
            Reflect.deleteProperty(this.items, key);
            this.size--;
            return true;
        } else {
            return false;
        }
    }

    clear() {
        this.items = {};
        this.size = 0;
    }

    keys() {
        return Object.keys(this.items);
    }

    values() {
        return Object.values(this.items);
    }

    forEach(fn, context) {
        for (let i = 0; i < this.size; i++) {
            let key = this.keys()[i];
            let value = this.values()[i];
            Reflect.apply(fn, context, [key, value]);
        }
    }
}


let productMap = new myMap();


function close_balidrop_alert () {

    jQuery(".balidrop-alert")
        .addClass(`balidrop-alert-info`)
        .removeClass("balidrop-show");
    jQuery("#myUl").remove();
}

function import_selected() {

    jQuery("#balidrop-loading").addClass("balidrop-show");

    let btn = jQuery(`#import_selected_btn`).button('loading');

    let categotyVal = jQuery('#woo_product_categoty').find('option:selected').val();

    let param = {
        action: "woo_create_product",
        productIds: productMap,
        categoty: categotyVal
    };

    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: product_script.ajaxurl,
        data: param,
        success: function (res) {
            let data = res['data'];
            let uls = [];

            data.forEach(msg => {
                uls.push(`<li>${msg}</li>`)
            });
            if(uls.length !== 0){
                myAlert("info", `<ul id="myUl">${uls.toString().replace(/,/g, "<br>")}</ul>`, 50000);
            }
            jQuery("#balidrop-loading").removeClass("balidrop-show");
            btn.button('reset');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            myAlert("error",`<ul id="myUl">${errorThrown}</ul>`, 50000);
            jQuery("#balidrop-loading").removeClass("balidrop-show");
            btn.button('reset')
        },
    });


}

function woo_get_product_categories() {

    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: product_script.ajaxurl,
        data: {action: "woo_product_categories"},
        success: function (response) {
            let map = response['data'];

            for (let key in map) {
                let obj = map[key];
                let id = obj['term_id']
                let name = obj['name']
                jQuery('#woo_product_categoty')
                    .append(' <option value= ' + id + ' > ' + name + '</option> ');
            }
        }
    });

}

//获取选品
function balidrop_get_product_categories() {

    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: product_script.ajaxurl,
        data: {action: "balidrop_product_categories"},
        success: function (response) {
            let categorie = JSON.parse(response['data'])["rows"][0];
            categorie['children'].forEach(children => {
                let id = children['id']
                let name = children['name']
                jQuery('#product_categories')
                    .append(' <option value= ' + id + ' > ' + name + '</option> ');
            })
        }
    });

};

function balidrop_product_soupin() {

    let total;
    jQuery('#goods_list').bootstrapTable('destroy').bootstrapTable({
        url: product_script.ajaxurl,
        method: 'POST',
        dataType: 'json',
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        striped: true,
        cache: false,
        pagination: true,
        sortOrder: "asc",
        queryParamsType: '',
        paginationShowPageGo: true,
        showJumpto: true,
        pageNumber: 1, //初始化加载第一页，默认第一页
        queryParams: function queryParams(params) {   //设置查询参数

            let selectedVal = jQuery('#product_categories').find('option:selected').val();
            let inputText = jQuery('#product_categories_input').val();

            let soupin = {
                "keyword": inputText,
                "categoryId": selectedVal,
                "pagingDto": {
                    "pageNo": params.pageNumber,
                    "pageSize": params.pageSize,
                    "pageTotal": total
                }
            }

            let param = {
                action: "balidrop_product_soupin",
                params: soupin
            };

            return param;
        },
        sidePagination: 'server',
        pageSize: 10,
        pageList: [10],
        search: false,
        silent: true,
        showRefresh: false,
        showToggle: false,
        detailView: false,
        // icons: {
        //     detailOpen: 'glyphicon glyphicon-plus',
        //     detailClose: 'glyphicon glyphicon-minus'
        // },
        showExport: false,
        minimumCountColumns: 2,
        uniqueId: "id",
        //表头
        columns: [{
            title: 'S/N',//标题  可不加
            formatter: function (value, row, index) {
                return index + 1;
            }
        },
            {
                checkbox: true,
                visible: true
            },
            {
                field: 'image',
                title: 'Image',
                align: 'center',
                formatter: function (value, row, index) {
                    return `<img  src=${value} class="img-rounded" alt=${row['categoryName']}  width='50' height='50' >`;
                }
            },
            {
                field: 'productName',
                title: 'ProductName',
                align: 'center'
            }, {
                field: 'supplierPrice',
                title: 'Supplier price',
                align: 'center'
            }, {
                field: 'recommended',
                title: 'Recommended',
                align: 'center'
            },
            {
                field: 'yourPrice',
                title: 'Your price',
                align: 'center'
            },
            {
                field: 'action',
                title: 'Action',
                align: 'center',
                events: operateEvents,
                formatter: operateFormatter
            }
        ],
        onClickRow: function (row, tr, flied) {
            //  console.log('选中行事件')
        },
        onCheckAll: function (rows) {
            rows.forEach((value, index) => {
                productMap.set(value.id, index + 1);
            })
            // console.log('全部选中事件')
        },
        onUncheckAll: function (rows) {
            productMap.clear();
            // console.log('全部不选中事件')
        },
        onCheck: function (row, $element) {
            let index = $element.data('index');
            productMap.set(row.id, index + 1);
            //  console.log('单独选中事件')
        },
        onUncheck: function (row) {
            productMap.delete(row.id);
            // console.log('取消选中')
        },
        responseHandler: function (res) {

            console.log("responseHandler");

            if (res.success === true) {
                let products = JSON.parse(res['data']);
                if (products.flag = true) {
                    let newData = [];
                    products["rows"].forEach(row => {
                        let dataNewObj = {
                            'id': row['id'],
                            "image": row['mainImgSrc'],
                            "categoryName": row['categoryName'],
                            'productName': row['productName'],
                            "supplierPrice": row['showPrice'],
                            // 'recommended': row['showPrice'],
                            // 'yourPrice': row['showPrice']
                        };

                        newData.push(dataNewObj);

                    });

                    total = products.total;
                    let data = {
                        total: products.total,
                        rows: newData
                    };
                    return data;
                }
            }
        }
    });
}

function myAlert(type, msg, time) {

    if (time === null || time === undefined) {
        time = 1500;
    }
    jQuery(".balidrop-alert")
        .addClass(`balidrop-alert-${type}`)
        .addClass("balidrop-show")
        .append(`${msg}`);
    window.setTimeout(function () {
        jQuery(".balidrop-alert")
            .addClass(`balidrop-alert-${type}`)
            .removeClass("balidrop-show");
        jQuery("#myUl").remove();
    },time);//显示的时间

}

window.operateEvents = {

    'click .Import': function (e, value, row, index) {

        jQuery("#balidrop-loading").addClass("balidrop-show");

        let btn = jQuery(`#button_${index}`).button('loading');
        productMap.clear();
        productMap.set(row.id, index + 1);
        let categotyVal = jQuery('#woo_product_categoty').find('option:selected').val();
        let param = {
            action: "woo_create_product",
            productIds: productMap,
            categoty: categotyVal
        };

        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: product_script.ajaxurl,
            data: param,
            success: function (res) {

                let data = res['data'];
                let uls = [];

                data.forEach(msg => {
                    uls.push(`<li>${msg}</li>`)
                });
                if(uls.length !== 0){
                    myAlert("info", `<ul id="myUl">${uls.toString().replace(/,/g, "<br>")}</ul>`, 10000);
                }
                jQuery("#balidrop-loading").removeClass("balidrop-show");
                btn.button('reset');
                productMap.clear();
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                jQuery("#balidrop-loading").removeClass("balidrop-show", 50000);
                btn.button('reset');
                myAlert("error",`<ul id="myUl">${errorThrown}</ul>`);
                productMap.clear();
            },
        });
    },
    'click .Detail': function (e, value, row, index) {
        window.open(`http://www.balidrop.com/listInfo?id=${row.id}`);
    }
};

function operateFormatter(value, row, index) {
    return [
        `<button type="button" id="button_${index}" style='width: 80px; margin-bottom: 10px'  data-dismiss="alert"  data-loading-text="Loading..." class="btn btn-default-g Import ">Import</button>`,
        `<button type="button"  style='width: 80px;' class="btn btn-default-g Detail ">Detail</button>`,
    ].join('');
}

jQuery(function () {
    woo_get_product_categories();
    balidrop_get_product_categories();
    balidrop_product_soupin();
});




