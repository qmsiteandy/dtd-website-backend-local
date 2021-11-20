<?php

   function postSearchResults($data) {

      $results = array();

      $mainQuery = new WP_Query(array(
         'post_type' => 'post',
         'p' => $data['postID']
      ));

      //以postID引數指定文章
      if($data['postID']){

         if($mainQuery->have_posts()) {
            $mainQuery->the_post();
            $url = get_permalink(get_the_ID());

            //wp_remote_get直接取得該文章single page的所有內容(以HTML傳送)
            $content = wp_remote_get($url)["body"];
            $content = ConvertContentLabel($content);

            $results = array(
               'id' => get_the_ID(),
               'title' => get_the_title(),
               'isLatest' => IsWithinSevenDays(),
               'content' => $content,
            );
         }
         return $results;
      }
      else{
         //取得所有post文章
         while($mainQuery->have_posts()) {
            $mainQuery->the_post();
   
            array_push($results, array(
               'id' => get_the_ID(),
               'groupTitle' => get_field('groupTitle'),
               'title' => get_the_title(),
               'isLatest' => IsWithinSevenDays(),
               'content' => get_the_content(),
            ));
         }
   
         return $results;
      }
   }


   //首頁最新系務公告、師生榮譽榜 各取五筆
   function postHomePageSearchResults($data) {

      //引數 (default = 5))
      $postPerGroup = ( $data['postPerGroup'] ) ? $data['postPerGroup'] : 5;

      $results = array(
         array(
            'groupId' => 0,
            'title' => '系務公告 / Announcement',
            'list' => array()
         ),
         array(
            'groupId' => 1,
            'title' => '師生榮譽榜 / Achievement',
            'list' => array()
         ),
      );

      //將系務公告的post放入result
      $mainQuery = new WP_Query(array(
         'post_type' => 'post',
         'category_name' => 'announcement',
         'ignore_sticky_posts' => true,   //設定至頂無效
         'posts_per_page' => $postPerGroup,
      ));

      while($mainQuery->have_posts()) {
         $mainQuery->the_post();

         array_push($results[0]['list'], array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'date' => get_the_date('Y/m/d'),
            'isLatest' => IsWithinSevenDays(),
         ));
      }

      //將師生榮譽的post放入result
      $mainQuery = new WP_Query(array(
         'post_type' => 'post',
         'category_name' => 'achievement',
         'ignore_sticky_posts' => true,   //設定至頂無效
         'posts_per_page' => $postPerGroup,
      ));

      while($mainQuery->have_posts()) {
         $mainQuery->the_post();

         array_push($results[1]['list'], array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'date' => get_the_date('Y/m/d'),
            'isLatest' => IsWithinSevenDays(),
         ));
      }

      return $results;
   }

   //師生榮譽榜葉面文章，預設一頁20筆
   function postAchievementsPageSearchResults($data) {
      $page = ( $data['page'] ) ? $data['page'] : 1;
      $postPerPage = ( $data['postPerPage'] ) ? $data['postPerPage'] : 20;

      $mainQuery = new WP_Query(array(
         'post_type' => 'post',
         'category_name' => 'achievement',
         'ignore_sticky_posts' => true,   //設定置頂無效
         'posts_per_page' => $postPerPage,   //每頁顯示N筆文章
         'paged' => $page  //切換到第幾頁
      ));

      $results = array();

      while($mainQuery->have_posts()) {
         $mainQuery->the_post();

         array_push($results, array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'isLatest' => IsWithinSevenDays(),
            'content' => ConvertContentLabel(get_the_content()),
         ));
      }

      return $results;
   }

    //系務公告頁面文章，預設一頁20筆
   function postAnnouncementsPageSearchResults($data) {
      $page = ( $data['page'] ) ? $data['page'] : 1;
      $postPerPage = ( $data['postPerPage'] ) ? $data['postPerPage'] : 20;

      $mainQuery = new WP_Query(array(
         'post_type' => 'post',
         'category_name' => 'announcement',
         'ignore_sticky_posts' => true,   //設定置頂無效
         'posts_per_page' => $postPerPage,   //每頁顯示N筆文章
         'paged' => $page  //切換到第幾頁
      ));

      $results = array();

      while($mainQuery->have_posts()) {
         $mainQuery->the_post();

         array_push($results, array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'isLatest' => IsWithinSevenDays(),
            'content' => ConvertContentLabel(get_the_content()),
         ));
      }

      return $results;
   }

   //去除多餘的HTML符號
   function ConvertContentLabel($content){
      $content = str_replace(array("\r"), '', $content); //將 \r 符號刪除
      $content = str_replace('\"', "\\\"", $content); //將 " 替換為 \"
      return $content;
   }

   //確認是否為七日內文章
   function IsWithinSevenDays(){
      $postDate = strtotime(get_the_date('Y/m/d'));   //轉換為Unix 時間戳
      $today = strtotime(date("Y/m/d"));   //取得今日Unix 時間戳
      $isWithinSevenDays = (($today - $postDate) / 3600 / 24) <= 7;
      return $isWithinSevenDays;
   }