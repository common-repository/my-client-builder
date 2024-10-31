<?php

if(isset($_POST["submit"])){
    $email = htmlentities($_POST["MERGE0"]);
    if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){
        if ( email_exists($email ) == false ) {
            $parts = explode("@", $email);
            $username = $parts[0];
            $user_id = username_exists( $username  );
            if($user_id){
                 $username = $email;
            }
            $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
            $user_id = wp_create_user( $username , $random_password, $email );
        }  
        $success_message = "Thanks For Subscribing!";
    }else{
        $error_message = "Error: Please enter a valid email";
    }
}

wp_enqueue_script('tcp-tcp',plugins_url('/gvate.js', __FILE__),array( 'jquery' ));
wp_enqueue_style('tcp-base',plugins_url('/template/base.css', __FILE__));
wp_enqueue_style('tcp-style',plugins_url('/template/style.css', __FILE__),array( 'tcp-base' ));

?>
<!DOCTYPE html>
<html>
<head>
<?php wp_head(); ?>
</head>
<body>
<?php
    echo '<div id="popup"><div id="popup-top">';
    $title = esc_attr( get_option('tcp_subscription_titile') );
    $newsletter_url = esc_attr( get_option('tcp_newsletter_url') );
    $subscription_label = esc_attr( get_option('tcp_subscription_label') );
    if(!empty($title)){
        echo "<p id='popup-title'>".$title ."</p>";
    }
    echo '<form action="'.$newsletter_url.'" method="post"><div id="popup-form">';
    echo '<input title="'.$subscription_label.'" class="tcp-input-text" type="text" name="MERGE0" value="'.$subscription_label.'" />';
    echo '<div id="popup-form-submit"><input type="submit"  name="submit" value="SUBSCRIBE"  class="input-submit" /></div></div></form></div><div id="popup-bottom">';
    if(!empty($error_message))
        echo "<p class='popup-error'>".$error_message."</p>";
    if(!empty($success_message))
        echo "<p class='popup-success'>".$success_message."</p>";
    echo '<h2>Before you leave, discover what\'s trending</h2><div class="gv-slider">';

    $args = array('posts_per_page'=>'-1','post_type'  => 'page','meta_key'   => 'tcp_sort','orderby'    => 'meta_value_num','order'      => 'DESC','meta_query' => array(array('key'=> 'tcp_enable', 'value'   => 1,),),);
    $query = new WP_Query( $args );
    while ( $query->have_posts() ) : $query->the_post(); 
    echo "<div class='slide'><div class='popup-page-image'><a target='_blank' href='".get_the_permalink($query->post->ID)."'>".get_the_post_thumbnail( $query->post->ID,array(280, 280))."</a></div><div class='popup-page-title'>".get_the_title( $query->post->ID )."</div></div>";
    endwhile;
    wp_reset_postdata();
    echo '</div></div></div></body></html>';