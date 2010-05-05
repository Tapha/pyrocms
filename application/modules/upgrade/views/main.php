<!-- Welcome -->
<h2><?php echo lang('upgrade.messages.title'); ?></h2>
<p><?php echo lang('upgrade.messages.intro'); ?></p>

<p>
	<strong><?php echo lang('upgrade.labels.current_version'); ?>:</strong> <?php echo $current_version; ?><br />
	<strong><?php echo lang('upgrade.labels.target_version'); ?>:</strong> <?php echo $target_version; ?>
</p>


<p id="next_step"><a href="<?php echo site_url('upgrade/process'); ?>"><?php echo lang('upgrade.labels.upgrade'); ?></a></p>

<br class="clear" />