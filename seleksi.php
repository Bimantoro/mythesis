<?php
//akses fungsi
require_once('fungsi/fungsi.php');
konek_db();

$peringatan="";
if(isset($_POST['seleksi'])){
		//QUERY UNTUK MENHITUNG TOTAL BARIS DATA YANG ADA :
		$query_total_data = mysql_query("SELECT ID FROM t_citra;");
		$total = mysql_num_rows($query_total_data);

		if($total==0){
			$peringatan="TIDAK ADA DATA CITRA";
		}

		//MENGHITUNG ENTROPY AWAL :
		$query = mysql_query("SELECT TARGET, count(ID) AS JUMLAH FROM t_citra GROUP BY TARGET;");
		$entropy_awal = 0;
		while ($data = mysql_fetch_array($query)) {
			$tmp_entropy_awal = entropy($data['JUMLAH'],$total); //MEMANGGIL FUNGSI ENTROPI
			$entropy_awal += $tmp_entropy_awal;
		}

		//MENGAMBIL KODE FITUR UNTUK LOOPING SELURUH FITUR
		$query_info_gain = mysql_query("SELECT KODE FROM t_info;");
		while($fitur = mysql_fetch_array($query_info_gain)){

			$info_gain_fitur = $entropy_awal;
			//MENGHITUNG ENTROPY SALAH SATU FITUR
			$sql_fitur = "SELECT ".$fitur['KODE']." AS FITUR, COUNT(ID) AS JUMLAH FROM t_citra GROUP BY ".$fitur['KODE'].";";
			$query_data_fitur = mysql_query($sql_fitur);
			while ($data_fitur = mysql_fetch_array($query_data_fitur)) {
				//MENGHITUNG ENTROPY SALAH SATU DATA DALAM SATU FITUR
				$sql = "SELECT TARGET, COUNT(ID) AS JUMLAH FROM t_citra WHERE ".$fitur['KODE']." = ".$data_fitur['FITUR']." GROUP BY TARGET;";
				$query_fitur = mysql_query($sql);
					$entropy_isi_fitur = 0;
					while($isi_fitur = mysql_fetch_array($query_fitur)){
						$tmp_entropy_isi_fitur = entropy($isi_fitur['JUMLAH'], $data_fitur['JUMLAH']); //MEMANGGIN FUNGSI ENTROPY
						$entropy_isi_fitur += $tmp_entropy_isi_fitur;
					}

					$entropy_data_fitur = $data_fitur['JUMLAH']/$total*$entropy_isi_fitur;
					$info_gain_fitur -= $entropy_data_fitur;

				
			}

			  $sql_info = "UPDATE t_info SET NILAI=".$info_gain_fitur." WHERE KODE='".$fitur['KODE']."';";
			  $update_gain = mysql_query($sql_info);
			  if (!$update_gain) {
			  	$error = mysql_error($update_gain);
			  	print($error);
			  }

		}

}


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
				<a class="navbar-brand" href="seleksi.php"><span style="color: #30a5ff;"><b>Seleksi Fitur</b></span></a>
				<a class="navbar-brand" href="fcm.php"><span style="color: #666666;">Clustering</span></a>
				<a class="navbar-brand" href="pengujian.php"><span style="color: #666666;">Pengujian</span></a>
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
					<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Tekan Tombol untuk Seleksi Fitur:</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<div class="form-group">
                  					<form method="post" action="#" enctype="multipart/form-data">		
										<div class="form-group">
											<p style="color: red;"><?php echo $peringatan; ?></p>
										</div>
										<div class="form-group">
											<button type="submit" name="seleksi" class="btn btn-primary" style="width: 100%;">Seleksi Fitur</button>
										</div>
                  					</form>
								</div>
              </div>
							<div class="col-md-2"></div>
						</div>
					</div>
				</div>

			</div>
			<div class="col-md-12">
				<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Information Gain :</div>
				<table class="table table-striped table-bordered table-hover">
					<tr>
					 <td align="center"><b>ID</b></td>
					 <td align="center"><b>KODE</b></td>
					 <td align="center"><b>FITUR</b></td>
					 <td align="center"><b>INFORMATION GAIN</b></td>
					</tr>

				 <?php

				 $sql = mysql_query("SELECT * FROM t_info;");
				 while ($isi = mysql_fetch_array($sql)) {
				 	
				?>
					 <tr align="center">
						 <td align="center"><?php echo $isi['ID']; ?></td>
						 <td align="center"><?php echo $isi['KODE']; ?></td>
						 <td align="center"><?php echo $isi['FITUR']; ?></td>
						 <td align="center"><?php echo $isi['NILAI']; ?></td>

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