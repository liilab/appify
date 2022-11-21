<?php

namespace WebToApp;

use WebToApp\traits\Singleton;

/**
 * Class Admin
 * @package WebToApp
 */

class Admin
{
    use Singleton;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_settings'));
    }

    public function create_settings()
    {
        $parent_slug = 'woocommerce';
        $page_title = 'Web To App';
        $menu_title = 'Web To App';
        $capability = 'manage_options';
        $slug = 'web-to-app';
        $callback = array($this, 'settings_content');
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $slug, $callback);
    }

    public function settings_content()
    {
        echo '
        <div id="welcome-panel" class="welcome-panel">
        <div class="welcome-panel-content">
            <div class="welcome-panel-header">
                <div class="welcome-panel-header-image">
                    <svg width="780" height="550" viewBox="0 0 780 550" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <g opacity=".5" fill="#273FCC" stroke="#627EFF" stroke-width="2" stroke-miterlimit="10">
                            <circle cx="434.757" cy="71.524" r="66.1"></circle>
                            <circle cx="432.587" cy="43.138" r="66.1"></circle>
                            <circle cx="426.277" cy="16.14" r="66.1"></circle>
                            <circle cx="416.143" cy="-9.165" r="66.1"></circle>
                            <circle cx="402.53" cy="-32.447" r="66.1"></circle>
                            <circle cx="385.755" cy="-53.376" r="66.1"></circle>
                            <circle cx="116.864" cy="-53.072" r="66.1"></circle>
                            <circle cx="99.984" cy="-32.183" r="66.1"></circle>
                            <circle cx="86.278" cy="-8.953" r="66.1"></circle>
                            <circle cx="76.078" cy="16.3" r="66.1"></circle>
                            <circle cx="69.714" cy="43.23" r="66.1"></circle>
                            <circle cx="67.518" cy="71.524" r="66.1"></circle>
                            <circle cx="67.518" cy="71.524" r="66.1"></circle>
                            <circle cx="67.518" cy="99.05" r="66.1"></circle>
                            <circle cx="67.518" cy="126.577" r="66.1"></circle>
                            <circle cx="67.518" cy="154.09" r="66.1"></circle>
                            <circle cx="67.518" cy="181.617" r="66.1"></circle>
                            <circle cx="67.518" cy="209.143" r="66.1"></circle>
                            <circle cx="67.518" cy="236.67" r="66.1"></circle>
                            <circle cx="67.518" cy="264.196" r="66.1"></circle>
                            <circle cx="67.518" cy="291.722" r="66.1"></circle>
                            <circle cx="67.518" cy="319.236" r="66.1"></circle>
                            <circle cx="67.518" cy="346.762" r="66.1"></circle>
                            <circle cx="67.518" cy="374.289" r="66.1"></circle>
                            <circle cx="67.518" cy="374.831" r="66.1"></circle>
                            <circle cx="68.471" cy="393.565" r="66.1"></circle>
                            <circle cx="71.249" cy="411.757" r="66.1"></circle>
                            <circle cx="75.76" cy="429.315" r="66.1"></circle>
                            <circle cx="81.925" cy="446.146" r="66.1"></circle>
                            <circle cx="89.651" cy="462.17" r="66.1"></circle>
                            <circle cx="411.579" cy="464.073" r="66.1"></circle>
                            <circle cx="423.208" cy="438.98" r="66.1"></circle>
                            <circle cx="430.986" cy="412.008" r="66.1"></circle>
                            <circle cx="434.558" cy="383.517" r="66.1"></circle>
                            <circle cx="433.831" cy="354.43" r="66.1"></circle>
                            <circle cx="428.777" cy="326.428" r="66.1"></circle>
                            <circle cx="419.635" cy="300.078" r="66.1"></circle>
                            <circle cx="406.763" cy="275.725" r="66.1"></circle>
                            <circle cx="390.491" cy="253.698" r="66.1"></circle>
                            <circle cx="371.189" cy="234.369" r="66.1"></circle>
                            <circle cx="349.188" cy="218.054" r="66.1"></circle>
                            <circle cx="324.846" cy="205.124" r="66.1"></circle>
                            <circle cx="298.506" cy="195.896" r="66.1"></circle>
                            <circle cx="270.512" cy="190.739" r="66.1"></circle>
                            <circle cx="241.368" cy="189.986" r="66.1"></circle>
                            <circle cx="213.003" cy="193.754" r="66.1"></circle>
                            <circle cx="186.147" cy="201.739" r="66.1"></circle>
                            <circle cx="161.157" cy="213.559" r="66.1"></circle>
                            <circle cx="138.389" cy="228.882" r="66.1"></circle>
                            <circle cx="118.174" cy="247.352" r="66.1"></circle>
                            <circle cx="100.857" cy="268.599" r="66.1"></circle>
                            <circle cx="86.794" cy="292.264" r="66.1"></circle>
                            <circle cx="76.316" cy="318.019" r="66.1"></circle>
                            <circle cx="69.781" cy="345.466" r="66.1"></circle>
                            <circle cx="67.518" cy="374.289" r="66.1"></circle>
                            <circle cx="712.577" cy="449.729" r="66.1"></circle>
                            <circle cx="712.577" cy="428.072" r="66.1"></circle>
                            <circle cx="712.577" cy="406.403" r="66.1"></circle>
                            <circle cx="712.577" cy="384.733" r="66.1"></circle>
                            <circle cx="712.577" cy="363.077" r="66.1"></circle>
                            <circle cx="712.577" cy="341.408" r="66.1"></circle>
                            <circle cx="712.577" cy="319.738" r="66.1"></circle>
                            <circle cx="712.577" cy="298.069" r="66.1"></circle>
                            <circle cx="712.577" cy="276.412" r="66.1"></circle>
                            <circle cx="712.577" cy="254.743" r="66.1"></circle>
                            <circle cx="712.577" cy="233.073" r="66.1"></circle>
                            <circle cx="712.577" cy="211.417" r="66.1"></circle>
                            <circle cx="712.577" cy="189.748" r="66.1"></circle>
                            <circle cx="712.577" cy="168.078" r="66.1"></circle>
                            <circle cx="712.577" cy="146.422" r="66.1"></circle>
                            <circle cx="712.577" cy="124.753" r="66.1"></circle>
                            <circle cx="712.577" cy="103.083" r="66.1"></circle>
                            <circle cx="712.577" cy="81.413" r="66.1"></circle>
                            <circle cx="712.577" cy="59.757" r="66.1"></circle>
                            <circle cx="712.577" cy="38.088" r="66.1"></circle>
                            <circle cx="712.577" cy="16.418" r="66.1"></circle>
                            <circle cx="712.577" cy="-5.238" r="66.1"></circle>
                            <circle cx="712.577" cy="-26.907" r="66.1"></circle>
                            <circle cx="712.577" cy="-48.577" r="66.1"></circle>
                            <circle cx="662.966" cy="-44.161" r="66.1"></circle>
                            <circle cx="646.429" cy="-21.024" r="66.1"></circle>
                            <circle cx="629.893" cy="2.113" r="66.1"></circle>
                            <circle cx="613.356" cy="25.25" r="66.1"></circle>
                            <circle cx="596.819" cy="48.387" r="66.1"></circle>
                            <circle cx="580.282" cy="71.524" r="66.1"></circle>
                            <circle cx="580.282" cy="465.515" r="66.1"></circle>
                        </g>
                    </svg>
                </div>
                <p>
                <h2>Welcome to Web To App!</h2>
                <a href="
                ' .
            $this->getAppUrl()
            . '
                ">
                    Learn more about the 1.0 version</a>
                </p>
            </div>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <rect width="48" height="48" rx="4" fill="#1E1E1E"></rect>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M32.0668 17.0854L28.8221 13.9454L18.2008 24.671L16.8983 29.0827L21.4257 27.8309L32.0668 17.0854ZM16 32.75H24V31.25H16V32.75Z"
                            fill="white"></path>
                    </svg>
                    <div class="welcome-panel-column-content">
                        <h3>Make your app quickly</h3>
                        <p>Block patterns are pre-configured block layouts. Use them to get inspired or create new pages
                            in a flash.</p>
                    </div>
                </div>
                <div class="welcome-panel-column">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <rect width="48" height="48" rx="4" fill="#1E1E1E"></rect>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M18 16h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H18a2 2 0 0 1-2-2V18a2 2 0 0 1 2-2zm12 1.5H18a.5.5 0 0 0-.5.5v3h13v-3a.5.5 0 0 0-.5-.5zm.5 5H22v8h8a.5.5 0 0 0 .5-.5v-7.5zm-10 0h-3V30a.5.5 0 0 0 .5.5h2.5v-8z"
                            fill="#fff"></path>
                    </svg>
                    <div class="welcome-panel-column-content">
                        <h3>Start Customizing</h3>
                        <p>Configure your site’s logo, header, menus, and more in the Customizer.</p>
                    </div>
                </div>
                <div class="welcome-panel-column">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" focusable="false">
                        <rect width="48" height="48" rx="4" fill="#1E1E1E"></rect>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M31 24a7 7 0 0 1-7 7V17a7 7 0 0 1 7 7zm-7-8a8 8 0 1 1 0 16 8 8 0 0 1 0-16z" fill="#fff">
                        </path>
                    </svg>
                    <div class="welcome-panel-column-content">
                        <h3>Discover a new way to build your App </h3>
                        <p>There is a new kind of WordPress theme, called a block theme, that lets you build the site
                            you’ve always wanted — with blocks and styles.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p id="footer-left" class="alignleft">
                <span id="footer-thankyou">Thank you from <a href="https://liilab.com/">LIILab</a>.</span>
            </p>
        ';
    }

    /**
     * @return string
     */

    public function getAppUrl()

    {

        $menu_slug = 'web-to-app';

        $url = admin_url('admin.php?page=' . $menu_slug);

        return $url;
    }
}
