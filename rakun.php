<?php

class Backend {

   var $db;
   var $connArray;
 
   function __construct($input) {   
        // TODO mapping yap
        /*
	   $this->db =  new mysqli($input["hostname"], $input["username"], $input["password"], $input["database"]);
	   if ($this->db->connect_error) {
   		 die("Connection failed: " . $this->db->connect_error);
		}
		$this->db->set_charset("utf8mb4");
        mysqli_query($this->db,'SET SESSION sql_mode = \'ANSI_QUOTES\';');
        */
    }

 	function get($key, $id){
        $url="https://www.googleapis.com/youtube/v3/videos?key=".$key."&part=snippet&id=".$id;
		$data = file_get_contents($url);
        $json = json_decode($data);
        // var_dump($json->items[0]->snippet->thumbnails);
       // $json->items[0]->snippet->channelTitle === "Fanatik Rakun";
		return $json;
    }
    
    function composeEmbedHTML($width, $height, $vidID, $thumbFileName,$title,$description){
        
     
                    

        $html = <<< EOT
            <!doctype html>
            <html class="no-js" lang="" xmlns="http://www.w3.org/1999/xhtml"
                        xmlns:fb="http://ogp.me/ns/fb#">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="x-ua-compatible" content="ie=edge">
                    <title>Fanatik Rakun</title>
                    <meta name="description" content="">
                    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <!-- Place favicon.ico in the root directory -->
                    <!-- These are FB related -->
                    <meta property="og:url"                content="http://www.smubcizgiroman.com/fanatikrakun/rakun.php/add/$vidID" />
                    <meta property="og:type"               content="article" />
                    <meta property="og:title"              content="$title" />
                    <meta property="og:description"        content="$description" />
                    <meta property="og:image"              content="http://www.smubcizgiroman.com/fanatikrakun/thumbs/$thumbFileName.jpg" />
                    <meta property="og:image:width" content="$width" />
                    <meta property="og:image:height" content="$height" />
                    <style>
                    .video-container {
                        position:relative;
                        padding-bottom:56.25%;
                        padding-top:30px;
                        height:0;
                        overflow:hidden;
                    }
                    .video-container iframe, .video-container object, .video-container embed {
                        position:absolute;
                        top:0;
                        left:0;
                        width:90%;
                        height:90%;
                    }
                    </style>
                </head>
                <body>
                    <!--[if lte IE 9]>
                        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
                    <![endif]-->
                    <!-- Add your site or application content here -->
                    <div class="video-container">
                    <iframe src="https://www.youtube.com/embed/$vidID?rel=0" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>
                        </div>
                </body>
            </html>
EOT;
        return $html;
    }

	function executeCommand(){
		header('Access-Control-Allow-Credentials: true');			 
		// header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
	
		$url = parse_url($_SERVER['REQUEST_URI']);
        $chunks = explode('/', $url["path"]);
        $res = '{}';
        $contentType = '';

		if($chunks[3]==='add'){
            // Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended. 
            $handle = fopen("ytdb.txt", "r");
            if ($handle) {
                // this should be zero because it is incremented later
                $seperate=[0,''];
                while (($line = fgets($handle)) !== false) {
                    // process the line. 0th element is index. 1st element is key
                    $seperate = explode(';', $line);
                    // remove whitespace characters that came from txt file
                    $seperate[3] = trim($seperate[3]);
                    // if key in db matches with key in url
                    if($seperate[1]===$chunks[4]){
                        $imagepath="./thumbs/".$seperate[0].".jpg";
                        echo $this->composeEmbedHTML( $seperate[2],
                     $seperate[3],
                     $seperate[1],
                      $seperate[0],
                     $seperate[4],
                      $seperate[5]);
                        return;
                    }
                }
                fclose($handle);
                // If you got here it means you couldn't match a key, so fetch data from youtube and dump json to browser
                $output = $this->get(($key,$chunks[4]);
                // Increment index to either init manifest at 1 or to add new entry
                $seperate[0]++;
                // var_dump($seperate[0]);
                // Check security use this to check author   https://noembed.com/embed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D+PY7aHbAhiVY  --"author_name":"- NVR",
                if($output->items[0]->snippet->channelTitle === "Fanatik Rakun"){
                    //$res = json_encode($output);
                     $imagepath="./thumbs/".$seperate[0].".jpg";
                     // var_dump($imagepath);
                    // if file exists return error
                    if(file_exists($imagepath)){
                        $contentType = 'application/json';
                        $res= "{\"error\":\"non indexed file!\"}";
                        echo $res;
                        return;
                    }
                    // otherwise fetch and store thumbnail, and add to manifest
                    // file_put_contents($imagepath,file_get_contents($output->items[0]->snippet->thumbnails->maxres->url));
                    $raw = imagecreatefromjpeg($output->items[0]->snippet->thumbnails->maxres->url);
                    $scaled = imagescale($raw,1200);
                    $cropped = imagecrop($scaled,['x' => 0, 'y' => 14, 'width' => 1200, 'height' => 627]);
                    // Load the stamp and the photo to apply the watermark to
                    $stamp = imagecreatefrompng('./play_button.png');
                    // Set the margins for the stamp and get the height/width of the stamp image
                    $marge_right = 0;
                    $marge_bottom = 0;
                    $sx = imagesx($stamp);
                    $sy = imagesy($stamp);
                    // Copy the stamp image onto our photo using the margin offsets and the photo 
                    // width to calculate positioning of the stamp. 
                    imagecopy($cropped, $stamp, imagesx($cropped) - $sx - $marge_right, imagesy($cropped) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
                    imagejpeg($cropped,$imagepath);
                    $title = $output->items[0]->snippet->title;
                    $description = substr($output->items[0]->snippet->description, 0, strpos($output->items[0]->snippet->description, PHP_EOL, 0));
                    // index value ; vidid ; width ; height ; title ; description
                    $txt = $seperate[0].";".$chunks[4].";1200;627;".$title.";".$description;
                    // append eol to string, append string to file, mutex lock
                    file_put_contents('ytdb.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
                    echo $this->composeEmbedHTML( 1200,
                     627,
                     $chunks[4],
                     $seperate[0],
                     $title,
                     $description);
                }
                
            } else {
                // error opening the file.
            } 

            
            
		} 
		else{
            $res = <<<'EOT'
            <!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>reöreö</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        
        <!-- Place favicon.ico in the root directory -->

        
    </head>
    <body>
        <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <p>kötü url</p>
    </body>
</html>
EOT;
 $contentType = 'text/html';
        }
       
		if (isset($_SERVER['REQUEST_METHOD'])) {
			header('Content-Type: '.$contentType.'; charset=utf-8');
		}
				
		
		
			echo $res;
		
	}
}

$conn = new Backend(parse_ini_file ("dbconfig.ini"));
$conn->executeCommand();






?>

