<?php

    function bannerSearchResults($data) {
        $mainQuery = new WP_Query(array(
            'post_type' => 'banners',
            'posts_per_page' => -1, //ALL
        ));

        $results = array();

        while($mainQuery->have_posts()) {
            $mainQuery->the_post();

            array_push($results, array(
                'id' => get_the_ID(),
                'bannerUrl' => get_field('bannerUrl')['url'],
                'link' => get_field('link'),
            ));
        }

        return $results;
    }