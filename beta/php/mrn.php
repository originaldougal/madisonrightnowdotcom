<? // 15APR18

$userAgent = "Mozilla/5.0 (compatible; MRN/2.0; +http://www.madisonrightnow.com)";

// Extracts content from scraped pages
function scrapeBetween($data, $start, $end){
	$data = stristr($data, $start); 			// Stripping all data from before $start
	$data = substr($data, strlen($start));  	// Stripping $start
	$stop = stripos($data, $end);   			// Getting the position of the $end of the data to scrape
	$data = substr($data, 0, $stop);    		// Stripping all data from after and including the $end of the data to scrape
	return $data;   							// Returning the scraped data from the function
}

// Jan 27 2015 log file writer
function writeToLogFile($msg) {
     $today = date("Y_m_d"); 
     $logfile = $today."_log.txt"; 
     $dir = 'data/logs';
     $saveLocation=$dir . '/' . $logfile;
     if  (!$handle = @fopen($saveLocation, "a")) {
          exit;
     }
     else {
          if (@fwrite($handle,"$msg\r\n") === FALSE) {
               exit;
          }
   
          @fclose($handle);
     }
}

// Feb 2 2015
function debugLog($msg) {
	$logfile = __DIR__."/../data/logs/debugLog.txt";
	file_put_contents($logfile, date("Y-m-d h:i:s A")." : ".$msg."\n", FILE_APPEND);
}

function curl($url) {
	global $userAgent;
    // Assigning cURL options to an array
    $options = Array(
        CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
        CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
        CURLOPT_AUTOREFERER => TRUE, 	// Automatically set the referer where following 'location' HTTP headers
        CURLOPT_CONNECTTIMEOUT => 5,   	// Setting the amount of time (in seconds) before the request times out
        CURLOPT_TIMEOUT => 5,  			// Setting the maximum amount of time for cURL to execute queries
        CURLOPT_MAXREDIRS => 10, 		// Setting the maximum number of redirections to follow
        CURLOPT_USERAGENT => $userAgent,// Setting the useragent
        CURLOPT_URL => $url, 			// Setting cURL's URL option with the $url variable passed into the function
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_BINARYTRANSFER => 1
    );
	
    $ch = curl_init();  				// Initialising cURL 
    curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
    $data = curl_exec($ch); 			// Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    				// Closing cURL 
    return $data;   					// Returning the data from the function
}