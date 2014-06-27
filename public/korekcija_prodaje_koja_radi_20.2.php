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
	$result = $mysqli->query("SELECT * FROM artikli");
	$artikli_obj=mysqli_fetch_object($result);
	$datum = $_SESSION['datum'];
	$prezentacija = $_POST['prezentacija'];
	
	//Izmena prezentaciju
	$query3 = "SELECT prezentacija FROM prezentacija WHERE datum = '".$datum."';";
		echo $query3.'</br>';
		$result3 = $mysqli->query($query3);
		
		//ako nema redova zanaci sa treba upisati prezentaciju
		if ($result3->num_rows == 0){
			$query = "INSERT INTO prezentacija (datum,prezentacija) VALUES ('$datum',$prezentacija)";
			$result = $mysqli->query($query);
			echo $query.'</br>';
		}
		else{
			$query = "UPDATE prezentacija SET prezentacija = $prezentacija WHERE datum = '".$_SESSION['datum']."'";
			$result = $mysqli->query($query);	
			echo $query.'</br>';
		}
	
	//promenljive za specialna racunanja
	$min = array(0,4,6,8,10,12);
	$max = array(5,7,9,11,13,15);
	$temp = 0;
	$id_arti = array();
	$kolicina = array();
	$i = 0;
	$j = 0;
	
	//za svaki artikal koji ima prodaju upisi vrednosti u niz
	foreach($_POST as $id_artikla => $prodata_kolicina) {
		if(is_numeric($id_artikla)){
			if($prodata_kolicina == '') $prodata_kolicina = 0;
			$kolicina[$i+1] = $prodata_kolicina;//ovo je niz za upis u tabelu stanje
			$kolicina2[$i+1] = $prodata_kolicina;//ovo je niz za upis u tabelu prodaja
			$i++;
		}
	}
	
	//print_r ($kolicina);
	
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
	
	print_r ($kolicina);
	
	//OBRISI artikle koji nemaju prodaju
	foreach($kolicina as $id_artikla => $prodata_kolicina) {
		if($prodata_kolicina == 0) unset($kolicina[$id_artikla]);
	}
	foreach($kolicina2 as $id_artikla => $prodata_kolicina) {
		if($prodata_kolicina == 0 ) unset($kolicina2[$id_artikla]);
	}	
	//print_r ($kolicina);	
	//artikli koji imaju prodaju se upisuju u tabele
	foreach($kolicina2 as $id_artikla => $nova_kolicina) {
		
		echo "id artikla jeste: $id_artikla, nova kolicina jeste $nova_kolicina</br>";
			
		//korekcija tabele prodaja
		$query2 = "SELECT kolicina FROM prodaja WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla.";";
		$result2 = $mysqli->query($query2);
		
		//ako ima upisanih vrednosti za prodaju izvrsi korekciju
		if ($result2->num_rows != 0){
			$obj = $result2->fetch_object();
			$stara_kolicina = $obj->kolicina;
			$korekcija_prodaje = $nova_kolicina - $stara_kolicina;
			$query = "UPDATE prodaja SET kolicina = kolicina + ".$korekcija_prodaje." 
					  WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
			$result = $mysqli->query($query);	
			echo $query.'</br>';
		}
		
		//ako nema upisanih vrednosti upisi prodatu kolicinu u tabelu prodaja
		else {
			$query = "INSERT INTO prodaja (id_artikla,datum,kolicina) 
					  VALUES (".$id_artikla.",'".$datum."', ".$nova_kolicina.");";
		$result = $mysqli->query($query);
		echo $query.'</br>';
		}
	}
	foreach($kolicina as $id_artikla => $nova_kolicina) {
			
		//korekcija tabele stanje
		//provera da li postoji upis u tabeli stanje za trenutni artikal
		$query3 = "SELECT kolicina,datum FROM stanje WHERE datum >= '".$datum."' AND id_artikla=".$id_artikla.";";
		echo $query3.'</br>';
		$result3 = $mysqli->query($query3);
		
		//ako postoji upisi u tabeli stanje izvrsi korekciju za sve datume u buducnosti
		if ($result3->num_rows != 0){
			while ($obj3=mysqli_fetch_object($result3)){
				$stara_kolicina = $obj3->kolicina;
				echo 'nova kolicina = '.$nova_kolicina.'</br>';
				echo 'stara kolicina = '.$stara_kolicina.'</br>';
				echo 'korekcija  = '.$korekcija_prodaje.'</br>';
				$datum4 = $obj3->datum;
				$query4 = "UPDATE stanje 
						  SET kolicina = kolicina - ".$korekcija_prodaje." 
						  WHERE datum='".$datum4."' AND id_artikla=$id_artikla";
				$result4 = $mysqli->query($query4);
				echo $query4.'</br>';
			}//kraj while petlje
		}//kraj uslova ako postoji unos u tabeli stanje
		
		//ako nema podatka u tabeli stanje upisi ga
		else{
			$query = "SELECT kolicina,cena FROM stanje WHERE id_artikla = $id_artikla AND datum <= '$datum'";
			echo $query.'</br>';
			$result = $mysqli->query($query);
			$obj = $result->fetch_object();
			echo "nova kolicina jeste:".$nova_kolicina."<br/>";
			echo "stara kolicina jeste:".$obj->kolicina."<br/>";
			$novo_stanje = $obj->kolicina - $nova_kolicina;
			echo "novo stanje jeste :".$novo_stanje."<br/>";
			$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) VALUES (".$id_artikla.",'".$datum."', 
					$novo_stanje,".$obj->cena.");";
			$result = $mysqli->query($query);
			echo $query.'</br>';
		}//kraj uslova ako nema unetih podataka u tabeli stanje za datum korekcije		
	}//kraj foreach
	$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!<span><br/><br/><br/>'; 
	//header( 'Location: adminIndex.php' ) ;
}// kraj if submited
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Korekcija prodaje</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
</head>
<body>
<div class="container2">
<div style="height:30px"></div>
<a href="adminindex.php">Glavni Meni</a>
<div style="height:30px"></div>
<div class="blink_me">
  <?php 
  if ($_SESSION['poruka_prodaja'] !=''){
	  echo $_SESSION['poruka_prodaja'];
	  $_SESSION['datum'] = $_SESSION['datum_prodaje'];
	  $datum = $_SESSION['datum_prodaje'];
  }
  else{
	  $_SESSION['datum'] = $_POST['datum_prodaje'];
  	  $datum = $_POST['datum_prodaje'];
  }
  ?>
</div>  <div style="color: #060; height: 100px; margin-top:10px; font-size: 36px">Korekcija prodaje za: <?php echo $_SESSION['datum'];?></div>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
    <input type="submit" name="submit" value="Upiši u bazu" />
    <?php
            $result = $mysqli->query("SELECT * FROM artikli");?>
            <div style="height:30px"></div>
            <div style="height:30px"></div>
            <span style="color:#F00">
			<div style="width:100px; float:left;">Staro</div>
            <div style='width:200px; float:left; height:50px'>Artikal</div>
            <div style='width:200px; float:left; height:50px'>Lokacija</div>
            <div style='height:50px'>Novo </div> 
            </span>
			<?php
            while ($obj=mysqli_fetch_object($result)){
				
				//ako postoji uneta vrednost za izabrani datu prikazi vrednost
				$id_artikla = $obj->id;
				$query = "SELECT kolicina FROM prodaja WHERE datum ='".$datum."' AND id_artikla = ".$id_artikla;
				//echo $query."</br>";
				$result2 = $mysqli->query($query);
				if ($result2->num_rows == 0) $kolicina = 0; 
				else {
					$temp = $result2->fetch_object();
					$kolicina = $temp->kolicina;
				}
				if($obj->lokacija == 'klub')$color = '#00FF00'; else $color = '#33CCFF';
				?>		
                <div style="width:100px; float:left;"><?php echo $kolicina;?></div>
                <div style='width:200px; float:left; height:50px'><?php echo $obj->ime;?></div>
                <div style='width:200px; float:left; height:50px; color:<?php echo $color;?>'><?php echo $obj->lokacija;?></div>
                <div style='height:50px'><input type='number' name='<?php echo $id_artikla;?>' value="0" /></div>	
			<?php }
			$query = "SELECT prezentacija FROM prezentacija WHERE datum ='".$datum."'";
			$result2 = $mysqli->query($query);
			if ($result2->num_rows == 0) $prezentacija = 0; 
			else {
				$temp = $result2->fetch_object();
				$prezentacija = $temp->prezentacija;
			}
			?>
			prezentacija <input type="number" name="prezentacija" value=<?php echo $prezentacija;?> />
            <?php $mysqli->close();
            ?>
    <input type="submit" name="submit" value="Izvrši korekciju" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
