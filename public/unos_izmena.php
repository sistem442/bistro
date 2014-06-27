<a href="adminIndex.php">Glavni Meni</a>
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
	$datum = $_SESSION['datum'];
	$result = $mysqli->query("SELECT * FROM artikli");
	$artikli_obj=mysqli_fetch_object($result);
	
	//za svaki artikal
	foreach($_POST as $id_artikla_all => $promenljiva) {
		//echo "id_artikla jeste:".$id_artikla_all.'</br>';
		
		//gledam sva polja za unos osim submit
		if(is_numeric($promenljiva)){
			//vrednost prijema
			if(is_numeric($promenljiva) && $promenljiva != 0 && substr_compare($id_artikla_all,"prim",1,4)==0){
				//dobijam id broj artikla
				$id_artikla = substr($id_artikla_all, 0, -4);
				//echo 'id artikla posle substr jeste: '.$id_artikla.'</br>';
				$kolicina_prijem = $promenljiva;
				//echo 'prijem:'.$kolicina_prijem.'</br>';
				
				//OBRADA prijem
				$query = "SELECT kolicina FROM prijem WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
				$result = $mysqli->query($query);
				$obj = $result->fetch_object();
				$row_cnt = $result->num_rows;
				//echo "broj redova prijem jeste: ".$row_cnt.'</br>';
				
				//Ako je vec uneta primljena kolicina izvrsiti update
				if ($row_cnt != 0){
					$query = "UPDATE prijem SET kolicina=".$kolicina_prijem." 
							  WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
					$result = $mysqli->query($query);	
				}
				
				//ako nije uneta primljena kolicina izvrsi insert
				else{
					$query = "INSERT INTO prijem (id_artikla, datum, kolicina) 
							  VALUES (".$id_artikla.",'".$datum."',".$kolicina_prijem.")"; 
					$result = $mysqli->query($query);
				}
				
				//provera da li vec postoji upis u tabelu stanje
				$query = "SELECT kolicina FROM stanje WHERE datum = '".$datum.
							 "' AND id_artikla =".$id_artikla;
				$result = $mysqli->query($query);
				$row_cnt = $result->num_rows;
								
				//Ako je vec postoji upis u tabeli stanje izvrsiti update tabele stanje
				if ($row_cnt != 0){
					$query = "SELECT kolicina FROM stanje WHERE datum < '".$datum.
							 "' AND id_artikla =".$id_artikla." ORDER BY datum DESC LIMIT 0,1";
					$result = $mysqli->query($query);
					$obj = $result->fetch_object();
					$stara_kolicina = $obj->kolicina;
					$nova_kolicina = $stara_kolicina + $kolicina_prijem;
					$query = "UPDATE stanje SET kolicina=".$nova_kolicina." 
							  WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
					$result = $mysqli->query($query);
				}//kraj bloka za update tabele stanje
				
				//ako jos ne postoji upis u tabeli stanje izvrsiti INSERT
				else{
					$query = "SELECT kolicina FROM stanje WHERE datum < '".$datum.
							 "' AND id_artikla =".$id_artikla." ORDER BY datum DESC LIMIT 0,1";
					$result = $mysqli->query($query);
					$obj = $result->fetch_object();
					$stara_kolicina = $obj->kolicina;
					$nova_kolicina = $stara_kolicina + $kolicina_prijem;
					$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) VALUES (".$id_artikla.",'".$datum."', 
						".$nova_kolicina.",".$artikli_obj->cena.");";
					$result = $mysqli->query($query);
					if ($mysqli->error) {
							try {    
								throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", 
								$mysqli->errno);    
							} 
							catch(Exception $e ) {
								echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
								echo nl2br($e->getTraceAsString());
								echo '<span style="color:red"></br></br>Došlo je do greške pri upisu u 
								tabelu stanje.</br> Ako je broj greške 1062 vrednosti su već unete 
								za ovaj dan</span></br></br>';
							}
					}
					else {
						$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!</span><br/><br/><br/>'; 
						header( 'Location: adminIndex.php' ) ;
					}
				}//kraj bloka za insert u tabelu stanje
			}//kraj bloka za obradu prijema
			
			//vrednost prodaje
			if(is_numeric($promenljiva) && $promenljiva != 0 && substr_compare($id_artikla_all,'prod',1,4)==0){
				//dobijam id broj artikla
				$id_artikla = substr($id_artikla_all, 0, -4);
				//echo 'id artikla posle substr jeste: '.$id_artikla.'</br>';
				$kolicina_prodaja = $promenljiva;
				//echo 'prodaja:'.$kolicina_prodaja.'</br>';
				//OBRADA prodaja
				$query2 = "SELECT kolicina FROM prodaja WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
				echo $query2.'</br>';
				$result2 = $mysqli->query($query2);
				$obj = $result2->fetch_object();
				$row_cnt2 = $result2->num_rows;
				
				//Ako je vec uneta primljena kolicina izvrsiti update
				if ($row_cnt2 != 0){
					$query = "UPDATE prodaja SET kolicina=".$kolicina_prodaja." 
							  WHERE datum = '".$datum."' AND id_artikla = ".$id_artikla."";
					//echo $query.'</br>';
					$result = $mysqli->query($query);	
				}
				//ako nije uneta primljena kolicina izvrsi insert
				else{
					$query = "INSERT INTO prodaja (id_artikla, datum, kolicina) 
							  VALUES (".$id_artikla.",'".$datum."',".$kolicina_prodaja.")"; 
					//echo $query.'</br>';
					$result = $mysqli->query($query);
					
					//upis prodate kolicine u tabelu stanje
					$query = "SELECT kolicina FROM stanje WHERE datum < '".$datum."' AND id_artikla = ".$id_artikla.
							 " ORDER BY datum DESC LIMIT 0,1";
					//echo $query;
					$result = $mysqli->query($query);
					$obj = $result->fetch_object();
					$stara_kolicina = $obj->kolicina;
					$nova_kolicina = $stara_kolicina - $prodata_kolicina;
					echo "stara kolicina:".$stara_kolicina."</br>";
					echo "nova kolicina:".$nova_kolicina;
					$query = "INSERT INTO stanje (id_artikla,datum,kolicina,cena) VALUES (".$id_artikla.",'".$datum."', 
						".$nova_kolicina.",".$artikli_obj->cena.");";
					$result = $mysqli->query($query);
					if ($mysqli->error) {
							try {    
								throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
							} 
							catch(Exception $e ) {
								echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
								echo nl2br($e->getTraceAsString());
								echo '<span style="color:red"></br></br>Došlo je do greške pri upisu u tabelu stanje.
									</br> Ako je broj greške 1062 vrednosti su već unete za ovaj dan</span></br></br>';
							}
						}
					else {
							$_SESSION['poruka'] = '<span style="color:green">Upis u bazu je uspešan!
													</span><br/><br/><br/>'; 
							header( 'Location: adminIndex.php' ) ;
					}
				}			
			}	
		}//kraj for each
	}//kraj if numeric
}//kraj IF 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unos prodaje</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
</head>
<body>
<div class="container2">
  <?php $_SESSION['datum'] = $_POST['datum_stanja'];?>
  <div style="color: #060; height: 100px; margin-top:30px; font-size: 36px">Unos prodaje za: <?php echo $_SESSION['datum'];?></div>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
    <input type="submit" name="submit" value="Upiši u bazu" />
    <?php
            $result = $mysqli->query("SELECT * FROM artikli");
            echo '<div style="height:30px"></div>';?>
			
			<div style="width:200px; float:left; height:50px">ARTIKAL</div>
            <div style="width:50px; float:left;">PRET.</div>
            <div style='height:50px; width:60px;float:left;'>
            PRIM</div>
            <div style='height:50px; width:60px;float:left; margin-left:10px'>
            PROD</div>
            <div style="width:50px; float:left;">STANJE</div>
            <div style="clear:both"></div>
                
			<?php
			//prikaz svih artikala iz tabele artikli
            while ($obj=mysqli_fetch_object($result)){
				
				$id_artikla = $obj->id;
				
				//pronadji kolicinu za prethodni dan
				$query = "SELECT kolicina FROM stanje WHERE datum <' ".$_SESSION['datum']."'
						  AND id_artikla = ".$id_artikla." ORDER BY datum DESC LIMIT 0,1";
				$result3 = $mysqli->query($query);
				if ($row_cnt = $result3->num_rows == 0) $kolicina_prethodnog_dana = 0; 
				else {
					$temp2 = $result3->fetch_object();
					$kolicina_prethodnog_dana = $temp2->kolicina;
				}
				
				//promadji primljenu kolicinu za trenutni datum
				$query = "SELECT kolicina FROM prijem WHERE datum =' ".$_SESSION['datum']."' 
						  AND id_artikla = ".$id_artikla;
				$result2 = $mysqli->query($query);
				if ($row_cnt = $result2->num_rows == 0) $kolicina_prijem = 0; 
				else {
					$temp = $result2->fetch_object();
					$kolicina_prijem = $temp->kolicina;
				}
				
				//promadji prodatu kolicinu za trenutni datum
				$id_artikla = $obj->id;
				$query = "SELECT kolicina FROM prodaja WHERE datum =' ".$_SESSION['datum']."' 
						  AND id_artikla = ".$id_artikla;
				$result3 = $mysqli->query($query);
				if ($row_cnt = $result3->num_rows == 0) $kolicina_prodaja = 0; 
				else {
					$temp3 = $result3->fetch_object();
					$kolicina_prodaja = $temp3->kolicina;
				}	
				
				//promadji stanje za trenutni datum
				$query = "SELECT kolicina FROM stanje WHERE datum =' ".$_SESSION['datum']."' 
						  AND id_artikla = ".$id_artikla;
				$result4 = $mysqli->query($query);
				if ($row_cnt = $result4->num_rows == 0) $kolicina_stanje = 0; 
				else {
					$temp4 = $result4->fetch_object();
					$kolicina_stanje = $temp4->kolicina;
				}				
				
				//izlistaj sve artikle ?>              
                <div style="width:200px; float:left; height:50px"><?php echo $obj->ime;?></div>
				<div style="width:50px; float:left;"><?php echo $kolicina_prethodnog_dana;?></div>
				<div style='height:50px; width:60px;float:left;'>
                <input type='number' style="width:60px" name='<?php echo $id_artikla.'prim';?>' 
                value='<?php echo $kolicina_prijem?>'/></div>
                <div style='height:50px; width:60px;float:left; margin-left:10px'>
                <input type='number' style="width:60px" name='<?php echo $id_artikla.'prod';?>' 
                value='<?php echo $kolicina_prodaja?>'/></div>
                <div style="width:50px; float:left;"><?php echo $kolicina_stanje;?></div>
                <div style="clear:both"></div>
			<?php
            }				
            //$mysqli->close();
            ?>
    <input type="submit" name="submit" value="Upiši u bazu" />
  </form>
  <br />
  <br />
</div>
<!--container-->
</body>
</html>
