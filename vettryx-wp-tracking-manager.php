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

// Evita conflitos de classe em ambientes multisite ou com múltiplos plugins
class Vettryx_Tracking_Manager {

    // Nome da opção no banco de dados para armazenar os IDs e scripts
    private $option_name = 'vettryx_tracking_data';

    // Construtor: Define hooks para admin e front-end
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
            add_action( 'admin_init', [ $this, 'register_settings' ] );
        } else {
            add_action( 'wp_head', [ $this, 'inject_head_scripts' ], 99 );
            add_action( 'wp_body_open', [ $this, 'inject_body_scripts' ], 1 );
        }
    }

    // Adiciona a página de configurações no menu do WordPress
    public function add_submenu_page() {
        add_submenu_page(
            'vettryx-core-modules',
            'Tracking Manager',
            'Tracking Manager',
            'manage_options',
            'vettryx-tracking-manager',
            [ $this, 'render_admin_page' ]
        );
    }

    // Registra as configurações para armazenar os IDs e scripts no banco de dados
    public function register_settings() {
        register_setting( 'vettryx_tracking_group', $this->option_name, [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_data' ]
        ] );
    }

    /**
     * Sanitiza os dados de entrada para garantir segurança e integridade
     */
    public function sanitize_data( $input ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return get_option( $this->option_name );
        }

        return [
            'gtm_id'   => sanitize_text_field( trim( $input['gtm_id'] ?? '' ) ),
            'ga4_id'   => sanitize_text_field( trim( $input['ga4_id'] ?? '' ) ),
            'pixel_id' => sanitize_text_field( trim( $input['pixel_id'] ?? '' ) ),
            'custom'   => $input['custom'] ?? '', // Mantém tags HTML intactas para emergências
        ];
    }

    /**
     * Injeta os scripts de rastreamento no <head> do site, otimizados para performance e compatibilidade
     */
    public function inject_head_scripts() {
        $data = get_option( $this->option_name, [] );
        $output = "";

        // 1. Google Tag Manager (GTM)
        if ( ! empty( $data['gtm_id'] ) ) {
            $gtm = esc_js( $data['gtm_id'] );
            $output .= "\n";
            $output .= "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n";
            $output .= "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n";
            $output .= "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n";
            $output .= "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n";
            $output .= "})(window,document,'script','dataLayer','{$gtm}');</script>\n";
        }

        // 2. Google Analytics 4 (GA4) - Caso não use GTM
        if ( ! empty( $data['ga4_id'] ) ) {
            $ga4 = esc_js( $data['ga4_id'] );
            $output .= "\n";
            $output .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga4}\"></script>\n";
            $output .= "<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag('js', new Date());\n  gtag('config', '{$ga4}');\n</script>\n";
        }

        // 3. Meta Pixel (Facebook) - Caso não use GTM
        if ( ! empty( $data['pixel_id'] ) ) {
            $pixel = esc_js( $data['pixel_id'] );
            $output .= "\n";
            $output .= "<script>\n!function(f,b,e,v,n,t,s)\n{if(f.fbq)return;n=f.fbq=function(){n.callMethod?\nn.callMethod.apply(n,arguments):n.queue.push(arguments)};\nif(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';\nn.queue=[];t=b.createElement(e);t.async=!0;\nt.src=v;s=b.getElementsByTagName(e)[0];\ns.parentNode.insertBefore(t,s)}(window, document,'script',\n'https://connect.facebook.net/en_US/fbevents.js');\nfbq('init', '{$pixel}');\nfbq('track', 'PageView');\n</script>\n";
        }

        // 4. Scripts Customizados de Emergência
        if ( ! empty( $data['custom'] ) ) {
            $output .= "\n" . $data['custom'] . "\n";
        }

        echo $output;
    }

    /**
     * Injeta o iframe do GTM no início do <body> para garantir que o GTM funcione mesmo com bloqueadores de anúncios, seguindo as melhores práticas recomendadas pelo Google
     */
    public function inject_body_scripts() {
        $data = get_option( $this->option_name, [] );
        
        if ( ! empty( $data['gtm_id'] ) ) {
            $gtm = esc_attr( $data['gtm_id'] );
            echo "\n\n";
            echo "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtm}\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
        }
    }

    // Renderiza a página de configurações no painel administrativo do WordPress, com campos para GTM, GA4, Pixel e scripts customizados
    public function render_admin_page() {
        $data = get_option( $this->option_name, [ 'gtm_id' => '', 'ga4_id' => '', 'pixel_id' => '', 'custom' => '' ] );
        ?>
        <div class="wrap">
            <h1><?php _e( 'VETTRYX Tech - Tracking Manager', 'vettryx-wp-core' ); ?></h1>
            <p><?php _e( 'Insira apenas os IDs de rastreamento. O sistema injetará os códigos otimizados automaticamente.', 'vettryx-wp-core' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'vettryx_tracking_group' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="gtm_id">Google Tag Manager ID</label></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[gtm_id]" id="gtm_id" value="<?php echo esc_attr( $data['gtm_id'] ); ?>" class="regular-text" placeholder="Ex: GTM-XXXXXXX">
                            <p class="description">Recomendado. Use o GTM para gerenciar as demais tags.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ga4_id">Google Analytics 4 ID</label></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[ga4_id]" id="ga4_id" value="<?php echo esc_attr( $data['ga4_id'] ); ?>" class="regular-text" placeholder="Ex: G-XXXXXXXXXX">
                            <p class="description">Preencha apenas se não estiver usando o GTM acima.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="pixel_id">Meta Pixel ID</label></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[pixel_id]" id="pixel_id" value="<?php echo esc_attr( $data['pixel_id'] ); ?>" class="regular-text" placeholder="Ex: 123456789012345">
                            <p class="description">Preencha apenas se não estiver usando o GTM acima.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="custom_script">Scripts Extras / Head</label></th>
                        <td>
                            <textarea name="<?php echo esc_attr( $this->option_name ); ?>[custom]" id="custom_script" rows="5" class="large-text code" placeholder="<script>...</script>"><?php echo esc_textarea( $data['custom'] ); ?></textarea>
                            <p class="description">Use apenas para códigos de verificação de domínio ou ferramentas de terceiros que não suportam GTM.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( 'Salvar Configurações' ); ?>
            </form>
        </div>
        <?php
    }
}

// Inicializa o plugin
new Vettryx_Tracking_Manager();
