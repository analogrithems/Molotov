<?php

namespace Arez\Core\Lib;

class Query
{

	protected $tables = array();
	protected $selects = array();
	protected $orders = array();
	protected $wheres = array();
	protected $query;
	protected $query_total;
	protected $offset;
	protected $paginate;
	protected $count;
	protected $filter;

	public function __construct($query = null)
	{
		$this->query = $query;
	}

	public function paginate($offset = null, $count = null)
	{	
		$this->paginate = true;
		
		return $this->limit($offset, $count);
	}

	public function limit($offset, $count)
	{
		$this->offset = $offset;
		$this->count = $count;
		return $this;
	}
	
    function prepare( $query, $args ) {
        if ( is_null( $query ) )
                return;

        // This is not meant to be foolproof -- but it will catch obviously incorrect usage.
        if ( strpos( $query, '%' ) === false ) {
                return $query;//no replacement, just bail
        }

        $args = func_get_args();
        array_shift( $args );
        // If args were passed as an array (as in vsprintf), move them up
        if ( isset( $args[0] ) && is_array($args[0]) )
                $args = $args[0];
        $query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
        $query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
        $query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
        $query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
        array_walk( $args, array( $this, 'escape_by_ref' ) );
        return @vsprintf( $query, $args );
    }

    private function escape_by_ref( &$string ) {
        if ( ! is_float( $string ) ) $string = $this->mres( $string );
    }

    private function mres($value)
	{
	    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
	    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
	
	    return str_replace($search, $replace, $value);
	}

	public function filter($filter, $alias = null)
	{
		$where = array();
		foreach($filter as $k => $v) {
			if(isset($alias)) {
				$where[] = "{$alias}.{$k}='{$v}'";
			} else {
				$where[] = "{$k}='{$v}'";
			}
 		}
 		$where = implode(' AND ', $where);
		$this->where($where);
		return $this;
	}

	public function live($term, $columns, $alias = null)
	{
		$where = array();
		foreach($columns as $column) {
			if(isset($alias)) {
				$where[] = "{$alias}.{$column} LIKE '%{$term}%'";
			} else {
				$where[] = "{$column} LIKE '%{$term}%'";
			}
 		}
 		$where = implode(' OR ', $where);
		$this->where($where);
		return $this;
	}

	public function where($where, $args = array())
	{
		$args = func_get_args();
		array_shift( $args );
		$this->wheres[] = array('statement' => $where, 'args' => $args);
		return $this; 
	}

	public function get()
	{
		if(!empty($this->tables)) $this->build();
		return $this->query;
	}

	public function getTotal()
	{
		return $this->query_total;
	}

	public function insert($table, $data)
	{
		foreach($data as $k => $v) {
			$v = addslashes($v);
			(!isset($cols)) ? $cols = "(" : $cols .= ", ";
			(!isset($vals)) ? $vals = "(" : $vals .= ", ";
			$cols .= "{$k}";
			$vals .= "'{$v}'";
		}
		$cols .= ")";
		$vals .= ")";
		$this->query = "
			INSERT INTO {$table}
			{$cols} VALUES {$vals}
			";

	return $this;
	}

	public function update($table, $data, $cond = array())
	{
		if(!empty($cond)) {
			foreach($cond as $k) {
				(!isset($where)) ? $where = "WHERE" : $where .= " AND";

				$where .= " {$k}";
			}
		} else {
			$where = "WHERE id='{$data['id']}'";
		}
		foreach($data as $k => $v) {
			(!isset($set)) ? $set = "" : $set .= ",";
			$set .= $this->prepare(" {$k}=%s", $v);
		}
		$this->query = "
			UPDATE {$table}
			SET {$set}
			{$where}
		";
		return $this;
	}

	public function delete($table, $cond)
	{
		if(!is_array($cond)){
			$cond = array($cond);
		}
		if(!empty($cond)) {
			foreach($cond as $k) {
				(!isset($where)) ? $where = "WHERE" : $where .= " AND";
				$where .= " {$k}";
			}
		} else {
			$where = "WHERE id='{$data['id']}'";
		}
		$this->query = "
			DELETE FROM {$table}
			{$where}
			";
		return $this;
	}

	/**
	 * Pass in array or string.
	 * select('MAX(version) AS version')
	 * select(array('text', 'DISTINCT(id) AS did'))
	 */
	public function select($selection = '*')
	{
		if(is_array($selection)) {
			foreach($selection as $selected) {
				$this->selects[] = $selected;
			}
		} else {
			$this->selects[] = $selection;
		}
		return $this;
	}

	public function from($from)
	{
		if(is_array($from)) {
			foreach($from as $table) {
				$this->tables[] = $table;
			}
		} else {
			$this->tables[] = $from;
		}
		return $this;
	}

	public function groupBy($column)
	{
		if(is_array($column)) {
			foreach($column as $c) {
				$this->groupings[] = $c;
			}
		} else {
			$this->groupings[] = $column;
		}
		return $this;
	}

	public function orderBy($column, $sort = 'DESC')
	{
		$this->orders[] = array('column' => $column, 'sort' => $sort);
		return $this;
	}

	public function having($having)
	{
		$this->having = $having;
		return $this;
	}

	public function addJoin($join, $on, $type = ''){
		$this->joins[] = array(
			'type' => $type,
			'join' => $join,
			'on' => $on
		);
		return $this;
	}

	public function join($join, $on){
		return $this->addJoin($join, $on);
	}

	public function innerJoin($join, $on){
		return $this->addJoin($join, $on, 'INNER');
	}

	public function leftJoin($join, $on){
		return $this->addJoin($join, $on, 'LEFT');
	}

	public function rightJoin($join, $on){
		return $this->addJoin($join, $on, 'RIGHT');
	}

	public function addSubQuery($sub, $key){
		if(is_array($sub)) {
			foreach($sub as $k => $v) {
				$this->sub_queries[$k] = $v;
			}
		} else {
			$this->sub_queries[$key] = $sub;
		}
		return $this;
	}

	public function build(){
		$query = "SELECT";
		if(!empty($this->selects)) {
			foreach($this->selects as $s) {
				$query .= " {$s},";
			}
			$query = substr($query, 0, -1);
		}

		$query .= " FROM";
		if(!empty($this->tables)) {
			foreach($this->tables as $t) {
				$query .= " {$t},";
			}
			$query = substr($query, 0, -1);
		}

		//joins
		if(!empty($this->joins)) {
			foreach($this->joins as $j) {
				if(is_array($j['on'])) {
					$on = join($j['on']," AND ");
				} else {
					$on = $j['on'];
				}

				$query .= " {$j['type']} JOIN {$j['join']} ON {$on}";
			}
		}

		//where
		if(!empty($this->wheres)) {
			$wheres = '';
			$query .= " WHERE 1";
			foreach($this->wheres as $where) {
				if(!isset($where['statement']) || empty($where['statement'])) continue;
				if(isset($where['args']) && !empty($where['args'])){
					$wheres .= ' AND ( '.$this->prepare($where['statement'],$where['args']).' ) ';
				}else{
					$wheres .= ' AND ( '.$where['statement'].' ) ';
				}
	 		}

	 		$query .= " {$wheres}";
 		}

		//groupby
		if(!empty($this->groupings)) {
			$query .= " GROUP BY";
			foreach($this->groupings as $g) {
				$query .= " {$g},";
			}
			$query = substr($query, 0, -1);
		}

		//having
		if(isset($this->having)) {
			$query .= " HAVING " . $this->having;
		}

		//orderby
		if(!empty($this->orders)){
			$o = array();
			foreach($this->orders as $order) {
				$o[] = "{$order['column']} {$order['sort']}";
			}
			$query .= " ORDER BY ".implode( ', ', $o );
		}

		//sub queries
		if(!empty($this->sub_queries)) {
			foreach($this->sub_queries as $k => $v) {
				$search = ":{$k}";
				$replacement = "(" . $v .")";
				$query = str_replace($search, $replacement, $query);
			}
		}

		if(isset($this->offset)) {
			if($this->paginate) {
				$this->query_total = $query;
			}
			$query .= " LIMIT {$this->offset}";
			if(isset($this->count)) {
				$query .= ", {$this->count}"; 
			}
		}

		$this->query = $query;
		return $this;
		//$this->totalQuery = $totalQuery;
	}

}