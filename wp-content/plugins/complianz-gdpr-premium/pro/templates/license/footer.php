<?php $status = COMPLIANZ::$license->get_license_status(); ?>
<input type="submit" class="button button-secondary" name="cmplz_license_save" value="<?php _e('Save', 'complianz-gdpr'); ?>"/>
<?php
if ($status && $status == 'valid') { ?>
	<input type="submit" class="button button-cmplz-tertiary" name="cmplz_license_deactivate" value="<?php _e('Deactivate license', 'complianz-gdpr'); ?>"/>
<?php } else { ?>
	<input type="submit" class="button button-primary" name="cmplz_license_activate" value="<?php _e('Activate license', 'complianz-gdpr'); ?>"/>
<?php } ?>
