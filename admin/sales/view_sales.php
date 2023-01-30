<?php
if (!isset($_GET['id']) || (isset($_GET['id']) && !is_numeric($_GET['id']))) {
	throw new ErrorException("Invalid Sales ID");
}
$sid = $_GET['id'];
$sales_qry = $conn->query("SELECT *, COALESCE((SELECT CONCAT(`code`, ' ', `name`) as `client` FROM `client_list` where `client_list`.`id` = `sales`.`client_id`), 'Genérico') as `client_name`, COALESCE((SELECT `username` FROM `users` where `users`.`id` = `sales`.`user_id`), 'User has been deleted') as `encoder` FROM `sales` where `id` = '{$sid}'");
if ($sales_qry->num_rows > 0) {
	foreach ($sales_qry->fetch_assoc() as $k => $v) {
		$$k = $v;
	}
} else {
	throw new ErrorException("Invalid Sales ID");
}

?>
<style>
	#salesTbl td {
		vertical-align: middle !important;
	}
</style>
<div class="card card-outline rounded-0 card-teal">
	<div class="card-header">
		<h3 class="card-title">Informe de Ventas</h3>
		<div class="card-tools">
			<a class="btn btn-primary btn-sm btn-flat rounded-0" href="<?= base_url . "admin/?page=sales/manage_sale&id={$id}" ?>"><i class="fa fa-save"></i> Editar</a>
			<button class="btn btn-danger btn-sm btn-flat rounded-0" id="delete_sales"><i class="fa fa-trash"></i> Eliminar</button>

			<a href="<?= base_url . "admin/?page=sales" ?>" class="btn btn-flat btn-default border btn-sm"><span class="fas fa-angle-left"></span> Volver</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<form action="" id="sales-form">

				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="invoice_code" class="control-label">Factura:</label>
							<div class="font-weight-bolder pl-4"><?= isset($invoice_code) ? $invoice_code : "" ?></div>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="invoice_code" class="control-label">Cliente:</label>
							<div class="font-weight-bolder pl-4"><?= isset($client_name) ? $client_name : "" ?></div>
						</div>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-stripped table-bordered table-hover table-sm" id="salesTbl">
						<colgroup>
							<col width="15%">
							<col width="35%">
							<col width="25%">
							<col width="25%">
						</colgroup>
						<thead>
							<tr class="bg-gradient bg-teal">
								<th class="text-center">CANT</th>
								<th class="text-center">Producto</th>
								<th class="text-center">Precio</th>
								<th class="text-center">Sub-Total</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sales_items = $conn->query("SELECT si.*, pl.name, pl.code FROM `sales_items` si inner join `product_list` pl on pl.id = si.product_id where si.`sales_id` = '{$id}'");
							while ($row = $sales_items->fetch_assoc()) :
							?>
								<tr>

									<td class="text-center">
										<?= format_num($row['quantity']) ?>
									</td>
									<td>
										<div style="line-height:1em">
											<div class="font-weight-light text-muted"><small class="product_code"><?= $row['code'] ?></small></div>
											<div class="product_name"><?= $row['name'] ?></div>
										</div>
									</td>
									<td class="text-right">
										<?= format_num(($row['price']), 2) ?>
									</td>
									<td>
										<div class="text-right font-weight-bold sub-total"><?= format_num(($row['price'] * $row['quantity']), 2) ?></div>
									</td>
								</tr>
							<?php endwhile; ?>
						</tbody>
						<tfoot>
							<tr class="bg-gradient-secondary">
								<th colspan="3" class="text-right">Total</th>
								<th id="total" class="text-right font-weight"><?= isset($total) ? $total : "0.00" ?></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="3" class="text-right">IVA (18%)</th>
								<th id="tax" class="text-right font-weight"><?= isset($total) ? format_num(($total * .18), 2) : "0.00" ?></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="3" class="text-right">Monto Pagado</th>
								<th class="text-right"><?= isset($tendered) ? format_num($tendered, 2) : 0 ?></th>
							</tr>
							<tr class="bg-gradient-secondary">
								<th colspan="3" class="text-right">Cambio</th>
								<th id="change" class="text-right font-weight"><?= isset($tendered) && isset($total) ? format_num(($tendered - $total), 2) : 0 ?></th>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<div class="form-group">
							<label for="invoice_code" class="control-label">Venta realizada por:</label>
							<div class="font-weight-bolder pl-4"><?= isset($encoder) ? $encoder : "" ?></div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	$(function() {
		$('#delete_sales').click(function() {
			_conf("¿Deseas eliminar esta venta de forma permanente?", "delete_sale", ["<?= isset($id) ? $id : "" ?>"])
		})
	})

	function delete_sale($id) {
		start_loader();
		$.ajax({
			url: _base_url_ + "classes/Master.php?f=delete_sale",
			method: "POST",
			data: {
				id: $id
			},
			dataType: "json",
			error: err => {
				console.log(err)
				alert_toast("Ocurrió un error", 'error');
				end_loader();
			},
			success: function(resp) {
				if (typeof resp == 'object' && resp.status == 'success') {
					location.replace('./?page=sales');
				} else {
					alert_toast("Ocurrió un error", 'error');
					end_loader();
				}
			}
		})
	}
</script>