<?php
class Front18_API {
    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        // Rota do Webhook: POST /wp-json/front18/v1/sync
        register_rest_route( 'front18/v1', '/sync', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'handle_webhook' ),
            'permission_callback' => array( $this, 'check_permission' )
        ) );
    }

    public function check_permission( WP_REST_Request $request ) {
        $api_key = get_option( 'front18_api_key', '' );
        if ( empty( $api_key ) ) return false;
        
        // Autorização via header Bearer
        $auth_header = $request->get_header( 'authorization' );
        if ( $auth_header && strpos( $auth_header, 'Bearer ' ) === 0 ) {
            $token = substr( $auth_header, 7 );
            if ( $token === $api_key ) return true;
        }
        
        // Ou autorização simples mandando a API key no payload JSON
        $body_key = $request->get_param( 'api_key' );
        if ( $body_key === $api_key ) return true;
        
        return false;
    }

    public function handle_webhook( WP_REST_Request $request ) {
        $rules = $request->get_param( 'rules' );
        
        if ( ! is_array( $rules ) ) {
            return new WP_Error( 'invalid_payload', __( 'Payload inválido. É esperado um objeto de regras.', 'front18' ), array( 'status' => 400 ) );
        }
        
        // Cuidado Extremo Nível Bancário: Sanitização das regras recebidas
        $sanitized_rules = array(
            'global' => ! empty( $rules['global'] ),
            'home'   => ! empty( $rules['home'] ),
            'cpts'   => ( isset( $rules['cpts'] ) && is_array( $rules['cpts'] ) ) ? array_map( 'sanitize_text_field', $rules['cpts'] ) : array()
        );
        
        $config_payload = $request->get_param( 'config' );
        if ( is_array( $config_payload ) ) {
            $sanitized_config = array(
                'display_mode'  => sanitize_text_field( $config_payload['display_mode'] ?? 'global_lock' ),
                'color_bg'      => sanitize_hex_color( $config_payload['color_bg'] ?? '#0f172a' ) ?: '#0f172a',
                'blur_amount'   => isset( $config_payload['blur_amount'] ) ? (int) $config_payload['blur_amount'] : 25,
                'blur_selector' => isset( $config_payload['blur_selector'] ) ? map_deep( wp_unslash( $config_payload['blur_selector'] ), 'sanitize_text_field' ) : 'img, video, iframe, [data-front18="locked"]',
            );
            update_option( 'front18_synced_config', $sanitized_config );
        }
        
        update_option( 'front18_synced_rules', $sanitized_rules );
        update_option( 'front18_last_sync', current_time( 'mysql' ) );
        
        return rest_ensure_response( array( 
            'success'   => true, 
            'message'   => __( 'Regras Sincronizadas com o SaaS via Push.', 'front18' ),
            'timestamp' => current_time( 'mysql' ), 
            'rules'     => $sanitized_rules 
        ) );
    }
}
