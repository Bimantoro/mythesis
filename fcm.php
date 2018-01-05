<?php
//akses fungsi
require_once('fungsi/fungsi.php');
konek_db();


$eps = 2.2204e-16;
$peringatan="";
$jml_cluster = 2;
$maxIter = 0;
$exp = "";
$threshold = "";
$w=2;
if(isset($_POST['cluster'])){

	$jml_cluster = $_POST['jumlah_cluster'];
	$maxIter = $_POST['iterasi'];
	$exp = $_POST['error'];
	$threshold = $_POST['threshold'];
	$w=$_POST['w'];
		
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
				<a class="navbar-brand" href="seleksi.php"><span style="color: #666666;">Seleksi Fitur</span></a>
				<a class="navbar-brand" href="fcm.php"><span style="color: #30a5ff;"><b>Clustering</b></span></a>
				<a class="navbar-brand" href="pengujian.php"><span style="color: #666666;">Pengujian</span></a>
			</div>

		</div><!-- /.container-fluid -->
	</nav>

	<div class="col-md-12">



		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">

				</div>
			</div>
		</div><!--/.row-->


		<div class="row" >
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Masukkan Parameter <i>Fuzzy C-Means</i> :</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<div class="form-group">
                  					<form method="post" action="#" enctype="multipart/form-data">

                  						<div class="form-group">
											<p><i>Threshold</i> :</p>
											<input type="text" name="threshold" class="form-control" required value=<?php echo $threshold; ?>>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<p>Jumlah <i>Cluster</i> :</p>
													<input type="text" name="jumlah_cluster" class="form-control" required value=<?php echo $jml_cluster; ?> readonly>
												</div>

												<div class="form-group">
													<p>Bilangan Pemangkat (<i>W</i>) :</p>
													<input type="number" name="w" class="form-control" required min=2 value=<?php echo $w; ?>>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<p>Target Error :</p>
													<input type="text" name="error" class="form-control" required value=<?php echo $exp; ?>>
												</div>

												<div class="form-group">
													<p>Maksimum Iterasi :</p>
													<input type="number" name="iterasi" class="form-control" required value=<?php echo $maxIter; ?>>
												</div>
											</div>
										</div>

										
										<div class="form-group">
											<p style="color: red;"><?php echo $peringatan; ?></p>
										</div>
										<div class="form-group">
											<button type="submit" name="cluster" class="btn btn-primary" style="width: 100%;">Clustering Fuzzy C-Means</button>
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
				<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Proses Clustering <i>Fuzzy C-Means</i> :</div>
				<?php 
					if (isset($_POST['cluster'])) {
				?>
					<table class="table table-striped table-bordered table-hover">
						<?php 
							$query_fitur_threshold = mysql_query("SELECT KODE FROM t_info WHERE NILAI >= '".$_POST['threshold']."';");
						  	$idx=0;

						 	while ($ftr = mysql_fetch_array($query_fitur_threshold)) {
						 		$att[$idx] = $ftr['KODE'];
						 		$idx++;
						 	}

						 		$colspan = count($att) + 1;
								$query_total_data = mysql_query("SELECT ID FROM t_citra;");
								$total_data = mysql_num_rows($query_total_data);

								$index_citra = 0;
								while ($id_ctr = mysql_fetch_array($query_total_data)) {
									$id_citra[$index_citra] = $id_ctr['ID'];
									$index_citra++;
								}

							//SET PARAMETER FCM :
								$jml_cluster = $_POST['jumlah_cluster']; //jumlah cluster yang digunakan
								$maxIter = $_POST['iterasi']; //maksimum iterasi yang diijinkan
								$exp = pow(10,$_POST['error']); //error target
								$t = 1; //iterasi awal
								$pt[0] = 0; //fungsi objectif
								$pt[1] = 0; //fungsi objectif
								$w = $_POST['w']; //pemangkat

							//membuat data set :
								//membuat query data untuk mengambil data sesuai threshold :

								$jml_attr = count($att);
								for ($i=0; $i < $jml_attr; $i++) { 
									if($i==0){
										$sql = $att[$i];
									}else{
										$sql = $sql.",".$att[$i];
									}
								}

								$script = "SELECT ".$sql." FROM t_citra;";
								$dataset_query = mysql_query($script);
								$idx_data = 0;
								while($dtset = mysql_fetch_array($dataset_query)){
									for ($i=0; $i < $jml_attr; $i++) { 
										$dataset[$idx_data][$i] = $dtset[$att[$i]];
									}
									$idx_data++;
								}					


							//proses pembangkitan matrik partisi awal (GA) :
								$jml_individu = 6;

								for ($dt=0; $dt < $total_data; $dt++) { 
									$fitness_terbaik = 0;
									$loop = 0;

									while($fitness_terbaik < 1){
										#PEMBANGKITAN POPULASI AWAL :
										if($loop==0){
											$populasi_awal = populasi_awal($jml_individu,$jml_cluster);
										}else{
											$populasi_awal=$elitisme;
										}		
										
										#SELEKSI INDUK :
										for ($i=0; $i < $jml_individu; $i++) { 
											$seleksi[$i] = seleksi($populasi_awal);
										}

										#REPRODUKSI CROSSOVER :
										for ($i=0; $i < $jml_individu-1; $i+=2) { 
											$keturunan = crossover($seleksi[$i],$seleksi[$i+1]);
											$tersilang[$i] = $keturunan[0];
											$tersilang[$i+1] = $keturunan[1];
										}

										#REPRODUKSI MUTASI :
										for ($i=0; $i < $jml_individu; $i++) { 
											$termutasi[$i] = mutasi($seleksi[$i]);
										}

										#ELITISME :
										$elitisme = elitisme($seleksi, $tersilang, $termutasi);

										$individu_terbaik = $elitisme[0];
										$fitness_terbaik = hitung_fitness($individu_terbaik);

										$loop++;
									}

									//mengambil matrik partisi awal :
									$partisi[$dt] = $individu_terbaik;
									for ($i=0; $i < 2; $i++) {
										$partisi[$dt][$i] = $partisi[$dt][$i]/10;
									}
								}

								$stop = 1;
								
								while ($t <= $maxIter && $stop >= $exp) {
								
									//MENGHITUNG PUSAT CLUSTER
									//inisialiasi pw (pangkat w) terlebih dahulu :

									 for ($i=0; $i < $jml_cluster ; $i++) {
									    $total_pw[$i]=0;
									    for ($j=0; $j < $jml_attr; $j++) {
									        $total_pw_attr[$i][$j]=0;
									     }
									  }

									 for ($i=0; $i < $jml_attr; $i++) {
									    $temp[$i]=0;
									  }



									for ($i=0; $i < $jml_cluster; $i++) { 
										for ($j=0; $j < $total_data; $j++) { 
											$pw[$i][$j] = pow($partisi[$j][$i], $w); //derajat keanggotaan dipangkat 2 (matrik partisi)
											for ($k=0; $k < $jml_attr; $k++) { 
												$pw_attr[$i][$j][$k] = $dataset[$j][$k] * $pw[$i][$j];
												$temp[$k] = $temp[$k] + $pw_attr[$i][$j][$k]; //menjumlah keseluruhan nilai setiap fitur/ciri/attribut
											}

											//Menghitung keseluruhan nilai PW (keanggotaan dipangkat W)
											$total_pw[$i] = $total_pw[$i] + $pw[$i][$j];
										}

										//menjumlahkan seluruh nilai PW * nilai fitur tiap data
										for ($k=0; $k < $jml_attr; $k++) { 
											$total_pw_attr[$i][$k] = $temp[$k];
										}

									}

									//menampilkan kedalam tabel :
									?>
										<tr>
											<td colspan=<?php echo $colspan; ?> align="center"> ITERASI KE <?php echo $t; ?> </td>
										</tr>

										<tr>
											<td align="center">Cluster</td>
											<?php 
												for ($i=0; $i < $jml_attr; $i++) { 
													?>
														<td align="center"> <?php echo $att[$i] ?> </td>
													<?php
												}

											 ?>
										</tr>

									<?php
									//menentukan pusat cluster

									for ($i=0; $i < $jml_cluster; $i++) { 
										?>
											<tr>
												<td align="center"><?php echo $i+1; ?></td>
											
										<?php

										for ($k=0; $k < $jml_attr; $k++) {
											if ($total_pw[$i] == 0) {

												$total_pw[$i] += $eps;
											}
											$pusat_cluster[$i][$k] = $total_pw_attr[$i][$k]/$total_pw[$i];

											?>
												<td align="center"><?php echo $pusat_cluster[$i][$k]; ?></td>
											<?php
										}

										?>
											</tr>
										<?php
									}

									//MENGHITUNG FUNGSI OBJECTIF
									$pt[0] = 0;

									for ($i=0; $i < $total_data; $i++) {
										for ($j=0; $j < $jml_cluster; $j++) { 
											for ($k=0; $k < $jml_attr; $k++) { 
												$nilai = pow(($dataset[$i][$k] - $pusat_cluster[$j][$k]), 2) * pow($partisi[$i][$j], $w);
												$pt[0] += $nilai;
											}
										}

									}

									$stop = abs($pt[0] - $pt[1]);

									?>
										<tr>
											<td align="center" colspan=<?php echo $colspan ?> >Pt - (Pt - 1) = <?php echo $stop; ?></td>
										</tr>
									<?php

									$pt[1] = $pt[0];


									//MEMPERBAIKI MATRIK PARTISI
									for ($i=0; $i < $total_data; $i++) { 
										$nilai_data[$i] = 0;

										for ($j=0; $j < $jml_cluster; $j++) { 
											$nilai_attr[$i][$j] = 0;

											for ($k=0; $k < $jml_attr; $k++) { 
												$nilai_attr[$i][$j] += pow(pow(($dataset[$i][$k] - $pusat_cluster[$j][$k]), 2), (-1/($w-1)));
											}

											$nilai_data[$i] += $nilai_attr[$i][$j];
										}
									}


									//UPDATE MATRIK :
									for ($i=0; $i < $total_data; $i++) {
										for ($j=0; $j < $jml_cluster; $j++) { 
											$partisi[$i][$j] = $nilai_attr[$i][$j] / $nilai_data[$i];
										}
									}

									$t++;

								}

								//MENYIMPAN DATA PUSAT CLUSTER :
								$query_update_pusat_cluster = mysql_query("DELETE FROM t_cluster;");
								for ($i=0; $i < $jml_attr; $i++) { 
									$sql_update = "INSERT INTO t_cluster VALUES('".$att[$i]."','".$pusat_cluster[0][$i]."','".$pusat_cluster[1][$i]."');";
									$query_update = mysql_query($sql_update);
								}

								//MENIMPAN NILAI KEANGGOTAAN (MATRIK PARTISI) :
								for ($i=0; $i < $total_data; $i++) {
									if($partisi[$i][0] > $partisi[$i][1]){
										$hasil = 0;
									}else{
										$hasil = 1;
									}
									$sql_update_partisi = "UPDATE t_citra SET C1=".$partisi[$i][0].", C2=".$partisi[$i][1].", HASIL=".$hasil." WHERE ID=".$id_citra[$i].";";
									$query_partisi = mysql_query($sql_update_partisi);
								}
						 ?>



					</table>

				<?php
					}
				 ?>
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