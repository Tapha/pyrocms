<h2><?php echo lang('user_login_header') ?></h2>

<?php echo form_open('users/login', array('id'=>'login')); ?>
	
<?php if (!empty($this->form_validation->error_string)): ?>
	<div class="error-box"><?php echo $this->form_validation->error_string;?></div>
<?php endif; ?>

<p>
	<label for="email"><?php echo lang('user_email')?></label>
	<input type="text" name="email" maxlength="120" value="<?php echo $user_data->email; ?>" />
</p>

<p>
	<label for="password"><?php echo lang('user_password')?></label>
	<input type="password" name="password" maxlength="20" value="<?php echo $user_data->password; ?>" />
</p>

<input type="submit" value="<?php echo lang('user_login_btn') ?>" name="btnLogin" />
	
<?php echo form_close(); ?>

<p><?php echo anchor('users/reset_pass', lang('user_reset_password_link'));?> | <?php echo anchor('register', lang('user_register_btn'));?></p>