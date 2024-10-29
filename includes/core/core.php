<?php

function balidrop_pages_menu()
{

    $foo = balidrop_config_menu();

    add_menu_page(
        $foo['title'],
        $foo['title'],
        $foo['capability'],
        $foo['key'],
        $foo['action'],
        $foo['icon']
    );

    if (isset($foo['submenu'])) foreach ($foo['submenu'] as $k => $v) {

        add_submenu_page(
            $foo['key'],
            $v['title'],
            $v['title'],
            $v['capability'],
            $k,
            $v['action']
        );
    }
}

add_action('admin_menu', 'balidrop_pages_menu');

function balidrop_config_menu()
{

    return [
        'key' => 'balidrop_product',
        'title' => 'balidrop',
        'action' => 'balidrop_pages_products',
        'icon' => 'dashicons-screenoptions',
        'capability' => 'activate_plugins',
        'submenu' => [
            'balidrop_product' => [
                'title' => __('Import Products', 'balidrop'),
                'capability' => 'activate_plugins',
                'action' => 'balidrop_pages_products',
            ],
            'balidrop_Home' => [
                'title' => __('Home', 'balidrop'),
                'capability' => 'activate_plugins',
                'action' => 'balidrop_pages_home',
            ]
        ]
    ];
}

function balidrop_pages_home()
{
    echo "<script> window.open('http://www.balidrop.com/balidrop/')</script>";
}

function balidrop_pages_products()
{
    $localize = array('ajaxurl' => admin_url('admin-ajax.php'));

    wp_enqueue_style('bd-bootstrap');
    wp_enqueue_style('bd-bootstrap-table');
    wp_enqueue_style('bd-style');
    wp_enqueue_script('product');
    wp_localize_script('product', 'product_script', $localize);
    require(BALIDROP_PATH . '/pages/product/product.php');
}


function balidrop_css_filter()
{

    $foo = [
        'bd-bootstrap' => BALIDROP_URL . '/src/css/bootstrap.min.css',
        'bd-bootstrap-table' => BALIDROP_URL . '/src/css/bootstrap-table.min.css',
        'bd-style' => BALIDROP_URL . '/src/css/style.css',
    ];

    foreach ($foo as $key => $val) {
        wp_register_style($key, $val, BALIDROP_VERSION);
    }

}

add_action('admin_init', 'balidrop_css_filter');


function balidrop_js_filter()
{

    $args = [
        'bd-bootstrap' => [
            'url' => BALIDROP_URL . '/src/js/bootstrap.min.js',
            'parent' => [],
            'ver' => '4.6.0'
        ],
        'bd-bootstrap-table' => [
            'url' => BALIDROP_URL . '/src/js/bootstrap-table.min.js',
            'parent' => [],
            'ver' => '1.18.2'
        ],
        'home' => [
            'url' => BALIDROP_URL . '/pages/home/home.js',
            'parent' => ['bd-bootstrap', 'bd-bootstrap-table'],
            'ver' => BALIDROP_VERSION
        ],
        'product' => [
            'url' => BALIDROP_URL . '/pages/product/product.js',
            'parent' => ['bd-bootstrap','bd-bootstrap-table'],
            'ver' => BALIDROP_VERSION
        ]
    ];

    wp_deregister_script('ellk-aliExpansion');
    foreach ($args as $key => $val) {

        wp_register_script(
            $key,
            $val['url'],
            $val['parent'],
            $val['ver'],
            true
        );
    }
}

add_action('admin_print_scripts', 'balidrop_js_filter');



