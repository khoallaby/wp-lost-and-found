<?php
if( $_POST ) {
	#do_action( 'product_register_post', $_POST['product_id'], $_POST['unique_code'] );
	$register = $this->product_register_post( $_POST['product_id'], $_POST['unique_code'] );
}

if( $this->errors ) {
?>
	<div class="alert alert-danger"><?php echo $this->errors; ?></div>
<?php } elseif( !$this->errors && $_POST && $register ) { ?>
	<div class="alert alert-success">Successfully added unique code: (<?php echo $_POST['unique_code']; ?>)</div>
<?php } ?>


<h3>Register a product</h3>
<form action="" method="post">
	<div>
		<label>
			<span>Unique Code</span>
			<input type="text" name="unique_code" value="<?php echo isset($_GET['ucode']) ? $_GET['ucode'] : ''; ?>" />
		</label>
	</div>
	<div>
		<label>
			<span>Product</span>
			<select name="product_id">
				<option></option>
				<?php
				$products = $this->wc_get_all_products();
				foreach( $products as $product ) :
				?>
				<option value="<?php echo $product->id; ?>"><?php echo $product->post->post_title; ?></option>
		        <?php endforeach; ?>
			</select>
		</label>
	</div>
	<div>
		<input type="submit" value="Submit" />
	</div>
</form>