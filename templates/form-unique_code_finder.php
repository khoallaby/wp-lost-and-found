<?php if( $this->errors ) { ?>
	<div class="alert alert-danger"><?php echo $this->errors; ?></div>
<?php } elseif( !$this->errors && $_POST && $this->success ) { ?>
	<div class="alert alert-success">Code found and  user has been emailed</div>
<?php } ?>

<form action="" method="post">
	<div>
		<label>
			<span>Unique Code</span>
			<input type="text" name="unique_code" value="" />
		</label>
	</div>
	<div>
		<label>
			<span>Email</span>
			<input type="text" name="email" value="" />
		</label>
	</div>
	<div>
		<label>
			<span>Details</span>
			<textarea name="details"></textarea>
		</label>
	</div>
	<div>
		<input type="submit" name="submit" value="Submit" />
	</div>

</form>