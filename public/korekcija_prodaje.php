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
	$result = $mysqli->query("SELECT * FROM artikli WHERE artikli.status='aktivan'");
	$artikli_obj=mysqli_fetch_object($result);
	$datum = $_SESSION['datum'];
	$prezentacija = $_POST['prezentacija'];
	$prezentacija_klub = $_POST['prezentacija_klub'];
	
	//Izmena prezentaciju
	$query3 = "SELECT prezentacija FROM prezentacija WHERE datum = '".$datum."';";
		echo $query3.'</br>';
		$log .= $query3.'<br/>';
		$result3 = $mysqli->query($query3);
		
		//ako nema redova zanaci sa treba upisati prezentaciju
		if ($result3->num_rows == 0){
			$query = "INSERT INTO prezentacija (datum,prezentacija) VALUES ('$datum',$prezentacija)";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			$log .= $query.'<br/>';
		}
		else{
			$query = "UPDATE prezentacija SET prezentacija = $prezentacija WHERE datum = '".$_SESSION['datum']."'";
			$result = $mysqli->query($query);	
			echo $query.'</br>';
			$log .= $query.'<br/>';
		}
		
	//Izmena prezentaciju za klub
	$query3 = "SELECT prezentacija_klub FROM prezentacija_klub WHERE datum = '".$datum."';";
		echo $query3.'</br>';
		$log .= $query3.'<br/>';
		$result3 = $mysqli->query($query3);
		
		//ako nema redova zanaci sa treba upisati prezentaciju
		if ($result3->num_rows == 0){
			$query = "INSERT INTO prezentacija_klub (datum,prezentacija_klub) VALUES ('$datum',$prezentacija_klub)";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			$log .= $query3.'<br/>';
		}
		else{
			$query = "UPDATE prezentacija_klub SET prezentacija_klub = $prezentacija_klub WHERE datum = '".$_SESSION['datum']."'";
			$result = $mysqli->query($query);	
			echo $query.'</br>';
			$log .= $query3.'<br/>';
		}
	
	
	//promenljive za specialna racunanja
	$temp = 0;
	$id_arti = array();
	$kolicina_stara = array();
	$kolicina_prodaja_nova = array();
	$kolicina_stanje_novo = array();
	$i = 0;
	$j = 0;
	
	//za svaki artikal koji ima prodaju upisi vrednosti u niz
	foreach($_POST as $id_artikla => $prodata_kolicina) {
		if(is_numeric($id_artikla)){
			if($prodata_kolicina == '') $prodata_kolicina = 0;
			$kolicina_stanje_novo[$id_artikla] = $prodata_kolicina;//ovo je niz za upis u tabelu stanje
			$kolicina_prodaja_nova[$id_artikla] = $prodata_kolicina;//ovo je niz za upis u tabelu prodaja
			
		}
	}
	
	//pronadnji razliku novog i starog stanja
	
		//get all sell values for current day from prodaja table and put them into array
		$z=1;
		$result = $mysqli->query("SELECT * FROM artikli WHERE artikli.status='aktivan'");
		
		echo "<table>";

		while ($obj=mysqli_fetch_object($result)){
			$id_artikla = $obj->id;
			$query = "SELECT kolicina FROM prodaja WHERE datum ='".$datum."' AND id_artikla = ".$id_artikla;
			//echo $query."</br>";
			$result2 = $mysqli->query($query);
			if ($result2->num_rows == 0) $kolicina = 0; 
			else {
				$temp = $result2->fetch_object();
				$kolicina = $temp->kolicina;
			}
			//niz za stare vrednosti
			$kolicina_stara[$id_artikla] = $kolicina;
			echo "</br>For item: ".$obj->ime." old sell value is: ".$kolicina." id of item is ".$z;
			
			//get difrrence for all 
			$diffrence_array_prodaja[$id_artikla] =  $kolicina_stara[$id_artikla] - $kolicina_prodaja_nova[$id_artikla];
			$diffrence_array_stanje[$id_artikla] =  $kolicina_stara[$id_artikla] - $kolicina_prodaja_nova[$id_artikla];
			
			
			
			echo "<tr><td>For item: ".$obj->ime."</td><td> old sell value is: ".$kolicina_stara[$id_artikla]."</td><td>
				new sell value is ".$kolicina_prodaja_nova[$id_artikla]."</td><td> diffrence  is:".$diffrence_array_prodaja[$id_artikla].
				"    id: ".$id_artikla."</td></tr>";
		}
		
		echo "</table>";
		echo "</br>Niz diffrence_array_prodaja</br>";
	print_r ($diffrence_array_prodaja);
	echo "</br>Niz kolicina stanje novo pre korekcije</br>";
	print_r ($kolicina_stanje_novo);
	
	echo "</br>Niz kolicina stanje novo pre unset</br>";
	print_r ($kolicina_stanje_novo);
	
	//zbog specialnih racunanja mora se pre specialnog sabiranja vrednosti prodaje za prvih 14 artikala za tabelu stanje izvrsiti nuliranje vrednosti u nizu kolicina stanje novo za artikle koji nemaju izmenu prodaje
	foreach($kolicina_stanje_novo as $id_artikla => $prodata_kolicina) {
		if($diffrence_array_prodaja[$id_artikla] == 0) $kolicina_stanje_novo[$id_artikla] = 0;
	}
	echo "</br>Niz kolicina stanje posle nulovanja </br>";
	print_r ($kolicina_stanje_novo);
	
	//korekcija kolicine za tabelu stanje
	$kolicina_stanje_novo[4] = $kolicina_stanje_novo[4] + $kolicina_stanje_novo[3] + $kolicina_stanje_novo[2] + $kolicina_stanje_novo[1];
	$diffrence_array_stanje[4] = $diffrence_array_stanje[4] + $diffrence_array_stanje[3] + $diffrence_array_stanje[2] + $diffrence_array_stanje[1];
	$diffrence_array_stanje[3] = 0;
	$diffrence_array_stanje[2] = 0;
	$diffrence_array_stanje[1] = 0;
	$kolicina_stanje_novo[3] = 0;
	$kolicina_stanje_novo[2] = 0;
	$kolicina_stanje_novo[1] = 0;
	$kolicina_stanje_novo[6] = $kolicina_stanje_novo[6] + $kolicina_stanje_novo[5];
	$kolicina_stanje_novo[5] = 0;
	$kolicina_stanje_novo[8] = $kolicina_stanje_novo[8] + $kolicina_stanje_novo[7];
	$kolicina_stanje_novo[7] = 0;
	$kolicina_stanje_novo[10] = $kolicina_stanje_novo[10] + $kolicina_stanje_novo[9];
	$kolicina_stanje_novo[9] = 0;
	$kolicina_stanje_novo[12] = $kolicina_stanje_novo[12] + $kolicina_stanje_novo[11];
	$kolicina_stanje_novo[11] = 0;
	$kolicina_stanje_novo[14] = $kolicina_stanje_novo[14] + $kolicina_stanje_novo[13];
	$kolicina_stanje_novo[13] = 0;
	$diffrence_array_stanje[6] = $diffrence_array_stanje[6] + $diffrence_array_stanje[5];
	$diffrence_array_stanje[5] = 0;
	$diffrence_array_stanje[8] = $diffrence_array_stanje[8] + $diffrence_array_stanje[7];
	$diffrence_array_stanje[7] = 0;
	$diffrence_array_stanje[10] = $diffrence_array_stanje[10] + $diffrence_array_stanje[9];
	$diffrence_array_stanje[9] = 0;
	$diffrence_array_stanje[12] = $diffrence_array_stanje[12] + $diffrence_array_stanje[11];
	$diffrence_array_stanje[11] = 0;
	$diffrence_array_stanje[14] = $diffrence_array_stanje[14] + $diffrence_array_stanje[13];
	$kolicina_stanje_novo[13] = 0;
	
	echo "</br>Niz kolicina stanje posle sabiranja </br>";
	print_r ($kolicina_stanje_novo);
	
	//OBRISI artikle koji nemaju promenu u tabeli stanje
	foreach($kolicina_stanje_novo as $id_artikla => $prodata_kolicina) {
		if($diffrence_array_stanje[$id_artikla] == 0) unset($kolicina_stanje_novo[$id_artikla]);
	}
	
	echo "</br>Niz kolicina stanje novo posle unset</br>";
	print_r ($kolicina_stanje_novo);
	echo "</br>Niz diffrence_array_prodaja</br>";
	print_r ($diffrence_array_prodaja);
	
	//OBRISI artikle koji nemaju promenu u tabeli prodaja
	foreach($kolicina_prodaja_nova as $id_artikla => $prodata_kolicina) {
		if($diffrence_array_prodaja[$id_artikla] == 0) unset($kolicina_prodaja_nova[$id_artikla]);
	}	
	
	echo "</br>Niz kolicina prodaj novo posle unset</br>";
	print_r ($kolicina_prodaja_nova);	
	
	//za artikle koji imaju promenjenu prodaju vrsi se upis u tabelu prodaja 
	//ili izmena vrednosti ako je vec postojala prodaja za uneti datum
	foreach($kolicina_prodaja_nova as $id_artikla => $nova_kolicina) {
		
		echo "</br>id artikla jeste: $id_artikla, nova kolicina jeste $nova_kolicina</br>";
			
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
			$log .= $query.'<br/>';
		}
		
		//ako nema upisanih vrednosti upisi prodatu kolicinu u tabelu prodaja
		else {
			$query = "INSERT INTO prodaja (id_artikla,datum,kolicina) 
					  VALUES (".$id_artikla.",'".$datum."', ".$nova_kolicina.");";
		$result = $mysqli->query($query);
		echo $query.'</br>';
		$log .= $query3.'<br/>';
		}
	}//kraj foreach za korekciju tabele prodaja
		
	//korekcija tabele stanje	
	foreach($kolicina_stanje_novo as $id_artikla => $nova_kolicina) {
			
		//provera da li postoji upis u tabeli stanje za trenutni artikal za trenutni datum
		$query9 = "SELECT kolicina,datum FROM stanje WHERE datum = '".$datum."' AND id_artikla=".$id_artikla.";";
		echo $query9.'</br>';
		$result9 = $mysqli->query($query9);
		//ako postoji upisi u tabeli stanje izvrsi korekciju 
		if ($result9->num_rows != 0){
			$obj9 = $result9->fetch_object();
			$stara_kolicina = $obj9->kolicina;
			echo "</br>Niz diffrence_array_stanje</br>";
			print_r ($diffrence_array_stanje);
			echo 'id artikla = '.$id_artikla.'</br>';
			echo 'nova kolicina = '.$nova_kolicina.'</br>';
			echo 'stara kolicina = '.$stara_kolicina.'</br>';
			echo 'korekcija  = '.$diffrence_array_stanje[$id_artikla].'</br>';
			$query4 = "UPDATE stanje 
					  SET kolicina = kolicina + ".$diffrence_array_stanje[$id_artikla]." 
					  WHERE datum='".$datum."' AND id_artikla=$id_artikla";
			$result4 = $mysqli->query($query4);
			echo $query4.'</br>';
			$log .= $query4.'<br/>';
		}//kraj uslova ako postoji unos u tabeli stanje za trenutni datum
		
		//ako nema podatka u tabeli stanje upisi ga
		else{
			$query = "SELECT kolicina,cena FROM stanje WHERE id_artikla = $id_artikla AND datum <= '$datum' ORDER BY datum DESC LIMIT 0,1 ";
			echo $query.'</br>';
			$log .= $query.'<br/>';
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
			$log .= $query.'<br/>';
		}//kraj uslova ako nema unetih podataka u tabeli stanje za datum korekcije	
		
		//provera da li postoji upis u tabeli stanje za trenutni artikal za datume u buducnosti
		$query3 = "SELECT kolicina,datum FROM stanje WHERE datum > '".$datum."' AND id_artikla=".$id_artikla.";";
		echo $query3.'</br>';
		$result3 = $mysqli->query($query3);
		$log .= $query3.'<br/>';
		
		//ako postoji upisi u tabeli stanje izvrsi korekciju za sve datume u buducnosti
		if ($result3->num_rows != 0){
			while ($obj3=mysqli_fetch_object($result3)){
				$stara_kolicina = $obj3->kolicina;
				echo 'nova kolicina = '.$nova_kolicina.'</br>';
				echo 'stara kolicina = '.$stara_kolicina.'</br>';
				echo 'korekcija  = '.$diffrence_array_stanje[$id_artikla].'</br>';
				$datum4 = $obj3->datum;
				$query4 = "UPDATE stanje 
						  SET kolicina = kolicina + ".$diffrence_array_stanje[$id_artikla]." 
						  WHERE datum='".$datum4."' AND id_artikla=$id_artikla";
				$result4 = $mysqli->query($query4);
				echo $query4.'</br>';
				$log .= $query4.'<br/>';
			}//kraj while petlje
		}//kraj uslova ako postoji unos u tabeli stanje	za trenutni artikal za datume u buducnosti
	}//kraj foreach
	$date_time = date('Y-m-d H:m:s');
	$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
				echo $query."</br>";
				$result = $mysqli->query($query);
	$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!<span><br/><br/><br/>';
	//die; 
	?><script type="text/javascript">  window.location.replace("adminIndex.php");</script><?php
}// kraj if submited

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Korekcija prodaje</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<script>
//change the background color of parent div where input is selected
$(document).ready(function(){
  $("input").focus(function(){
    $(this).parents(".input_wrapper").css( "background", "yellow" );
  });
  $("input").blur(function(){
    $(this).parents(".input_wrapper").css( "background", "white" );
  });
});</script>
</head>
<body>
<div class="container2">
<div style="height:30px"></div>
<a href="adminIndex.php">Glavni Meni</a>
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
            $result = $mysqli->query("SELECT * FROM artikli WHERE artikli.status='aktivan' ORDER BY poredak ASC");?>
            <div style="height:30px"></div>
            <div style="height:30px"></div>
            <span style="color:#F00">
				<div style="width:100px; float:left;display:inline;">Staro</div>
            	<div style='width:200px;height:50px; float:left;display:inline;'>Novo </div>
            	<div style='width:200px; float:left;display:inline; height:50px'>Artikal</div>             
            	<div style='width:200px; height:50px;display:inline;'>Lokacija</div>
            </span>
            <div style="clear:both"></div>
			<?php
            while ($obj=mysqli_fetch_object($result)){
				
				//ako postoji uneta vrednost za izabrani datu prikazi vrednost
				$id_artikla = $obj->id;
				$query = "SELECT kolicina FROM prodaja WHERE datum ='".$datum."' AND id_artikla = ".$id_artikla;
				//echo $query."</br>";
				$result2 = $mysqli->query($query);
				if ($result2->num_rows == 0) $kolicina = '0'; 
				else {
					$temp = $result2->fetch_object();
					$kolicina = $temp->kolicina;
				}
				if($obj->lokacija == 'klub')$color = '#00FF00'; else $color = '#33CCFF';
				?>	
                
                
                
                <div class="input_wrapper">
                    <div style="width:100px; float:left; display:inline;"><?php echo $kolicina;?></div>
                    
                    <div style='width:200px; float:left;display:inline;'>
                        <input type='number' name='<?php echo $id_artikla;?>' value=<?php echo $kolicina;?> />
                    </div>
                    
                    <div style='width:200px; float:left; display:inline;height:50px'><?php echo $obj->ime;?></div>
                   
                    <div style='width:200px; display:inline;color:<?php echo $color;?>'>
                        <?php echo $obj->lokacija;?></div>
                </div>
                <div style="clear:both"></div>	
                
                
                
                
			<?php }
			$query = "SELECT prezentacija FROM prezentacija WHERE datum ='".$datum."'";
			$result2 = $mysqli->query($query);
			if ($result2->num_rows == 0) $prezentacija = 0; 
			else {
				$temp = $result2->fetch_object();
				$prezentacija = $temp->prezentacija;
			}
			$query2 = "SELECT prezentacija_klub FROM prezentacija_klub WHERE datum ='".$datum."'";
			$result3 = $mysqli->query($query2);
			if ($result3->num_rows == 0) $prezentacija_klub = 0; 
			else {
				$temp2 = $result3->fetch_object();
				$prezentacija_klub = $temp2->prezentacija_klub;
			}
			?>
			prezentacija <input type="number" name="prezentacija" value=<?php echo $prezentacija;?> />
            prezentacija za klub <input type="number" name="prezentacija_klub" value=<?php echo $prezentacija_klub;?> />
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
