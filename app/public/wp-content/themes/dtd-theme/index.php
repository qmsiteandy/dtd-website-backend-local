<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php wp_head(); ?>
</head>

<body>
    <?php get_header() ?>
    <?php
    while (have_posts()) {
        the_post() ?>
        <h2>
            <a href="<?php the_permalink() ?>">
                <?php the_title() ?>
            </a>
        </h2>
    <?php }
    ?>
    <?php get_footer() ?>
</body>

</html>
