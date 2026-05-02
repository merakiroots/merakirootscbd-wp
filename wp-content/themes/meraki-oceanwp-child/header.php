<?php
/**
 * Meraki Roots custom header.
 */
defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="mr-skip-link screen-reader-text" href="#mr-main"><?php esc_html_e('Skip to content', 'meraki-roots'); ?></a>
<?php get_template_part('template-parts/meraki/announcement-bar'); ?>
<?php get_template_part('template-parts/meraki/header-desktop'); ?>
<?php get_template_part('template-parts/meraki/header-mobile'); ?>
<main id="mr-main" class="mr-site-main">
