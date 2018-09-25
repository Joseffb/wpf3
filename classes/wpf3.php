<?php
/**
 * Created by Joseff Betancourt
 */

class WPF3 extends prefab
{
    private $f3 = null;

    function __construct()
    {
        $this->f3 = base::instance();
        $this->f3->set("DEBUG", 4);
        $this->load_config();
        $this->update_Globals(); //F3 has it's own GLOBALS var so we identify the WP ones with WP_ Prefix.
        $this->run_actions();
        //echo "Test Message: " . $this->f3->testMessage;
    }

    /** Load config values from config files, and wordpress config file */
    private function load_config()
    {
        if (defined('WPF3_CONFIGS')) {
            if (!empty(WPF3_CONFIGS['config_File'])) {
                echo "inside 1";
                //force the use of a config file. File should be in the wpf3 plugin directory.
                if (file_exists(WPF3_CONFIGS['config_File'])) {

                    echo "inside 2";
                    $this->f3->config(WPF3_CONFIGS['config_File']);
                } else if (file_exists(plugin_dir_path(__DIR__) . WPF3_CONFIGS['config_File'])) {

                    echo "inside 3";
                    $this->f3->config(plugin_dir_path(__DIR__) . WPF3_CONFIGS['config_File']);
                }
            } else {
                foreach (WPF3_CONFIGS as $key => $value) {
                    echo "inside 4 loop";
                    $this->f3->set($key, $value);
                }
            }
        } else {
            //settings in wordpress config file should override other configs unless otherwise directed.
            $file = plugin_dir_path(__DIR__) . "config/configs.ini";
            if (file_exists($file)) {
                $this->f3->config($file);
            }
        }

        //Set a standard autoload path to be the classes folder.
        $autoload = plugin_dir_path(__DIR__) . "classes/";
        $this->f3->set('AUTOLOAD', !empty($this->f3->AUTOLOAD) ? $this->f3->AUTOLOAD . ";" . $autoload : $autoload);
    }

    /** Copies the WordPress Globals array into a f3 Hive setting. */
    private function update_Globals()
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
    public function activate($network_wide = false)
    {
        if ($network_wide && is_multisite()) {
            $sites = wp_get_sites(array('limit' => false));

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
     *
     * @mvc Controller
     *
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
    protected function single_activate($network_wide)
    {
        //flush_rewrite_rules();
    }

    /**
     * Rolls back activation procedures when de-activating the plugin
     *
     * @mvc Controller
     */
    public function deactivate()
    {
        //flush_rewrite_rules();
    }

    /**
     * This will run the wordpress specific actions and filters.
     */
    public function run_actions()
    {
        //intercept the wordpress routing and run our own route check.
        add_action('pre_get_posts', array($this, 'check_routes'));

    }
    function willMatchARoute() {
        if (!$this->f3->ROUTES)
            // No routes defined
            return false;
        // Match specific routes first
        $paths=[];
        foreach ($keys=array_keys($this->f3->ROUTES) as $key) {
            $path=preg_replace('/@\w+/','*@',$key);
            if (substr($path,-1)!='*')
                $path.='+';
            $paths[]=$path;
        }
        $vals=array_values($this->f3->ROUTES);
        array_multisort($paths,SORT_DESC,$keys,$vals);
        $this->f3->ROUTES=array_combine($keys,$vals);
        // Convert to BASE-relative URL
        $req=urldecode($this->f3->PATH);
        foreach ($this->f3->ROUTES as $pattern=>$routes) {
            if (!$args=$this->f3->mask($pattern,$req))
                continue;
            return true;
        }
        return false;
    }

    function check_routes()
    {
        //$this->f3->run();
        if($this->willMatchARoute()) {
            $this->f3->run();
            die();
        }
    }
}
