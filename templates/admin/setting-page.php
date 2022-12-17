<div class="wooapp-container">
    <div id="wooapp-layout" class="wooapp-layout">
        <div class="wooapp-logo-container">
            <!--            <img class="img-fluid d-none d-md-block" src="-->
            <?php //echo WTA_ASSETS 
            ?>
            <!--/images/wooapp-logo.png" alt="logo">-->
            <img style="width: 200px; height: 80px; object-fit: contain;" src="<?php echo WTA_ASSETS ?>/images/wooapp-logo.png" alt="logo" />
        </div>

        <div class="wooapp-app-making-area">

            <h4>Welcome to WooApp!</h4>
            <p>WooApp helps you connect your website with your app.</p>

            <div id="wooapp-loader" class="wooapp-action">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading...</p>
            </div>

            <div id="wooapp-create-app" class="wooapp-action d-none">
                <p>Let's get started with WooApp.</p>
                <button id="wooapp-get-app-btn" class="btn btn-primary">Create app</button>
            </div>

            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Launch demo modal
            </button>

            <?php
            require_once WTA_DIR_PATH . 'templates/admin/setting-page-modal.php';
            ?>

            <?php
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $binary_url = get_user_meta($user_id, 'binary_url', true);
            $preview_url = get_user_meta($user_id, 'preview_url', true);
            ?>
            <div id="wooapp-download-app-btn" class="wooapp-action d-none">
                <a href="<?php echo $preview_url; ?>" class="btn btn-primary" download>
                    Download Preview
                </a>

                <a href="<?php echo $binary_url; ?>" class="mt-2" download>
                    Get app bundle
                </a>
            </div>

            <div id="wooapp-progressbar-section" class="d-none" style="margin-top: 128px; padding: 0 32px;">
                <p id="wooapp-progressbar-msg" class="text-center">
                    Building in progress.<br>It can take 3 to 7 minutes.
                </p>
                <div id="wooapp-progressbar" class="my-3 me-3 progress">
                    <div id="wooapp-progressbar-loader" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>