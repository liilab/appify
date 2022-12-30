<div class="wooapp-main-container mt-5">

<?php do_action( 'wooapp_main_content_before'); ?>

    <div class="wooapp-container">
        <div class="wooapp-header">
            <img class="wooapp-logo" src="<?php echo WTA_ASSETS ?>/build/img/wooapp-logo-467x100.png" alt="wooapp-logo" />
            <h1 class="wooapp-title mt-4"><?php esc_html_e('Welcome to WooApp','wooapp'); ?></h1>
            <h2 class="wooapp-subtitle mt-1"><?php esc_html_e('WooApp helps you to connect your WooCommerce website with your mobile app.','wooapp'); ?></h2>
        </div>

        <?php do_action( 'wooapp_body_content_before' );?>

        <div class="wooapp-body mt-5">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="wooapp-body-left row">
                        <h4 class="wooapp-text1 col-6 col-lg-12">
                            <?php esc_html_e('WooApp App Builder builds apps in five minutes without coding','wooapp'); ?>
                        </h4>
                        <p class="wooapp-text2 col-6 col-lg-12 mt-lg-5">
                            <?php esc_html_e('Create your Android App in easy steps without any coding.','wooapp'); ?>
                            <br /><?php esc_html_e('Once your app is ready, you can publish it on Google Play.','wooapp'); ?>
                        </p>
                    </div>
                </div>

                <div class="mt-5 mt-lg-0 col-12 col-lg-6">
                        <div class="wooapp-body-right">
                            <!-- Initial Loader-->
                            <div id="wooapp-loader" class="wooapp-loader d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden"><?php esc_html_e('Loading','wooapp'); ?>...</span>
                                </div>
                                <p class="mt-2"><?php esc_html_e('Loading','wooapp'); ?>...</p>
                            </div>
                            <!-- Build History Card -->
                            <div id="wooapp-build-history-card" class="wooapp-build-history-card d-none">
                                <div class="d-flex">
                                    <div class="">
                                        <div class="wooapp-build-history-card-icon"
                                            style="background-color: rgb(244, 246, 252);">
                                            <i class="bi bi-flag" style="color: rgb(33, 36, 61);"></i>
                                        </div>
                                    </div>
                                    <div class="mx-2 ps-0">
                                        <p class="wooapp-build-card-title">
                                            <?php esc_html_e('WooApp App Builder','wooapp'); ?>
                                        </span>
                                        <p class="fw-normal wooapp-build-card-version"><?php esc_html_e('Last build histories','wooapp'); ?></p>
                                    </div>
                                    <div class="ms-auto">
                                        <button id="wooapp-get-app-rebuild-btn" class="btn wooapp-create-app-btn"><?php esc_html_e('Create
                                            New App','wooapp'); ?></button>
                                    </div>
                                </div>


                            <div class="wooapp-body-right-body">
                                <ul id="wooapp-build-history">
                                </ul>
                            </div>
                        </div>
                        <!-- Build Progress Loader  -->
                        <div id="wooapp-progressbar-section" class="wooapp-progressbar-section d-none">
                            <p id="wooapp-progressbar-msg" class="text-center wooapp-progressbar-msg">
                                <?php esc_html_e('Building in progress.','wooapp'); ?><br><?php esc_html_e('It takes 3 to 7 minutes.','wooapp'); ?>
                            </p>
                            <div id="wooapp-progressbar" class="my-3 me-3 progress">
                                <div id="wooapp-progressbar-loader" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                            </div>
                        </div>

                        <div id="wooapp-form-wrap" class="wrap d-none">
                            <form id="wooapp-form" method="POST" action="options.php">
                                <div class="mb-3">
                                    <label for="wooapp-appname" class="form-label text-secondary  wooapp-build-card-title"><?php esc_html_e('App name','wooapp'); ?></label>
                                    <input required type="text" class="form-control" id="wooapp-appname" value="<?php echo get_bloginfo('name'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="wooapp-storename" class="form-label text-secondary  wooapp-build-card-title"><?php esc_html_e('Store name','wooapp'); ?></label>
                                    <input required type="text" class="form-control" id="wooapp-storename" value="<?php echo get_bloginfo('name'); ?>">
                                </div>

                                <?php

                                $custom_logo_id = get_theme_mod('custom_logo');
                                $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

                                $dummy_logo = 'https://play-lh.googleusercontent.com/BUB9hjJqtkBjHekgrqsINgzNMzA-G34nyZQDRmzmQdw6_qbpO8E9l78Z9wS0eCp8QFKE';

                                $field = array(
                                    'section' => 'wta_custom_section',
                                    'label' => 'Logo',
                                    'id' => 'wooapp-icon',
                                    'type' => 'media',
                                    'returnvalue' => 'url',
                                    'editable' => 'true',
                                    'default' => $logo ? $logo[0] : $dummy_logo,
                                );

                                $value = $field['default'];
                                $field_url = $value;

                                ?>

                                    <?php

                                    printf(
                                        '<label for="%s" class="form-label text-secondary wooapp-build-card-title">App Logo</label><br>
                                        <input required style="display:none;" id="%s" name="%s" type="text" value="%s"  data-return="%s">
                    <div id="preview%s" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:cover;background-repeat:no-repeat;background-position:center;">
                    </div>
                    <br>
                    <input style="margin-right:5px;" class="button menutitle-media" id="%s_button" name="%s_button" type="button" value="Select" />
                    <input style="" class="button remove-media d-none" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                                        $field['id'],
                                        $field['id'],
                                        $field['id'],
                                        $value,
                                        $field['returnvalue'],
                                        $field['id'],
                                        $field_url,
                                        $field['id'],
                                        $field['id'],
                                        $field['id'],
                                        $field['id']
                                    );

                                    ?>
                                <br>
                                <?php wp_nonce_field( 'wooapp-create-app-nonce-action', 'wooapp-create-app-nonce-field'); ?>
                                <div class="d-flex flex-row-reverse bd-highlight wooapp-buildhistory-btn">
                                    <button id="wooapp-create-app-btn" type="submit" class="btn btn-primary"><?php esc_html_e('Create app','wooapp'); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php do_action( 'wooapp_body_content_after' );?>
    </div>
    <?php do_action( 'wooapp_main_content_after'); ?>
</div>