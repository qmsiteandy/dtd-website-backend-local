<?php

   function graduateProjectSearchResults($data) {
      
      $mainQuery = new WP_Query(array(
         'post_type' => 'graduate_projects',
         'posts_per_page' => -1, //ALL
         'p' => $data['postID'],  //用PostID搜尋特定文章
         'meta_value' => $data['graduateYear'], //當request帶有graduateYear=XXX，只顯示XXX學年組的文章
      ));

      //要求回傳單一筆文章
      if($data['postID']){
         if($mainQuery->have_posts()) {
            $mainQuery->the_post();
            $results = GraduateProject_ReturnCollection();
         }
         return $results;
      }
      //如果沒有要求特定文章，就回傳全部文章或是單獨某個學年組的文章
      else{
         $gradRange = [100, 150];

         $results = array();
         //先建立所有年份的資料空間，後續再將多餘的刪除
         for($i = $gradRange[1]; $i >= $gradRange[0]; $i--){
            array_push($results,array(
               'sortTitle' => $i,
               'sortList' => array()
            ));
         }

         while($mainQuery->have_posts()) {
            $mainQuery->the_post();

            $sortTitle = get_field('sortTitle');

            $collection = GraduateProject_ReturnCollection();
            //將collection放入對應的年份
            $results[$gradRange[1] - $sortTitle]['sortList'] = GraduateProject_RandomInsertCollection($results[$gradRange[1] - $sortTitle]['sortList'], $collection);
         }
         //刪除空白沒有資料的年份
         for($i = $gradRange[1] - $gradRange[0]; $i >= 0; $i--){
            if(count($results[$i]['sortList']) == 0){
               array_splice($results, $i, 1);
               $results = array_values($results);
            }
         }
         return $results;
      }
   }

   //統整作品輸出格式
   function GraduateProject_ReturnCollection(){
      $collection = array(
         'id' => get_the_ID(),
         'workTitle' => get_field('workTitle'),
         'workImgUrl' => get_field('workImgUrl')['url'],
         'introduction' => get_field('introduction'),
         'author' => get_field('author'),
         'instructor' => get_field('instructor'),
         'honor' => get_field('honor'),
         'relatedLinks' => array(
            array(
               'linkTitle' => get_field('relatedLinks')['link1']['linkTitle'],
               'linkUrl' => get_field('relatedLinks')['link1']['linkUrl'],
            ),  
            array(
               'linkTitle' => get_field('relatedLinks')['link2']['linkTitle'],
               'linkUrl' => get_field('relatedLinks')['link2']['linkUrl'],
            ),  
            array(
               'linkTitle' => get_field('relatedLinks')['link3']['linkTitle'],
               'linkUrl' => get_field('relatedLinks')['link3']['linkUrl'],
            ),  
            array(
               'linkTitle' => get_field('relatedLinks')['link4']['linkTitle'],
               'linkUrl' => get_field('relatedLinks')['link4']['linkUrl'],
            ),  
         )
      );
      //消除該筆資料relatedLinks中空白的項目
      for($i = count($collection['relatedLinks']) - 1; $i >= 0; $i--){
         if($collection['relatedLinks'][$i]['linkUrl'] == ''){
            array_splice($collection['relatedLinks'], $i, 1);
         }
      }

      return $collection;
   }

   //無法直接插入在某index資料會出問題，因此使用此函式
   function GraduateProject_RandomInsertCollection($array, $collection){
      
      //先插在最後一項
      array_push($array, $collection);

      $lastIndex = count($array) - 1;

      //再取隨機一項進行交換
      $randomIndex = rand(0, count($array) - 1);
      if($randomIndex != $lastIndex){
         
         $storage = array();

         $storage = $array[$randomIndex];
         $array[$randomIndex] = $array[$lastIndex];
         $array[$lastIndex] = $storage;
      }

      return $array;
   }