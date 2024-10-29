<?php
/**
 * Author: hyl
 * Date: 2021-01-29
 */

class Balidrop_Plugin
{

    protected $upImagesApi;

    function __construct()
    {

        $this->upImagesApi = new Balidrop_UpImagesApi();

        add_action('wp_ajax_woo_product_categories', array($this, 'woo_product_categories'));

        add_action('wp_ajax_woo_create_product', array($this, 'woo_create_product'));

        add_action('wp_ajax_balidrop_product_categories', array($this, 'balidrop_product_categories'));

        add_action('wp_ajax_balidrop_product_categories_detail', array($this, 'balidrop_product_categories_detail'));

        add_action('wp_ajax_balidrop_product_soupin', array($this, 'balidrop_product_soupin'));

    }


    function woo_product_categories()
    {

        $orderby = 'name';
        $order = 'asc';
        $hide_empty = false;
        $cat_args = array(
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => $hide_empty,
        );

        $product_categories = get_terms('product_cat', $cat_args);

        wp_send_json_success($product_categories);

        die();
    }


    function balidrop_product_soupin()
    {

        $json = $_POST["params"];
        if (empty($json)) {
            wp_send_json(array(
                'message' => "params Cannot be empty!",
                'flsg' => false
            ));
            die();
        }

        $response = wp_remote_post('https://www.balidrop.com/api/platformSupplierProduct/soupin', array(
            'body' => json_encode($json, JSON_FORCE_OBJECT),
            'headers' => array(
                'Content-type' => 'application/json'),
        ));

        $body = wp_remote_retrieve_body($response);
        wp_send_json_success($body);

        die();
    }


    function balidrop_product_categories()
    {
        $response = wp_remote_get('https://www.balidrop.com/api/goodsCategory/list');
        $body = wp_remote_retrieve_body($response);
        wp_send_json_success($body);
        die();
    }

    function balidrop_product_categories_detail()
    {

        $productId = $_POST["productId"];
        if (empty($productId)) {
            wp_send_json(array(
                'message' => "${$productId} Cannot be empty!",
                'flsg' => false
            ));
            die();
        }

        $response = wp_remote_get("https://www.balidrop.com/api/platformSupplierProduct/item/{$productId}?productId={$productId}");
        $body = wp_remote_retrieve_body($response);
        wp_send_json_success($body);

        die();
    }

    function verifySku($data)
    {

        foreach ($data->itemSkuList as $key => $item) {

            $skuAttr = explode(";", $item->commerceSkuAttr);
            foreach ($skuAttr as $key => $skuValue) {

                $variationAttributes = [];

                $skuValueArr = explode(":", $skuValue);
                $attrNo = $skuValueArr[0];
                $attrValueNo = $skuValueArr[1];

                foreach ($data->attrNames as $key => $attrNameValue) {

                    if ($attrNameValue->attrNo == $attrNo) {
                        $name = $attrNameValue->name;
                        foreach ($attrNameValue->valueList as $key => $value) {
                            if ($value->attrValueNo == $attrValueNo) {
                                $variationAttributes[$name] = $value->attrValue;
                            }
                        }
                    }
                }
            }

            if (!empty(wc_get_product_id_by_sku($item->skuId))) {
                return 'There are duplicate products';
            } else {
                return '';
            }
        }

    }


    function woo_create_product()
    {
        $productIds = $_POST["productIds"];

        if (empty($productIds)) {
            wp_send_json(array(
                'message' => "${$productIds} Cannot be empty!",
                'flsg' => false
            ));
            die();
        }
        //this string
        $categotyId = $_POST["categoty"];
        if ( !empty($categotyId) ) {
            $categotyIds = ( !is_array( $categotyId ) )? array ( $categotyId ): $categotyId;
        }

        $errorMsg = [];
        $successMsg = [];
        foreach ($productIds[items] as $id => $sn) {

            $response = wp_remote_get("https://www.balidrop.com/api/platformSupplierProduct/item/{$id}?productId={$id}");
            $body = wp_remote_retrieve_body($response);
            $productInfo = json_decode($body);
            $data = $productInfo->obj;

            $product = new WC_Product_Variable();
            $product->set_category_ids($categotyIds);

            $verifyMsg = $this->verifySku($data);

            if ($verifyMsg !== '') {
                array_push($errorMsg, "S/N: {$sn}  {$verifyMsg}");
                continue;
            }

            if ($productInfo->flag == true) {

                $product->set_name($data->productName);
                $product->set_slug($data->productName);
                $product->set_description($data->desc);
                $product->set_status('publish');

                //sku
                if ($data->hasSku == true) {

                    $attributes = [];
                    foreach ($data->attrNames as $key => $value) {

                        $options = [];
                        foreach ($value->valueList as $key => $attrValue) {
                            array_push($options, $attrValue->attrValue);
                        }

                        $attribute = new WC_Product_Attribute();
                        $attribute->set_name(strtolower(str_replace(' ', '_', $value->name)));
                        $attribute->set_options($options);
                        $attribute->set_visible(true);
                        $attribute->set_variation(true);
                        array_push($attributes, $attribute);
                    }
                    $product->set_attributes($attributes);
                    $id = $product->save();

                    foreach ($data->itemSkuList as $key => $item) {
                        $variationAttributes = [];
                        $skuAttr = explode(";", $item->commerceSkuAttr);
                        foreach ($skuAttr as $key => $skuValue) {

                            $skuValueArr = explode(":", $skuValue);
                            $attrNo = $skuValueArr[0];
                            $attrValueNo = $skuValueArr[1];

                            foreach ($data->attrNames as $key => $attrNameValue) {

                                if ($attrNameValue->attrNo == $attrNo) {
                                    $name = strtolower(str_replace(' ', '_', $attrNameValue->name));
                                    foreach ($attrNameValue->valueList as $key => $value) {
                                        if ($value->attrValueNo == $attrValueNo) {
                                            $variationAttributes[$name] = $value->attrValue;
                                        }
                                    }
                                }
                            }
                        }

                        try {

                            $variation = new WC_Product_Variation();
                            $variation->set_parent_id($id);
                            $variation->set_sku($item->skuId);
                            $variation->set_weight($item->weight);
                            $variation->set_stock_status($item->stock);
                            $variation->set_price($item->ecommercePrice);
                            $variation->set_regular_price($item->ecommercePrice);
                            $variation->set_sale_price($item->ecommercePrice);
                            $variation->set_attributes($variationAttributes);
                            $variation->save();

                        } catch (Exception $e) {
                            array_push($errorMsg, "S/N: {$sn} {$e->getMessage()}");
                            continue;
                        }

                    }

                } else {

                    $product->set_regular_price($data->showPrice);
                    $product->set_price($data->showPrice);
                    $product->set_sale_price($data->showPrice);
                    $product->set_stock_quantity($data->noSkuStock);
                    $product->set_weight($data->noSkuWeight);
                    $id = $product->save();
                }

                $product = wc_get_product($id);
                $res = $this->upImagesApi->attachmentImage($id, $data->mainImgSrc);
                $product->set_image_id($res[id]);

                $imgIds = [];
                foreach ($data->images as $key => $value) {
                    $res = $this->upImagesApi->attachmentImage($id, $value);
                    array_push($imgIds, $res[id]);
                }

                $product->set_gallery_image_ids($imgIds);
                $product->save();

                array_push($successMsg, "S/N: {$sn}  Import done");

            } else {
                array_push($errorMsg, "S/N: {$sn}  Import failed, product data error");
            }

        }

        wp_send_json_success(array_merge(array_unique($successMsg), array_unique($errorMsg)));

        die();
    }


    function activation()
    {
        echo 'The plugin was activation';
    }

    function deactivation()
    {
        echo 'The plugin was deactivation';

    }

    function uninstall()
    {
        echo 'Uninstall the success';
    }


}
