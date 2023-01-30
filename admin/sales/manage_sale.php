<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
	if (!is_numeric($_GET['id'])) {
		throw new ErrorException("ID de Venta Inválido");
	}
	$sid = $_GET['id'];
	$sales_qry = $conn->query("SELECT *, COALESCE((SELECT CONCAT(`code`, ' ', `name`) as `client` FROM `client_list` where `client_list`.`id` = `sales`.`client_id`), 'Genérico') as `client_name`, COALESCE((SELECT `username` FROM `users` where `users`.`id` = `sales`.`user_id`), 'Usuario eliminado, correctamente') as `encoder` FROM `sales` where `id` = '{$sid}'");
	if ($sales_qry->num_rows > 0) {
		foreach ($sales_qry->fetch_assoc() as $k => $v) {
			$$k = $v;
		}
	} else {
		throw new ErrorException("ID de Venta Inválido");
	}
}
?>
<style>
	input.no-spinner[type=number]::-webkit-inner-spin-button,
	input.no-spinner[type=number]::-webkit-outer-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	#salesTbl td {
		vertical-align: middle !important;
	}
</style>
<div class="card card-outline rounded-0 card-teal">
	<div class="card-header">
		<h3 class="card-title"><?= isset($id) ? "Actualizar Información de Venta" : "Registrar Venta" ?></h3>
		<div class="card-tools">
			<button class="btn btn-primary btn-sm btn-flat rounded-0" type="submit" form="sales-form"><i class="fa fa-save"></i> Guardar</button>
			<?php if (isset($id)) : ?>
				<a href="<?= base_url . "admin/?page=sales/view_sales&id={$id}" ?>" class="btn btn-flat btn-default border btn-sm"><span class="fas fa-angle-left"></span> Cancelar</a>
			<?php else : ?>
				<a href="<?= base_url . "admin/?page=sales" ?>" class="btn btn-flat btn-default border btn-sm"><span class="fas fa-angle-left"></span> Cancelar</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<form action="" id="sales-form">
				<input type="hidden" name="id" value="<?= isset($id) ? $id : "" ?>">
				<input type="hidden" name="total" value="<?= isset($total) ? $total : "" ?>">
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="invoice_code" class="control-label">Factura / Código</label>
							<input type="text" class="form-control form-control-sm rounded-0" id="invoice_code" name="invoice_code" value="<?= isset($invoice_code) ? $invoice_code : "" ?>" readonly>
							<small class="text-muted font-weight-light"><em>Se generará un código automáticamente</em></small>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="invoice_code" class="control-label">Cliente</label>
							<select class="form-control form-control-sm rounded-0" id="client_id" name="client_id" required <?= isset($is_guest) && $is_guest == 1 ? "disabled" : "" ?>>
								<option value="" <?= !isset($client_id) ? "selected" : "" ?> disabled="disabled"></option>
								<?php
								$client_qry = $conn->query("SELECT `id`, `code`, `name` FROM `client_list` where `delete_flag` = 0 " . (isset($client_id) && !is_null($client_id) ? " or `id` ='{$id}'" : "") . " order by `name` asc");
								while ($row = $client_qry->fetch_assoc()) :
								?>
									<option value="<?= $row['id'] ?>" <?= (isset($client_id) && $client_id == $row['id'] ? "selected" : "") ?>><?= $row['code'] . " " . (ucwords($row['name'])) ?></option>
								<?php endwhile; ?>
							</select>
							<div class="custom-control custom-switch">
								<input type="checkbox" name="is_guest" value="1" class="custom-control-input custom-control-input-success" id="is_guest" <?= isset($is_guest) && $is_guest == 1 ? "checked" : "" ?>>
								<label class="custom-control-label" for="is_guest">Cliente Genérico</label>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="row align-items-end">
					<div class="col-lg-5 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="product" class="control-label">Producto</label>
							<select id="product" class="form-control form-control-sm rounded-0">
								<option value="" selected disabled="disabled"></option>
								<?php
								$product_prices = [];
								$product_names = [];
								$product_code = [];
								$product_qry = $conn->query("SELECT `id`, `code`, `name`, `price` FROM `product_list` where `delete_flag` = 0 order by `name` asc");
								$products = $product_qry->fetch_all(MYSQLI_ASSOC);
								$product_prices = array_column($products, 'price', 'id');
								$product_names = array_column($products, 'name', 'id');
								$product_code = array_column($products, 'code', 'id');
								foreach ($products as $row) :
								?>
									<option value="<?= $row['id'] ?>"><?= $row['code'] . " " . (ucwords($row['name'])) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col-auto">
						<div class="form-group">
							<button class="btn-default border btn-sm btn-flat rounded-0" id="add_item"><i class="far fa-plus-square"></i> Agregar Producto</button>
						</div>
					</div>
				</div>
				<hr>
				<div class="table-responsive">
					<table class="table table-stripped table-bordered table-hover table-sm" id="salesTbl">
						<colgroup>
							<col width="5%">
							<col width="10%">
							<col width="35%">
							<col width="25%">
							<col width="25%">
						</colgroup>
						<thead>
							<tr class="bg-gradient bg-teal">
								<th class="text-center"></th>
								<th class="text-center">CANT</th>
								<th class="text-center">Producto</th>
								<th class="text-center">Precio</th>
								<th class="text-center">Sub-Total</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (isset($id)) :
								$sales_items = $conn->query("SELECT si.*, pl.name, pl.code FROM `sales_items` si inner join `product_list` pl on pl.id = si.product_id where si.`sales_id` = '{$id}'");
								while ($row = $sales_items->fetch_assoc()) :
							?>
									<tr>
										<td class="text-center">
											<button class="btn btn-sm btn-flat btn-outline-danger py-1 rem-item" type="button"><i class="fa fa-times"></i></button>
										</td>
										<td class="text-center">
											<input type="hidden" name="product_id[]" value="<?= $row['product_id'] ?>">
											<input type="number" class="border-0 text-center" min="1" max="1000" name="quantity[]" value="<?= $row['quantity'] ?>" required>
										</td>
										<td>
											<div style="line-height:1em">
												<div class="font-weight-light text-muted"><small class="product_code"><?= $row['code'] ?></small></div>
												<div class="product_name"><?= $row['name'] ?></div>
											</div>
										</td>
										<td class="text-center">
											<input type="number" class="border-0 w-100 text-right no-spinner" step="any" min="0" name="price[]" value="<?= $row['price'] ?>" required>
										</td>
										<td>
											<div class="text-right font-weight-bold sub-total"><?= format_num(($row['price'] * $row['quantity']), 2) ?></div>
										</td>
									</tr>
								<?php endwhile; ?>
							<?php endif; ?>
						</tbody>
						<tfoot>
							<tr class="bg-gradient-secondary">
								<th colspan="4" class="text-right">Total</th>
								<th id="total" class="text-right font-weight"><?= isset($total) ? $total : "0.00" ?></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="4" class="text-right">IVA (19%)</th>
								<th id="tax" class="text-right font-weight"><?= isset($total) ? format_num(($total * .19), 2) : "0.00" ?></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="4" class="text-right">Dinero Cliente</th>
								<th><input type="number" class="form-control form-control-sm rounded-0 text-right no-spinner" step="any" name="tendered" id="tendered" value="<?= isset($tendered) ? $tendered : 0 ?>"></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="4" class="text-right">Cambio</th>
								<th id="change" class="text-right font-weight"><?= isset($tendered) && isset($total) ? format_num(($tendered - $total), 2) : 0 ?></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	var item_row_content = `
						<td class="text-center">
							<button class="btn btn-sm btn-flat btn-outline-danger py-1 rem-item" type="button"><i class="fa fa-times"></i></button>
						</td>
						<td class="text-center">
							<input type="hidden" name="product_id[]">
							<input type="number" class="border-0 text-center" min="1" max="1000" name="quantity[]" value="1" required>
						</td>
						<td>
							<div style="line-height:1em">
								<div class="font-weight-light text-muted"><small class="product_code"></small></div>
								<div class="product_name"></div>
							</div>
						</td>
						<td class="text-center">
							<input type="number" class="border-0 w-100 text-right no-spinner" step="any" min="0" name="price[]" required>
						</td>
						<td>
							<div class="text-right font-weight-bold sub-total">0.00</div>
						</td>
					`;
	var client_select2;
	var product_select2;
	var prices = `<?= json_encode($product_prices) ?>`;
	var names = `<?= json_encode($product_names) ?>`;
	var code = `<?= json_encode($product_code) ?>`;

	function calc_totals() {
		var tbl = $('#salesTbl')
		var total = 0;
		var tax = 0;
		var tendered = $('#tendered').val();
		var change = 0;
		tendered = tendered > 0 ? tendered : 0;

		tbl.find('tbody tr').each(function() {
			var quantity = $(this).find('input[name="quantity[]"]').val()
			quantity = quantity > 0 ? quantity : 0;

			var price = $(this).find('input[name="price[]"]').val()
			price = price > 0 ? price : 0;

			var sub_total = parseFloat(quantity) * parseFloat(price)
			total += parseFloat(sub_total)
		})
		// console.log(tbl.find('body tr'))
		$('input[name="total"]').val(total)
		$('#total').text(total.toLocaleString("en-US", {
			style: "decimal",
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}))
		tax = parseFloat(total * .12);
		$('#tax').text(tax.toLocaleString("en-US", {
			style: "decimal",
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}))
		change = tendered - total;
		$('#change').text(change.toLocaleString("en-US", {
			style: "decimal",
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}))

	}
	$(document).ready(function() {
		prices = $.parseJSON(prices);
		names = $.parseJSON(names);
		code = $.parseJSON(code);
		if ($('#is_guest').is(':checked') === false) {
			client_select2 = $('#client_id').select2({
				"placeholder": "Selecciona Cliente Registrado",
				"containerCssClass": "rounded-0"
			})
		}

		product_select2 = $('#product').select2({
			"placeholder": "Selecciona el producto",
			"containerCssClass": "rounded-0"
		})

		$('#add_item').click(function(e) {
			e.preventDefault();
			var id = $('#product').val()
			if (id == "" || id == null || id == undefined)
				return false;
			if ($(`#salesTbl tbody tr[data-id="${id}"]`).length > 0) {
				alert("El producto ya se encuentra ingresado");
				return false;
			}
			var tr = $('<tr>')
			tr.append(item_row_content)
			if (!!prices[id]) {
				tr.find('input[name="price[]"]').val(prices[id])
				tr.find('.sub-total').text(parseFloat(prices[id]).toLocaleString("en-US", {
					style: "decimal",
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				}))
			}
			if (!!names[id]) {
				tr.find('.product_name').text(names[id])
			}
			if (!!code[id]) {
				tr.find('.product_code').text(code[id])
			}
			tr.find('[name="product_id[]"]').val(id)

			$('#salesTbl tbody').append(tr)
			tr.find('.rem-item').click(function(e) {
				e.preventDefault()
				if (confirm(`¿Desear eliminar este producto de la lista?`) === true) {
					tr.remove();
					calc_totals()
				}
			})
			tr.find('input[name="quantity[]"]').on('input change', function(e) {
				e.preventDefault()
				var quantity = $(this).val()
				quantity = quantity > 0 ? quantity : 0;
				var price = !!prices[id] && prices[id] > 0 ? prices[id] : 0;
				var sub = parseFloat(quantity) * parseFloat(price);
				tr.find('.sub-total').text(parseFloat(sub).toLocaleString("en-US", {
					style: "decimal",
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				}))
				calc_totals()

			})
			tr.attr('data-id', id);
			calc_totals()
			$("#product").val('').trigger('change')
		})

		$('#salesTbl tbody tr').find('.rem-item').click(function(e) {
			e.preventDefault()
			if (confirm(`¿Deseas eliminar este producto de la lista?`) === true) {
				$(this).closest('tr').remove();
				calc_totals()
			}
		})
		$('#salesTbl tbody').find('input[name="quantity[]"]').on('input change', function(e) {
			e.preventDefault()
			var quantity = $(this).val()
			quantity = quantity > 0 ? quantity : 0;
			var price = $(this).closest('tr').find('input[name="price[]"]').val();
			price = price > 0 ? price : 0;
			var sub = parseFloat(quantity) * parseFloat(price);
			$(this).closest('tr').find('.sub-total').text(parseFloat(sub).toLocaleString("en-US", {
				style: "decimal",
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			}))
			calc_totals()

		})

		$('#tendered').on('input change', function(e) {
			e.preventDefault()
			calc_totals()
		})

		$('#is_guest').on('change', function(e) {
			e.preventDefault()
			var is_checked = $(this).is(":checked")
			if (is_checked == true) {
				$('#client_id').val('').trigger('change')
				client_select2.select2('destroy')
				$('#client_id').attr('required', false)
				$('#client_id').attr('disabled', true)
			} else {
				$('#client_id').val(`<?= isset($client_id) ? $client_id : '' ?>`).trigger('change')
				$('#client_id').attr('required', true)
				$('#client_id').attr('disabled', false)
				client_select2 = $('#client_id').select2({
					"placeholder": "Selecciona Cliente",
					"containerCssClass": "rounded-0"
				})
			}
		})
		$('#sales-form').submit(function(e) {
			e.preventDefault();
			var _this = $(this)
			$('.err-msg').remove();
			var change = $('#change').text()
			change = change.replace(/,/gi, '')
			change = !isNaN(change) ? change : 0;
			if (change < 0) {
				alert_toast(" Monto de cliente incorrecto", "warning")
				return false;
			}
			if ($('#salesTbl tbody tr').length <= 0) {
				alert_toast(" Seleccione al menos 1 artículo del producto primero ", "warning")
				return false;
			}
			start_loader();
			$.ajax({
				url: _base_url_ + "classes/Master.php?f=save_sale",
				data: new FormData($(this)[0]),
				cache: false,
				contentType: false,
				processData: false,
				method: 'POST',
				type: 'POST',
				dataType: 'json',
				error: err => {
					console.log(err)
					alert_toast("Ocurrió un error", 'error');
					end_loader();
				},
				success: function(resp) {
					if (typeof resp == 'object' && resp.status == 'success') {
						location.replace("<?= base_url ?>admin/?page=sales/view_sales&id=" + resp.sid);
					} else if (resp.status == 'error' && !!resp.error) {
						var el = $('<div>')
						el.addClass("alert alert-danger err-msg").text(resp.error)
						_this.prepend(el)
						el.show('slow')
						$("html, body").scrollTop(0);
						end_loader()
						if (!!resp.error_details)
							console.error(resp.error_details)
					} else {
						alert_toast("Ocurrió un error", 'error');
						end_loader();
						console.error(resp)
					}
				}
			})
		})

	})
</script>