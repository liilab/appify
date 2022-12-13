<div class="wooapp-container">
    <div id="wooapp-layout" class="wooapp-layout">
        <div class="wooapp-logo-container">
            <!--            <img class="img-fluid d-none d-md-block" src="-->
            <?php //echo WTA_ASSETS ?><!--/images/wooapp-logo.png" alt="logo">-->
            <img
                    style="width: 200px; height: 80px; object-fit: contain;"
                    src="<?php echo WTA_ASSETS ?>/images/wooapp-logo.png"
                    alt="logo"
            />
        </div>

        <div class="wooapp-app-making-area">

            <h4>Welcome to WooApp!</h4>
            <p>WooApp helps you connect your website with your app.</p>

            <div id="wooapp-create-app" class="wooapp-action-btn d-none">
                <p>Let's get started with WooApp.</p>
                <button id="wooapp-get-app-btn" class="btn btn-primary">Create app</button>
            </div>

            <?php
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            // $user_id = 1131;
            $is_url_exist = get_user_meta($user_id, 'build_url', true);
            ?>
            <div id="wooapp-download-app-btn" class="wooapp-action-btn d-none">
                <!--                    <p>Download your app.</p>-->
                <a
                        href="<?php echo $is_url_exist; ?>"
                        class="btn btn-primary"
                        download
                >
                    Download App
                </a>
            </div>

            <div id="wooapp-progressbar-section" class="d-none" style="margin-top: 128px; padding: 0 32px;">
                <p id="wooapp-progressbar-msg" class="text-center">
                    Building in progress.<br>It can take 3 to 7 minutes.
                </p>
                <div id="wooapp-progressbar" class="my-3 me-3 progress">
                    <div
                            id="wooapp-progressbar-loader"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            aria-valuenow="75"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="width: 10%"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>
