<?php

   //規章辦法頁面
   function pageRegulationsSearchResults($data) {

      //找尋符合標題的page post
      $mainQuery = new WP_Query(array(
         'post_type' => 'page',
         's' => '規章辦法'
      ));

      //指引到對應的post
      $mainQuery->the_post();

      $results = array(
         'id' => get_the_ID(),
         'title' => get_the_title(),
         'content' => array()
      );

      //取得原始content，內容類似HTML
      $content = get_the_content();
      //去除換行符號
      $content = str_replace("\n", "", $content);

      //找尋所有包在<h2>中的內容，findPattern是preg_match_all搜尋的目標格式
      $findPattern = "#<h2>(.*?)</h2>#";
      preg_match_all( $findPattern, $content, $matches_h2);

      //$results['content']中建立陣列
      foreach($matches_h2[1] as $h2){
         array_push($results['content'], array(
            'subtitle' => $h2,
            'documentList' => array()
           ));
      }
      
      //找尋所有包在<ul>中的內容，findPattern是preg_match_all搜尋的目標格式
      $findPattern = "#<ul>(.*?)</ul>#";
      preg_match_all( $findPattern, $content, $matches_ul);

      for($i = 0; $i< count($matches_ul[1]); $i++){

         //找尋此ul中所有包在<ui>中的內容，findPattern是preg_match_all搜尋的目標格式
         $findPattern = "#<li>(.*?)</li>#";
         preg_match_all( $findPattern, $matches_ul[1][$i], $matches_li);

         for($j = 0; $j < count($matches_li[1]); $j++){

            $li_content = $matches_li[1][$j];

            //去除<li>
            $li_content = str_replace("<li>", "", $li_content);
            $li_content = str_replace("</li>", "", $li_content);

            //插入資料
            array_push($results['content'][$i]['documentList'], $li_content);
         }
      }

      return $results;
   }

   