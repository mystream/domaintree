<?php

class DomainTree {

	private static $left = 0;

	public static function prepare ( $urls=array() ) {

		$a	= self::stripPrefixes($urls);
		$n	= self::domainExtraction($a);

		unset($a);

		foreach($n as $d => $c){
			self::$left = 0;
			$n[$d]['children']	= self::recurseNextLevel($c['children']);
			$n[$d]['children']	= self::setNodeNumbering($n[$d]['children']);
			$n[$d]['left']		= 0;
			$n[$d]['right']		= ++self::$left;
		}

		return $n;

	}

	private static function stripPrefixes ( $a=array() ) {

		$o = $a;

		$n = 1;

		if(is_array($a) && 0 < count($a)){
			foreach($a as $i=>$p){
				$u = trim($p);
				if(      '//'==substr($p,0,2)){	$u = str_replace('//','',$u,$n);		} else 
				if('https://'==substr($p,0,8)){	$u = str_replace('https://','',$p,$n);	} else 
				if( 'http://'==substr($p,0,7)){	$u = str_replace('http://','',$p,$n);	}
				$o[$i]=$u;
			}
			unset($a);
		}

		return $o;
	}

	private static function domainExtraction ( $a=array() ) {
		$o = array();
		$n = 1;

		if(is_array($a) && 0 < count($a)){
			foreach($a as $i=>$u){
				$p = parse_url($u);
				$h = (isset($p['host']) ? $p['host'] : '');
				if($h==''){
					$path = explode('/',$u);
					$h = current($path);
				}
				if('' !== $h){
					if(!array_key_exists($h,$o)){
						$o[$h]=array('children'=>array());
					}
					$o[$h]['children'][] = str_replace($h.'/','',$u,$n);
				}
			}
		}

		return $o;
	}

	private static function recurseNextLevel ( $p=array() ) {

		$o = array();

		// Get Immediate Keys
		if(is_array($p) && 0 < count($p)){
			foreach($p as $i=>$u){
				$r = strrev($u);
				if(substr($r,0,1)=='/'){
					$r = substr($r,1);
					$u = strrev($r);
				}
				$x = explode('/',$u);
				$k=$x[0];
				if(0 < strlen(trim($k))){
					if(1 < count($x)){
						if(!array_key_exists($k, $o)){
							$o[$k]=array(
								'path'		=> $k,
								'left'		=> 0,
								'right'		=> 0,
								'children'	=> array()
							);
						}
						$t = $x;
						array_shift($t);
						$o[$k]['children'][] = implode('/',$t);
					} else {
						$o[$k]=array(
							'path'		=> $k,
							'left'		=> 0,
							'right'		=> 0,
							'children'	=> false
						);
					}
				}
			}

			unset($p);

			// Loop Paths for Each Set of Keys
			if(is_array($o)){
				foreach($o as $k=>$d){
					if(is_array($d['children'])){
						$o[$k]['children'] = self::recurseNextLevel($d['children']);
					}
				}
			}
		}

		return $o;
	}

	private static function setNodeNumbering ( $p=array() ) {

		$o = array();

		foreach($p as $k => $d){
			$o[$k]['left'] = ++self::$left;
			$r = false;
			if(is_array($d['children']) && 0 < count($d['children'])){
				$o[$k]['children'] = self::setNodeNumbering($d['children']);
				$n = end($o[$k]['children']);
				$r = $n['right']+1;
				++self::$left;
			}
			if(!$r){
				$r = ++self::$left;
			}
			$o[$k]['right'] = $r;
		}

		return $o;
	}

}

?>