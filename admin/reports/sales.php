<?php
$month = isset($_GET['month']) ? $_GET['month'] : date("Y-m");
?>
<div class="content py-5 px-3 bg-gradient-secondary">
    <h2>Reporte Mensual de Ventas</h2>
</div>
<div class="row flex-column mt-4 justify-content-center align-items-center mt-lg-n4 mt-md-3 mt-sm-0">
    <div class="col-lg-11 col-md-11 col-sm-12 col-xs-12">
        <div class="card rounded-0 mb-2 shadow">
            <div class="card-body">
                <fieldset>
                    <legend>Filtro</legend>
                    <form action="" id="filter-form">
                        <div class="row align-items-end">
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="month" class="control-label">Selecciona el Mes</label>
                                    <input type="month" class="form-control form-control-sm rounded-0" name="month" id="month" value="<?= $month ?>" required="required">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-flat btn-secondary"><i class="fa fa-filter"></i> Filtro</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="col-lg-11 col-md-11 col-sm-12 col-xs-12">
        <div class="card rounded-0 mb-2 shadow">
            <div class="card-header py-1">
                <div class="card-tools">
                    <button class="btn btn-flat btn-sm btn-light bg-gradient-light border text-dark" type="button" id="print"><i class="fa fa-print"></i> Imprimir</button>
                </div>
            </div>
            <div class="card-body">
                <div class="container-fluid" id="printout">
                    <div class="table-responsive">
                        <table class="table table-bordered table-stripped table-sm">
                            <!-- <colgroup>
                                <col width="10%">
                                <col width="15%">
                                <col width="30%">
                                <col width="15%">
                                <col width="30%">
                            </colgroup> -->
                            <thead>
                                <tr class="bg-gradient-secondary">
                                    <th class="px-1 py-1 text-center">#</th>
                                    <th class="px-1 py-1 text-center">Fecha Creación</th>
                                    <th class="px-1 py-1 text-center">Cliente</th>
                                    <th class="px-1 py-1 text-center">No. de Producto</th>
                                    <th class="px-1 py-1 text-center">Vendido por</th>
                                    <th class="px-1 py-1 text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $g_total = 0;
                                $i = 1;
                                $sales = $conn->query("SELECT *, COALESCE((SELECT CONCAT(`code`, ' ', `name`) as `client` FROM `client_list` where `client_list`.`id` = `sales`.`client_id`), 'Genérico') as `client_name`, COALESCE((SELECT `username` FROM `users` where `users`.`id` = `sales`.`user_id`), 'User has been deleted') as `encoder`, COALESCE((SELECT SUM(`quantity`) FROM `sales_items` where `sales_items`.`sales_id` = `sales`.`id`), '0') as `items` from `sales` where delete_flag = 0 and date_format(`created_at`, '%Y-%m') = '{$month}' order by abs(unix_timestamp(`created_at`)) desc ");
                                // $stock = $conn->query("SELECT s.*, i.name as `item`, c.name as `category`, i.unit FROM `waste_list` s inner join `item_list` i on s.item_id = i.id inner join category_list c on i.category_id = c.id where date_format(s.date, '%Y-%m') = '{$month}' order by date(s.`date`) asc");
                                while ($row = $sales->fetch_assoc()) :
                                    $g_total += $row['total'];

                                ?>
                                    <tr>
                                        <td class="px-1 py-1 align-middle text-center"><?= $i++ ?></td>
                                        <td class="px-1 py-1 align-middle"><?= date("F d, Y g:i A", strtotime($row['created_at'])) ?></td>
                                        <td class=""><?= strtoupper($row['client_name']) ?></td>
                                        <td class="text-right"><?= format_num($row['items']) ?></td>
                                        <td class="px-1 py-1 align-middle"><?= ($row['encoder']) ?></td>
                                        <td class="px-1 py-1 align-middle text-right"><?= format_num($row['total'], 2) ?></td>

                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($sales->num_rows <= 0) : ?>
                                    <tr>
                                        <td class="py-1 text-center" colspan="6">Sin registros</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gradient-secondary">
                                    <th class="py-1 text-center" colspan="5">Total de ventas realizadas este mes</th>
                                    <th class="py-1 text-right font-weight-bold"><?= format_num($g_total, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<noscript id="print-header">
    <div>
        <style>
            html {
                min-height: unset !important;
            }
        </style>
        <div class="d-flex w-100 align-items-center">
            <div class="col-2 text-center">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="" class="rounded-circle border" style="width: 5em;height: 5em;object-fit:cover;object-position:center center">
            </div>
            <div class="col-8">
                <div style="line-height:1em">
                    <div class="text-center font-weight-bold h5 mb-0">
                        <large><?= $_settings->info('name') ?></large>
                    </div>
                    <div class="text-center font-weight-bold h5 mb-0">
                        <large>Reporte de Ventas Mensuales</large>
                    </div>
                    <div class="text-center font-weight-bold h5 mb-0">Del mes de <?= date("F Y", strtotime($month . "-01")) ?></div>
                </div>
            </div>
        </div>
        <hr>
    </div>
</noscript>
<script>
    function print_r() {
        var h = $('head').clone()
        var el = $('#printout').clone()
        var ph = $($('noscript#print-header').html()).clone()
        el.find('tr.bg-gradient-teal').removeClass('bg-gradient-teal')
        el.find('tr.bg-gradient-secondary').removeClass('bg-gradient-secondary')
        h.find('title').text("Reporte de Ventas del Mes - Vista de Impresión")
        var nw = window.open("", "_blank", "width=" + ($(window).width() * .8) + ",left=" + ($(window).width() * .1) + ",height=" + ($(window).height() * .8) + ",top=" + ($(window).height() * .1))
        nw.document.querySelector('head').innerHTML = h.html()
        nw.document.querySelector('body').innerHTML = ph[0].outerHTML
        nw.document.querySelector('body').innerHTML += el[0].outerHTML
        nw.document.close()
        start_loader()
        setTimeout(() => {
            nw.print()
            setTimeout(() => {
                nw.close()
                end_loader()
            }, 200);
        }, 300);
    }
    $(function() {
        $('#filter-form').submit(function(e) {
            e.preventDefault()
            location.href = './?page=reports/sales&' + $(this).serialize()
        })
        $('#print').click(function() {
            print_r()
        })

    })
</script>