<div class="wooapp-container d-none">
    <div id="wooapp-layout" class="wooapp-layout">

        <div class="wooapp-logo-container">
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
                <button type="button" id="wooapp-get-app-btn" class="btn btn-primary">Create app
                </button>
            </div>

            <?php
            // require_once WTA_DIR_PATH . 'templates/admin/setting-page-modal.php';
            ?>

            <?php
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $binary_url = get_user_meta($user_id, 'binary_url', true);
            $preview_url = get_user_meta($user_id, 'preview_url', true);
            ?>
            <div id="wooapp-download-app-btn" class="wooapp-action d-none">
                <a href="<?php echo $preview_url; ?>" class="btn btn-primary" download>
                    Download APK
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

<div class="wooapp-main-container mt-5">
    <div class="container">
        <div class="wooapp-header">
            <img style="width: 200px; height: 80px; object-fit: contain;" src="<?php echo WTA_ASSETS ?>/images/wooapp-logo1.png" alt="logo" />
            <h4 class="mt-3 fw-bold">Welcome to WooApp</h4>
            <p class="mt-3">WooApp helps you connect your website with your app.</p>
        </div>
        <div class="wooapp-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="wooapp-body-left">
                        <h4 class="fw-bold">App Builder that builds apps in five minutes without coding</h4>
                        <p class="mt-5">Create your Android and iOS apps in easy steps without any coding.<br> In
                            todays world for PHP developer, WordPress plugin development is becoming very popoular
                            nowadays. And after coming React</p>
                    </div>
                    <div class="vl"></div>
                </div>
                <div class="col-md-6">
                    <div class="wooapp-body-right">
                        <div class="wooapp-body-right-header">
                            <div class="row">
                                <div class="col-1">
                                    <i class="bi bi-display"></i>
                                </div>
                                <div class="col-11">
                                    <h5 class="fw-bold">App Builder</h5>
                                    <p class="mt-3 fw-bold">V1.0.2</p>
                                </div>
                            </div>
                        </div>
                        <div class="wooapp-body-right-body">
                            <ul>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="row">
                                        <div class="col-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5">
                                            <h5 class="fw-bold">Chilla Store !.05.6</h5>
                                            <p class="mt-1">V1.0.2</p>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-primary">Install</button>
                                            <button class="btn btn-outline-primary">Activate</button>
                                            <button class="btn btn-outline-primary">Deactivate</button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
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
    </div>
</div>