<div class="wooapp-layout">
    <div id="wooapp-layout">
        <div class="wooapp-appmaking-area">
            <div class="wooapp-description">
                <h4>Thank you for choosing WooApp!</h4>
                <p>WooApp plugin helps you connect your website with your app.</p>
                <?php
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $user_id = 1008;
                $is_url_exist = get_user_meta($user_id, 'build_url', true);
                if (empty($is_url_exist)) {
                ?>
                    <div id="wooapp-create-app-section" class="mt-5">
                        <p>Let's get started</p>
                        <button id="wooapp-getappbtn" class="btn btn-primary">Create app</button>
                    </div>
                <?php } else { ?>
                    <a href="<?php echo $is_url_exist; ?>" class="mt-5 btn btn-primary" download>Download App</a>
                <?php } ?>
            </div>
        </div>

        <div id="wooapp-progressbar" class="d-none mt-5">
            <p>Genereting app...</p>
            <div class="my-3 me-3 progress">
                <div id="wooapp-progressbar-loader" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 10%" />
            </div>
        </div>

    </div>
</div>