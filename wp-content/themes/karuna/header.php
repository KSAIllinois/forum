<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Karuna
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NV3PFBC');</script>
<!-- End Google Tag Manager -->
</head>

<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NV3PFBC"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'karuna' ); ?></a>

	<header id="masthead" class="site-header" role="banner">

		<?php // get_template_part( 'components/header/site', 'top-bar' ); ?>

		<?php get_template_part( 'components/header/site', 'branding' ); ?>

		<?php /* if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() ) ) : ?>
			<?php // Silence. ?>
		<?php elseif ( karuna_has_post_thumbnail() && karuna_jetpack_featured_image_display() && ( is_single() || is_page() ) ) : ?>
			<?php the_post_thumbnail( 'karuna-hero' ); ?>
		<?php elseif ( is_post_type_archive( 'jetpack-testimonial' ) ) :

				$jetpack_options = get_theme_mod( 'jetpack_testimonials' );

				if ( isset( $jetpack_options['featured-image'] ) && '' != $jetpack_options['featured-image'] ) :
					echo wp_get_attachment_image( (int)$jetpack_options['featured-image'], 'karuna-hero' );
				endif;

			elseif ( get_header_image() ) : ?>
			<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="" class="custom-header">
    <?php endif; // End featured/header image check. */ ?>

		<?php get_template_part( 'components/header/site', 'bottom-bar' ); ?>

	</header>
	<div id="content" class="site-content">
