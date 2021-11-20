<?php

    require ('search-route-post.php');
    require ('search-route-page.php');
    require ('search-route-banner.php');
    require ('search-route-staff.php');
    require ('search-route-graduateProject.php');
    require ('search-route-classProject.php');
    require ('search-route-excellentProject.php');
    
    function dtd_custom_route() {
        register_rest_route('dtd/v1', 'banner', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'bannerSearchResults'
        ));
        register_rest_route('dtd/v1', 'post', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'postSearchResults'
        ));
        register_rest_route('dtd/v1', 'page/regulations', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'pageRegulationsSearchResults'
        ));
        register_rest_route('dtd/v1', 'post/homePage', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'postHomePageSearchResults'
        ));
        register_rest_route('dtd/v1', 'post/achievementsPage', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'postAchievementsPageSearchResults'
        ));
        register_rest_route('dtd/v1', 'post/announcementsPage', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'postAnnouncementsPageSearchResults'
        ));
        register_rest_route('dtd/v1', 'staff', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'staffSearchResults'
        ));
        register_rest_route('dtd/v1', 'graduateProject', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'graduateProjectSearchResults'
        ));
        register_rest_route('dtd/v1', 'classProject', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'classProjectSearchResults'
        ));
        register_rest_route('dtd/v1', 'excellentProject', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'excellentProjectSearchResults'
        ));
    }

    add_action('rest_api_init', 'dtd_custom_route');

