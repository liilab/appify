<div class="wooapp-wrap mt-5">
    <div class="container">
        <div class="wooapp-header">
            <img style="width: 200px; height: 80px; object-fit: contain;" src="<?php echo WTA_ASSETS ?>/images/wooapp-logo.png" alt="logo" />
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
                                <div class="col-11 pb-2">
                                    <h5 class="fw-bold">App Builder</h5>
                                    <h6 class="fw-bold">V1.0.2</h6>
                                </div>
                            </div>
                        </div>
                        <div class="wooapp-body-right-body">
                            <ul>
                                <li>
                                    <div class="row">
                                        <div class="col-1 wooapp-prev-icon">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        </div>
                                        <div class="col-5 wooapp-building-history">
                                            <h5 class="fw-bold">Cirilla Store 1.0.1383</h5>
                                            <h6 class="fw-bold">Fri, 16 Dec 2022 09:26:01 GMT</h6>
                                        </div>
                                        <div class="col-6 wooapp-icon d-flex justify-content-end">
                                            <i class="bi bi-apple"></i>
                                            <i class="bi bi-view-list"></i>
                                            <i class="bi bi-key-fill"></i>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-md-6">
                        <div id="wooapp-progressbar-section" class="d-none" style="margin-top: 128px; padding: 0 32px;">
                            <p id="wooapp-progressbar-msg" class="text-center">
                                Building in progress.<br>It can take 3 to 7 minutes.
                            </p>
                            <div id="wooapp-progressbar" class="my-3 me-3 progress">
                                <div id="wooapp-progressbar-loader"
                                    class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                            </div>
                        </div>
                    </div> -->

                <div class="col-md-6 d-none">

                    <div class="wrap">
                        <?php settings_errors(); ?>
                        <form method="POST" action="options.php">
                            <?php
                            settings_fields('wta_custom');
                            do_settings_sections('wta_custom');
                            $other_attributes = array('id' => 'wta-save-appinfo');
                            submit_button(__("Save Info & Let's go", 'wta'), '', 'wta-save-appinfo', true, $other_attributes);
                            ?>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>