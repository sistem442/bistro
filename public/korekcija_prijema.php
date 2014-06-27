<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";

$log = '';

if(isset($_POST['submit'])){
	$result = $mysqli->query("SELECT * FROM artikli");
	$artikli_obj=mysqli_fetch_object($result);
	$datum = $_SESSION['datum'];
	
	//za svaki artikal u niz kolicina prijem novo upisi vrednost nove kolicine
	foreach($_POST as $commodity_id => $primljena_kolicina) {
		if(is_numeric($commodity_id)){
			if($primljena_kolicina == '') $primljena_kolicina = 0;
			$quantity_prijem_novo[$commodity_id] = $primljena_kolicina;//ovo je niz za upis u tabelu prijem
		}
	}
	//print_r ($quantity_prijem_novo);	
		
	  //compare new and old values for entrance table
		
		//get all entrance values from prijem table for current day
		
		$result = $mysqli->query("SELECT * FROM artikli WHERE artikli.status='aktivan'");
		$diffrence_array = array();
		
		while ($obj=mysqli_fetch_object($result)){
			$commodity_id = $obj->id;
			$query = "SELECT kolicina FROM prijem WHERE datum ='".$datum."' AND id_artikla = ".$commodity_id;
			//echo $query."</br>";
			$result2 = $mysqli->query($query);
			if ($result2->num_rows == 0) $quantity = 0; 
			else {
				$temp = $result2->fetch_object();
				$quantity = $temp->kolicina;
			}
			//niz za stare vrednosti
			$quantity_stara[$commodity_id] = $quantity;
			//echo "</br>For item: ".$obj->ime." old sell value is: ".$quantity;
			
			//get difrrence for all 
			$diffrence_array[$commodity_id] =  $quantity_stara[$commodity_id] - $quantity_prijem_novo[$commodity_id];
			
		}
		echo "</br>niz kolicina stara</br>";
		print_r ($quantity_stara);
		echo "</br>niz kolicina prijem novo</br>";
		print_r ($quantity_prijem_novo);
		echo "</br>niz deffrence array</br>";
		print_r ($diffrence_array);

		//da ne bi upisivao nule za sve artikle		
		foreach($diffrence_array as $commodity_id => $prodata_kolicina) {
			if($diffrence_array[$commodity_id] == 0) unset($diffrence_array[$commodity_id]);
		}
		echo "</br>niz deffrence array posle unset</br>";
		print_r ($diffrence_array);
		//Korekcija tabele prijem
		foreach($diffrence_array as $commodity_id => $razlika){
			$query2 = "SELECT kolicina FROM prijem WHERE datum = '".$datum."' AND id_artikla = ".$commodity_id.";";
			//echo $query2;
			$log .= $query2.'<br/>';
			$result2 = $mysqli->query($query2);
			
			//ako ima upisanih vrednosti za prodaju izvrsi korekciju
			if ($result2->num_rows != 0){
				$obj = $result2->fetch_object();
				$stara_kolicina = $obj->kolicina;
				$query = "UPDATE prijem SET kolicina = $stara_kolicina - ".$diffrence_array[$commodity_id]." 
						  WHERE datum = '".$datum."' AND id_artikla = ".$commodity_id."";
				$result = $mysqli->query($query);	
				//echo $query.'</br>';
				$log .= $query.'<br/>';
			}
			
			//ako nema upisanih vrednosti upisi prodatu kolicinu u tabelu prodaja
			else {
				$query = "INSERT INTO prijem (id_artikla,datum,kolicina) 
						  VALUES (".$commodity_id.",'".$datum."', -".$diffrence_array[$commodity_id].");";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			$log .= $query.'<br/>';
			}
		}//kraj foreach za korekciju tabele prodaja
		
	//korekcija tabele stanje	
	foreach($diffrence_array as $commodity_id => $nova_kolicina) {
		
		//provera da li postoji upis u tabeli stanje za trenutni artikal za trenutni datum
		$query9 = "SELECT kolicina,datum FROM stanje WHERE datum = '".$datum."' AND id_artikla=".$commodity_id.";";
		echo $query9.'</br>';
		$result9 = $mysqli->query($query9);
		$log .= $query9.'<br/>';
		//ako postoji upisi u tabeli stanje izvrsi korekciju za sve datume u buducnosti
		if ($result9->num_rows != 0){
			$obj9 = $result9->fetch_object();
			$stara_kolicina = $obj9->kolicina;
			echo 'nova kolicina = '.$nova_kolicina.'</br>';
			echo 'stara kolicina = '.$stara_kolicina.'</br>';
			echo 'korekcija  = '.$diffrence_array[$commodity_id].'</br>';
			$query4 = "UPDATE stanje 
					  SET kolicina = kolicina - ".$diffrence_array[$commodity_id]." 
					  WHERE datum='".$datum."' AND id_artikla=$commodity_id";
			$result4 = $mysqli->query($query4);
			echo $query4.'</br>';
			$log .= $query4.'<br/>';
		}//kraj uslova ako postoji unos u tabeli stanje
		
		//ako nema podatka u tabeli stanje upisi ga
		else{
			
			//procitaj vrednost prethodnog stanja
			$query = "SELECT kolicina,cena FROM stanje WHERE id_artikla = $commodity_id AND datum <= '$datum' ORDER BY datum DESC LIMIT 0,1 ";
			echo $query.'</br>';
			$log .= $query.'<br/>';
			$result = $mysqli->query($query);
			$obj = $result->fetch_object();
			echo "nova kolicina jeste:".$nova_kolicina."<br/>";
			echo "stara kolicina jeste:".$obj->kolicina."<br/>";
			
			//izracunaj novo stanje
			$novo_stanje = $obj->kolicina - $diffrence_array[$commodity_id];
			echo "novo stanje jeste :".$novo_stanje."<br/>";
			
			//upisi novo stanje u tabelu stanje
			$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) VALUES (".$commodity_id.",'".$datum."', 
					$novo_stanje,".$obj->cena.");";
			$result = $mysqli->query($query);
			echo $query.'</br>';
			$log .= $query.'<br/>';
		}//kraj uslova ako nema unetih podataka u tabeli stanje za datum korekcije	
		
		//provera da li postoji upis u tabeli stanje za trenutni artikal za datume u bubucnosti
		$query3 = "SELECT kolicina,datum FROM stanje WHERE datum > '".$datum."' AND id_artikla=".$commodity_id.";";
		echo $query3.'</br>';
		$log .= $query3.'<br/>';
		$result3 = $mysqli->query($query3);
		
		//ako postoji upisi u tabeli stanje izvrsi korekciju za sve datume u buducnosti
		if ($result3->num_rows != 0){
			while ($obj3=mysqli_fetch_object($result3)){
				$stara_kolicina = $obj3->kolicina;
				echo 'nova kolicina = '.$nova_kolicina.'</br>';
				echo 'stara kolicina = '.$stara_kolicina.'</br>';
				echo 'korekcija  = '.$diffrence_array[$commodity_id].'</br>';
				$datum4 = $obj3->datum;
				$query4 = "UPDATE stanje 
						  SET kolicina = kolicina - ".$diffrence_array[$commodity_id]." 
						  WHERE datum='".$datum4."' AND id_artikla=$commodity_id";
				$result4 = $mysqli->query($query4);
				echo $query4.'</br>';
				$log .= $query4.'<br/>';
			}//kraj while petlje
		}//kraj uslova ako postoji unos u tabeli stanje
		
		
	}//end of foreach for table stanje
	$date_time = date('Y-m-d H:m:s');
	$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
				echo $query."</br>";
				$result = $mysqli->query($query);	
	$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!<span><br/><br/><br/>'; 
	?><script type="text/javascript">  window.location.replace("adminIndex.php");</script><?php
}// kraj if submited
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Korekcija prijema</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<script type="text/javascript">
//change the background color of parent div where input is selected
$(document).ready(function(){
  $("input").focus(function(){
    $(this).parents(".input_wrapper").css( "background", "yellow" );
  });
  $("input").blur(function(){
    $(this).parents(".input_wrapper").css( "background", "white" );
  });
});
</script>
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
	  $_SESSION['datum'] = $_SESSION['datum_prijema'];
	  $datum = $_SESSION['datum_prijema'];
  }
  else{
	  $_SESSION['datum'] = $_POST['datum_prijema'];
  	  $datum = $_POST['datum_prijema'];
	  //echo 'datum jeste '.$datum;
  }
  ?>
</div>  <div style="color: #060; height: 100px; margin-top:10px; font-size: 36px">Korekcija prijema za: <?php echo $_SESSION['datum'];?></div>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
    <input type="submit" name="submit" value="Upiši u bazu" />
    <?php
            $result = $mysqli->query("SELECT * FROM artikli ORDER BY poredak ASC");?>
            <div style="height:30px"></div>
            <div style="height:30px"></div>
            <span style="color:#F00">
			<div style="width:100px; float:left; display:inline;">Stara vrednost</div>
            <div style='height:50px; width:200px;float:left; display:inline;'>Unesite novu vrednost </div>
            <div style='width:200px; height:50px; display:inline;'>Artikal</div>
             <div style="clear:both"></div>
            </span>
			<?php
			$special_items = array(1,2,3,5,7,9,11,13);
            while ($obj=mysqli_fetch_object($result)){
				$commodity_id = $obj->id;
				
				//if item is special disable input 
				if(in_array($commodity_id,$special_items)) $switch = 'hidden'; else $switch = 'number';
				
				//if value exists in prijem table display it in left column
				$query = "SELECT kolicina FROM prijem WHERE datum ='".$datum."' AND id_artikla = ".$commodity_id;
				//echo $query."</br>";
				$result2 = $mysqli->query($query);
				if ($result2->num_rows == 0) $quantity = '0'; 
				else {
					$temp = $result2->fetch_object();
					$quantity = $temp->kolicina;
				}?>
           		<div class="input_wrapper">				
					<div style="width:100px; float:left;display:inline;"><?php echo $quantity;?></div>
    	            <div style='float:left; width:200px;display:inline;'>
                    	<input name="<?php echo $commodity_id.'" type="'.$switch.'" value="'.$quantity.'"';?>/>
                        &nbsp;
                    </div>
        	        <div style='width:200px; display:inline;'><?php echo $obj->ime;?></div>
                </div>
            	<div style='clear:both'></div>
			<?php }
            $mysqli->close();
            ?>
    <input type="submit" name="submit" value="Izvrši korekciju" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
