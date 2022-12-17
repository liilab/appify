<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg  modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header mt-4 mt-md-0">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Give your information</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="wrap">
                    <?php settings_errors(); ?>
                    <form method="POST" action="options.php">
                        <?php
                        settings_fields('wta_custom');
                        do_settings_sections('wta_custom');
                        $other_attributes = array( 'id' => 'wta-save-appinfo' );
                        submit_button( __( "Save Info & Let's go", 'wta' ), 'primary', 'wta-save-appinfo', true, $other_attributes );
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>