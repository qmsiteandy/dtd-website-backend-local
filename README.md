# DTD 數位系網後台改造 - 後端
## 專案介紹
過往使用 Wordpress 建立網站時，通常會直接使用內建的文章管理後台以及前端模板，數位系過去的系網也是如此運作。然而，如果我們想要自己建立前端頁面、擺脫制式模板，但同時想保有 Wordpress 方便的文章管理後台呢？ 這就是我擔任系網團隊期間的主要任務。

也就是改造成：前端使用 React，後台文章管理系統&資料庫則使用 Wordpress，並透過 Restful API 串接。

## 我的工作
於系網團隊中擔任後端組。其中我主要負責規劃文章資料分類、以及開發 Restful API 讓前端能撈取所需要的文章資訊。至於建立 Wordpress 及伺服器架設由另一位學長負責。

## 此 Repo 程式碼補充說明
這個 Repo 的內容是進行開發及測試時，在自己電腦建立 Local Wordpress 的資料，待測試功能完畢後才將程式碼複製到系網正式環境的程式碼中。而我撰寫的部分集中在 [這個位置](https://github.com/qmsiteandy/dtd-website-backend-local/tree/master/app/public/wp-content/mu-plugins)。

# 功能說明
大綱：  
- [客製化 Wordpress 後台介面，建立各文章類型](#客製化-wordpress-後台介面建立各文章類型-post-type)  
- [Router 路由設定](#router-路由設定)
- [首頁 Banner 圖片資料](#首頁-banner-圖片資料)
- [文章列表 (系務公告/師生榮譽榜)](#文章列表-系務公告師生榮譽榜)
- [單篇文章內容](#單篇文章內容)
- [教師資訊](#教師資訊)
- [畢業作品/課程作品](#畢業作品課程作品)



## 客製化 Wordpress 後台介面，建立各文章類型 (Post-Type)
### 說明
考量到系網許多頁面內容需經常修改，為方便系上助教調整，我規劃設計多種類型的「文章發布類型 Post-type」讓助教可以針對內容選取對應的發文方式，包含：
1. 一般文章 (系務公告/師生榮譽榜)
2. 首頁 Banner 圖片內容
3. 教師資料
4. 畢業作品/課程作品/研究成果內容  

並且設定每種文章類型擁有不同屬性資料可以填寫，將在後續說明。

![](https://i.imgur.com/5msrdhw.png)

### 設定
>設定文章類型的程式碼 [dtd-post-types.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/dtd-post-types.php)

使用 Wordpress 提供的 [register_post_type](https://developer.wordpress.org/reference/functions/register_post_type/) 功能註冊各類型的 post-type，並設定 Hook 在初始化程式時執行。如下方程式以 banner 類型為例：

```php
function prefix_register_dtd_routes_projects() {

    // 註冊 banner post-type
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
}
// Hook: init 時呼叫設定 post-type 的函式
add_action('init', 'prefix_register_dtd_routes_projects');
```

接著再到 wordpress 後台的自訂欄中規劃該 post-type 的屬性細節
> 這邊我選用 Wordpress 外掛 Advanced Custom Fields 快速建立屬性欄位。  
>詳見 [這篇文章](https://progressbar.tw/posts/180)

![](https://i.imgur.com/75ef8WS.png)

發表該類型文章時的畫面就會顯示設定好的欄位  
<img src="https://i.imgur.com/xqMs7uD.png" width="350px">

## Router 路由設定
設定各類型文章的撈取路由程式，例如 banner 類型的路由程式內容 [search-route-banner.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-banner.php)
```php
<?php
    function bannerSearchResults($data) {
        ... //運算

        return $results;
    }
```
接著在 [function.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/functions.php) 中引入，並設定 add_action 的 Hook 在 [rest_api_init](https://developer.wordpress.org/reference/hooks/rest_api_init/) 時呼叫函式建立路由。

以下方的 banner 路由為例，呼叫 Api 路徑 `{host}/dtd/v1/banner` 時就會運行 bannerSearchResults 函式並取得回傳資訊。

```php
<?php
    require ('search-route-banner.php');
    
    function dtd_custom_route() {
        
        register_rest_route('dtd/v1', 'banner', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'bannerSearchResults'
        ));
    }

    add_action('rest_api_init', 'dtd_custom_route');
```

## 首頁 Banner 圖片資料
### 設定
程式： [search-route-banner.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-banner.php)

使用 WP_Query 撈取 banners 類型的所有文章，並用 have_posts() 及 $mainQuery->the_post() 一一指引到對應文章，在將資訊存入陣列後回傳。

```php
<?php
    function bannerSearchResults($data) {
        // 使用 WP_Query 撈取文章
        $mainQuery = new WP_Query(array(
            'post_type' => 'banners', // 撈取 banners type
            'posts_per_page' => -1, //ALL
        ));

        $results = array();

        // 當 $mainQuery 有內容時觸發迴圈，類似 foreach 功能
        while($mainQuery->have_posts()) {
            $mainQuery->the_post(); // 指引到對應文章

            // 將撈取到的資訊一一放入陣列
            array_push($results, array(
                'id' => get_the_ID(),
                'bannerUrl' => get_field('bannerUrl')['url'],
                'link' => get_field('link'),
            ));
        }

        return $results;
    }
```

### 結果展示
<img src="https://i.imgur.com/X8SDU5l.png" width="350px"> 

![](https://i.imgur.com/nII3dY7.gif)

## 文章列表 (系務公告/師生榮譽榜)
系網的文章分為兩種
1. 系務公告
2. 師生榮譽榜

列表功能也一頁面需求分為三種：
1. 首頁需要從「系務公告」及「師生榮譽榜」各取最新五筆文章標題資訊
2. 系務公告頁需要撈取 20 筆文章，並實現分頁功能。
3. 師生榮譽榜同樣需要撈取 20 筆文章，並實現分頁功能。

>相關程式碼都在 [search-route-post.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-post.php)

### 首頁列表

`postHomePageSearchResults` 路由函式中負責撈取兩類文章各五筆資訊，運算邏輯如下：
1. 判斷是否有 queryString 參數 postPerGroup 設定取得筆數，若無則設定預設值為 5。
    ```php
    function postHomePageSearchResults($data) {
        //引數 (default = 5))
        $postPerGroup = ( $data['postPerGroup'] ) ? $data['postPerGroup'] : 5;
        ...
    ```
2. 宣告 `$result` 並設定內容
    ```php
    $results = array(
         array(
            'groupId' => 0,
            'title' => '系務公告 / Announcement',
            'list' => array() // 空陣列
         ),
         array(
            'groupId' => 1,
            'title' => '師生榮譽榜 / Achievement',
            'list' => array()
         ),
      );
    ```
3. 設定 `WP_Query` 抓取 `announcement` 分類的文章，並設定 `posts_per_page`。再用 `$mainQuery->have_posts()` 迴圈 及 `$mainQuery->the_post();` 指引到對應文章，將資訊一一放入 `$result` 陣列中。

    >其中 `IsWithinSevenDays()` 是用來判斷最新文章的自訂函式，邏輯為判斷發文時間是否距今 7 日內，並回傳 boolean 值。 


    ```php
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
    ```
4. 同第三步驟將另一分類文章的列表放入 $result 中，最後回傳給 Client。

### 「系務公告」及「師生榮譽榜」的列表 
1. 從 queryString 取得頁碼、每頁數量的參數，若無則設為預設值。
2. `WP_Query` 設定撈取對應文章項目
3. 使用迴圈將資料一一推入 `$result` 陣列中。
    >使用 `IsWithinSevenDays()` 自訂函式判斷是否為近期文章  
    >使用 `ConvertContentLabel()` 去除多餘的 HTML 符號

```php
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
        'isLatest' => IsWithinSevenDays(), //判斷是否為近期文章的自訂函式
        'content' => ConvertContentLabel(get_the_content()), //去除多餘的HTML符號
        ));
    }

    return $results;
}
```

### 列表功能展示
![](https://i.imgur.com/cgq2dMx.png)
![](https://i.imgur.com/DMHNWv3.png)

## 單篇文章內容
當使用者在列表中點選一篇文章時，呼叫取得單篇文章內容。
>相關程式碼都在 [search-route-post.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-post.php)

### 設定
路由設定邏輯如下：
1. `WP_Query` 撈取符合 queryString `$data['postID']` 的文章
2. 如果有符合的文章，取得該文章的永久連結
3. `wp_remote_get` 取得連結內容的 body
4. 用自訂函式 `ConvertContentLabel()` 消除不需要的 HTML 標籤符號
5. 最後將資料存入 `$result` 並回傳
>原本用 `wp_remote_get` 撈取的資訊會包含很多「\r」符號需要消除、並且為了將完整的HTML內容以字串形式回傳給 Client，需要將「"」符號修改成「\"」

```php
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
}
```

### 功能展示
經過設定後，可以將 Wordpress 後台發布的文章 HTML 內容，回傳至 Client 端。

後台內容：
![](https://i.imgur.com/tfzzHC0.png)

前端畫面：
![](https://i.imgur.com/22I9Z5z.png)

## 教師資訊
回傳所有教師資訊並以教學領域分組

>相關程式碼在 [search-route-staff.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-staff.php)

### 設定
1. 在路由函式中設定 `WP_Query` 搜尋方式，並加入 `s` 允許加入 queryString 參數 `term` 進行教師搜尋 
    ```php 
    function staffSearchResults($data) {
      $mainQuery = new WP_Query(array(
         'post_type' => 'staff',
         'posts_per_page' => -1, //ALL
         's' => sanitize_text_field($data['term']) //搜尋 query 參數
      ));
    ```
2. 如果取得的文章數大於一篇 (代表是要求所有教師資訊)，先建立 `$result` 內容
    ```php
    if($mainQuery->post_count > 1){
        $results = array(
            array(
                'groupid' => 0,
                'title' => "專任教師/互動科技領域",
                'list' => array(),
            ),
            array(
                'groupid' => 1,
                'title' => "專任教師/遊戲設計領域",
                'list' => array(),
            ),
            ...
        );
    ```
3. 使用迴圈將資訊一篇一篇加到 `$result` 中
    ```php
    while($mainQuery->have_posts()) {
        $mainQuery->the_post();

        $groupID = 0;
        switch(get_field('groupTitle')){
        case "專任教師/互動科技領域":
            $groupID = 0; break;
        case "專任教師/遊戲設計領域":
            $groupID = 1; break;
        ...
    ```
4. 取得教師的資訊並推入對應分組陣列中。這邊我設定 `ReturnStaffCollection()` 自訂函式用來控管要那些資料項目。
    ```php
        $collection = ReturnStaffCollection();
        array_push($results[$groupID]['list'], $collection);  
        
        ...
    ```
    ```php
    //用來控管需要哪些資料項目
    function ReturnStaffCollection(){
        $collection = array(
            'id' => get_the_ID(),
            'groupTitle' => get_field('groupTitle'),
            'sortWeight' => get_field('sortWeight'),
            'teacherName' => get_field('teacherName'),
            'englishName' => get_field('englishName'),
            'title' => get_field('title'),
            'phone' => get_field('phone'),
            'room' => get_field('room'),
            'website' => get_field('website'),
            'education' => get_field('education'),
            'website' => get_field('website'),
            'skill' => get_field('skill'),
            'email' => get_field('email'),
            'imgUrl' => get_field('imgUrl')['url'],);
        return $collection;
    }
    ```
5. 每增加一位教師資料後，依據排序權重使用 Selection sort 調整教師資料的順序。(系上要求頁面需依據教授值等排序)
    ```php
    //增加教師後，依據sort_wright排序
    for($i = 0; $i < count($results[$groupID]['list']); $i++){
        $top_sort_index = $i; //紀錄最大值的 index
        // 找尋最大值的 index
        for($j = $i + 1; $j < count($results[$groupID]['list']); $j++){
            if($results[$groupID]['list'][$j]['sortWeight'] > $results[$groupID]['list'][$top_sort_index]['sortWeight']){
                $top_sort_index = $j;
            }
        }
        //交換內容
        $t = $results[$groupID]['list'][$top_sort_index];
        $results[$groupID]['list'][$top_sort_index] = $results[$groupID]['list'][$i];
        $results[$groupID]['list'][$i] = $t;
    }
    ```
6. 回傳結果

### 功能展示
後台管理介面設定教師資訊
![](https://i.imgur.com/xAOTqXB.png)
    
前端頁面展示教師列表，點進去可以看到教師詳細資訊
![](https://i.imgur.com/U2GeZeq.png)
![](https://i.imgur.com/DWurMN3.png)

## 畢業作品/課程作品
用來刊登學生的作品，回傳內容依作品類型分組，並且每次作品排版順序都會不同。

>相關的程式碼  
> [search-route-classProject.php ](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-classProject.php)  
> [search-route-graduateProject.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/search-route-graduateProject.php)


### 建立作品標籤
首先需要在設定新的作品標籤，相關程式寫在 [dtd-post-types.php](https://github.com/qmsiteandy/dtd-website-backend-local/blob/master/app/public/wp-content/mu-plugins/dtd-post-types.php) 中。
```php
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
```
接著就能在這個文章類型中找到標籤 (不過我取名叫作品分類)，並在作品分類中建立多種課程作品標籤。
![](https://i.imgur.com/rYX3dMY.png)

## 程式設定
1. 路由函式中設定 `WP_Query` 搜尋全部作品，並設定 `'p' => $data['postID']` 提供使用 `queryString` 搜尋單筆文章內容的方法。假如只要一篇文章，那 
`WP_Query` 只會取得一篇資料，並直接回傳。

    ```php
    function classProjectSearchResults($data) {
        
        $mainQuery = new WP_Query(array(
            'post_type' => 'class_projects',
            'posts_per_page' => -1, //ALL
            'p' => $data['postID'],  //用PostID搜尋特定文章
        ));
        
        //如果要求特定文章，WP_Query應該只會取得一篇資料，就只回傳那篇
        if($data['postID']){
            if($mainQuery->have_posts()) {
                $mainQuery->the_post();
                $collection = ClassProject_ReturnCollection();
            }
            return $collection;
        }
        ...

    ```

2. 如果 queryString 有設定 `workType` 參數代表只要顯示某一項標籤的作品資料，會在撈取的所有作品使用迴圈，一一判定是否有符合的標籤，若有則插入到陣列中隨意位置。
    > - `IsPostHasTheTaxonomy($workType)` 函式來判斷現在迴圈指向的文章是否擁有該標籤。
    > - `RandomInsertCollection()` 函式用來將文章的內容插入到陣列中的隨意一個 index 以達到隨機排序作品的功能。
    ```php
    $workType = ($data['workType']) ;

    //如果要求特定作品分類
    if($workType != null){

        $results = array();
        array_push($results, array(
            'sortTitle' => $workType,
            'sortList' => array()
        ));

        while($mainQuery->have_posts()) {
            $mainQuery->the_post();

            //如果這篇文章有特定的分類標籤
            if(ClassProject_IsPostHasTheTaxonomy($workType)){
                $collection = ClassProject_ReturnCollection();
                $results[0]['sortList'] = ClassProject_RandomInsertCollection($results[0]['sortList'], $collection);
            }
        }
        return $results;
    }
    ```
3. 若沒有指定要哪一類則會將所有作品分類並回傳。首先取得所有分類項目並設定 `$result` 內容。
    ```php
    //取得全部的課程分類
        $taxonomy = get_terms([
            'taxonomy' => 'taxonomy_workType',
            'hide_empty' => false,
        ]);

        $results = array();

        for($i = 0; $i < count($taxonomy); $i++){
        array_push($results, array(
            'sortId' => $taxonomy[$i]->term_id,
            'sortTitle' => ClassProject_ReturnFiltedTaxonomyName($taxonomy[$i]->name),
            'sortList' => array()
        ));
    }
    ```
4. 透過迴圈 `while($mainQuery->have_posts()){}` 迴圈判斷每一項作品屬於哪個分類，並插到 `$result` 中的對應位置。
    > 同樣使用
    > - `IsPostHasTheTaxonomy($workType)` 函式來判斷現在迴圈指向的文章是否擁有該標籤。
    > - `RandomInsertCollection()` 函式用來將文章的內容插入到陣列中的隨意一個 index 以達到隨機排序作品的功能。

    ```php
    while($mainQuery->have_posts()) {
        $mainQuery->the_post();

        $collection = ClassProject_ReturnCollection();

        for($i = 0; $i < count($results); $i++){
            if(ClassProject_IsPostHasTheTaxonomy($results[$i]['sortTitle'])){
                $results[$i]['sortList'] = ClassProject_RandomInsertCollection($results[$i]['sortList'], $collection);
            }
        }
    }
    ```
5. 最後消除 `$result` 中沒有作品的標籤項目，並回傳結果。
    ```php
    //消除沒有作品的分類項目
    for($i = count($results) - 1; $i >= 0; $i--){
        if(count($results[$i]['sortList']) == 0){
            array_splice($results, $i, 1);
        }
    }
    return $results;
    ```

### 功能展示
![](https://i.imgur.com/qqHAvg1.png)

# 後記
目前網路上較少看到將 Wordpress 單純作為後端系統的專案，能實際參與開發讓我很有成就感。

雖然現在看程式並沒有使用到甚麼困難技術或演算邏輯，但我認為這項專案最困難的點在於「了解使用者的需求並做設計」。需要與系上助教不斷討論，了解它們在管理文章或系網資訊的習慣，並依據習慣將資料分類；同時還需要與前端不斷開會訂定 API 的呼叫方式及回傳格式；有時也需要針對資料撈取的演算邏輯做優化，以此提升系網 Loading 速度等等，有許多沒想過的細節。

經過半年開發總算完成預期功能，目前助教也能輕易上手使用，專案也要傳承給學弟妹了。待未來如果要開發自己的部落格網頁，我也期待自己能將這段經驗用上。