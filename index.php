<?php
//akses fungsi
require_once('fungsi/fungsi.php');
konek_db();

$peringatan="";
if(isset($_POST['upload'])){
	$target_dir = "asset/citra/";
      $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
      $uploadOk = 1;
      $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
      $check = getimagesize($_FILES["gambar"]["tmp_name"]);
      if($check !== false) {
              $uploadOk = 1;
           
          } else {
              $peringatan="maaf yang anda pilih bukan gambar :-(";
              $uploadOk = 0;

          }
      

      if ($_FILES["gambar"]["size"] > 2000000) {
          $peringatan='ukuran gambar terlalu besar pilih gambar dengan ukuran kurang 2 MB';
          $uploadOk = 0;
         
      }

      if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "JPG" ) {
          $peringatan='pilih gambar dengan format JPG, PNG atau JPEG';
          $uploadOk = 0;
          
      }

      if ($uploadOk == 0) {

      } else {
          if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
              $nama_file=$_FILES['gambar']['name'];

          } else {
              echo "<script>alert('gambar tidak terupload ke server'); </script>";
              echo "<meta http-equiv='refresh' content='0; url=update_studio.php'>";
              
          }
      }

      if ($uploadOk == 1) {
      	# code...
      

      //ekstraksi ciri warna:
      	$nama_gambar = $target_dir.$nama_file;
      	$img = imagecreatefromjpeg($nama_gambar);
     	$width = imagesx($img);
		$height = imagesy($img);

		$red = 0;
		$green = 0;
		$blue = 0;

		for ($i=0; $i < 256; $i++) { 
	        $histo[$i]=0;
	        $fn[$i]=$i;
		}



		for($x=0; $x<$width; $x++){
			for($y=0; $y<$height; $y++){

				//mengambil data RGB tiap piksel
				$rgb = imagecolorat($img, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				//menghitung jumlah keseluruhan nilai RGB :
				$red = $red + $r;
				$green = $green + $g;
				$blue = $blue + $b;

				//konversi ke grayscale
				$grayscale = round(($r + $g + $b) / 3);

				//menghitung jumlah nilai keabuan
				$histo[$grayscale] = $histo[$grayscale] + 1;
			}
		}

		$mean_r = round($red/($width*$height));
		$mean_g = round($green/($width*$height));
		$mean_b = round($blue/($width*$height));

		//membuat histogram:
		for ($i=0; $i < 256; $i++) { 
			$histo[$i]=$histo[$i]/($width*$height);
		}

		//menghitung mean :
		//mean = I * H
		$mean = 0;
        for ($k=0; $k < 256; $k++) { 
                        //echo "hallo";
            $total = $fn[$k]*$histo[$k];
            $mean = $mean + $total;
        }

		//menghitung variance :
		//variance = ((I-mean)^2)*H

		//menghitung (I-mean):
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = $fn[$i]-$mean;
		}

		//menghitung (I-mean)^2
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = pow($temp[$i],2);
		}

		//menhitung variance :
		$variance = 0;
		for ($i=0; $i < 256; $i++) { 
			$var = $temp[$i] * $histo[$i];
			$variance = $variance + $var; //ini variance
		}

		//menghitung skewness
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = $fn[$i]-$mean;
		}

		//menghitung (I-mean)^3
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = pow($temp[$i],3);
		}


		//menhitung (I-mean).^3*H
		$skewness_temp = 0;
		for ($i=0; $i < 256; $i++) { 
			$skew = $temp[$i] * $histo[$i];
			$skewness_temp = $skewness_temp + $skew;
		}

		//menghitung skewness = (I-mean).^3*H/variance^1.5;
		$skewness = $skewness_temp/pow($variance,1.5); //ini skewness

		//menghitung kurtosis
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = $fn[$i]-$mean;
		}

		//menghitung (I-mean)^4
		for ($i=0; $i < 256; $i++) { 
			$temp[$i] = pow($temp[$i],4);
		}

		//menhitung (I-mean).^4*H
		$kurtosis_temp = 0;
		for ($i=0; $i < 256; $i++) { 
			$kur = $temp[$i] * $histo[$i];
			$kurtosis_temp = $kurtosis_temp + $kur;
		}


		//menghitung kurtosis = ((I-mean).^4*H/variance^2)-3;
		$kurtosis = ($kurtosis_temp/pow($variance,2))-3; //ini kurtosis

		//menghitung entropy
		//set epsilon

		$eps = 2.2204e-16;
		
		for ($i=0; $i < 256; $i++) { 
			$histo_temp[$i] = log(($histo[$i])+$eps,2);
		}


		$entropy=0;
		for ($i=0; $i < 256; $i++) { 
			$entropy_temp = $histo[$i] * $histo_temp[$i];
			$entropy = $entropy + $entropy_temp; //ini entropy
		}


		//pembulatan ke bilangan bulat untuk proses seleksi fitur :
		$mean = round($mean);
		$variance = round($variance);
		$skewness = round($skewness);
		$kurtosis = round($kurtosis);
		$entropy = round($entropy);


      

      $cluster = $_POST['cluster'];
      $query = mysql_query("INSERT INTO t_citra VALUES('','".$nama_file."','".$mean_r."','".$mean_g."','".$mean_b."','".$mean."','".$variance."','".$skewness."','".$kurtosis."','".$entropy."','".$cluster."','')");
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
				<a class="navbar-brand" href="index.php"><span style="color: #30a5ff;"><b>Data Citra Tanah</b></span></a>
				<a class="navbar-brand" href="seleksi.php"><span style="color: #666666;">Seleksi Fitur</span></a>
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
					<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Upload Citra Tanah:</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<div class="form-group">
                  <form method="post" action="#" enctype="multipart/form-data">
										<div class="form-group">
											<p>
												Cluster Target :
											</p>
											<select class="form-control" name="cluster">
												<?php 
													$query = mysql_query("SELECT * from cluster_tanah LIMIT 2;");
													while($data = mysql_fetch_array($query)){
												 ?>
												<option value=<?php echo $data['ID']; ?> > <?php echo $data['CLUSTER']; ?> </option>
												<?php } ?>
											</select>
											
										</div>
										<div class="form-group">
											<input type="file" name="gambar" class="form-control">
										</div>

										<div class="form-group">
											<p style="color: red;"><?php echo $peringatan; ?></p>
										</div>
										<div class="form-group">
											<button type="submit" name="upload" class="btn btn-primary" style="width: 100%;">Upload</button>
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
				<div class="panel-heading"><svg class="glyph stroked desktop"><use xlink:href="#stroked-desktop"></use></svg>Data Fitur Citra Tanah :</div>
				<table class="table table-striped table-bordered table-hover">
					<tr>
					 <td align="center"><b>ID</b></td>
					 <td align="center"><b>Citra</b></td>
					 <td align="center"><b>Nama</b></td>
					 <td align="center"><b>R</b></td>
					 <td align="center"><b>G</b></td>
					 <td align="center"><b>B</b></td>
					 <td align="center"><b>M</b></td>
					 <td align="center"><b>V</b></td>
					 <td align="center"><b>S</b></td>
					 <td align="center"><b>K</b></td>
					 <td align="center"><b>E</b></td>
				 <!--<td align="center"><b>Target</b></td> -->
					 <td align="center"><span class="glyphicon glyphicon-trash"></span></td>
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
						 <td align="center"><?php echo $isi['R']; ?></td>
						 <td align="center"><?php echo $isi['G']; ?></td>
						 <td align="center"><?php echo $isi['B']; ?></td>
						 <td align="center"><?php echo $isi['M']; ?></td>
						 <td align="center"><?php echo $isi['V']; ?></td>
						 <td align="center"><?php echo $isi['S']; ?></td>
						 <td align="center"><?php echo $isi['K']; ?></td>
						 <td align="center"><?php echo $isi['E']; ?></td>
						 <?php 

						 	//$sqli = mysql_query("SELECT CLUSTER from cluster_tanah WHERE ID='".$isi['TARGET']."'");
						 	//$value = mysql_fetch_array($sqli);
						?>
						<!--<td align="center"><?php //echo $value['CLUSTER']; ?></td> -->
						<td align="center"><a href="delete_citra.php?id=<?php echo $isi['ID']; ?>" class="btn btn-danger" style="width: 100%;"><span class="glyphicon glyphicon-trash"></span></a></td>
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