<?php
/*
Plugin Name: Enlaces relacionados a Mitad del Post
Plugin URI: https://jagonzalez.org/
Description: Este plugin muestra enlaces a artículos relacionados basados en las palabras clave de Rank Math SEO a mitad del articulo.
Version: 1.0
Author: belial9826
Author URI: https://jagonzalez.org/
License: GPL2
*/

function func_ja_linkspost() {
  wp_enqueue_style( 'plug-linkpost', plugins_url( 'assets/css/plugstyles.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'func_ja_linkspost' );

// Código del plugin aquí

// Obtener palabras clave de Rank Math SEO
/*function mi_plugin_palabras_clave() {
  ob_start();
  rank_math_the_breadcrumbs();
  $palabras_clave = ob_get_clean();
  return $palabras_clave;
}*/

// Obtener palabras clave del focus keyword de Rank Math SEO
function mi_plugin_palabras_clave() {
  $palabras_clave = '';
  if (class_exists('RankMath') && is_singular('post')) {
    $focus_keyword = get_post_meta(get_the_ID(), 'rank_math_focus_keyword', true);

    if ($focus_keyword) {
      $palabras_clave = $focus_keyword;
    }
  }
  return $palabras_clave;
}

// Mostrar enlaces a artículos relacionados
/*function mi_plugin_articulos_relacionados() {
  $palabras_clave = mi_plugin_palabras_clave();
  $args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 4,
    'orderby' => 'rand',
    'tax_query' => array(
      array(
        'taxonomy' => 'category',
        'field' => 'slug',
        'terms' => $palabras_clave,
        'operator' => 'IN'
      )
    )
  );
  $articulos = new WP_Query($args);
  if ($articulos->have_posts()) {
    echo '<h3>Artículos Relacionados</h3>';
    echo '<ul>';
    while ($articulos->have_posts()) {
      $articulos->the_post();
      echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
    }
    echo '</ul>';
    wp_reset_postdata();
  }
}*/

// Obtener artículos relacionados basados en el contenido
function mi_plugin_articulos_relacionados() {
  $enlaces = '';
  $palabras_clave = mi_plugin_palabras_clave();
  if ($palabras_clave) {
    $args = array(
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => 4,
      'orderby' => 'rand',
      's' => $palabras_clave,
      'post__not_in' => array(get_the_ID())
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
      $enlaces .= '<div class="titulo">Contenido Recomendado</div>';
      $enlaces .= '<ul>';
      while ($query->have_posts()) {
        $query->the_post();
        $enlaces .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
      }
      $enlaces .= '</ul>';
    }
    wp_reset_postdata();
  }
  return $enlaces;
}

// Mostrar enlaces a artículos relacionados en la mitad del contenido del post
/*function mi_plugin_mostrar_articulos_relacionados($content) {
  if (is_single()) {
    $mitad = strlen($content) / 2;
    $antes = substr($content, 0, $mitad);
    $despues = substr($content, $mitad);
    $enlaces = mi_plugin_articulos_relacionados();
    return $antes . $enlaces . $despues;
  }
  return $content;
}
add_filter('the_content', 'mi_plugin_mostrar_articulos_relacionados');*/


function mi_plugin_mostrar_articulos_relacionados($contenido) {
  $enlaces = mi_plugin_articulos_relacionados();
  if ($enlaces) {
    $parrs = explode('</p>', $contenido);
    $num_parrs = count($parrs);
    $mitad_parrs = ceil($num_parrs / 2);
    $ultimo_parr = '';
    $num_palabras = 0;
    for ($i = $mitad_parrs - 1; $i < $num_parrs; $i++) {
      $parr = $parrs[$i] . '</p>';
      $num_palabras += str_word_count(strip_tags($parr));
      if ($num_palabras > 50) {
        $ultimo_parr = $parr;
        break;
      }
    }
    $enlaces = '<div class="plug-linkspost">' . $enlaces . '</div>';
    $contenido = str_replace($ultimo_parr, $ultimo_parr . $enlaces, $contenido);
  }
  return $contenido;
}
add_filter('the_content', 'mi_plugin_mostrar_articulos_relacionados');