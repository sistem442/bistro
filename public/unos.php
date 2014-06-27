<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
}
include "database_connect.php";

if(isset($_POST['submit'])){
	$datum = $_POST['datum'];
	$result = $mysqli->query("SELECT * FROM artikli");
	$row_cnt = $result->num_rows;
    $broj_artikala = $result->num_rows;
	//echo $broj_artikala;
	foreach($_POST as $key => $field) {
    	if(is_numeric($field) && $field != 0){
			$query = "INSERT INTO prodaja (id_artikla,datum,kolicina) VALUES (".$broj_artikala.",".$datum.", 
			 ".$kolicina.");";
			 echo $query;
			$result = $mysqli->query($query);
			}
	}
   mysqli_close($con);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unos prodaje</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
</head>
<body class="container2">
	<div class="container2">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
            <input type="date" name="datum"  />
            <?php
            $result = $mysqli->query("SELECT * FROM artikli");
            echo '<div style="height:30px"></div>';
            while ($obj=mysqli_fetch_object($result))
                {
                echo "<div style='width:200px; float:left; height:50px'>".$obj->ime."</div> <div style='height:50px'><input type='number' name='".$obj->id."' value='0'/></div>";
                }
            	
            mysqli_free_result($result);
            mysqli_close($con);
            ?>
            <input type="submit" name="submit" value="Upiši u bazu" />
	</form>
<br />
<br />
</div><!--container-->
</body>
</html>
