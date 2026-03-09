<?php
/**
 * Plugin Name: VETTRYX WP Tracking Manager
 * Plugin URI:  https://github.com/vettryx/vettryx-wp-tracking-manager
 * Description: Gerenciador nativo e blindado para injeção de scripts de marketing (Analytics, Pixel, GTM, etc).
 * Version:     1.0.0
 * Author:      VETTRYX Tech
 * Author URI:  https://vettryx.com.br
 * License:     GPLv3
 */

// Segurança: Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Vettryx_Tracking_Manager {

    // Nome da chave no banco de dados (wp_options)
    private $option_name = 'vettryx_tracking_scripts';

    public function __construct() {
        // Hooks do painel de administração
        if ( is_admin() ) {
            add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
            add_action( 'admin_init', [ $this, 'register_settings' ] );
        } else {
            // Hooks do Front-end (Injeção dos scripts)
            // Prioridade 99 para rodar mais pro final, ou 1 para rodar no topo
            add_action( 'wp_head', [ $this, 'inject_head_scripts' ], 99 );
            add_action( 'wp_body_open', [ $this, 'inject_body_scripts' ], 1 );
            add_action( 'wp_footer', [ $this, 'inject_footer_scripts' ], 99 );
        }
    }

    /**
     * Cria o submenu dentro do menu principal do Core ("VETTRYX Tech")
     */
    public function add_submenu_page() {
        add_submenu_page(
            'vettryx-core-modules',               // Slug do menu pai (do Core)
            'Tracking Manager',                   // Título da página
            'Tracking Scripts',                   // Título no menu
            'manage_options',                     // Capacidade (Apenas administradores)
            'vettryx-tracking-manager',           // Slug desta página
            [ $this, 'render_admin_page' ]        // Função que desenha a tela
        );
    }

    /**
     * Registra a variável no banco de dados
     */
    public function register_settings() {
        register_setting( 'vettryx_tracking_group', $this->option_name, [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_scripts' ]
        ] );
    }

    /**
     * Sanitização Focada: NÃO removemos tags <script> ou <iframe>.
     * A segurança é garantida verificando a permissão do usuário que está salvando.
     */
    public function sanitize_scripts( $input ) {
        // Se não for administrador, aborta e mantém o que já estava no banco
        if ( ! current_user_can( 'manage_options' ) ) {
            return get_option( $this->option_name );
        }

        // Retorna o array bruto para manter a integridade dos códigos de rastreamento
        return [
            'head'   => isset( $input['head'] ) ? $input['head'] : '',
            'body'   => isset( $input['body'] ) ? $input['body'] : '',
            'footer' => isset( $input['footer'] ) ? $input['footer'] : '',
        ];
    }

    /**
     * Front-end: Injeta no <head>
     */
    public function inject_head_scripts() {
        $scripts = get_option( $this->option_name );
        if ( ! empty( $scripts['head'] ) ) {
            echo "\n\n" . $scripts['head'] . "\n";
        }
    }

    /**
     * Front-end: Injeta após abrir o <body>
     */
    public function inject_body_scripts() {
        $scripts = get_option( $this->option_name );
        if ( ! empty( $scripts['body'] ) ) {
            echo "\n\n" . $scripts['body'] . "\n";
        }
    }

    /**
     * Front-end: Injeta no <footer>
     */
    public function inject_footer_scripts() {
        $scripts = get_option( $this->option_name );
        if ( ! empty( $scripts['footer'] ) ) {
            echo "\n\n" . $scripts['footer'] . "\n";
        }
    }

    /**
     * Desenha a interface do formulário no painel
     */
    public function render_admin_page() {
        // Pega os scripts do banco, ou array vazio por padrão
        $scripts = get_option( $this->option_name, [ 'head' => '', 'body' => '', 'footer' => '' ] );
        ?>
        <div class="wrap">
            <h1><?php _e( 'VETTRYX Tech - Tracking Manager', 'vettryx-wp-core' ); ?></h1>
            <p><?php _e( 'Insira seus códigos de rastreamento abaixo. Os scripts são injetados de forma nativa para garantir a máxima performance.', 'vettryx-wp-core' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'vettryx_tracking_group' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="vettryx_head"><?php _e( 'Scripts no <head>', 'vettryx-wp-core' ); ?></label><br>
                            <small style="font-weight: normal; color: #666;"><?php _e( 'Ideal para Google Analytics, Meta Pixel, TikTok, etc.', 'vettryx-wp-core' ); ?></small>
                        </th>
                        <td>
                            <textarea name="<?php echo esc_attr( $this->option_name ); ?>[head]" id="vettryx_head" rows="8" class="large-text code" placeholder="<script>...</script>"><?php echo esc_textarea( $scripts['head'] ); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vettryx_body"><?php _e( 'Scripts após o <body>', 'vettryx-wp-core' ); ?></label><br>
                            <small style="font-weight: normal; color: #666;"><?php _e( 'Ideal para a tag <noscript> do Google Tag Manager.', 'vettryx-wp-core' ); ?></small>
                        </th>
                        <td>
                            <textarea name="<?php echo esc_attr( $this->option_name ); ?>[body]" id="vettryx_body" rows="8" class="large-text code" placeholder="<noscript>...</noscript>"><?php echo esc_textarea( $scripts['body'] ); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vettryx_footer"><?php _e( 'Scripts no <footer>', 'vettryx-wp-core' ); ?></label><br>
                            <small style="font-weight: normal; color: #666;"><?php _e( 'Scripts de menor prioridade ou widgets.', 'vettryx-wp-core' ); ?></small>
                        </th>
                        <td>
                            <textarea name="<?php echo esc_attr( $this->option_name ); ?>[footer]" id="vettryx_footer" rows="8" class="large-text code"><?php echo esc_textarea( $scripts['footer'] ); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( __( 'Salvar Scripts', 'vettryx-wp-core' ) ); ?>
            </form>
        </div>
        <?php
    }
}

// Inicia o módulo
new Vettryx_Tracking_Manager();
