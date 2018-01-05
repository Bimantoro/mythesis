<?php 
		
		mysql_connect('localhost','skripsi','skripsi');
		mysql_select_db('histogram');

		$nama_gambar = "TS_7.JPG";
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
			print($histo[$i]);
			print("\n");

			$query = mysql_query("INSERT INTO histogram VALUES(".$i.",".$histo[$i].")");
		}


 ?>
