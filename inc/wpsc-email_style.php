<?php
/**
 * Template file that applies styling around all emails, like gift wrapping. 
 * Content is generated elsewhere, and inserted using ecse_get_email_content()
 */


?>
<html>
	<head>
		<title><?php echo ecse_get_email_subject(); ?></title>
		<style>
		@font-face {
			font-family: 'linux_libertine_cregular';
			src: url('<?php echo get_stylesheet_directory_uri() ?>/fonts/linlibertinec_re-4.0_.1_-webfont.eot');
			src: url('<?php echo get_stylesheet_directory_uri() ?>/fonts/linlibertinec_re-4.0_.1_-webfont.eot?#iefix') format('embedded-opentype'),
			url('<?php echo get_stylesheet_directory_uri() ?>/fonts/linlibertinec_re-4.0_.1_-webfont.woff') format('woff'),
			url('<?php echo get_stylesheet_directory_uri() ?>/fonts/linlibertinec_re-4.0_.1_-webfont.ttf') format('truetype'),
			url('<?php echo get_stylesheet_directory_uri() ?>/fonts/linlibertinec_re-4.0_.1_-webfont.svg#linux_libertine_cregular') format('svg');
			font-weight: normal;
			font-style: normal;
		}
		html {
			width:100%; height:100%;
		}
		body {
			font-family: 'linux_libertine_cregular';
		}
		</style>
	</head>
	<body style="font-family:'linux_libertine_cregular', serif; font-size:12px; margin:0; width:100%; height:100%;">
		<style>
		body, p, td {
		  font-family:'linux_libertine_cregular', 'serif';
		  font-size:13px
		}
		td, tr, th {
			padding:2px 5px;
		}
		</style>
		<table cellspacing="4" cellpadding="4" style="width:11in; height:8.5in">
			<?php echo ecse_get_email_content(); ?>
		</table>
	</body>
</html>