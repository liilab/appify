<div class="wooapp-main-container">
    <div class="wooapp-container">
        <div class="wooapp-header">
            <img class="wooapp-logo" src="<?php echo WTA_ASSETS ?>/images/wooapp-logo.png" alt="wooapp-logo" />
            <h1 class="wooapp-title mt-4">Welcome to Woo App</h1>
            <h2 class="wooapp-subtitle mt-1">WooApp helps you connect your website with your app.</h2>
        </div>
        <div class="wooapp-body mt-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="wooapp-body-left">
                        <h4 class="wooapp-text1">
                            Wooapp App Builder builds apps in five minutes without coding
                        </h4>
                        <p class="mt-5 wooapp-text2">
                            Create your Android and iOS apps in easy steps without any coding.
                            <br />Once your app is ready, you can publish it on Google Play and App Store.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="wooapp-body-right">
                        <!-- Initial Loader-->
                        <div id="wooapp-loader" class="wooapp-loader">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading...</p>
                        </div>
                        <!-- Build History Card -->
                        <div id="wooapp-build-history-card" class="d-none wooapp-build-history-card">
                            <div class="row">
                                <div class="col-2">
                                    <div class="wooapp-build-history-card-icon" style="background-color: rgb(244, 246, 252);">
                                        <i class="bi bi-flag" style="color: rgb(33, 36, 61);"></i>
                                    </div>
                                </div>
                                <div class="col-6 ps-0">
                                    <span style="color: rgb(172,175,201); font-weight: 500; font-size: 1rem;">
                                        Wooapp APK Builder
                                    </span>
                                    <p class="fw-bold">V1.0.1</p>
                                </div>
                                <div class="col-4">
                                    <button id="wooapp-get-app-rebuild-btn" class="btn wooapp-create-app-btn">Create New App</button>
                                </div>
                            </div>
                            <div class="wooapp-body-right-body">
                                <ul id="wooapp-build-history">
                                </ul>
                            </div>
                        </div>
                        <!-- Build Progress Loader  -->
                        <div id="wooapp-progressbar-section" class="d-none" style="margin-top: 128px; padding: 0 32px;">
                            <p id="wooapp-progressbar-msg" class="text-center">
                                Building in progress.<br>It can take 3 to 7 minutes.
                            </p>
                            <div id="wooapp-progressbar" class="my-3 me-3 progress">
                                <div id="wooapp-progressbar-loader" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                            </div>
                        </div>
            
                        <div id="wooapp-form-wrap" class="wrap d-none">
                            <form id="wooapp-form" method="POST" action="options.php">
                                <div class="mb-3">
                                    <label for="wooapp-appname" class="form-label">App name</label>
                                    <input type="text" class="form-control" id="wooapp-appname" value="<?php echo get_bloginfo('name'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="wooapp-storename" class="form-label">Store name</label>
                                    <input type="text" class="form-control" id="wooapp-storename" value="<?php echo get_bloginfo('name'); ?>">
                                </div>
                                <button id="wooapp-create-app-btn" type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>