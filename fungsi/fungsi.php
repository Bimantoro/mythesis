<?php
	
	//fungsi untuk koneksi database
	function konek_db(){
		mysql_connect('localhost','skripsi','skripsi');
		mysql_select_db('skripsi');
	}

	//fungsi menghitung entropy :
	function entropy($jumlah, $total){
		$entropy = -(($jumlah/$total)*log(($jumlah/$total),2));
		return $entropy;
	}

	//fungsi untuk GA (pembangkitan matriks awal)
	#fungsi objectif (menghitung persamaan dengan syarat semua bilangan partisi dalam satu data jika dijumlahkan adalah 1)
	function f_obj($individu){
		$cluster = count($individu); //menghitung jumlah cluster berdasarkan individu
		$target = 10;
		$hasil = 0;
		for ($i=0; $i < $cluster; $i++) { 
			$hasil += $individu[$i];
		}

		$evaluasi = abs($hasil - $target);
		return $evaluasi; //nilai fitness masih 0-10
	}

	#fungsi untuk menghitung nilai fitness
	function hitung_fitness($individu){
		$fitness = 1/(1+f_obj($individu)); //agar nilai fitness menjadi 0-10
		return $fitness;
	}

	#fungsi untuk membangkitkan populasi awal
	function populasi_awal($jml_individu, $jml_cluster){
		for ($i=0; $i < $jml_individu; $i++) { 
			for ($j=0; $j < $jml_cluster; $j++) { 
				$awal[$i][$j] = rand(0,10);
			}
		}
		return $awal;
	}

	#fungsi untuk seleksi individu (RWS)
	function seleksi($individu){
		$jumlah_individu = count($individu);

		$total_fitness = 0;

		//menghitung total nilai fitness dari seluruh individu :
		for ($i=0; $i < $jumlah_individu; $i++) { 
			$total_fitness += hitung_fitness($individu[$i]);
		}

		//menghitung probabilitas tiap individu :
		for ($i=0; $i < $jumlah_individu; $i++) { 
			$probabilitas[$i] = hitung_fitness($individu[$i])/$total_fitness;
		}

		$random = rand(0,10)/10;
		$i=0;
		$sum = $probabilitas[$i];
		while ($sum<$random) {
			$i++;
			$sum += $probabilitas[$i];
			if ($i==$jumlah_individu-1) {
				break;
			}
		}
		return $individu[$i]; //mengembalikan individu terpilih sebagai hasil seleksi
	}

	#fungsi crossover (uniform) :
	function crossover($induk1,$induk2){
		$pc=0.99;
		$anak1=$induk1;
		$anak2=$induk2;
		$jumlah_gen = count($anak1);
		for ($i=0; $i < $jumlah_gen; $i++) { 
			$random = rand(0,10)/10;
			if ($random<=$pc) {
				$tmp = $anak1[$i];
				$anak1[$i] = $anak2[$i];
				$anak2[$i] = $tmp;
			}
		}

		$hasil[0] = $anak1;
		$hasil[1] = $anak2;

		return $hasil;
	}

	#fungsi untuk melakukan mutasi
	function mutasi($induk){
		$pm = 0.11;
		$jumlah_gen = count($induk);
		for ($i=0; $i < $jumlah_gen; $i++) { 
			$random = rand(0,10)/10;
			if($random <= $pm){
				$induk[$i] = rand(0,10);
			}
		}
		return $induk;
	}

	#fungsi untuk elitisme (mempertahankan individu terbaik) :
	function elitisme($seleksi, $tersilang, $termutasi){
		$jumlah_individu = count($seleksi);

		//penggabungan :
		for ($i=0; $i < $jumlah_individu; $i++) { 
			$union[$i] = $seleksi[$i];
			$union[$i + $jumlah_individu] = $tersilang[$i];
			$union[$i + ($jumlah_individu*2)] = $termutasi[$i];
		}

		//shorting :
		for ($i=0; $i < ($jumlah_individu*3)-1; $i++) { 
			for ($j=0; $j < ($jumlah_individu*3)-1; $j++) { 
				if(hitung_fitness($union[$j]) < hitung_fitness($union[$j+1])){
					$tmp = $union[$j];
		          	$union[$j]=$union[$j+1];
		          	$union[$j+1]=$tmp;
				}
			}
		}

		//seleksi individu terbaik :
		for ($i=0; $i < $jumlah_individu; $i++) { 
			$terbaik[$i] = $union[$i];
		}

		return $terbaik;
	}
 ?>
