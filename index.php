<?php
/*
Plugin Name: My Client Builder
Plugin URI: http://app.gvate.com/wordpress/
Description: Finally a solution developed by one of New York's Top 10 SEO companies (GVATE) to help you convert traffic into customers, subscribers and leads. 
Version: 1.2
Author: Tony
Author URI: http://app.gvate.com/wordpress/
*/

add_action( 'load-post.php', 'tcp_page_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'tcp_page_post_meta_boxes_setup' );
add_action( 'save_post', 'tcp_page_save_postdata' );
add_action('admin_menu', 'tcp_create_menu');
add_action( 'init', 'tcp_popup' );

function tcp_popup() {
    if($_GET["tpc_popup"] == 1){
    require_once("popup.php");
    die();
    }
}
function tcp_page_post_meta_boxes_setup() {
    add_action( 'add_meta_boxes', 'tcp_page_add_post_meta_boxes' );
}
function tcp_page_add_post_meta_boxes() {
    add_meta_box('page_section',__('My Client Builder Section', 'page_metabox' ),'tcp_page_post_class_meta_box','page','advanced','high');
}
function tcp_page_save_postdata( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
    if($_POST["tcp_enable"] == 1){
        update_post_meta($post_id , 'tcp_enable', 1);
    }else{
        delete_post_meta( $post_id, 'tcp_enable' );
    }
    if(isset($_POST["tcp_sort"]) && is_numeric($_POST["tcp_sort"])){
        update_post_meta($post_id , 'tcp_sort', sanitize_text_field( $_POST['tcp_sort'] ));
    }else{
        delete_post_meta( $post_id, 'tcp_sort' );
    }
}
function tcp_page_post_class_meta_box( $post ) {
    global $wpdb;
    echo "<style>#page-list label{width:50%;float:left}</style>";
    if(!empty($_SESSION["error"])){
        echo "<p style='color:red'>".$_SESSION["error"] ."</p>";
    }
    echo '<ul id="page-list"><li><label for="tcp_enable">'.__("Enable For My Client Builder:")."</label>";
    $gvtcpate_enable = "";
    if(get_post_meta($post->ID, 'tcp_enable',true) == 1)
        $tcp_enable = " checked " ;
    echo '<input type="checkbox" id="tcp_enable" autocomplete="off"  name="tcp_enable" '.$tcp_enable.' value="1"  /></li>';
    echo '<li><label for="tcp_sort">'.__("Sort Order For My Client Builder:").'</label><input type="text" id="tcp_sort" autocomplete="off"  name="tcp_sort" value="'.get_post_meta($post->ID, 'tcp_sort',true).'" size="25" /></li></ul>';

}
function tcp_create_menu() {
    add_menu_page('My Client Builder Settings', 'My Client Builder Settings', 'administrator','tcp_settings_page', 'tcp_settings_page',plugins_url('/images/small_logo.png', __FILE__));
    add_submenu_page('tcp_settings_page','Help', "Help", 'administrator','tcp_help_page', 'tcp_help_page');
    add_action( 'admin_init', 'register_tcp_settings' );
}
function tcp_help_page() {
?>
<style type="text/css">
fieldset{ border: 2px solid black;margin: 0;padding: 10px;}
legend{font-size:15pt;}
#featured-list{list-style:solid;padding-left: 15px;}
</style>
<div class="wrap">
    <h2>My Client Builder</h2>
    <fieldset><legend>Help | How To</legend>
    <ul id="featured-list"><li>Enable My Client Builder from My Client Builder Settings page.</li>
    <li>Select the template of choice from My Client Builder Settings page.</li>
    <li>If you have the template subscription license for premium version, enter it into the respective field.</li>
    <li>Enter Your Subscription Label.</li>
    <li>Enter Your Subscription Title and click Save.</li>
    <li>Open the pages that you want to add to the campaign, and enable that page in the My Client Builder Section.</li>
    <li>Set the order in which you’ll like to display that page on the campaign.</li>
    <li>To the right of the page of interest is a section titled featured image, this is where you select the image that you wish to represent that page with in your campaign.</li>
    <li>Update the page. You can go back to My Client Builder page to preview what your campaign will look like.</li>
    </ul>
    <p><strong>Note: Your campaign will not always show because we use your cache to detect if you are an immediately returning user within the same day. This is crucial to prevent your visitors from getting annoyed by a pop up always coming up.</strong></p>
    </fieldset>

    <fieldset><legend>How to Subscribe to Premium Plan</legend>
<ol>
    <li>Visit <a href="http://app.gvate.com/wordpress" target="_blank">http://app.gvate.com/wordpress</a></li>
    <li>Click on get more template button</li>
    <li>Select the plan that you are interested in</li>
    <li>Enter billing information</li>
    <li>Copy past your customer/transaction ID into the form that you were asked to copy paste it into.</li>
    <li>Click submit.</li>
    <li>You will receive the license key within 48 hours.</li>
    </ol>
 
    </fieldset>
</div>
<?php
}
function register_tcp_settings() {
	//register our settings
	register_setting( 'tcp-settings-group', 'tcp_notification_email' );
	register_setting( 'tcp-settings-group', 'tcp_newsletter_url' );
	register_setting( 'tcp-settings-group', 'tcp_subscription_label' );
	register_setting( 'tcp-settings-group', 'tcp_subscription_titile' );
	register_setting( 'tcp-settings-group', 'tcp_template' );
	register_setting( 'tcp-settings-group', 'tcp_license' );
	register_setting( 'tcp-settings-group', 'tcp_enabled' );
}
function tcp_deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::tcp_deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
function tcp_removeOldTemplateFiles(){
    $path_parts = pathinfo(__FILE__);
    $path = $path_parts["dirname"]."/template/";
    //$plugin_path = 
    $files = glob($path . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            tcp_deleteDir($file);
        } else {
            unlink($file);
        }
    }
}
function tcp_downloadTcpTemplate($template){
    $path_parts = pathinfo(__FILE__);
    $path = $path_parts["dirname"]."/template/";
    $domain = base64_encode($_SERVER["SERVER_NAME"]);
    $license = base64_encode(esc_attr( get_option('gvate_license')));
    $templates_json = file_get_contents('http://www.gvate.com/popup/api.php?template='.$template.'&domain='.$domain.'&license='.$license );
    $templates_array = json_decode($templates_json);
    file_put_contents($path."template_info.txt", "template:".$template);
    file_put_contents($path."style.css",base64_decode($templates_array->files->css));
    file_put_contents($path."base.css",base64_decode($templates_array->files->base_css));
    if(count($templates_array->files->images) > 0){
        mkdir($path."img/");
        chmod($path."img/", 0755); 
        foreach($templates_array->files->images as $im){
            file_put_contents($path."img/".$im->name,base64_decode($im->data));
        }
    }
}
function tcp_isNewTemplate($template){
    $path_parts = pathinfo(__FILE__);
    $path = $path_parts["dirname"]."/template/";
    $data = file_get_contents($path."template_info.txt");
    if($data == "template:".$template){
        return false;
    }
    return true;
}
function tcp_settings_page() {
    $selected_template = esc_attr( get_option('tcp_template') );
    $tcp_license = esc_attr( get_option('tcp_license') );
    if(tcp_isNewTemplate($selected_template)){
        tcp_removeOldTemplateFiles();
        tcp_downloadTcpTemplate($selected_template);
    }
?>
<style type="text/css">
fieldset{ border: 2px solid black;margin: 0;padding: 10px;}
legend{font-size:15pt;}
#templates-list li{float:left}
.points{list-style:solid;padding-left: 15px;}
.form-table .field-desc span {display: none;padding: 10px;}
.form-table label{float:left;}
.form-table .field-desc{float:left;margin-left: 5px;}
.form-table .field-desc span{display:none;padding:10px;}
.form-table .field-desc:hover span{display:block;position:absolute;background: none repeat scroll 0 0 white;}
.form-table .field-desc img{float:left;cursor: pointer;}
.form-table th{width:250px;}
.form-table input[type="radio"]{float:left;}
</style>
<div class="wrap">
    <h2>My Client Builder</h2>
    <fieldset><legend>About My Client Builder</legend>
    <p  style="float: left;font-size: 11pt;font-weight: bold;" ><a href="http://app.gvate.com/wordpress/" target="_blank"><img style="float:left;padding-right:10px;" src="<?php echo plugins_url('/images/logo.png', __FILE__) ?>" /></a>Ever felt that the work you pour into driving traffic to your business site does not significantly build your client list or increase revenue? Don't worry, we have  a revolutionary marketing solution that effectively ensures a substantial increase in traffic conversion.</p>
    <ul class="points"><li>A non intrusive exit intent technology that captures new visitors only when they are on their way to leaving the website</li>
    <li>Technology offers you a chance to showcase your hottest selling products or most popular pages to new and returning visitors on their way to leaving the website</li>
    <li>A subscription form is provided to allow new visitors to join your mailing list. (Activated on Premium Version)</li>
    <li>Growing database of Clean and Free Templates to match your website design and service</li>
    </ul>
    <h4>Features</h4>
    <ul class="points"><li><strong>Improve SEO</strong> - This app gets people to spend more time on your website and make more clicks which helps with SEO.</li>
    <li><strong>Add Titles</strong> - Add a title to your featured hottest product or pages.</li>
    <li><strong>Add Images</strong> - Add images of your hottest product or screenshot of your most popular page.</li>
    <li><strong>Add Redirect Links</strong> - Add Link to the Product or Page that you are featuring.</li>
    <li><strong>Support</strong> - 24/7 Support Service.</li>
    <li><strong>Subscriptions</strong> - Subscription form to allow users to subscribe to your newsletter or for great deals.</li>
    <li><strong>Featured Item</strong> - Showcase Up To 6 Products or Pages or a combination of both at a time on the light-box. (Note: Only 2 product or pages allowed for Free Plan)</li>
    <li><strong>Unlimited Modern Templates</strong> - Unlimited access to Free and growing database of clean light-box template designs.</li>
    </ul>
    <h4>Benefits of Upgrading To Premium Version:</h4>
    <ul class="points"><li>Unlimited Access to a Growing Database of Template</li>
    <li>Showcase Up To 6 Products at a time on the light-box</li>
    <li>Active Subscription box to enable Opt-ins</li>
    </ul>
    <h4>For support, please contact us at helpme@app.gvate.com</h4>
    </fieldset>
    <form method="post" action="options.php">
    <?php
    settings_fields( 'tcp-settings-group' );
    do_settings_sections( 'tcp-settings-group' );
    $enabled = esc_attr( get_option('tcp_enabled') );
    ?>
    <fieldset><legend>My Client Builder Campaign Settings</legend>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Enabled (<a target="_blank" href="<?php echo get_site_url();?>/?tpc_popup=1">Preview</a>)</th>
        <td><input type="radio" group="tcp_enabled" name="tcp_enabled" <?php if($enabled == 1){ echo "checked"; }; ?> value="1" /><label>Yes&nbsp;&nbsp;</label><input type="radio" group="tcp_enabled" <?php if($enabled == 0){ echo "checked"; }; ?> name="tcp_enabled" value="0" /><label>No</label></td>
        </tr>
        <tr valign="top">
        <th scope="row"><label>License For Premium Version</label><div class="field-desc"><img src="<?php echo plugins_url('/images/mark.jpg', __FILE__); ?>"><span>Enter your premium license is here</span></div></th>
        <td><input type="password" name="tcp_license" value="<?php echo esc_attr( get_option('tcp_license') ); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><label>Subscription Label</label><div class="field-desc"><img src="<?php echo plugins_url('/images/mark.jpg', __FILE__); ?>"><span>This label will be display inside the subscription box. e.g Subscribe now for a Free eBook. </span></div></th>
        <td><input type="text" name="tcp_subscription_label" value="<?php echo esc_attr( get_option('tcp_subscription_label') ); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><label>Subscription Titile</label><div class="field-desc"><img src="<?php echo plugins_url('/images/mark.jpg', __FILE__); ?>"><span>This is the title of the subscription form. e.g Enter your Email for more Great Deals.</span></div></th>
        <td><input type="text" name="tcp_subscription_titile" value="<?php echo esc_attr( get_option('tcp_subscription_titile') ); ?>" /></td>
        </tr>
    </table>
    </fieldset>
    <fieldset><legend>Templates</legend>
    <?php
    $domain = base64_encode($_SERVER["SERVER_NAME"]);
    $license = base64_encode(esc_attr( get_option('tcp_license')));
    $templates_json = file_get_contents('http://www.gvate.com/popup/api.php?domain='.$domain.'&license='.$license);
    $templates_array = json_decode($templates_json);

    echo '<ul style="width:100%;float:left;" id="templates-list">';
    if(isset($templates_array->templates)){
        foreach($templates_array->templates as $value){
            $selected_text = "";
            if($selected_template == $value->id){ $selected_text = "checked"; }
            echo '<li><input '.$selected_text.' class="template" class="radio" type="radio" name="tcp_template" value="'.$value->id.'" /><img width="100px" src="http://www.gvate.com/popup/WixApps/TrafficSuperGlue/template/'.$value->path.'/screenshot.jpg" alt="" /></li>';
        }
    }
    echo '</ul>';
    if($templates_array->premium == 0){
        echo "<p style='width='100%;float:left;'><a target='_blank' href='http://app.gvate.com/acceptstripepayments-checkout/'>Click here to get more template</a></p>";
    }
    echo '</fieldset>';
    submit_button();
    echo '</form>';
    echo '</div>';

}
function tcp_scripts() {
    $enabled = esc_attr( get_option('tcp_enabled') );
    if($enabled == 1){
        wp_enqueue_style( 'tcp-colorbox-css', plugins_url('/colorbox/colorbox.css', __FILE__) );
        wp_enqueue_script( 'gvtcpate-colorbox-script',plugins_url('/colorbox/colorbox.js', __FILE__) , array(), '1.0.0', true );
        wp_enqueue_script( 'tcp-script','//popup.gvate.com/popupout.js', array(), '1.0.0', true );
        wp_enqueue_script( 'popup-script',plugins_url('/popup_f.js', __FILE__) , array(), '1.0.1', true );
    }
}
add_action( 'wp_enqueue_scripts', 'tcp_scripts' );
function tcp_footer() {
    $enabled = esc_attr( get_option('tcp_enabled') );
    if($enabled == 1){
        echo '<div id="mcb-popup" class="fancybox.iframe" href="'.get_site_url().'/?tpc_popup=1" ></div>';
        //echo '<div style="display:none" id="mcb-popupcontent">xxxxx</div>';
    }
}
add_action('wp_footer', 'tcp_footer', 100);