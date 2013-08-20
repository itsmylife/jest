<?php

namespace J;

class Helper {
	public static function camelize($string, $ucFirst=false)	{
		$string = str_replace('-','_',strtolower($string));		
		$stringParts = explode('_',$string);
		$string = '';
		foreach ($stringParts as $stringPart) $string .= ucfirst($stringPart);
		if (!$ucFirst) $string = lcfirst($string);
		return $string;
	}
}