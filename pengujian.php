<?php
//akses fungsi
require_once('fungsi/fungsi.php');
konek_db();


//MENGHITUNG AKURASI :
$sql = mysql_query("SELECT TARGET, HASIL FROM t_citra;");
$total_data = mysql_num_rows($sql);
$total_benar  = 0;
while ($isi = mysql_fetch_array($sql)) {
	if($isi['TARGET'] == $isi['HASIL']){
		$total_benar++;
	}
}

$akurasi = $total_benar/$total_data*100;

 ?>

  <!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Clustering Citra Tanah untuk Penentuan Lokasi Penanaman Tanaman Cengkeh</title>

<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/datepicker3.css" rel="stylesheet">
<link href="css/styles.css" rel="stylesheet">

<!--Icons-->
<script src="js/lumino.glyphs.js"></script>

<!--[if lt IE 9]>
<script src="js/html5shiv.js"></script>
<script src="js/respond.min.js"></script>
<![endif]-->

</head>

<body style="background:white;">
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="background: #ececec;">
		<div class="container-fluid">
			<div class="navbar-header" align="center">
				<a class="navbar-brand" href="index.php"><span style="color: #666666;">Data Citra Tanah</span></a>
				<a class="navbar-brand" href="seleksi.php"><span style="color: #666666;">Seleksi Fitur</span></a>
				<a class="navbar-brand" href="fcm.php"><span style="color: #666666;">Clustering</span></a>
				<a class="navbar-brand" href="pengujian.php"><span style="color: ##30a5ff;"><b>Pengujian</b></span></a>
			</div>

		</div><!-- /.container-fluid -->
	</nav>

	<div class="col-md-8 col-md-offset-2">



		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">

				</div>
			</div>
		</div><!--/.row-->


		<div class="row" >
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Pusat Cluster:</div>
					<table class="table table-striped table-bordered table-hover">
						<tr>
							<td align="center"><b>No.</b></td>
							<td align="center"><b>Fitur</b></td>
							<td align="center"><b>Pusat Cluster 1</b></td>
							<td align="center"><b>Pusat Cluster 2</b></td>
						</tr>
							<?php
								$index = 1;
								$query_pc = mysql_query("SELECT * FROM t_cluster;");
								while ($value = mysql_fetch_array($query_pc)) {
									?>
									<tr>
										<td align="center"><?php echo $index; ?></td>
										<td align="center"><?php echo $value['FITUR']; ?></td>
										<td align="center"><?php echo $value['PC_1']; ?></td>
										<td align="center"><?php echo $value['PC_2']; ?></td>
									</tr>
									<?php
									$index++;
								}
							 ?>
					</table>
					
				</div>

			</div>
			<div class="col-md-12">
				<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Hasil Clustering Citra Tanah ( Akurasi = <?php echo $akurasi; ?>% ) :</div>
				<table class="table table-striped table-bordered table-hover">
					<tr>
					 <td align="center"><b>ID</b></td>
					 <td align="center"><b>Citra</b></td>
					 <td align="center"><b>Nama</b></td>
					 <td align="center"><b>Cluster 1</b></td>
					 <td align="center"><b>Cluster 2</b></td>
					 <td align="center"><b>Target</b></td>
					 <td align="center"><b>Hasil</b></td>
					 <td align="center"><b>Uji</b></td>
					</tr>
				 <?php
				 $idx=0;
				 $sql = mysql_query("SELECT * FROM t_citra;");
				 while ($isi = mysql_fetch_array($sql)) {
				 	$idx++;
					 ?>
					 <tr align="center">
						 <td align="center"><?php echo $idx; ?></td>
						 <td align="center"><img src="asset/citra/<?php echo $isi['NAMA']; ?>" height="35px"></td>
						 <td align="center"><?php echo $isi['NAMA']; ?></td>
						 <td align="center"><?php echo $isi['C1']; ?></td>
						 <td align="center"><?php echo $isi['C2']; ?></td>
						 <?php 
						 	$query_sql = mysql_query("SELECT CLUSTER FROM cluster_tanah WHERE ID='".$isi['TARGET']."';");
						 	$value = mysql_fetch_array($query_sql);

						  ?>
						 <td align="center"><?php echo $value['CLUSTER']; ?></td>

						 <?php 
						 	$query_sql = mysql_query("SELECT CLUSTER FROM cluster_tanah WHERE ID='".$isi['HASIL']."';");
						 	$value = mysql_fetch_array($query_sql);
						 ?>
						 <td align="center"><?php echo $value['CLUSTER']; ?></td>
						 <?php 
						 	if($isi['TARGET'] == $isi['HASIL']){
						 		$uji="BENAR";
						 	}else{
						 		$uji="SALAH";
						 	}
						  ?>
						  <td align="center"><?php echo $uji; ?></td>
					</tr>
					 <?php
				 }

				?>

				</table>
			</div>
		</div>


		</div><!--/.row-->
	</div>	<!--/.main-->

	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/chart.min.js"></script>
	<script src="js/chart-data.js"></script>
	<script src="js/easypiechart.js"></script>
	<script src="js/easypiechart-data.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>

	<script>
    $(document).ready(function() {
        $('#dataTables-example').DataTable({
               "language": {
            "lengthMenu": "Menampilkan _MENU_ baris tiap halaman",
            "zeroRecords": "Maaf, Data tidak ditemukan !",
            "info": "Halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)"
        }

        });
    });
    </script>

	<script>
		$('#calendar').datepicker({
		});

		!function ($) {
		    $(document).on("click","ul.nav li.parent > a > span.icon", function(){
		        $(this).find('em:first').toggleClass("glyphicon-minus");
		    });
		    $(".sidebar span.icon").find('em:first').addClass("glyphicon-plus");
		}(window.jQuery);

		$(window).on('resize', function () {
		  if ($(window).width() > 768) $('#sidebar-collapse').collapse('show')
		})
		$(window).on('resize', function () {
		  if ($(window).width() <= 767) $('#sidebar-collapse').collapse('hide')
		})
	</script>

</body>

</html>