<?php
/**
 * Plugin Name: Local MailHog SMTP
 * Description: Routes local WordPress mail through MailHog.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'phpmailer_init', function ( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'mailhog';
    $phpmailer->Port = 1025;
    $phpmailer->SMTPAuth = false;
  } );

