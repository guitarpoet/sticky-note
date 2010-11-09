<?php 
	defined( 'uservice' ) or die( 'You should not see this.' );

	class mysqli_extended extends mysqli {
		protected $selfReference;

		public function __construct($dbHost, $dbUsername, $dbPassword, $dbDatabase) {
			parent::__construct($dbHost, $dbUsername, $dbPassword, $dbDatabase);
		}

		public function prepare($query) {
			$stmt = new stmt_extended($this, $query);
			return $stmt;
		}
	}

	class stmt_extended extends mysqli_stmt {
		protected $varsBound = false;
		protected $results;

		public function __construct($link, $query) {
			parent::__construct($link, $query);
		}

		public function get_type($data) {
			switch(gettype($data)) {
			case 'boolean':
			case 'integer':
				return 'i';
			case 'double':
				return 'd';
			default:
				return 's';
			}
		}

		public function ref_values($arr) {
		    if (strnatcmp(phpversion(),'5.3') >= 0) { //Reference is required for PHP 5.3+
			$refs = array();
			foreach($arr as $key => $value)
			    $refs[$key] = &$arr[$key];
			 return $refs;
		     }
		     return $arr;
		}

		public function bind_args($args) {
			$types = array();
			foreach($args as $arg) {
				$types []= self::get_type($arg);
			}
			call_user_func_array('mysqli_stmt_bind_param', array_merge(array($this, implode($types)), self::ref_values($args)));
		}

		public function fetch_assoc() {
			// checks to see if the variables have been bound, this is so that when
			//  using a while ($row = $this->stmt->fetch_assoc()) loop the following
			// code is only executed the first time
			if (!$this->varsBound) {
				$meta = $this->result_metadata();
				while ($column = $meta->fetch_field()) {
					// this is to stop a syntax error if a column name has a space in
					// e.g. "This Column". 'Typer85 at gmail dot com' pointed this out
					$columnName = str_replace(' ', '_', $column->name);
					$bindVarArray[] = &$this->results[$columnName];
				}
				call_user_func_array(array($this, 'bind_result'), $bindVarArray);
				$this->varsBound = true;
			}

			if ($this->fetch() != null) {
				// this is a hack. The problem is that the array $this->results is full
				// of references not actual data, therefore when doing the following:
				// while ($row = $this->stmt->fetch_assoc()) {
				// $results[] = $row;
				// }
				// $results[0], $results[1], etc, were all references and pointed to
				// the last dataset
				foreach ($this->results as $k => $v) {
					$results[$k] = $v;
				}
				return $results;
			} else {
				return null;
			}
		}
	}
?>
