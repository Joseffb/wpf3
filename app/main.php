<?php
/**
 * Created by Joseff Betancourt
 */

namespace wpf3;
class main extends \prefab
{
    private ?object $f3 = null;

    public function __construct()
    {
        $this->f3 = \base::instance();
        $this->f3->set("DEBUG", 4);
        $this->load_config();
        $this->update_globals(); //F3 has its own GLOBALS var, so we identify the WP ones with WP_ Prefix.
        $this->run_actions();
    }

    /** Load config values from config files, and wordpress config file */
    private function load_config(): void
    {
        if (defined('WPF3_CONFIGS')) {
            $WPF3_CONFIGS = constant('WPF3_CONFIGS');
            //give a chance to pass in a config via global config var, else use the config path in this plugin.
            if (file_exists($WPF3_CONFIGS['config_File'])) {
                $this->f3->config($WPF3_CONFIGS['config_File']);
            } else {
                foreach ($WPF3_CONFIGS as $key => $value) {
                    $this->f3->set($key, $value);
                }
            }
        } else {
            $configs = ['env.ini','global.ini','maps.ini','redirects.ini','routes.ini'];
            $file = plugin_dir_path(__DIR__) . "config/configs.ini";
            $current_path = plugin_dir_path(__DIR__);
            foreach($configs as $config) {
                $path = "{$current_path}config/$config";
                if (file_exists($path)) {
                    $this->f3->config($file);
                }
            }

        }

        //Set a standard autoload path in f3 to match that of Composer.
        $wpf3_classes = plugin_dir_path(__DIR__) . "app/";
        $autoload = empty($this->f3->AUTOLOAD) ? $wpf3_classes : $this->f3->AUTOLOAD . ";" . $wpf3_classes;
        $this->f3->set('AUTOLOAD', $autoload);
    }

    /** Copies the WordPress Globals array into a f3 Hive setting. */
    private function update_globals(): void
    {
        $this->f3->set('WP_GLOBALS', $GLOBALS);
    }

    /**
     * Prepares sites to use the plugin during single or network-wide activation
     *
     * @mvc Controller
     *
     * @param bool $network_wide
     */
    public function activate($network_wide = false): void
    {
        if ($network_wide && is_multisite()) {
            $sites = get_sites(array('limit' => false));

            foreach ($sites as $site) {
                switch_to_blog($site['blog_id']);
                $this->single_activate($network_wide);
                restore_current_blog();
            }
        } else {
            $this->single_activate($network_wide);
        }
    }

    /**
     * Runs activation code on a new WPMS site when it's created
     * @param int $blog_id
     */
    public function activate_new_site($blog_id)
    {
        switch_to_blog($blog_id);
        $this->single_activate(true);
        restore_current_blog();
    }

    /**
     * Prepares a single blog to use the plugin
     *
     * @mvc Controller
     *
     * @param bool $network_wide
     */
    protected function single_activate($network_wide): void
    {
        flush_rewrite_rules();
    }

    /**
     * Rolls back activation procedures when de-activating the plugin
     *
     * @mvc Controller
     */
    public function deactivate()
    {
        flush_rewrite_rules();
    }

    /**
     * This will run the wordpress specific actions and filters.
     */
    public function run_actions(): void
    {
        //intercept the WordPress routing and run our own route check.
        add_action('pre_get_posts', array($this, 'check_routes'));
    }

    public function will_match_a_route(): bool
    {
        if (!$this->f3->ROUTES)
            // No routes defined
            return false;
        // Match specific routes first
        $paths = [];
        foreach ($keys = array_keys($this->f3->ROUTES) as $key) {
            $path = preg_replace('/@\w+/', '*@', $key);
            if (substr($path, -1) !== '*')
                $path .= '+';
            $paths[] = $path;
        }
        $vals = array_values($this->f3->ROUTES);
        array_multisort($paths, SORT_DESC, $keys, $vals);
        $this->f3->ROUTES = array_combine($keys, $vals);
        // Convert to BASE-relative URL
        $req = urldecode($this->f3->PATH);
        foreach ($this->f3->ROUTES as $pattern => $routes) {
            if (!$args = $this->f3->mask($pattern, $req)) {
                continue;
            }
            return true;
        }
        return false;
    }

    public function check_routes(): void
    {
        //$this->f3->run();
        if ($this->will_match_a_route()) {
            $this->f3->run();
            die();
        }
    }
}
