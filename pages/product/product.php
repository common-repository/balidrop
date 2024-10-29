
    <div class="wrap">

        <div class="balidrop-alert ">
            <div class="balidrop-alert-close" onclick="close_balidrop_alert()">X</div>

        </div>

        <div class="box">

            <div class="balidrop-loading" id="balidrop-loading">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48c3R5bGU+QGtleWZyYW1lcyBsb2FkezAle3RyYW5zZm9ybTpyb3RhdGUoMCl9dG97dHJhbnNmb3JtOnJvdGF0ZSgtMzYwZGVnKX19PC9zdHlsZT48ZyBzdHlsZT0iYW5pbWF0aW9uOmxvYWQgMXMgbGluZWFyIGluZmluaXRlO3RyYW5zZm9ybS1vcmlnaW46Y2VudGVyIGNlbnRlciI+PGxpbmVhckdyYWRpZW50IGlkPSJyaWdodCIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSIxNTAiIHkxPSIyMCIgeDI9IjE1MCIgeTI9IjE4MCI+PHN0b3Agb2Zmc2V0PSIwIiBzdG9wLWNvbG9yPSIjMzU3Y2UxIi8+PHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjMzU3Y2UxIi8+PC9saW5lYXJHcmFkaWVudD48cGF0aCBkPSJNMTAwIDB2MjBjNDQuMSAwIDgwIDM1LjkgODAgODBzLTM1LjkgODAtODAgODB2MjBjNTUuMiAwIDEwMC00NC44IDEwMC0xMDBTMTU1LjIgMCAxMDAgMHoiIGZpbGw9InVybCgjcmlnaHQpIi8+PGxpbmVhckdyYWRpZW50IGlkPSJsZWZ0IiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjUwIiB5MT0iMCIgeDI9IjUwIiB5Mj0iMTgwIj48c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiMzNTdjZTEiIHN0b3Atb3BhY2l0eT0iMCIvPjxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzM1N2NlMSIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZD0iTTIwIDEwMGMwLTQ0LjEgMzUuOS04MCA4MC04MFYwQzQ0LjggMCAwIDQ0LjggMCAxMDBzNDQuOCAxMDAgMTAwIDEwMHYtMjBjLTQ0LjEgMC04MC0zNS45LTgwLTgweiIgZmlsbD0idXJsKCNsZWZ0KSIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjEwIiByPSIxMCIgZmlsbD0iIzM1N2NlMSIvPjwvZz48L3N2Zz4=" alt="">
                <p>加载中...</p>
            </div>

            <div class="searchBox">
                <span class="searchBox-title">Search Products</span>
                <div class="inputBox">
                    <input type="text" placeholder="" id="product_categories_input">
                    <select class="select1" id="product_categories">
                        <option value= ''>IN ALL CATEGORIES</option>
                    </select>
                </div>
                <button class="btn danger" onclick="balidrop_product_soupin()" >Search</button>
            </div>

        </div>

        <div class="selectBox">
            <select class="select2" name="" id="woo_product_categoty">
                <option value="">SELECT CATEGOTY</option>
            </select>
            <button type="button" id="import_selected_btn" class="btn btn-primary importBtn "  data-loading-text="Loading..." onclick="import_selected()"  >Import selected</button>
            <span class="tip">click "Import selact"</span>
        </div>

        <table class="table table-striped table-bordered table-hover" id="goods_list"></table>
    </div>


