<? // 5APR18 A
$time_start = microtime(true);

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// ^ good for hitting the script directly but will foul the js for lack of jasons

include '../../../mrn_db_info.php';
// ^ in order to keep db login info away from apache i tuck it back here

//	GET THE DATE AND TIME -----------------------------------------------------------
$tz = 'America/Chicago';
$timestamp = time();
$dt = new DateTime("now", new DateTimeZone($tz));
$dt->setTimestamp($timestamp); //adjust the object to correct timestamp

//	MAIN DB CONNECTOR ---------------------------------------------------------------
try {
  $dbhandle = new PDO("mysql:host=$dbhost;dbname=$dbdb", $dbuser, $dbpass);
  $dbhandle -> setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $e) {
	echo $e->getMessage();
	echo "<br/>A database anomaly has been logged.<br/>";
	file_put_contents('error_log', $e->getMessage(), FILE_APPEND);
}

//	CARD RENDERER -------------------------------------------------------------------
function renderCard($cardName,$dbhandle){
	// GET CARD RECORD
	$sthandle = $dbhandle->prepare("SELECT * FROM cards WHERE name = '$cardName'");
	$sthandle->setFetchMode(PDO::FETCH_ASSOC); $sthandle->execute(); $data = array();
	$row = $sthandle->fetch();
	// SET CARD VARS 
	$cardID = $row['id'];
	$cardName = $row['name'];
	$cardTitle = $row['title'];
	$cardMaxWidth = $row['width'];
	$cardBody = $row['body'];
	$cardMayCollapse = $row['mayCollapse'];
	$cardDefaultState = $row['defaultState'];
	$trimColor = $row['trimColor'];

	// CARD
	echo "<div id=\"".$cardName."\" class=\"card border-secondary m-1\" style=\"max-width: $cardMaxWidth%;\">\n";

	// TITLE
	if($cardMayCollapse == 1){
		echo "<a data-toggle=\"collapse\" href=\"#".$cardName."CardBody\">\n"; // add link to collapse
		echo "<div id=\"$cardName\" class=\"card-header\" style=\"background-color:#$trimColor;\">".$cardTitle."</div></a>\n"; }		// adds /a
	else{
		echo "<div class=\"card-header\">".$cardTitle."</div>\n";			// not collapsible
	}

	//BODY
	if($cardMayCollapse == 1){
		if($cardDefaultState == 1){
			echo "<div class=\"card-body collapse in show\" id=\"".$cardName."CardBody\">\n";	// collapsible and shown
		}
		else{
			echo "<div class=\"card-body collapse\" id=\"".$cardName."CardBody\">\n";		// collapsible and not shown
		}
	}
	else{
		echo "<div class=\"card-body\" id=\"card".$cardID."body\">\n";					// not collapsible
	}
	//echo "<h4 class=\"card-title\">".$cardTitle."</h4>\n";
	
	if($cardBody){echo $cardBody;}
	
	// GET RESOURCES FOR THIS CARD
	$sthandle2 = $dbhandle->prepare("SELECT * FROM resources WHERE card = $cardID ORDER BY title ASC");
	$sthandle2->setFetchMode(PDO::FETCH_ASSOC); $sthandle2->execute(); $data2 = array();
	while($row2 = $sthandle2->fetch()) {
		$data2[] = $row2;
		// SET RESOURCES VARS
		$resourceID = $row2['id'];
		$url = $row2['url'];
		$urlBig = $row2['urlBig'];
		$resourceType = $row2['type'];
		$resourceTitle = $row2['title'];
		// GENERATE IMAGE THUMBNAILS AND LINKS
		echo "<a href=\"#\" data-featherlight-allowfullscreen=\"true\" id=\"zoom$resourceID\" data-featherlight=\"
				<img src='$urlBig' class='$resourceType img responsive img-responsive img-fluid lightboxed' alt='$resourceTitle' data-toggle='tooltip' data-placement='top' title='$resourceTitle'/>
			\" class=\"lightboxLink\">";
			echo "<img src=\"".$url."\" id=\"$resourceID\" class=\"$resourceType img responsive img-responsive img-fluid inGallery\" alt=\"$resourceTitle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"$resourceTitle\">";
		echo "</a>";
	}
	echo "\n</div></div>\n"; // closes the card
}

function renderAllCards($dbhandle) {
	// GET CARDS RECORD
	$sthandle = $dbhandle->prepare("SELECT * FROM cards ORDER BY displayOrder ASC");
	$sthandle->setFetchMode(PDO::FETCH_ASSOC); $sthandle->execute(); $data = array();
	$row = $sthandle->fetch();
	while($row = $sthandle->fetch()) {
		$data[] = $row;
		// SET RESOURCES VARS
		$cardName = $row['name'];
		renderCard($cardName, $dbhandle);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Real-time information about Madison, Wisconsin">
    <meta name="keywords" content="Madison Wisconsin, dane, webcams, cameras, live, news, traffic, weather, MSN, KMSN, airport, WI, city, beltline, conditions, traffic, road, civic data">
    <meta name="author" content="originaldougal.com">
    <meta name="mobile-web-app-capable" content="yes">
	<meta property="og:title" content="MadisonRightNow">
	<meta property="og:description" content="Real-time information about Madison, Wisconsin">
	<meta property="og:url" content="http://madisonrightnow.com">
	<meta name="twitter:title" content="MadisonRightNow">
	<meta name="twitter:description" content="Real-time information about Madison, Wisconsin">
    <title>MRN β [<?=$dt->format('g:i')?>]</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/featherlight.min.css" rel="stylesheet">
	<link href="css/theme.css" rel="stylesheet">
	<link href="css/mrn.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Rock+Salt' rel='stylesheet' type='text/css'>
	<link rel="icon" sizes="192x192" href="app-icon.png">
	<link rel="manifest" href="manifest.webmanifest">
    <!--[if lt IE 9]><script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
	<a id="thetop"></a>
    <div id="wrapper">
        <div class="container-fluid" id="main">
            <div class="row">
				<div class="card border-primary mb-1 collapse" style="width: 100%; ">
					<div class="card-body" id="logoHeader">
						<a onclick="window.location.reload()" style="text-decoration: none;">
							<h2 class="shadowed-title">
								<strong>Madison</strong>
								<span class="jazzy">Right</span>
								Now&nbsp;
								<span
								id="statusSpan" 
								data-toggle="tooltip" 
								title="Stream status" 
								data-placement="left"
								style="float:right; margin-top:8px;"
								>
								</span>
							</h2>
						</a>
					<p style="font-weight:normal;">on <span id="clockSpan"></span> CST</p>
					</div>
				</div>
			</div>
			<div class="row flex">
				<? renderAllCards($dbhandle); ?>
			</div><!-- ends the first row -->
			<div class="row">
				<div class="card border-secondary mb-1" style="width: 100%;">
				  <div class="card-body">
					<p class="card-text"><small>madison right now dot com</small></p>
				  </div>
				</div>
			</div><!-- ends second row -->
        </div><!-- cl container -->
    </div><!-- cl wrapper -->
<div id="snackbar" class="featherlight-inner" style="z-index:100;font-size:1.5em;"></div>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<!-- survey
<script async="" defer="" src="//survey.g.doubleclick.net/async_survey?site=yffdeoo36pilsbbbcvtihbaf74"></script>
-->
<script src="js/featherlight.min.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAUJOBriuBRTv_ZcDomoFkwG5SG07Ll_7U&callback=initMap"></script>
<script src="js/mapStyle.js"></script>
<script defer src="js/fa.js"></script>
<script src="js/mrn.js"></script>
<script async src="js/ga.js"></script>
</body>
</html>
<?
$dbhandle = null;
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<!-- generated in '.$execution_time.' sec-->';
?>