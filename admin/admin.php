<?php
if (isset($_POST['submit'])) {
	/* copy and overwrite $post for checkboxes */
	$form = $_POST;

	foreach ($defaults as $name=>$atts) {
		$type = isset($atts['type']) ? $atts['type'] : '';

		/* checkboxes don't get sent if not checked */
		if ($type === 'checkbox') {
			$form[$name] = isset($_POST[$name]) ? 1 : 0;
		}
		update_option($prefix . $name, stripslashes( $form[$name]) );
	}
?>
<div class="updated">
   <p>Options Updated!</p>
</div>
<?php
} elseif (isset($_POST['reset'])) {
	foreach ($defaults as $name=>$atts) {
		if (isset($atts['default']) && !isset($atts['noreset'])) {
			update_option($prefix . $name, $atts['default']);
		}
	}
?>
<div class="updated">
   <p>Options have been reset to default values!</p>
</div>
<?php
}
?>

<div class="wrap">
	<h2><?php echo $plugin_title; ?></h2>
	<div class="wrap">
	<form method="post">
	<?php
	function option_label ($opt) {
	    $opt = explode('_', $opt);
	    
	    foreach($opt as &$v) {
	        $v = ucfirst($v);
	    }
	    echo implode(' ', $opt);
	}

	foreach ($defaults as $name=>$atts) {
		$type = isset($atts['type']) ? $atts['type'] : '';
	?>
	<div class="container">
		<label>
			<span class="label"><?php option_label($name); ?></span>
			<span class="input-group">
			<?php
			if ($type === 'select') {
			?>
                <select id="<?php echo $name; ?>"
                	name="<?php echo $name; ?>"
                	class="full-width">
                <?php
                foreach ($atts['options'] as $o => $n) {
                ?>
                    <option value="<?php echo $o; ?>"<?php if (get_option($prefix . $name) == $o) echo ' selected' ?>>
                    	<?php echo $n; ?>
                   	</option>
                <?php
                }
                ?>
                </select>
			<?php
			} elseif ($type === 'text') {
			?>
				<input 
					class="full-width" 
					name="<?php echo $name; ?>" 
					type="text" 
					id="<?php echo $name; ?>" 
					value="<?php echo htmlspecialchars( get_option($prefix . $name, $atts['default']) ); ?>" 
					/>
			<?php
			} elseif ($type === 'textarea') {
			?>
				<textarea 
					id="<?php echo $name; ?>"
					class="full-width" 
					name="<?php echo $name; ?>"><?php echo htmlspecialchars( get_option($prefix . $name, $atts['default']) ); ?></textarea>
			<?php
			} elseif ($type === 'checkbox') {
			?>
				<input 
					class="checkbox" 
					name="<?php echo $name; ?>" 
					type="checkbox" 
					id="<?php echo $name; ?>"
					<?php if (get_option($prefix . $name, $atts['default'])) echo ' checked="checked"' ?> 
					/>
			<?php
			}
			?>
			</span>
		</label>
		<?php
		if (isset($atts['helptext'])) {
			?>
		<div class="helptext">
			<p class="description"><?php echo $atts['helptext']; ?></p>
		</div>
			<?php
		}
		?>
	</div>
	<?php
	}
	?>

	<div class="container">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
	</div>
	<div class="container">
		<input type="submit" name="reset" id="reset" class="button button-secondary" value="Reset to Defaults">
	</div>

	</form>
	</div>
</div>