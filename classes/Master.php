<?php
require_once('../config.php');
class Master extends DBConnection
{
	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	function capture_err()
	{
		if (!$this->conn->error)
			return false;
		else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function delete_img()
	{
		extract($_POST);
		if (is_file($path)) {
			if (unlink($path)) {
				$resp['status'] = 'success';
			} else {
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete ' . $path;
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown ' . $path . ' path';
		}
		return json_encode($resp);
	}
	function save_product()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!empty($data)) $data .= ",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `product_list` where `code` = '{$code}' and delete_flag = 0 " . (!empty($id) ? " and id != {$id} " : "") . " ")->num_rows;
		if ($this->capture_err())
			return $this->capture_err();
		if ($check > 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = "El código del producto ya existe. El código debe ser único";
			return json_encode($resp);
			exit;
		}
		if (empty($id)) {
			$sql = "INSERT INTO `product_list` set {$data} ";
		} else {
			$sql = "UPDATE `product_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		if ($save) {
			$cid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['cid'] = $cid;
			$resp['status'] = 'success';
			if (empty($id))
				$resp['msg'] = "Nuevo producto guardado con éxito.";
			else
				$resp['msg'] = " Producto actualizado con éxito.";
		} else {
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error . "[{$sql}]";
		}
		// if($resp['status'] == 'success')
		// 	$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_product()
	{
		extract($_POST);
		$del = $this->conn->query("UPDATE `product_list` set `delete_flag` = 1 where id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', " Producto eliminado con éxito.");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_client()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!empty($data)) $data .= ",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}

		if (empty($id)) {
			$code_pref = date("Ymd");
			$code_num = "1";
			while (true) {
				$tmp_code = $code_pref . "-" . (sprintf("%'.04d", $code_num));
				$check_code = $this->conn->query("SELECT id FROM `client_list` where `code` = '{$tmp_code}'")->num_rows;
				if ($check_code > 0) {
					$code_num++;
				} else {
					if (!empty($data)) $data .= ",";
					$data .= " `code`='$tmp_code' ";
					break;
				}
			}
			$sql = "INSERT INTO `client_list` set {$data} ";
		} else {
			$sql = "UPDATE `client_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		if ($save) {
			$cid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['cid'] = $cid;
			$resp['status'] = 'success';
			if (empty($id))
				$resp['msg'] = "Cliente registrad@ correctamente";
			else
				$resp['msg'] = " Cliente actualizad@ correctamente";
		} else {
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error . "[{$sql}]";
		}
		// if($resp['status'] == 'success')
		// 	$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_client()
	{
		extract($_POST);
		$del = $this->conn->query("UPDATE `client_list` set `delete_flag` = 1 where id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', " Cliente eliminad@ correctamente");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_sale()
	{
		extract($_POST);
		if (empty($invoice_code)) {
			$code_pref = date("Ymd");
			$code = 1;
			while (true) {
				$tmp_code = $code_pref . (sprintf("%'.05d", $code));
				$check_invoice_code = $this->conn->query("SELECT `id` FROM `sales` where `invoice_code` = '{$tmp_code}' and `delete_flag` = 0")->num_rows;
				if ($check_invoice_code > 0) {
					$code++;
				} else {
					$_POST['invoice_code'] = $tmp_code;
					$invoice_code = $tmp_code;
					break;
				}
			}
		} else {
			$check_invoice_code = $this->conn->query("SELECT `id` FROM `sales` where `invoice_code` = '{$invoice_code}' and `delete_flag` = 0 " . (isset($id) && !empty($id) ? " and `id` != '{$id}' " : ""))->num_rows;
			if ($check_invoice_code > 0) {
				return json_encode(['status' => 'error', 'error' => "El código de factura de venta ingresado ya existe."]);
			}
		}
		if (isset($_POST['client_id']) && empty($_POST['client_id'])) {
			$_POST['client_id'] = "";
		}
		if (isset($_POST['is_guest'])) {
			$_POST['is_guest'] = 1;
		} else {
			$_POST['is_guest'] = 0;
		}
		$_POST['user_id'] = $_SESSION['userdata']['id'];
		// print_r($_POST);
		$data = "";
		$sales_allowed_field = ['invoice_code', 'client_id', 'notes', 'total', 'tendered', 'is_guest', 'user_id'];
		foreach ($_POST as $k => $v) {
			if (!in_array($k, $sales_allowed_field))
				continue;
			if (!is_numeric($v) && !empty($v)) {
				$v = $this->conn->real_escape_string(addslashes($v));
			}
			if (!empty($data)) $data .= ", ";
			if (empty($v) && !is_numeric($v)) {
				$data .= " `{$k}` = NULL ";
			} else {
				$data .= " `{$k}` = '{$v}' ";
			}
		}
		if (empty($id)) {
			$sales_sql = "INSERT INTO `sales` set {$data}";
		} else {
			$sales_sql = "UPDATE `sales` set {$data} where `id` = '{$id}'";
		}
		$save_sales = $this->conn->query($sales_sql);
		if ($save_sales) {
			if (empty($id)) {
				$sid = $this->conn->insert_id;
			} else {
				$sid = $id;
			}
			$data2 = "";
			foreach ($product_id as $k => $v) {
				if (!empty($data2)) $data2 .= ", ";
				$data2 .= "('{$sid}', '{$v}', '$price[$k]', '{$quantity[$k]}')";
			}
			$this->conn->query("DELETE FROM `sales_items` where `sales_id` = '{$sid}'");
			if (!empty($data2)) {
				$sales_item_sql = "INSERT INTO `sales_items` (`sales_id`, `product_id`, `price`, `quantity`) VALUES {$data2}";
				$sales_items_save = $this->conn->query($sales_item_sql);
			}

			if (!isset($this->conn->error) || (isset($this->conn->error) && empty($this->conn->error))) {
				if (empty($id)) {
					$this->settings->set_flashdata('success', " Los datos de ventas se han agregado con éxito");
				} else {
					$this->settings->set_flashdata('success', " Los datos de ventas se han actualizado correctamente.");
				}
				return json_encode(['status' => "success", "sid" => $sid]);
			} else {
				if (isset($sid))
					$this->conn->query("DELETE FROM `sales` where `id` = '{$sid}'");
				return json_encode(['status' => "error", "error" => "Ocurrió un error al guardar los datos.", "error_details" => $this->conn->error]);
			}
		} else {
			return json_encode(['status' => "error", "error" => "Ocurrió un error al guardar los datos.", "error_details" => $this->conn->error]);
		}
	}
	function delete_sale()
	{
		extract($_POST);
		$del = $this->conn->query("UPDATE `sales` set `delete_flag` = 1 where id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', " Los detalles de la venta se han eliminado correctamente.");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'delete_img':
		echo $Master->delete_img();
		break;
	case 'save_product':
		echo $Master->save_product();
		break;
	case 'delete_product':
		echo $Master->delete_product();
		break;
	case 'save_client':
		echo $Master->save_client();
		break;
	case 'delete_client':
		echo $Master->delete_client();
		break;
	case 'save_sale':
		echo $Master->save_sale();
		break;
	case 'delete_sale':
		echo $Master->delete_sale();
		break;
	default:
		// echo $sysset->index();
		break;
}
