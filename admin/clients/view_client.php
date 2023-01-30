<?php
require_once('./../../config.php');
if (isset($_GET['id']) && $_GET['id'] > 0) {
	$qry = $conn->query("SELECT * from `client_list` where id = '{$_GET['id']}' and delete_flag = 0 ");
	if ($qry->num_rows > 0) {
		foreach ($qry->fetch_assoc() as $k => $v) {
			$$k = $v;
		}
	} else {
		echo '<script>alert("ID Cliente no válido"); location.replace("./?page=clients")</script>';
	}
} else {
	echo '<script>alert("ID Cliente es Obligatorio"); location.replace("./?page=clients")</script>';
}
?>
<style>
	#uni_modal .modal-footer {
		display: none;
	}
</style>
<div class="container-fluid">
	<dl>
		<dt class="text-muted">Código</dt>
		<dd class="pl-4"><?= isset($code) ? $code : "" ?></dd>
		<dt class="text-muted">Nombre</dt>
		<dd class="pl-4"><?= isset($name) ? $name : "" ?></dd>
		<dt class="text-muted">Teléfono</dt>
		<dd class="pl-4"><?= isset($contact) ? $contact : '' ?></dd>
		<dt class="text-muted">Estado</dt>
		<dd class="pl-4">
			<?php if ($status == 1) : ?>
				<span class="badge badge-success px-3 rounded-pill">Activo</span>
			<?php else : ?>
				<span class="badge badge-danger px-3 rounded-pill">Inactivo</span>
			<?php endif; ?>
		</dd>
	</dl>
</div>
<hr class="mx-n3">
<div class="text-right pt-1">
	<button class="btn btn-sm btn-flat btn-light bg-gradient-light border" type="button" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
</div>