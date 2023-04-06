<?php
/*
Plugin Name: Organizator
Description: O Organizator é um plugin para organizar posts com filtro de pesquisa e seleção por categorias
Version: 1.0
Author: Amaury Toledo
*/

function organizator_adicionar_metabox() {
    add_meta_box(
        'organizator-metabox',
        'Organizar post',
        'organizator_render_metabox',
        'post',
        'side',
        'default'
    );
}

function organizator_render_metabox() {
    global $wpdb;
    
    // Cria um seletor de categorias
    $categories = get_categories();
    echo '<label for="organizator-category">Categoria:</label> ';
    echo '<select name="organizator-category" id="organizator-category">';
    echo '<option value="">Todas as categorias</option>';
    foreach ($categories as $category) {
        echo '<option value="' . $category->slug . '">' . $category->name . '</option>';
    }
    echo '</select><br><br>';
    
    // Cria um campo de pesquisa
    echo '<label for="organizator-search">Buscar:</label> ';
    echo '<input type="text" name="organizator-search" id="organizator-search" value=""><br><br>';
    
    // Adiciona um script para lidar com a filtragem de resultados
    echo '<script>
        jQuery(document).ready(function($) {
            $("#organizator-category, #organizator-search").change(function() {
                var category = $("#organizator-category").val();
                var search = $("#organizator-search").val();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "organizator_filtrar_posts",
                        category: category,
                        search: search
                    },
                    success: function(result) {
                        $("#organizator-results").html(result);
                    }
                });
            });
        });
    </script>';
    
    // Adiciona um elemento para exibir os resultados filtrados
    echo '<div id="organizator-results"></div>';
}

function organizator_filtrar_posts() {
    global $wpdb;
    
    // Recupera as variáveis do filtro
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    // Constroi a query para recuperar os posts filtrados
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Exibe todos os posts
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );

    if (!empty($category)) {
        $args['category_name'] = $category;
    }

    if (!empty($search)) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);

    // Cria um loop para exibir os posts
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<p><a href="' . get_permalink() . '">' . get_the_title() . '</a></p>';
        }
        wp_reset_postdata();
    } else {
        echo '<p>Nenhum post encontrado.</p>';
    }

    // Encerra a execução da função
    wp_die();

}

// Registra as funções do plugin
add_action('add_meta_boxes','organizator_adicionar_metabox');
add_action('wp_ajax_organizator_filtrar_posts', 'organizator_filtrar_posts');
add_action('wp_ajax_nopriv_organizator_filtrar_posts', 'organizator_filtrar_posts');