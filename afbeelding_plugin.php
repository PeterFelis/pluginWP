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






function haal_eerste_afbeelding_op()
{
    global $wpdb;
    $tabel_naam = 'ImageDetails';

    // SQL query opbouwen om slechts één resultaat op te halen
    $query = "SELECT * FROM $tabel_naam LIMIT 1";

    // Resultaat ophalen
    $result = $wpdb->get_row($query);

    // Controleer of er een resultaat is en retourneer de afbeeldings-URL
    if ($result) {
        return $result->image_url;
    }

    // Retourneer een lege string als er geen resultaat is
    return '';
}


function test_haal_afbeeldingen_op_shortcode()
{
    ob_start();  // Start output buffering

    // Roep je functie aan
    $result = haal_eerste_afbeelding_op();

    // Dump het resultaat
    var_dump($result);

    return ob_get_clean();  // Returneer en wis de output buffer
}
add_shortcode('test_haal_afbeeldingen_op', 'test_haal_afbeeldingen_op_shortcode');



/// rest api

add_action('rest_api_init', function () {
    register_rest_route('mijn-plugin/v1', '/afbeeldingen', array(
        'methods' => 'GET',
        'callback' => 'haal_afbeeldingen_op',
    ));
});



function haal_afbeeldingen_op($request)
{
    global $wpdb;
    $tabel_naam = 'ImageDetails';
    $query = "SELECT * FROM $tabel_naam";
    $results = $wpdb->get_results($query);
    $afbeeldingen = array();
    foreach ($results as $result) {
        $afbeeldingen[] = $result->image_url;
    }
    return new WP_REST_Response($afbeeldingen, 200);
}




//nodig voor opslaan in wordpress database
// 24-10 chat
function registreer_afbeeldingen_cpt()
{
    $args = array(
        'public' => true,
        'label'  => 'Afbeeldingen',
        'supports' => array('title', 'thumbnail'),
    );
    register_post_type('afbeelding', $args);
}
add_action('init', 'registreer_afbeeldingen_cpt');
