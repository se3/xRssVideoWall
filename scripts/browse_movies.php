<?php
header("Content-type: text/xml");
echo"<?xml version='1.0' ?>";

//not all working extensions!
$supported_extensions = array( "iso", "mov", "3g2", "3gp", "asf", "asx", "avi", "avs", "d2v", "d3v", "dat", "divx", "dv", "dvr-ms", "dvx", "f4v", "m1v", "m2t", "m2ts", "m2v", "m4v", "mgv", "mkv", "mp4", "mpeg", "mpg", "mts", "ogm", "ogv", "rm", "rts", "swf", "ts", "wmv", "xvid" );
?>

<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<?php

$root = "/tmp/usbmounts/";
$aMovieFolder = array();
$aMovieFiles = array();

$moviepage = $_GET["page"] ;

if ("" == $moviepage)
{
   echo "<title>Movie HDD Browser</title>\n";
   unset($aMovieFolder);
   scanfolder( $root );   
   printfolder( $root );
}
else
{
   $moviepage = str_replace("%20", " ", $moviepage);
   echo "<title>..".substr($moviepage, strlen($root))."</title>\n";
   unset($aMovieFolder);
   scanfolder( $moviepage );
   printfolder( $moviepage );
   printfiles( $moviepage );
}


function scanfolder( $moviepage )
{
   global $aMovieFolder, $aMovieFiles;
      
   if ( is_dir( $moviepage ) ) {
      if ( $dh = opendir($moviepage) ) {
         while ( false !== ($file = readdir($dh)) ) {  
                  
            if( is_dir( $moviepage."/".$file ) ) {
               $aMovieFolder[] = $moviepage."/".$file;
            }
            
            if( is_file( $moviepage.$file ) ){
               $aMovieFiles[] = $moviepage.$file;
            }
         }
      }
   }
}

function printfolder( $moviepage )
{
   global $aMovieFolder, $root;
   if ( 0 < count($aMovieFolder))
   foreach( $aMovieFolder as $sFolder ) {
           
      if ( basename($sFolder) != "." && realpath($sFolder) != dirname($root) ){     
         $sFile = realpath($sFolder)."/";
         
         if ( basename($sFolder) == ".." ){
            $sCimage = '/tmp/usbmounts/sda1/scripts/defaultposterback.png';
            $sFile = dirname(dirname($sFolder))."/";
         }
         else{
            $sCimage = $sFolder.'/folder.jpg';
          
            if( !file_exists($sCimage)) {
               $sCimage = '/sbin/www/xmproot/scripts/defaultposterfolder.png';
            }
         }
         
         echo "<item>\n";      
         echo '<media:thumbnail url="'.$sCimage.'" width="80" height="120" />'."\n";
         echo "<title>..".substr($sFile, strlen($root))."</title>\n";
         
         $sFile = str_replace("\ ", "%20", $sFile);
         $sFile = str_replace(" ", "%20", $sFile);
         echo '<link>http://127.0.0.1/media/sda1/scripts/browse_movies.php?page='.$sFile."</link>\n";
         echo '<mediaDisplay name="photoView" 
            rowCount="2"
            columnCount="7"
            drawItemText="no"
            menuBorderColor="0:0:0"
            sideColorBottom="0:0:0"
            sideColorTop="0:0:0"
            itemImageXPC="10"
            itemImageYPC="5"
            itemOffsetXPC="7"
            itemOffsetYPC="0"
            backgroundColor="0:0:0"   
            sliding="yes"
            idleImageXPC="45"
            idleImageYPC="42"
            idleImageWidthPC="7"
            idleImageHeightPC="16"/>';

         echo "\n</item>\n\n";
      }
   }
}

function printfiles( $moviepage )
{
   global $aMovieFiles, $supported_extensions;
   if ( 0 < count($aMovieFiles))
   foreach( $aMovieFiles as $sFile ) {
      
      $aFileinfo = stat($sFile);
      
      $info = pathinfo($sFile);
      if ( 0 < array_search ( $info['extension'], $supported_extensions ) )
      {
         $file_name =  basename($sFile,'.'.$info['extension']);
       
         $sCimage = $moviepage."/".$file_name.'.jpg';
       
         if( !file_exists($sCimage)) {
            $sCimage = '/sbin/www/xmproot/scripts/defaultpostermovies.png';
         }
               
         echo "<item>\n";             
         echo "<title>$file_name - ".intval( ($aFileinfo['size'])/1000000 )." MB </title>\n" ;
         echo '<media:thumbnail url="'.$sCimage.'" width="80" height="120" />'."\n";
         echo '<link>file://'.$sFile.'</link>';
         echo "<enclosure type=\"video/mp4\" url=\"$sFile\"/>\n";
         
         echo "\n</item>\n\n";
      }
   }
}





?>
</channel>
</rss>