<?php

/**
 * Plugin Name: WP Table Exporter
 * Description: Adds export functionality to admin dashboard tables, allowing users to choose which rows and columns to export as CSV.
 * Version: 1.0.2
 * Author: Diogo Carvalho
 */

function wpte_enqueue_scripts()
{
  // Enqueue the CSS file
  wp_enqueue_style('wpte-style', plugin_dir_url(__FILE__) . 'css/wpte.css');

  // Enqueue the JS file
  wp_enqueue_script('wpte-script', plugin_dir_url(__FILE__) . 'js/wpte.js', array('jquery'), '1.0.2', true);
}

// Hook the function to load scripts in the admin footer
add_action('admin_enqueue_scripts', 'wpte_enqueue_scripts');
