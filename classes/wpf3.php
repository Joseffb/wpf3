<?php
/**
 * Created by PhpStorm.
 * User: joseff
 * Date: 9/21/18
 * Time: 3:14 PM
 */

class WPF3 extends prefab
{
    private $f3 = null;
    function __construct()
    {
        $this->f3= base::instance();
        $this->load_config();
        $this->update_Globals(); //F3 has it's own GLOBALS var so we identify the WP ones with WP_ Prefix.
    }

    /** Load config values from config files, and wordpress config file */
    private function load_config() {

        if(defined('WPF3_CONFIGS')) {
            if (!empty(WPF3_CONFIGS['config_File'])) {
                //force the use of a config file. File should be in the wpf3 plugin directory.
                if (file_exists(WPF3_CONFIGS['config_File'])) {
                    $this->f3->config(WPF3_CONFIGS['config_File']);
                } else if (file_exists(plugin_dir_path( __DIR__ )."/".WPF3_CONFIGS['config_File'])) {
                    $this->f3->config(plugin_dir_path( __DIR__ )."/".WPF3_CONFIGS['config_File']);
                }
            } else {
                foreach(WPF3_CONFIGS as $key => $value) {
                    $this->f3->set($key, $value);
                }
            }
        } else {
            //settings in wordpress config file should override other configs unless otherwise directed.
            $file = plugin_dir_path( __DIR__ )."/config/config.ini";
            if (file_exists($file)) {
                $this->f3->config($file);
            }
        }

        //Set a standard autoload path to be the classes folder.
        $autoload = plugin_dir_path( __DIR__ )."/classes/";
        $this->f3->set('AUTOLOAD', !empty($this->f3->AUTOLOAD)? $this->f3->AUTOLOAD.";".$autoload:$autoload);
    }

    /** Copies the WordPress Globals array into a f3 Hive setting. */
    private function update_Globals() {
        $this->f3->set('WP_GLOBALS', $GLOBALS );
    }

    /**
     * Prepares sites to use the plugin during single or network-wide activation
     *
     * @mvc Controller
     *
     * @param bool $network_wide
     */
    public function activate( $network_wide = false) {
        if ( $network_wide && is_multisite() ) {
            $sites = wp_get_sites( array( 'limit' => false ) );

            foreach ( $sites as $site ) {
                switch_to_blog( $site['blog_id'] );
                $this->single_activate( $network_wide );
                restore_current_blog();
            }
        } else {
            $this->single_activate( $network_wide );
        }
    }

    /**
     * Runs activation code on a new WPMS site when it's created
     *
     * @mvc Controller
     *
     * @param int $blog_id
     */
    public function activate_new_site( $blog_id ) {
        switch_to_blog( $blog_id );
        $this->single_activate( true );
        restore_current_blog();
    }

    /**
     * Prepares a single blog to use the plugin
     *
     * @mvc Controller
     *
     * @param bool $network_wide
     */
    protected function single_activate( $network_wide ) {
        //flush_rewrite_rules();
    }

    /**
     * Rolls back activation procedures when de-activating the plugin
     *
     * @mvc Controller
     */
    public function deactivate() {
        //flush_rewrite_rules();
    }

}
