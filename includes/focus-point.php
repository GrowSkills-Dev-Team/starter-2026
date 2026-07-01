<?php
/**
 * Focus Point
 * Permite definir um ponto focal (x%, y%) por USO da imagem — a mesma
 * imagem pode ter focus points diferentes em posts/páginas diferentes.
 *
 * Compatibilidade: mantém leitura das chaves antigas (_gs_focus_point_x /
 * _gs_focus_point_y) para não perder pontos focais já guardados antes desta
 * versão. Esses valores antigos funcionam como fallback "global".
 */

function gs_focus_point_meta_key(): string
{
    return '_gs_focus_point';
}

// ─── 1. SAVE FOCUS POINT VIA AJAX ────────────────────────────────────────────

add_action('wp_ajax_gs_save_focus_point', function () {
    check_ajax_referer('gs_focus_point_nonce', 'nonce');

    if (!current_user_can('upload_files')) {
        wp_send_json_error(__('No access.', 'gs'));
    }

    $attachment_id = intval($_POST['attachment_id'] ?? 0);
    $context_id    = sanitize_key($_POST['context_id'] ?? 'global');
    $x = max(0, min(100, floatval($_POST['x'] ?? 50)));
    $y = max(0, min(100, floatval($_POST['y'] ?? 50)));

    $data = get_post_meta($attachment_id, gs_focus_point_meta_key(), true);
    if (!is_array($data)) $data = [];

    $data[$context_id] = ['x' => $x, 'y' => $y];

    update_post_meta($attachment_id, gs_focus_point_meta_key(), $data);

    wp_send_json_success(['x' => $x, 'y' => $y]);
});

// ─── 2. GET FOCUS POINT VIA AJAX ─────────────────────────────────────────────

add_action('wp_ajax_gs_get_focus_point', function () {
    check_ajax_referer('gs_focus_point_nonce', 'nonce');

    $attachment_id = intval($_POST['attachment_id'] ?? 0);
    $context_id    = sanitize_key($_POST['context_id'] ?? 'global');

    wp_send_json_success(gs_get_focus_point($attachment_id, $context_id));
});

// ─── 3. ENQUEUE ADMIN JS + CSS ───────────────────────────────────────────────

add_action('admin_enqueue_scripts', function () {

    wp_enqueue_script(
        'gs-focus-point',
        THEME_URI . '/js/focus-point.js',
        ['jquery'],
        filemtime(THEME_PATH . '/js/focus-point.js'),
        true
    );

    wp_localize_script('gs-focus-point', 'gsFocusPoint', [
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('gs_focus_point_nonce'),
        'saved'      => __('Saved!', 'gs'),
        'saving'     => __('Saving...', 'gs'),
        'save'       => __('Save', 'gs'),
        'reset'      => __('Center (default)', 'gs'),
        'label'      => __('Image focus point (só nesta página)', 'gs'),
        'btnText'    => __('Set focus point', 'gs'),
        'modalTitle' => __('Click on the image to set the focus point', 'gs'),
        'close'      => __('Close', 'gs'),
    ]);

    wp_enqueue_style(
        'gs-focus-point-admin',
        THEME_URI . '/css/focus-point-admin.css',
        [],
        filemtime(THEME_PATH . '/css/focus-point-admin.css')
    );
});

// ─── 4. HELPER: valor legado (sistema antigo, antes dos contextos) ──────────

function gs_get_legacy_focus_point(int $attachment_id): ?array
{
    $x = get_post_meta($attachment_id, '_gs_focus_point_x', true);
    $y = get_post_meta($attachment_id, '_gs_focus_point_y', true);

    if ($x === '' && $y === '') {
        return null;
    }

    return [
        'x' => floatval($x !== '' ? $x : 50),
        'y' => floatval($y !== '' ? $y : 50),
    ];
}

// ─── 5. HELPER: GET FOCUS POINT PARA (attachment + contexto) ────────────────

function gs_get_focus_point(int $attachment_id, string $context_id = 'global'): array
{
    $data = get_post_meta($attachment_id, gs_focus_point_meta_key(), true);

    // 1) Valor específico deste contexto (novo sistema)
    if (is_array($data) && isset($data[$context_id])) {
        return [
            'x' => floatval($data[$context_id]['x'] ?? 50),
            'y' => floatval($data[$context_id]['y'] ?? 50),
        ];
    }

    // 2) Valor "global" gravado no novo sistema (ex: veio de outro contexto)
    if (is_array($data) && isset($data['global'])) {
        return [
            'x' => floatval($data['global']['x'] ?? 50),
            'y' => floatval($data['global']['y'] ?? 50),
        ];
    }

    // 3) Fallback: valor do sistema antigo (pré-contextos)
    $legacy = gs_get_legacy_focus_point($attachment_id);
    if ($legacy !== null) {
        return $legacy;
    }

    // 4) Nada gravado — centro
    return ['x' => 50.0, 'y' => 50.0];
}

// ─── 6. HELPER: CONTEXTO ATUAL NO FRONT-END ──────────────────────────────────
// Contexto = post + (se estivermos dentro de um bloco ACF) o ID único desse
// bloco. Isto separa automaticamente duas ocorrências da MESMA imagem em
// blocos DIFERENTES dentro do MESMO post — sem precisar de tocar em nenhum
// bloco, porque o ACF já expõe $block['id'] (estável, gravado no bloco)
// sempre que renderiza um bloco.

function gs_current_focus_context(): string
{
    global $post, $block;

    $context = 'global';

    if ($post instanceof WP_Post) {
        $context = 'post_' . $post->ID;
    }

    if (is_array($block) && !empty($block['id'])) {
        $context .= '_' . sanitize_key($block['id']);
    }

    return $context;
}

// ─── 7. HELPER: object-position CSS ──────────────────────────────────────────

function gs_focus_point_css(int $attachment_id, string $context_id = null): string
{
    $context_id = $context_id ?? gs_current_focus_context();
    $point = gs_get_focus_point($attachment_id, $context_id);

    if ($point['x'] === 50.0 && $point['y'] === 50.0) {
        return 'center center';
    }
    return $point['x'] . '% ' . $point['y'] . '%';
}

// ─── 8. APLICAR object-position VIA CSS PURO (sem JS) ────────────────────────
// object-fit:cover + object-position já faz nativamente o crop mantendo o
// ponto focal visível — não precisa de nenhum cálculo em JS.

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
    global $gs_focus_context_suffix;

    $context_id = gs_current_focus_context();
    if (!empty($gs_focus_context_suffix)) {
        $context_id .= '_' . sanitize_key($gs_focus_context_suffix);
    }

    $point = gs_get_focus_point($attachment->ID, $context_id);

    if ($point['x'] === 50.0 && $point['y'] === 50.0) {
        return $attr;
    }

    $position = esc_attr($point['x'] . '% ' . $point['y'] . '%');

    // Junta ao style existente em vez de o substituir (caso já haja um, ex: vindo de um block core)
    $existing_style = isset($attr['style']) ? rtrim(trim($attr['style']), ';') . '; ' : '';
    $attr['style'] = $existing_style . 'object-position: ' . $position . ';';

    return $attr;
}, 10, 2);