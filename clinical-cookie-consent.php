<?php
/**
 * Plugin Name: Clinical Cookie Consent
 * Description: A lightweight, style-controlled cookie consent banner with support for CSS variables and clean client-ready defaults.
 * Version:     1.0.1
 * Author:      Do The Site
 * Author URI:  https://dothesite.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: clinical-cookie-consent
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ClinicalCookieConsent' ) ) {
    class ClinicalCookieConsent {
        const OPTION_NAME = 'ccc_options';

        /**
         * Returns default plugin options.
         *
         * @return array
         */
        public static function defaults() {
            return array(
                'enabled'             => 1,
                'box_width_desktop'   => '480px',
                'box_width_mobile'    => '100%',
                'container_radius'    => '8px',
                'button_radius'       => '4px',
                'container_bg'        => '#0f172a',
                'container_text'      => '#ffffff',
                'accept_bg'           => '#16a34a',
                'accept_text'         => '#ffffff',
                'accept_hover_bg'     => '#15803d',
                'accept_hover_text'   => '#ffffff',
                'reject_bg'           => '#ef4444',
                'reject_text'         => '#ffffff',
                'reject_hover_bg'     => '#b91c1c',
                'reject_hover_text'   => '#ffffff',
                'required_bg'         => '#475569',
                'required_text'       => '#ffffff',
                'required_hover_bg'   => '#334155',
                'required_hover_text' => '#ffffff',
                'policy_link'         => '#93c5fd',
                'policy_link_hover'   => '#bfdbfe',
                'message'             => 'We use cookies to enhance your clinical experience. Please choose your preference.',
                'expiration_days'     => 180,
                'policy_url'          => '#',
            );
        }

        public function __construct() {
            // Register admin + frontend hooks.
            add_action( 'admin_menu', array( $this, 'register_menu' ) );
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
            add_action( 'wp_footer', array( $this, 'render_banner' ) );
        }

        /**
         * Activation hook ensures defaults exist.
         */
        public static function activate() {
            if ( ! get_option( self::OPTION_NAME ) ) {
                add_option( self::OPTION_NAME, self::defaults() );
            }
        }

        /**
         * Deactivation hook placeholder (kept for future use).
         */
        public static function deactivate() {}

        /**
         * Uninstall hook deletes options.
         */
        public static function uninstall() {
            delete_option( self::OPTION_NAME );
        }

        /**
         * Registers the settings page.
         */
        public function register_menu() {
            add_options_page(
                __( 'Clinical Cookie Consent', 'clinical-cookie-consent' ),
                __( 'Clinical Cookie Consent', 'clinical-cookie-consent' ),
                'manage_options',
                'clinical-cookie-consent',
                array( $this, 'render_settings_page' )
            );
        }

        /**
         * Registers settings, sections, and fields.
         */
        public function register_settings() {
            register_setting( 'ccc_settings', self::OPTION_NAME, array( $this, 'sanitize_options' ) );

            add_settings_section(
                'ccc_layout_section',
                __( 'Layout Controls', 'clinical-cookie-consent' ),
                '__return_false',
                'clinical-cookie-consent'
            );

            add_settings_section(
                'ccc_colors_section',
                __( 'Color Controls', 'clinical-cookie-consent' ),
                '__return_false',
                'clinical-cookie-consent'
            );

            add_settings_section(
                'ccc_content_section',
                __( 'Content + Behavior', 'clinical-cookie-consent' ),
                '__return_false',
                'clinical-cookie-consent'
            );

            $fields = $this->get_fields();

            foreach ( $fields as $field ) {
                add_settings_field(
                    $field['id'],
                    $field['title'],
                    array( $this, 'render_field' ),
                    'clinical-cookie-consent',
                    $field['section'],
                    $field
                );
            }
        }

        /**
         * Settings field definitions.
         *
         * @return array
         */
        private function get_fields() {
            return array(
                array(
                    'id'        => 'enabled',
                    'title'     => __( 'Enable Plugin', 'clinical-cookie-consent' ),
                    'type'      => 'checkbox',
                    'section'   => 'ccc_content_section',
                    'label_for' => 'enabled',
                    'desc'      => __( 'Toggle to enable or disable the banner.', 'clinical-cookie-consent' ),
                ),
                array(
                    'id'        => 'box_width_desktop',
                    'title'     => __( 'Box width (desktop)', 'clinical-cookie-consent' ),
                    'type'      => 'text',
                    'section'   => 'ccc_layout_section',
                    'label_for' => 'box_width_desktop',
                ),
                array(
                    'id'        => 'box_width_mobile',
                    'title'     => __( 'Box width (mobile)', 'clinical-cookie-consent' ),
                    'type'      => 'text',
                    'section'   => 'ccc_layout_section',
                    'label_for' => 'box_width_mobile',
                ),
                array(
                    'id'        => 'container_radius',
                    'title'     => __( 'Container border radius', 'clinical-cookie-consent' ),
                    'type'      => 'text',
                    'section'   => 'ccc_layout_section',
                    'label_for' => 'container_radius',
                ),
                array(
                    'id'        => 'button_radius',
                    'title'     => __( 'Button border radius', 'clinical-cookie-consent' ),
                    'type'      => 'text',
                    'section'   => 'ccc_layout_section',
                    'label_for' => 'button_radius',
                ),
                array(
                    'id'        => 'container_bg',
                    'title'     => __( 'Container background color', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'container_bg',
                ),
                array(
                    'id'        => 'container_text',
                    'title'     => __( 'Container text color', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'container_text',
                ),
                array(
                    'id'        => 'accept_bg',
                    'title'     => __( 'Accept button background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'accept_bg',
                ),
                array(
                    'id'        => 'accept_text',
                    'title'     => __( 'Accept button text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'accept_text',
                ),
                array(
                    'id'        => 'accept_hover_bg',
                    'title'     => __( 'Accept button hover background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'accept_hover_bg',
                ),
                array(
                    'id'        => 'accept_hover_text',
                    'title'     => __( 'Accept button hover text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'accept_hover_text',
                ),
                array(
                    'id'        => 'reject_bg',
                    'title'     => __( 'Reject button background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'reject_bg',
                ),
                array(
                    'id'        => 'reject_text',
                    'title'     => __( 'Reject button text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'reject_text',
                ),
                array(
                    'id'        => 'reject_hover_bg',
                    'title'     => __( 'Reject button hover background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'reject_hover_bg',
                ),
                array(
                    'id'        => 'reject_hover_text',
                    'title'     => __( 'Reject button hover text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'reject_hover_text',
                ),
                array(
                    'id'        => 'required_bg',
                    'title'     => __( 'Required button background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'required_bg',
                ),
                array(
                    'id'        => 'required_text',
                    'title'     => __( 'Required button text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'required_text',
                ),
                array(
                    'id'        => 'required_hover_bg',
                    'title'     => __( 'Required button hover background', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'required_hover_bg',
                ),
                array(
                    'id'        => 'required_hover_text',
                    'title'     => __( 'Required button hover text', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'required_hover_text',
                ),
                array(
                    'id'        => 'policy_link',
                    'title'     => __( 'Policy link text color', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'policy_link',
                ),
                array(
                    'id'        => 'policy_link_hover',
                    'title'     => __( 'Policy link hover color', 'clinical-cookie-consent' ),
                    'type'      => 'color',
                    'section'   => 'ccc_colors_section',
                    'label_for' => 'policy_link_hover',
                ),
                array(
                    'id'        => 'message',
                    'title'     => __( 'Message text', 'clinical-cookie-consent' ),
                    'type'      => 'textarea',
                    'section'   => 'ccc_content_section',
                    'label_for' => 'message',
                ),
                array(
                    'id'        => 'expiration_days',
                    'title'     => __( 'Cookie expiration (days)', 'clinical-cookie-consent' ),
                    'type'      => 'number',
                    'attr'      => array( 'min' => 1, 'max' => 730 ),
                    'section'   => 'ccc_content_section',
                    'label_for' => 'expiration_days',
                ),
                array(
                    'id'        => 'policy_url',
                    'title'     => __( 'Policy link URL', 'clinical-cookie-consent' ),
                    'type'      => 'url',
                    'section'   => 'ccc_content_section',
                    'label_for' => 'policy_url',
                ),
            );
        }

        /**
         * Renders individual field inputs.
         *
         * @param array $field Field config.
         */
        public function render_field( $field ) {
            $options = get_option( self::OPTION_NAME, self::defaults() );
            $value   = isset( $options[ $field['id'] ] ) ? $options[ $field['id'] ] : '';
            $attr    = '';

            if ( isset( $field['attr'] ) && is_array( $field['attr'] ) ) {
                foreach ( $field['attr'] as $key => $val ) {
                    $attr .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $val ) );
                }
            }

            switch ( $field['type'] ) {
                case 'checkbox':
                    printf(
                        '<label><input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s /> %4$s</label>',
                        esc_attr( $field['id'] ),
                        esc_attr( self::OPTION_NAME ),
                        checked( $value, 1, false ),
                        isset( $field['desc'] ) ? esc_html( $field['desc'] ) : ''
                    );
                    break;
                case 'textarea':
                    printf(
                        '<textarea id="%1$s" name="%2$s[%1$s]" rows="4" class="large-text"%4$s>%3$s</textarea>',
                        esc_attr( $field['id'] ),
                        esc_attr( self::OPTION_NAME ),
                        esc_textarea( $value ),
                        $attr
                    );
                    break;
                case 'number':
                    printf(
                        '<input type="number" id="%1$s" name="%2$s[%1$s]" value="%3$s"%4$s />',
                        esc_attr( $field['id'] ),
                        esc_attr( self::OPTION_NAME ),
                        esc_attr( $value ),
                        $attr
                    );
                    break;
                case 'color':
                    printf(
                        '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" placeholder="#000000 or var(--color)"%4$s />',
                        esc_attr( $field['id'] ),
                        esc_attr( self::OPTION_NAME ),
                        esc_attr( $value ),
                        $attr
                    );
                    break;
                case 'url':
                case 'text':
                default:
                    printf(
                        '<input type="%5$s" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text"%4$s />',
                        esc_attr( $field['id'] ),
                        esc_attr( self::OPTION_NAME ),
                        esc_attr( $value ),
                        $attr,
                        esc_attr( $field['type'] )
                    );
                    break;
            }
        }

        /**
         * Sanitizes and merges options.
         *
         * @param array $input Raw input.
         *
         * @return array
         */
        public function sanitize_options( $input ) {
            $defaults = self::defaults();
            $clean    = array();

            foreach ( $defaults as $key => $default ) {
                if ( 'enabled' === $key ) {
                    $clean[ $key ] = isset( $input[ $key ] ) ? 1 : 0;
                    continue;
                }

                if ( 'expiration_days' === $key ) {
                    $clean[ $key ] = max( 1, intval( $input[ $key ] ?? $default ) );
                    continue;
                }

                if ( in_array( $key, array( 'message' ), true ) ) {
                    $clean[ $key ] = sanitize_textarea_field( $input[ $key ] ?? $default );
                    continue;
                }

                if ( 'policy_url' === $key ) {
                    $clean[ $key ] = esc_url_raw( $input[ $key ] ?? $default );
                    continue;
                }

                if ( false !== strpos( $key, 'color' ) || false !== strpos( $key, '_bg' ) || false !== strpos( $key, '_text' ) || false !== strpos( $key, 'link' ) ) {
                    $clean[ $key ] = $this->sanitize_color( $input[ $key ] ?? $default, $default );
                    continue;
                }

                // Generic text values (widths, radii, etc.).
                $clean[ $key ] = sanitize_text_field( $input[ $key ] ?? $default );
            }

            return $clean;
        }

        /**
         * Color sanitization allowing hex, CSS variables, and color functions.
         */
        private function sanitize_color( $value, $default ) {
            $value = trim( (string) $value );

            // Allow CSS variable references such as var(--surface) or with fallbacks.
            if ( preg_match( '/^var\([^\)]+\)$/', $value ) ) {
                return $value;
            }

            // Allow common color functions (rgb/rgba/hsl/hsla).
            if ( preg_match( '/^(rgb|rgba|hsl|hsla)\([^\)]+\)$/i', $value ) ) {
                return $value;
            }

            // Allow shorthand and full hex colors.
            if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value ) ) {
                return $value;
            }

            return $default;
        }

        /**
         * Outputs the admin settings page.
         */
        public function render_settings_page() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Clinical Cookie Consent', 'clinical-cookie-consent' ); ?></h1>
                <form action="options.php" method="post">
                    <?php
                        settings_fields( 'ccc_settings' );
                        do_settings_sections( 'clinical-cookie-consent' );
                        submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        /**
         * Enqueues frontend CSS/JS when enabled.
         */
        public function enqueue_assets() {
            $options = get_option( self::OPTION_NAME, self::defaults() );

            if ( empty( $options['enabled'] ) ) {
                return;
            }

            $version = '1.0.1';

            wp_enqueue_style( 'ccc-frontend', plugins_url( 'assets/css/frontend.css', __FILE__ ), array(), $version );
            wp_enqueue_script( 'ccc-frontend', plugins_url( 'assets/js/frontend.js', __FILE__ ), array(), $version, true );

            wp_localize_script(
                'ccc-frontend',
                'cccConsentData',
                array(
                    'expirationDays' => intval( $options['expiration_days'] ),
                    'policyUrl'      => esc_url_raw( $options['policy_url'] ),
                    'preferencesKey' => 'ccc_choice',
                )
            );

            $css_vars = sprintf(
                ':root{--ccc-container-bg:%1$s;--ccc-container-text:%2$s;--ccc-accept-bg:%3$s;--ccc-accept-text:%4$s;--ccc-accept-hover-bg:%5$s;--ccc-accept-hover-text:%6$s;--ccc-reject-bg:%7$s;--ccc-reject-text:%8$s;--ccc-reject-hover-bg:%9$s;--ccc-reject-hover-text:%10$s;--ccc-required-bg:%11$s;--ccc-required-text:%12$s;--ccc-required-hover-bg:%13$s;--ccc-required-hover-text:%14$s;--ccc-policy-link:%15$s;--ccc-policy-link-hover:%16$s;--ccc-container-radius:%17$s;--ccc-button-radius:%18$s;--ccc-width-desktop:%19$s;--ccc-width-mobile:%20$s;}',
                $options['container_bg'],
                $options['container_text'],
                $options['accept_bg'],
                $options['accept_text'],
                $options['accept_hover_bg'],
                $options['accept_hover_text'],
                $options['reject_bg'],
                $options['reject_text'],
                $options['reject_hover_bg'],
                $options['reject_hover_text'],
                $options['required_bg'],
                $options['required_text'],
                $options['required_hover_bg'],
                $options['required_hover_text'],
                $options['policy_link'],
                $options['policy_link_hover'],
                esc_attr( $options['container_radius'] ),
                esc_attr( $options['button_radius'] ),
                esc_attr( $options['box_width_desktop'] ),
                esc_attr( $options['box_width_mobile'] )
            );

            wp_add_inline_style( 'ccc-frontend', $css_vars );
        }

        /**
         * Outputs the consent banner markup.
         */
        public function render_banner() {
            $options = get_option( self::OPTION_NAME, self::defaults() );

            if ( empty( $options['enabled'] ) ) {
                return;
            }

            $title   = __( 'Clinical Cookie Consent', 'clinical-cookie-consent' );
            $message = esc_html( $options['message'] );
            $policy  = esc_url( $options['policy_url'] );
            ?>
            <div id="ccc-banner" class="ccc-hidden" role="dialog" aria-live="polite" aria-label="<?php echo esc_attr( $title ); ?>">
                <div class="ccc-header">
                    <strong class="ccc-title"><?php echo esc_html( $title ); ?></strong>
                    <button type="button" class="ccc-close" aria-label="<?php esc_attr_e( 'Close banner', 'clinical-cookie-consent' ); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="ccc-message">
                    <p><?php echo $message; ?></p>
                    <?php if ( $policy ) : ?>
                        <a class="ccc-policy" href="<?php echo $policy; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read our policy', 'clinical-cookie-consent' ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="ccc-actions">
                    <button type="button" class="ccc-btn ccc-reject" data-ccc-action="reject"><?php esc_html_e( 'Reject All', 'clinical-cookie-consent' ); ?></button>
                    <button type="button" class="ccc-btn ccc-required" data-ccc-action="required"><?php esc_html_e( 'Required Only', 'clinical-cookie-consent' ); ?></button>
                    <button type="button" class="ccc-btn ccc-accept" data-ccc-action="accept"><?php esc_html_e( 'Accept All', 'clinical-cookie-consent' ); ?></button>
                </div>
            </div>
            <?php
        }
    }
}

register_activation_hook( __FILE__, array( 'ClinicalCookieConsent', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ClinicalCookieConsent', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'ClinicalCookieConsent', 'uninstall' ) );

new ClinicalCookieConsent();
