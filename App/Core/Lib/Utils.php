<?php
namespace Molotov\Core\Lib;

class Utils{
	public function is_json($str){
	    try{
			if(is_array($str)) return false;
		        $jObject = json_decode($str,1);
	    }catch(Exception $e){
	        return false;
	    }
	    if(is_object($jObject) || is_array($jObject)){
			return true;
	    }else{
			return false;
	    }
	}

}