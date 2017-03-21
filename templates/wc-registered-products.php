<table>
	<thead>
		<tr>
			<td>Your Products</td>
			<td>Unique Code</td>
		</tr>
	</thead>
	<tbody>
	<?php
	$registered_products = $this->get_registered_products( get_current_user_id() );
	foreach( (array)$registered_products as $rp ) :
		$product = new WC_Product( $rp->product_id );
	?>
		<tr>
			<td><?php echo $product->get_title(); ?></td>
			<td><?php echo $rp->unique_code; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php
#echo '<h3>Register a product</h3>';
#include "form-unique_code_product.php";
?>