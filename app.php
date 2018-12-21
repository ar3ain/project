<?php
error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);

if($_POST){
	$q = $_POST["txtSearch"];
}
else{
	$q = $_GET["q"];
}
				
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<meta name="viewport" content="width=device-width, initial-scale=1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript">

function capture(e){
	var evt = e || window.event; 
	if(evt.keyCode==13){
		//alert('you pressed enter');
	}
}
document.onkeyup=capture

</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<!--<link href="style.css" rel="stylesheet">-->
<style>
.active{
 color: red !important;
}
</style>
<title>Untitled Document</title>
</head>
<body>
<form action="app.php" method="post">
<table width="600" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td><p><strong>GitHub Search</strong></p>
      <table width="98%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td><p align="center">
            <input type="text" name="txtSearch" id="txtSearch" size="80" value="<?php echo $q;?>" />
          </p>
          <p>
		  <?php 
		  	$totalCount="";		
			if($_POST || $_GET){
								
				$rowperpage = 10; // Total rows display
 
				$row = 0;
				if(isset($_GET['page'])){
					$row = $_GET['page']-1;
					if($row < 0){
						$row = 0;
					}
				}				
				
				/*https://api.github.com/search/repositories?per_page=${per_page}&q=*/
				/*$url = "https://api.github.com/search/repositories?q=".$q."&per_page=10&page=3";*/
				$url = "https://api.github.com/search/repositories?q=".$q."&per_page=".$rowperpage."&page=".$_GET['page'];
				//echo $url."<br>";
				$headers = array(
					'Accept: application/vnd.github.v3+json',
					'User-Agent: node.js'
				);
				
				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_GET, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				//curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
				$result = curl_exec($ch);
				//print_r($result);
				
				if($result === false){
					$json_data = "Curl error: " . curl_error($ch);
				}
				else{
					$json = $result;
					//echo $json;
					if(substr($json, 0, 5) == "<?xml") {
						//echo 'Is XML';
						$xml = simplexml_load_string($json);
						$json_data = json_encode($xml);
					} else {
						//echo 'Not XML';
						$json_data = $result;
					}
				}
				//echo $json_data;
				
				$data = json_decode($json_data);
				//print_r("Decode: ".$result);
				$totalCount = $data->total_count;
				$items = $data->items;
				
				// calculate total pages
				$total_pages = ceil($totalCount / $rowperpage);
				
				if($totalCount<>0){
					echo $totalCount." repository results";
				}
				
				//print_r($items);
				$array = json_decode(json_encode($items),true);
				//print_r($array);
		?>
          </p>
            <table width="98%" border="0" cellspacing="0" cellpadding="0">
            <?php				
				if($totalCount<>0){
										
					//print_r($dataArray);
					foreach($array as $x=>$x_value){
						//echo $x." - ";
						//print_r($x_value);
						//echo "<br><br>";
						foreach($x_value as $y=>$y_value){
							/*echo $y." - ";
							print_r($y_value);
							echo "<br>";*/
							if($y=="full_name"){
								$fullName = "<font color=#0099CC><strong>".$y_value."</strong></font>"; 
							}
							if($y=="description"){
								$desc = "<em>".$y_value."<em>"; 
							}
							if($y=="updated_at"){
								$dt = substr($y_value,0,10);
								$nameOfDay = date('D', strtotime($dt));
								$dtDesc = date('M d Y', strtotime($dt));
								
								$updatedDt = "Updated on ".$nameOfDay." ".$dtDesc."<br>";
							}
							if($y=="language"){
								$lang = $y_value; 
								if($lang<>""){
									$lang = "&#8226;&nbsp;".$y_value; 
								}
							}
							if($y=="stargazers_count"){
								$star = $y_value; 
							}
							
						}
			?>
              <tr>
                <td colspan="3">________________________________________________________________________</td>
                </tr>
              <tr>
                <td width="63%">
				<?php 
					echo $fullName."<br>".htmlspecialchars_decode($desc)."<br>".$updatedDt."<br>";
				?>
                </td>
                <td width="22%">&nbsp;<?php echo $lang; ?></td>
                <td width="15%">&nbsp;<img src="star.png" width="26" height="23" /><?php echo $star; ?></td>
              </tr>
				<?php		//}
					}
				?>
              <tr>
                <td colspan="3">
                <!-- Number list (start) -->
				<div align="center"><ul class="pagination">
                 
			<?php				
                
				$i = 1;
				$prev = 0;
                
				// Total number list show
				$numpages = 10;
                
				// Set previous page number and start page 
				if(isset($_GET['next'])){
					$i = $_GET['next']+1;
					$prev = $_GET['next'] - ($numpages);
				}
                
				if($prev <= 0) 
					$prev = 1;
				if($i == 0) 
					$i=1;
				
				// Previous button next page number
				
				$prevnext = 0;
				if(isset($_GET['next'])){
					$prevnext = ($_GET['next'])-($numpages+1);
					if($prevnext < 0){
						$prevnext = 0;
					}
				}
				
				// Previous Button
				if($totalCount>10){
					echo '<li ><a href="?q='.$q.'&page='.$prev.'&next='.$prevnext.'">Previous</a></li>';
				}
				
				if($i != 1){
					echo '<li ><a href="?q='.$q.'&page='.($i-1).'&next='.$_GET['next'].'" '; 
					if( ($i-1) == $_GET['page'] ){
						echo ' class="active" ';
					}
					echo ' >'.($i-1).'</a></li>';
				}
				
				// Number List
				for ($shownum = 0; $i<=$total_pages; $i++,$shownum++) {
					if($i%($numpages+1) == 0){
						break;
					}
				
					if(isset($_GET['next'])){ 
						echo "<li><a href='?q=".$q."&page=".$i."&next=".$_GET['next']."'";
					}else{
						echo "<li><a href='?q=".$q."&page=".$i."'";
					}
				
					// Active
					if(isset($_GET['page'])){
						if ($i==$_GET['page']) 
							echo " class='active'";
					}
					echo ">".$i."</a></li> ";
				}
				
				// Set next button
				$next = $i+$rowperpage;
				if(($next*$rowperpage) > $totalCount){
					$next = ($next-$rowperpage)*$rowperpage;
				}
				
				// Next Button
				if( ($next-$rowperpage) < $totalCount ){ 
					if($shownum == ($numpages)){
						echo '<li ><a href="?q='.$q.'&page='.$i.'&next='.$i.'">Next</a></li>';
					}
				}
                 
			?>
				</ul></div>
				<!-- Numbered List (end) -->
                </td>
              </tr>
            
            <?php						
				}
				 
			}
            if($_POST && $totalCount==0){
			
			?>
              <tr>
                <td colspan="3"><font color="#FF0000"><strong>Sorry, No Record Found</strong></font></td>
              </tr>
            <?php 
			}
			?>
            </table></td>
        </tr>
      </table>
      <p>&nbsp;</p></td>
  </tr>
</table>
</form>
</body>
</html>
