
<?php

session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
if(isset($_POST['submit'])){
	
	/*
	1. ubaci u dva niza unete vrednosti za prodaju
	2. izvrsi korekciju jednog niza
	3. upisi u tabelu prodaja
	4. upisi u tabeli stanje 	
	*/
	$datum = $_SESSION['datum'];
	
	//upis u tabelu prezentacija
	if($_POST['prezentacija'] != 0){
	$query = "INSERT INTO prezentacija (datum,prezentacija) VALUES ('$datum',".$_POST['prezentacija'].")";
	$result = $mysqli->query($query);
		echo $query.'</br>'; 
	}
	
	if($_POST['prezentacija_klub'] != 0){
	$query = "INSERT INTO prezentacija_klub (datum,prezentacija_klub) VALUES ('$datum',".$_POST['prezentacija_klub'].")";
	$result = $mysqli->query($query);
		echo $query.'</br>'; 
	}
	
		
	$result = $mysqli->query("SELECT * FROM artikli");
	$artikli_obj=mysqli_fetch_object($result);
	
	
	//promenljive za specialna racunanja
	$temp = 0;
	$id_arti = array();
	$kolicina = array();
	$kolicina2 = array();
	$i = 0;
	$j = 0;
	
	//1. 
	//za svaki artikal koji ima prodaju upisi vrednosti u niz
	foreach($_POST as $id_artikla => $prodata_kolicina) {
		if(is_numeric($id_artikla)){
			$id_arti[$i] = $id_artikla;
			if($prodata_kolicina == '') $prodata_kolicina = 0;
			$kolicina[$id_artikla] = $prodata_kolicina;//ovo je niz za upis u tabelu stanje
			$kolicina2[$id_artikla] = $prodata_kolicina;//ovo je niz za upis u tabelu prodaja
			echo '</br> id jeste:'.$id_artikla; echo 'kolicina jeste: '.$prodata_kolicina;			
		}
	}
	print_r ($kolicina);
	//korekcija prodaje
	$kolicina[4] = $kolicina[4] + $kolicina[3] + $kolicina[2] + $kolicina[1];
	$kolicina[3] = 0;
	$kolicina[2] = 0;
	$kolicina[1] = 0;
	$kolicina[6] = $kolicina[6] + $kolicina[5];
	$kolicina[5] = 0;
	$kolicina[8] = $kolicina[8] + $kolicina[7];
	$kolicina[7] = 0;
	$kolicina[10] = $kolicina[10] + $kolicina[9];
	$kolicina[9] = 0;
	$kolicina[12] = $kolicina[12] + $kolicina[11];
	$kolicina[11] = 0;
	$kolicina[14] = $kolicina[14] + $kolicina[13];
	$kolicina[13] = 0;
	//OBRISI artikle koji nemaju prodaju
	foreach($kolicina as $id_artikla => $prodata_kolicina) {
		if($prodata_kolicina == 0) unset($kolicina[$id_artikla]);
	}
	foreach($kolicina2 as $id_artikla => $prodata_kolicina) {
		if($prodata_kolicina == 0) unset($kolicina2[$id_artikla]);
	}

	//2.
	//upis u tabelu prodaja
	foreach($kolicina2 as $id_artikla => $prodata_kolicina){
		//echo $id_artikla.'; prodata kolicina = '.$prodata_kolicina.'<br>';
		
		//upis
		$query = "INSERT INTO prodaja (id_artikla,datum,kolicina) VALUES (".$id_artikla.",'".$datum."', 
		".$prodata_kolicina.");";
		$result = $mysqli->query($query);
		echo $query.'</br>';
	}//kraj upisa u tabelu prodaja
	
	//3.
	//upis u tabelu stanje
	foreach($kolicina as $id_artikla => $prodata_kolicina){
		//da li postoji unos u tabeli stanje za dati artikal za trenutni datum
		$query = "SELECT kolicina 
				  FROM stanje 
				  WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla;
		$result = $mysqli->query($query);
		echo $query.'</br>';
		
		//ako nema upisa u tabeli stanje upisi prodaju i izvrsi korekciju stanja za datume u buducnosti
		if ($result->num_rows == 0){
	
			//upis prodate kolicine u tabelu stanje 
			$query = "SELECT kolicina,cena 
					  FROM stanje 
					  WHERE datum < '".$datum."' AND id_artikla = $id_artikla 
					  ORDER BY datum DESC LIMIT 0,1";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			$obj = $result->fetch_object();
			$stara_kolicina = $obj->kolicina;
			$nova_kolicina = $stara_kolicina - $prodata_kolicina;
			$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) VALUES (".$id_artikla.",'".$datum."', 
				".$nova_kolicina.",".$obj->cena.");";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			
			//korekcija vrednosti za sva stanja u buducnosti za prodatu kolicinu
			$query = "UPDATE stanje SET kolicina = kolicina - ".$prodata_kolicina." 
					  WHERE datum > '".$datum."' AND id_artikla = ".$id_artikla;
			$result = $mysqli->query($query);
			echo $query.'</br>';
			
		}//kraj bloka ako nema upisa u tabeli stanje
		
		//ako ima upisa u tabeli stanje izmeni kolicinu u tabeli stanje za sadasnji datum i sve naredne datume
		else{
			$query = "UPDATE stanje SET kolicina = kolicina - ".$prodata_kolicina." 
					  WHERE datum >= '".$datum."' AND id_artikla = ".$id_artikla;
			$result = $mysqli->query($query);	
			echo $query.'</br>';				
		}//kraj bloka ako ima upisa u tabeli stanje
	}//kraj foreach za upis u tabelu stanje
	
	$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!<span><br/><br/><br/>';
	?><script type="text/javascript">  window.location.replace("adminIndex.php");</script><?php
}//kraj ako je izvrsen submit
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unos prodaje</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<script>
$( "p" ).parent( ".selected" ).css( "background", "yellow" );
$("#input").focus(function(){
  $(this).parent().css( "background", "yellow" );
});
</script>
</head>
<body>
<div class="container2">
<a href="adminIndex.php">Glavni Meni</a>
  <?php $_SESSION['datum'] = $_POST['datum_prodaje'];
  $datum=$_POST['datum_prodaje']?>
  <div style="color: #060; height: 100px; margin-top:30px; font-size: 36px">
  		Unos prodaje za: <?php echo $_SESSION['datum'];?></div>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
    <input type="submit" name="submit" value="Upiši u bazu" />
    <?php
            //Ako vec postoji uneta prodaja za izabrani datum preusmeri korisnika na izmenu prodaje
			$result = $mysqli->query("SELECT * FROM artikli ORDER BY poredak ASC");
            echo '<div style="height:30px"></div>';
			$query = "SELECT kolicina FROM prodaja WHERE datum ='".$datum."'";
			$query2 = "SELECT prezentacija FROM prezentacija WHERE datum ='".$datum."'";
			$query3 = "SELECT prezentacija_klub FROM prezentacija_klub WHERE datum ='".$datum."'";
			$result7 = $mysqli->query($query);
			$result8 = $mysqli->query($query2);
			$result9 = $mysqli->query($query3);
			if (($result7->num_rows != 0)||($result8->num_rows != 0)||($result9->num_rows != 0)){  
				$_SESSION['poruka_prodaja'] = '<span style="color:green">
				Za izabrani datum je vec uneta prodaja, možete izvršiti samo izmenu prodaje.
				</span><br/><br/><br/>';
				$_SESSION['datum_prodaje'] =  $_POST['datum_prodaje'];
				header( 'Location: korekcija_prodaje.php' ) ;
			}
			
			//label ant text field color
			$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');				
			while ($obj=mysqli_fetch_object($result)){
				$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
				//ako postoji uneta vrednost za izabrani datu prikazi vrednost
				$id_artikla = $obj->id;
				$query2 = "SELECT kolicina FROM prodaja WHERE datum ='".$datum."' AND id_artikla = ".$id_artikla;
				//echo $query."</br>";
				$result2 = $mysqli->query($query2);
	     		//izlistaj sve artikle               
                echo "
				<div style='width:200px; float:left; height:50px; id='".$id_artikla."''>
					<u style='color:$color;'><span style='color:#000'>".$obj->ime."</span></u>
				</div>
			    <div style='height:50px'><input id='input' style='border-color:$color' type='number' name='".$id_artikla."' value=000000 /></div>";
			}// kraj petlje koja lista artikle
			?>
            <br />
<br />
<br />

			<div style='width:200px; float:left; height:50px;'>prezentacija</div> 
            <div style='height:50px'><input type="number" name="prezentacija" value="0000000"/></div>
            <div style='width:200px; float:left; height:50px;'>prezentacija za klub</div>
            <div style='height:50px'><input type="number" name="prezentacija_klub" value="00000000"/></div>
            <?php $mysqli->close();
            ?>
    <input type="submit" name="submit" value="Upiši u bazu" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
