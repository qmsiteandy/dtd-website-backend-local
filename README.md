# DTD 數位系網後台改造 - 後端
## 專案介紹
過往使用 Wordpress 建立網站時，通常會直接使用內建的文章管理後台以及前端模板，數位系過去的系網也是如此運作。然而，如果我們想要自己建立前端頁面、擺脫制式模板，但同時想保有 Wordpress 方便的文章管理後台呢？ 這就是我擔任系網團隊期間的主要任務。

也就是改造成：前端使用 React，後台文章管理系統&資料庫則使用 Wordpress，並透過 Restful API 串接。

## 我的工作
於系網團隊中擔任後端組。其中我主要負責規劃文章資料分類、以及開發 Restful API 讓前端能撈取所需要的文章資訊。至於建立 Wordpress 及伺服器架設由另一位學長負責。

## 此 Repo 程式碼補充說明
這個 Repo 的內容是進行開發及測試時，在自己電腦建立 Local Wordpress 的資料，待測試功能完畢後才將程式碼複製到系網正式環境的程式碼中。而我撰寫的部分集中在 [這個位置](https://github.com/qmsiteandy/dtd-website-backend-local/tree/master/app/public/wp-content/mu-plugins)。

# 功能說明
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

## 教師資訊

## 畢業作品/課程作品

