<?php // exit if uninstall constant is not defined
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// remove plugin options
delete_option('drewl_mp_options');
