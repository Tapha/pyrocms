<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<!-- Stylesheets -->
		<?php echo css('reset.css', 'upgrade'); ?>
		<?php echo css('style.css', 'upgrade'); ?>
		<?php echo js('jquery/jquery.js'); ?>
		<title><?php echo lang('upgrade.title'); ?></title>
	</head>
	<body>
		<!-- Main wrapper -->
		<div id="wrapper">
			<div id="logo">
				<?php echo image('logo.png', 'upgrade'); ?>
			</div>
			<!-- The header -->
			<div id="header">
				<ul>
					<li><?php echo anchor('upgrade', lang('upgrade.links.intro'), $this->uri->segment(2, '') == '' ? 'id="current"' : ''); ?></li>
					<li><span id="<?php echo $this->uri->segment(2, '') == 'process' ? 'current' : ''?>"><?php echo lang('upgrade.links.process'); ?></span></li>
					<li><span id="<?php echo $this->uri->segment(2, '') == 'complete' ? 'current' : ''?>"><?php echo lang('upgrade.links.complete'); ?></span></li>
				</ul>
			</div>
			<!-- The content -->
			<div id="content">
				<?php if($this->session->flashdata('message')): ?>
				<div id="notification" class="<?php if($this->session->flashdata('message_type')){echo $this->session->flashdata('message_type');} ?>">
					<p><?php echo $this->session->flashdata('message'); ?></p>
				</div>
				<?php endif; ?>
				<?php echo $page_output; echo "\n"; ?>
			</div>
		</div>
	</body>
</html>
