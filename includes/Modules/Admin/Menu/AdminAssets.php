<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Admin\Menu;

use MagicalConnection\Support\AjaxNonce;

class AdminAssets
{

    private AdminMenu $adminMenu;

    public function __construct(AdminMenu $adminMenu)
    {
        $this->adminMenu = $adminMenu;
    }

    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'loadAssets']);
        add_action('admin_print_footer_scripts', [ $this , 'FrontEndTranslator' ] , 999999 );
    }

    public function loadAssets(string $hook): void
    {
        wp_enqueue_style(
            MAGICAL_CONNECTION_TEXT_DOMAIN . "-dialogs",
            MAGICAL_CONNECTION_URL . 'assets/css/admin/dialogs/transfer.css',
            [],
            MAGICAL_CONNECTION_VERSION
        );

        wp_enqueue_script('mgs-vue-source', MAGICAL_CONNECTION_URL . 'assets/js/vue.js', [], MAGICAL_CONNECTION_VERSION, false);
        wp_enqueue_script('mgs-axios-source', MAGICAL_CONNECTION_URL . 'assets/js/axios.js', ['mgs-vue-source'], MAGICAL_CONNECTION_VERSION, false);
        wp_register_script(
            'mgs-admin-config',
            false,
            [],
            MAGICAL_CONNECTION_VERSION,
            false
        );

        wp_enqueue_script('mgs-admin-config');

        wp_add_inline_script(
            'mgs-admin-config',
            'window.MagicalConnection = ' . wp_json_encode([
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => AjaxNonce::create(),
            ]) . ';',
        );

        wp_enqueue_style(
            MAGICAL_CONNECTION_TEXT_DOMAIN . "-font-dana",
            MAGICAL_CONNECTION_URL . 'assets/fonts/dana/font.css',
            [],
            MAGICAL_CONNECTION_VERSION
        );

        if ($hook !== $this->adminMenu->getHookSuffix()) {
            wp_enqueue_style(
                'magical-connection-media-dialog',
                MAGICAL_CONNECTION_URL . 'vue-app/dist/assets/css/media-dialog.css',
                [],
                MAGICAL_CONNECTION_VERSION ,
            );
        }

        wp_enqueue_style(
            'magical-connection-style',
            MAGICAL_CONNECTION_URL . 'vue-app/dist/assets/css/main.css',
            [],
            MAGICAL_CONNECTION_VERSION ,
        );


        wp_localize_script(
            'mgs-vue-source' ,
            'MagicalConnectionObject' ,
            [
                'ajax_url' => admin_url('admin-ajax.php') ,
                'url' => MAGICAL_CONNECTION_URL ,
                'pluginUrl' => MAGICAL_CONNECTION_URL ,
                'pluginVersion' => MAGICAL_CONNECTION_VERSION ,
                'nonce' => AjaxNonce::create() ,
                'pluginPage' => admin_url('admin.php?page=magical-connection') ,
            ]
        );

        wp_set_script_translations(
            'magical-main-app-js',
            MAGICAL_CONNECTION_TEXT_DOMAIN,
            MAGICAL_CONNECTION_PATH . 'languages'
        );

    }

    public function FrontEndTranslator(){
        static $done = false;
        if ($done) return;
        $done = true;

        $locale = determine_locale();
        $path   = MAGICAL_CONNECTION_PATH . 'languages/';

        $files = glob($path . MAGICAL_CONNECTION_TEXT_DOMAIN . '-' . $locale . '-*.json');
        $all_messages = [];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (empty($data['locale_data']['messages'])) continue;

            foreach ($data['locale_data']['messages'] as $key => $value) {
                if (isset($all_messages[$key]) && !empty($all_messages[$key][0])) {
                    continue;
                }
                if (empty($value[0]) && isset($all_messages[$key])) {
                    continue;
                }
                $all_messages[$key] = $value;
            }
        }

        if (!empty($all_messages)) {
            echo '<script>';
            echo 'wp.i18n.setLocaleData(' . wp_json_encode($all_messages) . ', "' . esc_js(MAGICAL_CONNECTION_TEXT_DOMAIN) . '");';
            echo '</script>';
        }
    }

}