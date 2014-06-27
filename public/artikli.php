<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Izmena artikala</title>
<link href="backEnd.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script type="application/javascript">
	$(function() {    
		var currentDate = new Date();  
		$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" }); 
		$( ".datepicker" ).datepicker( "setDate",currentDate );		
	 });

	
	
	//validates date
	function isValidDate(dateString)
	{
		// First check for the pattern
		var regex_date = /^\d{4}\-\d{1,2}\-\d{1,2}$/;
	
		if(!regex_date.test(dateString))
		{
			return false;
		}
	
		// Parse the date parts to integers
		var parts   = dateString.split("-");
		var day     = parseInt(parts[2], 10);
		var month   = parseInt(parts[1], 10);
		var year    = parseInt(parts[0], 10);
	
		// Check the ranges of month and year
		if(year < 1000 || year > 3000 || month == 0 || month > 12)
		{
			return false;
		}
	
		var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
	
		// Adjust for leap years
		if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
		{
			monthLength[1] = 29;
		}
	
		// Check the range of the day
		if( day < 0 || day > monthLength[month - 1])
		{
			return false;
		}
		
	}
function validate(){
		var date = $('date_of_price_change').val();
		alert(date);
		if(isValidDate(date)){
			if(confirm('Uneli ste datum: '+date+'. Da li je datum ispravan?' )){
				$("#frmSale").submit();
			}
			else{
				$('#date_of_sale').css('border-color','red');
			}
		}
		else{
			alert('Nije unet validan datum!');
			$('#date_of_sale').css('border-color','red');
		}
	} 

</script>
</head>
<?php
\session_start();
if (!isset($_SESSION['user'])) {
	echo "Niste prijavljeni!<br />
	<a href='login.php'>Prijava</a>
	<br />";
	die;
}
include "database_connect.php";
$message_flag = false;
echo $message_flag;
//price change
$log = '';
if(isset($_POST['submit2'])){
    		//edit artikli table
		$query = "UPDATE artikli SET cena ='".$_POST['cena']."' WHERE ime='".$_POST['ime']."'";
		$log .= $query.'<br/>';
			$result = $mysqli->query($query);
			if ($mysqli->error) {
				try {    
					throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
				} catch(Exception $e ) {
					echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
					echo nl2br($e->getTraceAsString());
					echo '<span style="color:red"></br></br>Došlo je do greške pri izmeni cene.</span></br></br>';
				}
			}
		//edit stanje table
			//check if entry for selected date exits in stanje table	
			$query = " SELECT id_artikla 
						FROM stanje
						WHERE id_artikla = ".$_POST['id']."
						AND datum = '".$_POST['date_of_price_change']."'";
			$log .= $query.'<br/>';
			$result = $mysqli->query($query);
			//echo $query.'<br/>';
			//if there are no entrys insert new entry with same amount of goods and new price and update 
			//all entrys in the future 			
			if($result->num_rows == 0)
			{
				//get amount of goods for previous date
				$query = " SELECT kolicina 
						FROM stanje
						WHERE id_artikla = ".$_POST['id']."
						AND datum < '".$_POST['date_of_price_change']."'
						ORDER BY datum DESC
						LIMIT 0,1";
				$result1 = $mysqli->query($query);
				$log .= $query.'<br/>';
				$obj1 = mysqli_fetch_object($result1);
				//echo $query.'<br/>';

				//insert new row into stanje table
				$query = " INSERT INTO stanje(id_artikla,datum,kolicina,cena)
							VALUES(	".$_POST['id'].",'".$_POST['date_of_price_change']."',".$obj1->kolicina.",".$_POST['cena'].")";
				$result = $mysqli->query($query);
				$log .= $query.'<br/>';
				$result = $mysqli->query($query);
				$log .= $query.'<br/>';
				//echo $query.'<br/>';
				$date_time = date('Y-m-d H:m:s');
				$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
                                
				//update all rows in the future<
				$query = "	UPDATE stanje 
							SET cena ='".$_POST['cena']."' 
							WHERE datum>'".$_POST['date_of_price_change']."'
							AND id_artikla =  ".$_POST['id'];
				$log .= $query.'<br/>';
				$result = $mysqli->query($query);
				$log .= $query.'<br/>';
				//echo $query.'<br/>';
				$date_time = date('Y-m-d H:m:s');
				$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
				//echo $query."</br>";
				$result = $mysqli->query($query);
                                $message = 'Cena je uspesno izmenjena, nova cena jeste:'.$_POST['cena'].' za artikal: '.$_POST['ime'];
                                $message_flag = true;
			}
 			else
			{
				//if there is entry for selected date just update price for selected date and all dates in the future
				$query = "	UPDATE stanje 
							SET cena ='".$_POST['cena']."' 
							WHERE datum>='".$_POST['date_of_price_change']."'
							AND id_artikla =  ".$_POST['id'];
				$log .= $query.'<br/>';
				$result = $mysqli->query($query);
				//echo $query;
					if ($mysqli->error) {
						try {    
							throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
						} catch(Exception $e ) {
							echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
							echo nl2br($e->getTraceAsString());
							echo '<span style="color:red"></br></br>Došlo je do greške pri izmeni cene.</span></br></br>';
						}
					}
				$date_time = date('Y-m-d H:m:s');
				$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
				//echo $query."</br>";
				$result = $mysqli->query($query);
                                $message = 'Cena je uspesno izmenjena, nova cena jeste:'.$_POST['cena'].' za artikal: '.$_POST['ime'];
                                $message_flag = true;
                                }
                        
}

if(isset($_POST['submit'])){
	
	//ubacivanje novog artikla
	if(isset($_POST['unos'])){
		$query1 = "SELECT max(id) maxId FROM artikli";
		$log .= $query1.'<br/>';
		$result1 = $mysqli->query($query1);
		if ($mysqli->error) {
			try {    
				throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());			
			}
		}
		$obj1 = mysqli_fetch_object($result1);
		$max_id = $obj1->maxId;
		$next_id = $max_id+1;
		$query = "INSERT INTO artikli (id,ime,cena,lokacija) 
				  VALUES (".$next_id.",'".$_POST['ime']."',".$_POST['cena'].",'".$_POST['lokacija']."');";
		$log .= $query.'<br/>';
		$result = $mysqli->query($query);
		$date_time = date('Y-m-d H:m:s');
		$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
		//echo $query."</br>";
		$result = $mysqli->query($query);
		if ($mysqli->error) {
			try {    
				throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());
				echo '<span style="color:red"></br></br>Došlo je do greške pri 
						ubacivanju novog artikla u bazu.</span></br></br>';
				echo 'query for id:'.$query1;			
			
			}
		}

		//add default stanje entry in order to all calculations work properly
		$query = "INSERT INTO stanje (id_artikla,cena,kolicina,datum) 
				  VALUES (".$next_id.",".$_POST['cena'].",0,'2014-01-01');";
		$log .= $query.'<br/>';
		$result = $mysqli->query($query);
		$date_time = date('Y-m-d H:m:s');
		$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
		//echo $query."</br>";
		$result = $mysqli->query($query);
		if ($mysqli->error) {
			try {    
				throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());
				echo '<span style="color:red"></br></br>Došlo je do greške pri 
						ubacivanju artikla u tabelu stanje</span></br></br>';
				echo 'query for id:'.$query;			
			
			}
		}
		
		else echo '<span style="color:green">Artikal je uspešno dodat u bazu</span>';
		}
		
	//izmena statusa artikla	
	else{	
		$query = "SELECT status FROM artikli WHERE ime='".$_POST['ime']."'";
		$result = $mysqli->query($query);
		if ($mysqli->error) {
			try {    
				throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());
				echo '<span style="color:red"></br></br>Došlo je do greške pri izmeni statusa artikla</span></br></br>';
			}
		}
		$obj=mysqli_fetch_object($result);
		if($obj->status == 'aktivan')$status = 'neaktivan'; else $status = 'aktivan';
		$query = "UPDATE artikli SET status ='".$status."' WHERE ime='".$_POST['ime']."'";
		$log .= $query2.'<br/>';
		$result = $mysqli->query($query);
		$date_time = date('Y-m-d H:m:s');
		$query = 'INSERT INTO log (query,date_time) VALUES ("'.$log.'", "'.$date_time.'")';
		//echo $query."</br>";
		$result = $mysqli->query($query);
		if ($mysqli->error) {
			try {    
				throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $mysqli->errno);    
			} catch(Exception $e ) {
				echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
				echo nl2br($e->getTraceAsString());
				echo '<span style="color:red"></br></br>Došlo je do greške pri izmeni statusa artikla</span></br></br>';
			}
		}
	}
}
?>
    <script type="text/javascript">
        var message_flag = <?php echo $message_flag;?>;
        if( message_flag == true);
        alert('<?php echo $message;?>');
    </script> 
<body>
	<div class="container2" style="width:1200px">
        <a href="adminIndex.php">Glavni Meni</a><br />
        <br />
        <br />
    	<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
            <input type="hidden" name="unos" value='TRUE' />
            ime: <input type="text" name="ime" />
            cena: <input type="number" name="cena" />
            lokacija: <select name="lokacija">
                <option value="bistro" >bistro</option>
                <option value="klub" >klub</option>
            </select>
            <input type="submit" name="submit" value="Dodaj artikal" />
        </form>
        <br />
        <br />
        <br />
		<?php
            $result = $mysqli->query("SELECT * FROM artikli ORDER BY poredak ASC");
			echo "<div style='height:30px'></div>";
            while ($obj=mysqli_fetch_object($result))
                {?>
				<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="prodaja" method="post">
                    <input type="hidden" name="ime" value="<?php echo $obj->ime; ?>"/>
					<input type="hidden" name="id" value="<?php echo $obj->id; ?>"/>
                    <div style='width:200px; float:left; height:50px'><?php echo $obj->ime; ?></div>
                    <div style='width:100px; float:left;height:50px'><?php echo $obj->status; ?></div>
                    <div style='width:120px; float:left;height:50px'>
                    	<input type='submit' name="submit" value='Izmeni status'/></div>
                    <div style='width:700px; float:left;height:50px'>Lokacija: 
                    	<select name="lokacija">
                        	<option value="bistro" <?php if($obj->lokacija == 'bistro') echo "selected='selected'"; ?>>bistro</option>
                           	<option value="klub" <?php if($obj->lokacija == 'klub') echo "selected='selected'"; ?>>klub</option>
                        </select>
						Cena:<input type="number" style="width:50px" value="<?php echo $obj->cena; ?>" name="cena"/>
						Datum promene cene:<input type="text" class="datepicker" name="date_of_price_change"  value=""/>
                        <input type="submit" name="submit2" value="Izmeni cene" id="submit2"/>
                    </div>
                    <div style="clear:both"></div>
                    
                </form>
                <?php 
				}
            	$mysqli->close();?>
</div><!--container-->
</body>
</html>
