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

function checkForMoviefolder( $folder )
{
   $moviefolder = "";
   if ( $dh = opendir($folder) ) {
      while ( false !== ($file = readdir($dh)) ) {
         if( "." != $file && ".." != $file && is_dir( $folder."/".$file ) ) {
            break;
         }else
         if( is_file( $folder."/".$file ) ){
            
            if ( substr($file, 0, strlen($file)-4 ) == basename( $folder ) ){
               $moviefolder = $folder."/".$file;
               break;
            }
            
         }
      }
   }
   return $moviefolder;
}
         
function scanfolder( $moviepage, $recursive = false )
{
   global $aMovieFolder, $aMovieFiles;
   $retval = false;

   if ( is_dir( $moviepage ) ) {
      if ( $dh = opendir($moviepage) ) {
         while ( false !== ($file = readdir($dh)) ) {
            if( is_dir( $moviepage.$file ) ) {
               $moviefolderfile = checkForMoviefolder( $moviepage.$file ) ;
               if ( "" != $moviefolderfile ){
                  $aMovieFiles[] = $moviefolderfile;
               }
               else {
                  $aMovieFolder[] = $moviepage.$file;
               }
            }else if( is_file( $moviepage.$file ) ){
               $aMovieFiles[] = $moviepage.$file;
            }
         }
      }
   }
   return $retval;
}

function printfolder( $moviepage )
{
   global $aMovieFolder, $root;
   
   if ( 0 < count($aMovieFolder))
   foreach( $aMovieFolder as $sFolder ) {
           
      if ( basename($sFolder) != "." && basename($sFolder) != ".." && realpath($sFolder) != dirname($root) ){     
         $sFile = realpath($sFolder)."/";         
         $sCimage = $sFolder.'/folder.jpg';
       
         if( !file_exists($sCimage)) {
            $sCimage = '/tmp/usbmounts/sda1/scripts/image/defaultposterfolder.png';
         }
         
         $output =  "<item>\n";      
         $output.=  '<media:thumbnail url="'.$sCimage.'" width="80" height="120" />'."\n";
         $output.=  "<title>..".substr($sFile, strlen($root))."</title>\n";
         
         $sFile = str_replace("\ ", "%20", $sFile);
         $sFile = str_replace(" ", "%20", $sFile);
         
         $output.=  '<link>http://127.0.0.1/media/sda1/scripts/browse_movies.php?page='.$sFile."</link>\n";
         $output.=  '<mediaDisplay name="photoView" 
            rowCount="2"
            columnCount="7"
            drawItemText="no"
            menuBorderColor="0:0:0"
            sideColorBottom="0:0:0"
            sideColorTop="0:0:0"
            sideColorLeft="0:0:0"
            sideColorRight="0:0:0" 
            itemImageXPC="10"
            itemImageYPC="5"
            itemOffsetXPC="7"
            itemOffsetYPC="0"
            backgroundColor="0:0:0"   
            itemBackgroundColor="0:0:0"
            sliding="yes"
            idleImageXPC="45"
            idleImageYPC="42"
            idleImageWidthPC="7"
            idleImageHeightPC="16"
            >
             <idleImage> image/POPUP_LOADING_01.jpg </idleImage>
             <idleImage> image/POPUP_LOADING_02.jpg </idleImage>
             <idleImage> image/POPUP_LOADING_03.jpg </idleImage>
         
             <idleImage> image/POPUP_LOADING_04.jpg </idleImage>
             <idleImage> image/POPUP_LOADING_05.jpg </idleImage>
             <idleImage> image/POPUP_LOADING_06.jpg </idleImage>
            </mediaDisplay>';
    
         $output.= "\n</item>\n\n";
         echo $output; 
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
            $sCimage = dirname($sFile)."/folder.jpg";
            if( !file_exists($sCimage))
              $sCimage = '/tmp/usbmounts/sda1/scripts/image/defaultpostermovies.png';
         }
         
         $output = "<item>\n";             
         $output.= "<title>$file_name - ".intval( ($aFileinfo['size'])/1000000 )." MB </title>\n" ;
         $output.= '<media:thumbnail url="'.$sCimage.'" width="80" height="120" />'."\n";
         $output.= '<link>file://'.$sFile.'</link>';
         $output.= "<enclosure type=\"video/mp4\" url=\"$sFile\"/>\n";
         
         $output.= "\n</item>\n\n";
         echo $output;
     }
   }
}


?>
</channel>
</rss>