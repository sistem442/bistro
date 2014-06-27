
<?php
session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
$_SESSION['datum'] = $_POST['datum_izvestaja'];
  $datum = $_POST['datum_izvestaja'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Klub <?php echo date("d-m-Y", strtotime($datum));?></title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<style type="text/css">
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
	table{border-collapse:collapse;}
	table, th, td{border: 1px solid black;}
	td{height:10px; text-align:center}
	.left{text-align:left}
	body{font:  14pt "Times New Roman"}
</style>
</head>
<body>
<div class="container2">
  <div class="naslov">DNEVNI OBRAČUN PROMETA I ZALIHA ROBE U UGOSTITELJSTVU: <div class="datum"><?php echo date("d-m-Y", strtotime($datum));?></div></div>
  <table>
   	<tr>
        <td>ime</td>
        <td>jedinica mere</td>
        <td>cena</td>
        <td>stanje pret. dana</td>
        <td>primljena količina</td>
        <td>vrednost prim. robe</td>
        <td>ukupno</td>
        <td>prodata količina</td>
        <td>vrednost prod. robe</td>
        <td>zalihe</td>
        <td>vrednost zaliha na kraju dana</td> 
    </tr> 
  <?php
  	//get presentation for current day
	$query = "SELECT prezentacija_klub FROM prezentacija_klub 
			  WHERE datum='".$datum."'";
	$result = $mysqli->query($query);
	if ($result->num_rows != 0){
	$result = $mysqli->query($query);
	$obj = $result->fetch_object();
	$prezentacija= $obj->prezentacija_klub;
	}
	else $prezentacija = 0;
	
	
            $query = "SELECT artikli.id,artikli.cena,artikli.ime FROM artikli 
					  WHERE artikli.status='aktivan' AND lokacija = 'klub'";
			//echo $query.'</br>';
			$result = $mysqli->query($query);
			$temp = 0;
			$pazar = 0;
			$korekcija = 0;
			$vrednost_zaliha_na_kraju_dana = 0;
			$vrednost_zaliha_na_kraju_dana_prethodni = 0;
			$primljena_kolicina = 0;
			$vrednost_prijema = 0;
            while ($obj=mysqli_fetch_object($result)){
				
				//procitaj vrednosti reda
				$id = $obj->id;
				$ime = $obj->ime;
				
				//zalihe
				$query4 = "SELECT kolicina,cena FROM stanje
					  WHERE id_artikla = $obj->id AND datum <='$datum' ORDER BY datum DESC LIMIT 0,1 ";
				$result4 = $mysqli->query($query4);
				$prodaja =mysqli_fetch_object($result4);
				$cena = $prodaja->cena; 
				//echo $query4.'</br>';
				if ($result4->num_rows != 0){ 
					$zalihe = $prodaja->kolicina;
				}
				else $zalihe = 0;
				
				
				//primljena kolicina
				$query1 = "SELECT kolicina FROM prijem
					  WHERE id_artikla = $obj->id AND datum='$datum'";
				$result1 = $mysqli->query($query1);
				$kolicina = mysqli_fetch_object($result1);
				//echo $query1.'</br>';
				if ($result1->num_rows != 0){ 
					$primljena_kolicina = $kolicina->kolicina;
					//echo $primljena_kolicina."</br>";
				}
				else {$primljena_kolicina = 0;}
				if ($result1->num_rows != 0){ 
				$suma_prijem = $kolicina->kolicina*$cena;
				}
				else $suma_prijem = 0;
				$vrednost_prijema = $vrednost_prijema + $suma_prijem;
				
				//prodata kolicina
				$query2 = "SELECT kolicina FROM prodaja
					  WHERE id_artikla = $obj->id AND datum='$datum'";
				$result2 = $mysqli->query($query2);
				$prodaja =mysqli_fetch_object($result2);
				//echo $query2.'</br>';
				if ($result2->num_rows != 0){ 
					$prodata_kolicina = $prodaja->kolicina;
				}
				else $prodata_kolicina = 0;
				
				//stanje prethodnog dana
				$query3 = "SELECT kolicina FROM stanje
					  WHERE id_artikla = $obj->id AND datum<'$datum' ORDER BY datum DESC LIMIT 0,1";
				$result3 = $mysqli->query($query3);
				$stanje_p =mysqli_fetch_object($result3);
				//echo $query3.'</br>';
				if ($result3->num_rows != 0){ 
					$stanje_prethodni_dan = $stanje_p->kolicina;
				}
				else $stanje_prethodni_dan = 0;
				
				//vrednost zaliha na kraju dana for previous day
				$vrednost_zaliha_na_kraju_dana_prethodni = 
					$vrednost_zaliha_na_kraju_dana_prethodni + $stanje_prethodni_dan * $cena;
				
				
				
				$pazar = $pazar + ($prodata_kolicina * $cena);
				$vrednost_zaliha_na_kraju_dana = $vrednost_zaliha_na_kraju_dana + $zalihe * $cena;
				
				//izlistaj sve artikle 
				?>
                <tr>
                    <td><?php echo $ime;?></td>
                    <td>kom</td>
                    <td><?php echo $cena;?></td>
                    <td><?php echo number_format($stanje_prethodni_dan);?></td>
                    <td><?php echo number_format($primljena_kolicina);?></td>
                    <td><?php echo number_format($primljena_kolicina * $cena);?></td>
                    <td><?php echo number_format($stanje_prethodni_dan + $primljena_kolicina);?></td>
                    <td><?php echo number_format($prodata_kolicina);?></td>
                    <td><?php echo number_format($prodata_kolicina * $cena);?></td>
                    <td><?php echo number_format($zalihe);?></td>
                    <td><?php echo number_format($zalihe * $cena);?></td> 
                </tr>   
                
                         
                <?php }
            $mysqli->close();
            ?>
            
            <tr>
                    <td style="width:150px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($vrednost_prijema);?></td>
                    <td></td>
                    <td>Predat pazar:</td>
                    <td><?php echo number_format($pazar); $pazar_predat = $pazar - $prezentacija; ?></td>
                    <td></td>
                    <td></td> 
                </tr>  
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Prezentacija:</td>
                    <td><?php echo  number_format($prezentacija);?></td>
                    <td></td>
                    <td></td> 
                </tr>  
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Predat pazar:</td>
                    <td><?php echo number_format($pazar_predat); ?></td>
                    <td></td>
                    <td><?php echo number_format($vrednost_zaliha_na_kraju_dana); ?></td> 
                </tr> 
            </table>
            <br />
Obračun sastavio:<br />
Katica Vasiljov<br />
<br />
-----------------------------------
    
  </form>
  <br />
  <br />
  <script type="text/javascript">
  x = <?php echo $vrednost_zaliha_na_kraju_dana_prethodni;?>;
  y = <?php echo $pazar;?>;
  r = <?php echo $vrednost_prijema;?>;
  t = x -y + r;
  
  alert ("Stanje prethodni dan ("+x+") - pazar ("+y+") + primljena kolicina ("+r+")= "+t)</script>
</div>
<!--container-->
</body>
</html>
