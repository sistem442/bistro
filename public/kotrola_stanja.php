
<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> KONTROLA stanja</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
</head>
<body>
<div class="container2">
<a href="adminIndex.php">Glavni Meni</a>
 KONTROLA STANJA</div>
  <table>
   	<tr>
        <td>id</td>
        <td>kolicina</td>
        <td>datum</td>
        <td>cena</td>
    </tr> 
  <?php
            $query = "SELECT * FROM stanje 
					  WHERE id_artikla <2000 ORDER BY datum DESC";
			//echo $query.'</br>';
			$result = $mysqli->query($query);
			$temp = 0;
            while ($obj=mysqli_fetch_object($result)){
				
				//procitaj vrednosti reda
				$id = $obj->id_artikla;
				$kolicina = $obj->kolicina;
				$datum = $obj->datum;
				$cena = $obj->cena;
				
				
				
				//izlistaj sve artikle 
				?>
                <tr>
                    <td><?php echo $id;?></td>
                    <td><?php echo $kolicina;?></td>
                    <td><?php echo $datum;?></td>
                    <td><?php echo $cena;?></td>
                </tr>             
                <?php }
            $mysqli->close();
            ?>
            </table>
    <input type="submit" name="submit" value="Upiši u bazu" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
