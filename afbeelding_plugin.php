<?php
/*
Plugin Name: Afbeelding plugin
Description: Een plugin om afbeeldingen uit een custom database tabel te tonen.
Version: 1.0
Author: Peter Felis & chat-gtp
*/

// Voorkom directe toegang tot het bestand.
if (!defined('ABSPATH')) {
    exit;
}

function toon_afbeeldingen()
{
    global $wpdb;
    $tabel_naam = 'ImageDetails';  // Pas aan indien nodig.
    $query = "SELECT id, image_url, image_text FROM $tabel_naam";
    $results = $wpdb->get_results($query);
    $output = '';
    $teller = 0;

    if ($results) {
        $output .= "<div class='image-grid'>";
        foreach ($results as $row) {
            $teller++;
            $image_url = $row->image_url;
            $small_image_url = str_replace('original', 'image-small', $image_url);
            $medium_image_url = str_replace('original', 'image-medium', $image_url);
            $large_image_url = str_replace('original', 'image-large', $image_url);
            $output .= "<div>";
            $output .= "<picture>";
            $output .= "<source srcset='" . esc_url($small_image_url) . "' media='(max-width: 767px)'>";
            $output .= "<source srcset='" . esc_url($medium_image_url) . "' media='(min-width: 768px) and (max-width: 1023px)'>";
            $output .= "<source srcset='" . esc_url($large_image_url) . "' media='(min-width: 1024px)'>";
            $output .= "<img src='" . esc_url($image_url) . "' alt='Fallback Image' loading='lazy' data-id='" . esc_attr($row->id) . "'>";
            $output .= "<p>" . esc_html($row->image_text) . "</p>";
            $output .= "</div>";
        }

        $output .= "</div>";
        $output .= "<p> Totaal " . $teller . "</p>";
    } else {
        $output .= "0 results";
    }

    return $output;
}

add_shortcode('toon_afbeeldingen', 'toon_afbeeldingen');




// met dynamische data, dit is gemaakt door chat
function haal_afbeeldingen_op($args = array())
{
    global $wpdb;
    $tabel_naam = $wpdb->prefix . 'ImageDetails';

    // Standaard argumenten
    $standaard_args = array(
        'aantal' => 10,
        // ... andere standaard argumenten
    );

    // Samenvoegen van argumenten
    $args = wp_parse_args($args, $standaard_args);

    // SQL query opbouwen
    $query = $wpdb->prepare(
        "SELECT * FROM $tabel_naam LIMIT %d",
        $args['aantal']
    );

    // Resultaten ophalen
    $results = $wpdb->get_results($query);

    return $results;
}
