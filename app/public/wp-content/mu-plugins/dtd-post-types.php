<?php

function prefix_register_dtd_routes_projects() {

   register_post_type('banners', array(
      'show_in_rest' => true,
      'rewrite' => array('slug' => 'banners'),
      'has_archive' => true,
      'supports' => array('title', 'thumbnail'),
      'public' => true,
      'labels' => array(
            'name' => '首頁輪播圖',
            'add_new' => '新增輪播圖',
            'edit_item' => '編輯輪播圖',
            'all_items' => '全部輪播圖',
            'singular_name' => 'Banners'
      ),
      'menu_icon' => 'dashicons-images-alt2'
   ));

   register_post_type('staff', array(
      'show_in_rest' => true,
      'rewrite' => array('slug' => 'staff'),
      'has_archive' => true,
      'supports' => array('title', 'thumbnail'),
      'public' => true,
      'labels' => array(
            'name' => '教職員',
            'add_new' => '新增教職員',
            'edit_item' => '編輯教職員',
            'all_items' => '全部教職員',
            'singular_name' => 'Staff'
      ),
      'menu_icon' => 'dashicons-admin-users'
   ));

   register_post_type('graduate_projects', array(
      'show_in_rest' => true,
      'rewrite' => array('slug' => 'graduate_projects'),
      'has_archive' => true,
      'supports' => array('title', 'thumbnail'),
      'public' => true,
      'labels' => array(
            'name' => '畢業專題',
            'add_new' => '新增專題',
            'edit_item' => '編輯專題',
            'all_items' => '全部專題',
            'singular_name' => 'graduate_projects'
      ),
      'menu_icon' => 'dashicons-welcome-learn-more'
   ));   

   register_post_type('class_projects', array(
      'show_in_rest' => true,
      'rewrite' => array('slug' => 'class_projects'),
      'has_archive' => true,
      'supports' => array('title', 'thumbnail'),
      'public' => true,
      'labels' => array(
            'name' => '課程作品',
            'add_new' => '新增作品',
            'edit_item' => '編輯作品',
            'all_items' => '全部作品',
            'singular_name' => 'class_projects'
      ),
      'menu_icon' => 'dashicons-archive'
   ));     

   register_post_type('excellent_projects', array(
      'show_in_rest' => true,
      'rewrite' => array('slug' => 'excellent_projects'),
      'has_archive' => true,
      'supports' => array('title', 'thumbnail'),
      'public' => true,
      'labels' => array(
            'name' => '研究成果',
            'add_new' => '新增成果',
            'edit_item' => '編輯成果',
            'all_items' => '全部成果',
            'singular_name' => 'excellent_projects'
      ),
      'menu_icon' => 'dashicons-image-filter'
   ));     
}
add_action('init', 'prefix_register_dtd_routes_projects');


//建立custom post-type中的分類
function create_taxonomies() 
{
   //class_projects、excellent_projects使用同一組分類項目
   register_taxonomy('taxonomy_workType',array('class_projects', 'excellent_projects' ), array(
    'hierarchical' => true,
    'labels' => array(
      'name' => '作品分類',
      'all_items' => '全部',
      'edit_item' => '編輯', 
    ),
    'show_in_rest' => true,
    'show_admin_column' => true,
    'show_ui' => true,
    'public' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'taxonomy_workType' ),
   ));
}
add_action( 'init', 'create_taxonomies');


