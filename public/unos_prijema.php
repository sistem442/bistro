<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}

//log string 
$log = '';


include "database_connect.php";
if(isset($_POST['submit'])){
	$result = $mysqli->query("SELECT * FROM artikli");
	$artikli_obj=mysqli_fetch_object($result);
	$datum = $_SESSION['datum'];
	$special_items = array(0,1,2,4,6,8,10,12);
	
	//za svaki artikal
	foreach($_POST as $commodity_id => $primljena_kolicina) {

		//Ako je input kolicina artikla
    	if(is_numeric($primljena_kolicina) && $primljena_kolicina != 0){
			
			//upis prodate kolicine u tabelu prodaja 
			$query = "INSERT INTO prijem (id_artikla,datum,kolicina) 
					  VALUES (".$commodity_id.",'".$datum."',".$primljena_kolicina.");";
			$result = $mysqli->query($query);
			echo $query."</br>";
			$log.= $query."</br>";			
		
			//da li postoji unos u tabeli stanje za dati artikal i trenutni ili veci datum
			$query = "SELECT kolicina 
					  FROM stanje 
					  WHERE datum = '".$datum."' AND id_artikla = ".$commodity_id;
			$result = $mysqli->query($query);
			echo $query."</br>";
			$log.= $query."</br>";	
			
			//ako nema upisa u tabeli stanje upisi prijem
			if ($result->num_rows == 0){

				//get price from artikli table
				$query = "SELECT cena   
						  FROM artikli 
						  WHERE id = ".$commodity_id;
				$result = $mysqli->query($query);
				echo $query.'</br>';
				$price_obj = $result->fetch_object();
				$cena = $price_obj->cena;
		
				//upis primljene kolicine u tabelu stanje 
				$query = "	SELECT kolicina 
							FROM stanje 
							WHERE datum < '".$datum."' AND id_artikla = $commodity_id 
							ORDER BY datum DESC 
							LIMIT 0,1";
				echo $query."</br>";
				$log.= $query."</br>";	
				$result = $mysqli->query($query);
				$obj = $result->fetch_object();
				$stara_kolicina = $obj->kolicina;
				$nova_kolicina = $stara_kolicina + $primljena_kolicina;
				$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) 
						VALUES (".$commodity_id.",'".$datum."', 
					".$nova_kolicina.",".$cena.");";
				$result = $mysqli->query($query);
				echo $query."</br>";
				$log.= $query."</br>";	
				
				//korekcija svih stanja u buducnosti za primljenu kolicinu
				$query = "UPDATE stanje SET kolicina = kolicina + ".$primljena_kolicina." 
						  WHERE datum > '".$datum."' AND id_artikla = ".$commodity_id;
				echo $query."</br>";
				$log.= $query."</br>";	
				$result = $mysqli->query($query);				
			}//kraj bloka ako nema upisa u tabeli stanje
			
			//ako ima upisa u tabeli stanje izmeni kolicinu u tabeli stanje za sadasnji datum i sve naredne datume
			else{ 
				$query = "UPDATE stanje SET kolicina = kolicina + ".$primljena_kolicina." 
						  WHERE datum >= '".$datum."' AND id_artikla = ".$commodity_id;
				echo $query."</br>";
				$log.= $query."</br>";	
				$result = $mysqli->query($query);					
			}//kraj bloka ako ima upisa u tabeli stanje
		}//kraj uslovca ako je input broj
	}//kraj forech bloka
	echo 'log entry:'.$log.'<br/>';
	$date_time = date('Y-m-d H:m:s');
	echo $date_time;
	$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
				echo $query."</br>";
				$log.= $query."</br>";	
				$result = $mysqli->query($query);
	$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!<span><br/><br/><br/>';
//die;
	?><script type="text/javascript"> window.location.replace("adminIndex.php");</script><?php
}//kraj ako je izvrsen submit
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unos prijema</title>
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
<a href="adminIndex.php">Glavni Meni</a>
  <?php $_SESSION['datum'] = $_POST['datum_prijema'];
  $datum=$_POST['datum_prijema']?>
  <div style="color: #060; height: 100px; margin-top:30px; font-size: 36px">
  		Unos prijema za: <?php echo $_SESSION['datum'];?></div>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
    <input type="submit" name="submit" value="Upiši u bazu" />
    <?php
		$result = $mysqli->query("SELECT * FROM artikli");
		echo '<div style="height:30px"></div>';
		$query = "SELECT kolicina FROM prijem WHERE datum ='".$datum."'";
		$result7 = $mysqli->query($query);
		if ($result7->num_rows != 0){  
			$_SESSION['poruka_prodaja'] = '
			<span style="color:green">
					Za izabrani datum je vec uneta prodaja, možete izvršiti samo izmenu prijema.
			</span><br/><br/><br/>';
			$_SESSION['datum_prijema'] =  $_POST['datum_prijema'];
			header( 'Location: korekcija_prijema.php' ) ;
		}				
		//if item is special do not allow input
		$special_items = array(1,2,3,5,7,9,11,13);
		while ($obj=mysqli_fetch_object($result)){
			$commodity_id = $obj->id;
			
			//if item is special disable input 
			if(in_array($commodity_id,$special_items)) $switch = 'hidden'; else $switch = 'number';
			
			//ako postoji uneta vrednost za izabrani datu prikazi vrednost
			$commodity_id = $obj->id;
			$query2 = "SELECT kolicina FROM prijem WHERE datum ='".$datum."' AND id_artikla = ".$commodity_id;
			$result2 = $mysqli->query($query2);
			
			//izlistaj sve artikle
			?>
			<div class='input_wrapper'>
				<div style='width:200px; float:left;'><?php echo $obj->ime;?></div>
			  	<div><input type='<?php echo $switch; ?>' name='<?php echo $commodity_id;?>' value=0 /> &nbsp; </div>
			</div>
			<div style='clear:both'></div>
		<?php }

		$mysqli->close();
            ?>
    <input type="submit" name="submit" value="Upiši u bazu" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
