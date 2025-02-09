<?php
/**
 * components functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Karuna
 */


/**
* Modify the wp-login.php 'Back to' URL
* @param String $url
* @return String $url
*/
function prefix_home_url( $url ) {
    global $pagenow;
    if( 'wp-login.php' === $pagenow ) {
        $url = 'https://group.illinoisksa.org/';
    }
    return $url;
}
add_filter( 'home_url', 'prefix_home_url' );


/**
 * Disable side widget for bbPress
 */
function disable_all_widgets( $sidebars_widgets ) {       
    if ( function_exists('is_bbpress') ) {
        if (is_bbpress()) {
            $sidebars_widgets = array(false);
            remove_all_actions('bp_register_widgets');
            unregister_sidebar( 'bp_core_widgets' );
        }
    }
    return $sidebars_widgets;
}

add_filter('sidebars_widgets', 'disable_all_widgets', 1, 1);

/**
 * Add description under each forum in forum_index shortcode
 */
function rw_singleforum_description() {
  #echo '&lt;div class="bbp-forum-content"&gt;';
  echo bbp_forum_content();
  #echo '&lt;/div&gt;';
}
add_action( 'bbp_template_before_single_forum' , 'rw_singleforum_description');


/**
 * Change editor to Advanced Editor (ie. TinyMCE)
 */
function bbp_enable_visual_editor( $args = array() ) {
    $args['tinymce'] = true;
    return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );


/**
 * Remove forum & topic notice (last updated)
 */

function bbpress_return_blank() {
  return '';
}
add_filter( 'bbp_get_single_forum_description', 'bbpress_return_blank' );
add_filter( 'bbp_get_single_topic_description', 'bbpress_return_blank' );
//add_filter( 'bbp_get_single_topic_meta', 'bbpress_return_blank' );

//add_filter( 'bbp_get_forum_pagination_count', '__return_false' );

// used for below
// READ: https://stackoverflow.com/a/834355
function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if ( !$length ) return true;
    return substr( $haystack, -$length ) === $needle;
}

/**
 * Ultimate Member:
 * Register restricted to school email address (domain illinois.edu)
 * READ: https://wordpress.org/support/topic/registration-whitelist-email-domain/
 */
function um_custom_validate_email_register( $args ) {
	global $ultimatemember;

	$email = $args['user_email'];

	if ( !isset( $email ) || !endsWith( $email, '@illinois.edu' ) ) {
		$ultimatemember->classes['form']->add_error( 'user_email', 'You must register with a email address ending in @illinois.edu' );
  }
}

function um_custom_validate_email_login( $args ) {
  global $ultimatemember;

  var_dump($args);
  $myfile = fopen("args.txt", "w");
  fwrite($myfile, $args);
  fclose($myfile);
  $email = $args['username']; // accepts email for key 'username'
  
  if ( !isset( $email ) || !endsWith( $email, '@illinois.edu' ) ) {
    $ultimatemember->classes['form']->add_error( 'username', 'You must register with a email address ending in @illinois.edu' );
  }
}

/**
 * Custom validation for the field "E-mail Address" by domain on registration.
 * @author  Ultimate Member support <support@ultimatemember.com>
 * @param   array  $args
 */
// READ: https://docs.ultimatemember.com/article/62-block-any-registration-that-does-not-use-gmail-as-mail-provider
/*
function um_validate_email_domain( $args ) {

	// Change allowed email domains here
	$allowed_email_domains = apply_filters( 'um_allowed_email_domains', array(
			'gmail.com',
			'yahoo.com',
			'hotmail.com'
			) );

	// Change error message here
	$message = __( 'You can not use this email domain for registration', 'ultimate-member' );

	if ( isset( $args['user_email'] ) && is_email( $args['user_email'] ) ) {
		$email_domain = array_pop( explode( '@', trim( $args['user_email'] ) ) );
		if ( !in_array( $email_domain, $allowed_email_domains ) ) {
			UM()->form()->add_error( 'user_email', $message );
		}
	}
}
add_action( 'um_submit_form_errors_hook__registration', 'um_validate_email_domain', 20 );*/

#add_action('um_submit_form_errors_hook_','um_custom_validate_email', 999, 1);
add_action('um_submit_form_errors_hook__login', 'um_custom_validate_email_login', 1, 1);
add_action('um_submit_form_errors_hook__registration', 'um_custom_validate_email_register', 1, 1);


/*
 * Assign User to a Private Group on Registration
 * -- private group is used to restrict access to certain forums & topics
 */
function assignUserGroupOnRegistration( $user_id, $args ) {

  $groupList = [
    'group1', // Basic Member
    'group2', // Undergraduate
    'group3', // Graduate
    'group4', // Faculty / Professor
  ];

  $assigned_group = 'group2';

  $userGrade = get_user_meta( um_user("ID"), "grade", true ) ?: "";
  //$userMajor = get_user_meta( um_user("ID"), "major", true ) ?: "";

  #iconv('UTF-8', 'ASCII//TRANSLIT', '박사')

  if ($userGrade == '박사 (Doctorate)' || $userGrade == '석사 (MS)' ) $assigned_group = 'group3';
  if ($userGrade == 'Faculty' || $userGrade == 'Professor' ) $assigned_group = 'group4';

  update_user_meta( $user_id, 'private_group', $assigned_group );
  // update subscriptions
  if ( get_option( '_bbp_enable_subscriptions' ) ) rpg_amend_subscriptions ( $user_id );
}

add_action( 'um_registration_complete', 'assignUserGroupOnRegistration', 10, 2 );


/*
 * Show the custom meta keys & values on User Profile page
 * (namely "grade" and "major")
 */
// READ: https://gist.github.com/kwafoawua/34c3b7bc7d72e7215b0e63785437a6e1
function showExtraUserMetaFields() {

  $custom_fields = [
    "major" => "Major",
    "grade" => "Grade",
  ];

  foreach ($custom_fields as $key => $value) {
    $fields[ $key ] = array(
      "title" => $value,
      "metakey" => $key,
      "type" => "select",
      "label" => $value,
    );

    $field_value = get_user_meta(um_user("ID"), $key, true) ?: "";

    $html = '<div class="um-field um-field-'.$key.'" data-key="'.$key.'">
      <div class="um-field-label">
        <label for="'.$key.'">'.$value.'</label>
        <div class="um-clear"></div>
      </div>
      <div class="um-field-area">
        <input class="um-form-field invalid disabled "
          type="text"
          id="'.$key.'" value="'.$field_value.'"
          placeholder=""
          data-validate="" data-key="'.$key.'" disabled>
      </div>
    </div>';

    echo $html;
  }
  apply_filters("um_account_secure_fields", $fields, get_current_user_id() );

}
add_action("um_after_account_general", "showExtraUserMetaFields", 10);

// READ: https://gist.github.com/magnific0/29c32c7dabc89ab9cae5
/* ensure that the custom fields are updated when the account is updated */
/*add_action("um_account_pre_update_profile", "getUMFormData", 100);

function getUMFormData(){
$id = um_user("ID");
$names = array("major", "grade"); // ADD THE META-KEYS HERE

foreach( $names as $name )
update_user_meta( $id, $name, $_POST[$name] );
}*/


/*
 *
 * Karuna theme stuff
 *
 */
if ( ! function_exists( 'karuna_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the aftercomponentsetup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function karuna_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on components, use a find and replace
	 * to change 'karuna' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'karuna', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'karuna-featured-image', 685, 9999 );
	add_image_size( 'karuna-hero', 2000, 9999 );
	add_image_size( 'karuna-grid', 342, 228, true );
	add_image_size( 'karuna-thumbnail-avatar', 100, 100, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'menu-1' => esc_html__( 'Header', 'karuna' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for custom logos
	add_theme_support( 'custom-logo',
		array(
			'width'       => 1000,
			'height'      => 200,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'karuna_custom_background_args', array(
		'default-color' => 'ffffff',
	) ) );
}
endif;
add_action( 'after_setup_theme', 'karuna_setup' );

/**
 * Return early if Custom Logos are not available.
 */
function karuna_the_custom_logo() {
	if ( ! function_exists( 'the_custom_logo' ) ) {
		return;
	} else {
		the_custom_logo();
	}
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function karuna_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'karuna_content_width', 685 );
}
add_action( 'after_setup_theme', 'karuna_content_width', 0 );


/*
 * Adjust $content_width for full-width and front-page.php templates
 */

if ( ! function_exists( 'karuna_adjusted_content_width' ) ) :

function karuna_adjusted_content_width() {
	global $content_width;

	if ( is_page_template( 'templates/full-width-page.php' ) || is_page_template( 'front-page.php' ) || is_active_sidebar( 'sidebar-5' ) || is_active_sidebar( 'sidebar-4' ) ) {
		$content_width = 1040; //pixels
	}
}
add_action( 'template_redirect', 'karuna_adjusted_content_width' );

endif; // if ! function_exists( 'karuna_adjusted_content_width' )

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function karuna_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'karuna' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Full-Width Header', 'karuna' ),
		'id'            => 'sidebar-4',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Full-Width Footer', 'karuna' ),
		'id'            => 'sidebar-5',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1', 'karuna' ),
		'id'            => 'sidebar-2',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2', 'karuna' ),
		'id'            => 'sidebar-3',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3', 'karuna' ),
		'id'            => 'sidebar-6',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4', 'karuna' ),
		'id'            => 'sidebar-7',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'karuna_widgets_init' );

/**
 * Register Google Fonts
 */
function karuna_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
	 * supported by Karla, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$karla = esc_html_x( 'on', 'Karla font: on or off', 'karuna' );

	if ( 'off' !== $karla ) {
		$font_families = array();
		$font_families[] = 'Karla:400,400italic,700,700italic';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;

}

/**
 * Enqueue scripts and styles.
 */
function karuna_scripts() {
	wp_enqueue_style( 'karuna-style', get_stylesheet_uri() );

  #wp_enqueue_style( 'theme-custom-style', get_template_directory_uri().'/custom.min.css', array(), filemtime(get_template_directory_uri().'/custom.min.css') );
  wp_enqueue_style( 'theme-custom-style', get_template_directory_uri().'/custom.min.css', array(), time() );

	wp_enqueue_style( 'karuna-fonts', karuna_fonts_url(), array(), null );

	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/assets/fonts/genericons/genericons.css', array(), '3.4.1' );

	wp_enqueue_script( 'karuna-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'karuna-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true );

	wp_enqueue_script( 'karuna-functions', get_template_directory_uri() . '/assets/js/functions.js', array( 'jquery' ), '20160531', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'karuna_scripts' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and a 'Continue reading' link.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
if ( ! function_exists( 'karuna_excerpt_more' ) ) :
	function karuna_excerpt_more( $more ) {
		$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
			esc_url( get_permalink( get_the_ID() ) ),
			/* translators: %s: Name of current post */
			sprintf( esc_html__( 'Continue reading %s', 'karuna' ), '<span class="screen-reader-text">' . get_the_title( get_the_ID() ) . '</span>' )
			);
		return ' &hellip; ' . $link;
	}
	add_filter( 'excerpt_more', 'karuna_excerpt_more' );
endif;

/**
 * Custom header support
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}



/**
 * Load plugin enhancement file to display admin notices.
 */
require get_template_directory() . '/inc/plugin-enhancements.php';
