<?php 
	require_once('fungsi/fungsi.php');
	konek_db();
	$id = $_GET['id'];
	$query = mysql_query("DELETE FROM t_citra where ID ='".$id."'");
	if($query){
		echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	}else{
		echo "<script>alert('gagal dihapus'); </script>";
		echo "<meta http-equiv='refresh' content='0; url=index.php'>";
	}

 ?>