<style>
  #system-cover {
    width: 100%;
    height: 45em;
    object-fit: cover;
    object-position: center center;
  }
</style>
<h1 class="">Hola de nuevo <?php echo $_settings->userdata('firstname') . " " . $_settings->userdata('lastname') ?>!</h1>
<hr>
<div class="row">
  <div class="col-12 col-sm-4 col-md-4">
    <div class="info-box">
      <span class="info-box-icon bg-gradient-light elevation-1"><i class="fas fa-th-list"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Productos</span>
        <span class="info-box-number text-right h5">
          <?php
          $category = $conn->query("SELECT * FROM product_list where delete_flag = 0 and `status` = 1")->num_rows;
          echo format_num($category);
          ?>
          <?php ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->
  <div class="col-12 col-sm-4 col-md-4">
    <div class="info-box">
      <span class="info-box-icon bg-gradient-dark elevation-1"><i class="fas fa-th-list"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Clientes</span>
        <span class="info-box-number text-right h5">
          <?php
          $clients = $conn->query("SELECT * FROM client_list where delete_flag = 0 and `status` = 0")->num_rows;
          echo format_num($clients);
          ?>
          <?php ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->
  <div class="col-12 col-sm-4 col-md-4">
    <div class="info-box">
      <span class="info-box-icon bg-gradient-teal elevation-1"><i class="fas fa-coins"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total de Ventas del Mes</span>
        <span class="info-box-number text-right h5">
          <?php
          $sales_this_month = $conn->query("SELECT COALESCE(SUM(`total`), 0) as `grand_total` FROM `sales` where delete_flag = 0 and date_format(`created_at`, '%Y-%m') = '" . (date("Y-m")) . "' ")->fetch_assoc()['grand_total'];
          echo format_num($sales_this_month);
          ?>
          <?php ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->
</div>
<div class="container-fluid text-center">
  <img src="<?= validate_image($_settings->info('cover')) ?>" alt="system-cover" id="system-cover" class="img-fluid">
</div>