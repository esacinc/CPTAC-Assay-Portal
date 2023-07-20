<?php
namespace swpg\models;

use finfo;

class utility{

    public function __construct(){
    	//placeholder in case we want to put something here later
    }

	public static function get_file_paths($dir,$recursive=false){
		$paths=array();
		if(is_dir($dir)){
			if ($handle = opendir($dir)) {
			    while (false !== ($entry = readdir($handle))) {
			    	$file_type = filetype($dir . "/" . $entry);
			        if ($entry != "." && $entry != "..") {
			        	if($file_type == "file"){
			        		$paths[] = $dir . "/" . $entry;
			        	}elseif($file_type == "dir"){
			        		if($recursive){
				        		$paths = array_merge($paths, self::get_file_paths($dir . "/" . $entry,true));
				        	}
			        	}
			        }
			    }
			    closedir($handle);
			}
		}
		return $paths;
	}

    public static function get_file_names($dir,$recursive=false){
        $names=array();
        if(is_dir($dir)){
            if ($handle = opendir($dir)) {
                while (false !== ($entry = readdir($handle))) {
                    $file_type = filetype($dir . "/" . $entry);
                    if ($entry != "." && $entry != "..") {
                        if($file_type == "file") {
                            $entry = preg_replace('/\\.[^.\\s]{3}$/', '', $entry);
                            $names[] = $entry;
                        } elseif($file_type == "dir") {
                            if($recursive){
                                $names = array_merge($names, self::get_file_names($dir . "/" . $entry,true));
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
        return $names;
    }
	
	public static function gump_parse_errors($gump_failed_validation_array,$array_prepend=false){
		$error_array = array();
		foreach($gump_failed_validation_array as $single_error){
			if($single_error["rule"] == "validate_required"){
				$error_array[$single_error["field"]] = ucwords(str_replace("_", " ",$single_error["field"])) . " is missing.";
			}else{
				if($single_error["value"]){
					$error_array[$single_error["field"]] = ucwords(str_replace("_", " ",$single_error["field"])) . " is invalid.";
				}
			}
		}
		if(!empty($array_prepend) && !empty($error_array)){
			return array($array_prepend => $error_array);
		}else{
			return $error_array;
		}
	}
	
	public static function array_flatten($passed_array, &$output_array = false, $array_key=false){
	    if(!is_array($output_array)){
	        $output_array = array();
	    }
	    foreach($passed_array as $passed_key => $passed_array_value){
	    	if(is_array($passed_array_value)){
	            self::array_flatten($passed_array_value, $output_array,$array_key);
	        }else{
	        	if(empty($array_key) || $array_key == $passed_key){
	            	array_push($output_array, $passed_array_value);
				}
	        }
	    }
	    return $output_array;
	}
	
	//taken from the php.net filesize page in the comments
	public static function _format_bytes($a_bytes){
	    if ($a_bytes < 1024) {
	        return $a_bytes .' B';
	    } elseif ($a_bytes < 1048576) {
	        return round($a_bytes / 1024, 2) .' KB';
	    } elseif ($a_bytes < 1073741824) {
	        return round($a_bytes / 1048576, 2) . ' MB';
	    } elseif ($a_bytes < 1099511627776) {
	        return round($a_bytes / 1073741824, 2) . ' GB';
	    } elseif ($a_bytes < 1125899906842624) {
	        return round($a_bytes / 1099511627776, 2) .' TB';
	    } elseif ($a_bytes < 1152921504606846976) {
	        return round($a_bytes / 1125899906842624, 2) .' PB';
	    } elseif ($a_bytes < 1180591620717411303424) {
	        return round($a_bytes / 1152921504606846976, 2) .' EB';
	    } elseif ($a_bytes < 1208925819614629174706176) {
	        return round($a_bytes / 1180591620717411303424, 2) .' ZB';
	    } else {
	        return round($a_bytes / 1208925819614629174706176, 2) .' YB';
	    }
	}
	
	public static function get_mime_type($file){
		$arrayZips = array("application/zip", "application/x-zip", "application/x-zip-compressed");
		$mime_map = array(
			"docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
			,"dotx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.template"
			,"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation"
			,"ppt" => "application/vnd.openxmlformats-officedocument.presentationml.presentation"
			,"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
		);
		
		//get the extension
		$ext = "";
		$ext_array = explode(".",$file);
		if(count($ext_array) > 1){
			$ext = $ext_array[count($ext_array) - 1];
		}
		
		$finfo = new finfo(FILEINFO_MIME);
		$type = $finfo->file($file);
		$type = substr($type, 0, strpos($type, ';'));
		
		if (in_array($type, $arrayZips) && array_key_exists($ext, $mime_map)){
		   return $mime_map[$ext];
		}else{
			return $type;
		}
	}
	
	public static function subvalue_sort($passed_array, $subkey, $sort="DESC", $start=false, $stop=false){
        $first_temp_array = array();	
		$second_temp_array = array();
        foreach($passed_array as $k => $v){
            $first_temp_array[$k] = strip_tags(strtolower($v[$subkey]));
        }
        if($sort == "DESC"){
            asort($first_temp_array);
        }elseif($sort == "ASC"){
            arsort($first_temp_array);
        }
		
        foreach($first_temp_array as $key => $val){
        	if(is_numeric($key)){
        		$second_temp_array[] = $passed_array[$key];
        	}else{
        		$second_temp_array[$key] = $passed_array[$key];
        	}
        }

        if($start || $stop){
            $final_array = array_slice($second_temp_array, $start, $stop);
        }else{
            $final_array = $second_temp_array;
        }

        return $final_array;
    }
}