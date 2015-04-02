<?php

namespace Arez\Core\Lib;

use Arez\Core\Lib\Query;

class Database extends \Phalcon\Db\Adapter\Pdo\Mysql
{
	public function createQuery($query = null)
	{
		return new Query($query);
	}

	public function fetchAll($sqlQuery, $fetchMode = NULL, $placeholders = NULL)
	{
		$data = parent::fetchAll($sqlQuery, $fetchMode, $placeholders);
		if( is_array( $data )) {
			foreach( $data as $key=>$d ){
				foreach( $d as $k=>$v ) {
					$d[$k] = html_entity_decode( htmlspecialchars_decode( trim($v) ), ENT_NOQUOTES,'UTF-8');
				}
				$data[$key]=$d;
			}
		}
		return $data;
	}

	public function fetchOne($sqlQuery, $fetchMode = NULL, $placeholders = NULL)
	{
		$data = parent::fetchOne($sqlQuery, $fetchMode, $placeholders);
		if( is_array( $data )) {
			foreach( $data as $key=>$d ){
				$data[$key] = html_entity_decode( htmlspecialchars_decode( trim($d) ), ENT_NOQUOTES,'UTF-8');
			}
		}
		return $data;
	}
}