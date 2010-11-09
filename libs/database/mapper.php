<?php
	defined( 'uservice' ) or die( 'You should not see this.' );
	require_once(dirname(__FILE__).'/mysqli_extended.php');

	function get_db() {
		$db = new mysqli_extended(DB_HOST, DB_USER, DB_PASSWD, DB_NAME);
		if (mysqli_connect_errno()) { 
			throw new Exception(sprintf("Connect failed: %s\n", mysqli_connect_error())); 
		} 
		return $db;	
	}

	class Query {
		public $page = 0; // The current page of the query, default to 0
		public $item_count = 15; // The item count to fetch, default to 15, if set to -1, don't use pagination at all
		public $type; // The type to fetch, can't be null, will look up the type from the entity table, using this type to easy the select and update process, all the query must be in the entity table, to manage all the sqls together.
		public $args = array(); // The arguments for the query, is an array.
		public function __construct($type) {
			$this->type = $type;
		}
	}

	class QueryResult {
		public $total;
		public $results;

		public function __construct($total, $results) {
			$this->total = $total;
			$this->results = $results;
		}
	}

	class Mapper {
		private static $instance;

		private function __construct() {
			// For singleton
		}

		public static function get_instance() {
			if(!isset(self::$instance)) {
				self::$instance = new Mapper();
			}
			return self::$instance; 
		}

		/**
		 * Load the entity, all the enetities has one filed called id.
		 */
		public function load_entity($entity, $id) {
			if(isset($GLOBALS['mapping_table'][$entity])){
				$mapping = $GLOBALS['mapping_table'][$entity];
				$query = sprintf('select %s from %s where id = ?', implode(',', $mapping['fields']), $entity);
				$db = get_db();
				$stmt = $db->prepare($query);
				debug($query);
				$stmt->bind_param('i', $id);
				$stmt->execute();
				$stmt->store_result();
				while($row = $stmt->fetch_assoc()){
					$result = $row;
					break;
				}
				$stmt->close();
				$db->close();
				return $result;
			}
			return null;
		}

		public function load_by_fields($entity, $obj, $op = 'and') {
			if(isset($GLOBALS['mapping_table'][$entity])){
				$mapping = $GLOBALS['mapping_table'][$entity];

				$params = array(); 
				$values = array();
				foreach(array_keys($obj) as $param) {
					$params []= $param.' = ?';
					$values []= $obj[$param];
				}
				
				$query = sprintf('select %s from %s where %s', implode(',', array_merge(array('id'), $mapping['fields'])), $entity, implode(' '.$op.' ', $params));
				debug($query);
				$db = get_db();
				$stmt = $db->prepare($query);
				$stmt->bind_args($values);
				$stmt->execute();
				$stmt->store_result();
				while($row = $stmt->fetch_assoc()){
					$result = $row;
					break;
				}
				$stmt->close();
				$db->close();
				if(isset($result))
					return $result;
			}
			return null;
		}

		public function delete_entity($entity, $id) {
			if(isset($GLOBALS['mapping_table'][$entity])){
				$mapping = $GLOBALS['mapping_table'][$entity];
				$query = sprintf('delete from %s where id = ?', $entity);
				$db = get_db();
				$stmt = $db->prepare($query);
				debug($query);
				$stmt->bind_param('i', $id);
				$result = $stmt->execute();
				$stmt->close();
				$db->close();
				return $result;
			}
			return false;
		}

		/**
		 * Save or update the entity, if successfully saved, return the insert id of the entity
		 */
		public function save_entity($entity, $obj) {
			if(isset($GLOBALS['mapping_table'][$entity])){
				$mapping = $GLOBALS['mapping_table'][$entity];
				$values = array();
				if(isset($obj['id'])){
					// Update the entity
					$ups = array();
					foreach(array_keys($obj) as $key) {
						if($key == 'id')
							continue;
						$ups []= sprintf('%s = ?', $key);
						$values []= $obj[$key];
					}
					// Special for id
					$types []= 'i';
					$values []= $obj['id'];
					$query = sprintf('update %s set %s where id = ?', $entity, implode(',', $ups));
				}
				else {
					// Insert the entity
					$qms = array();
					foreach(array_keys($obj) as $key) {
						$qms []= '?';
						$values []= $obj[$key];
					}
					$query = sprintf('insert into %s (%s) values (%s)', $entity, implode(',', array_keys($obj)), implode(',', $qms));
				}
				$db = get_db();
				$stmt = $db->prepare($query);
				debug($query);
				$stmt->bind_args($values);
				$result = $stmt->execute();
				$id = $stmt->insert_id;
				$stmt->close();
				$db->close();
				return $id;
			}
			return false;
		}

		public function exec($type, $args = array(), $page = 0, $item_count = ITEM_COUNT) {
			$query = new Query($type);
			$query->args = $args;
			$query->page = $page;
			$query->item_count = $item_count;
			return self::query($query);
		}

		public function query($query) {
			if(isset($query->type) && isset($GLOBALS['mapping_table'][$query->type])) {
				$mapping = $GLOBALS['mapping_table'][$query->type];
				$db = get_db();
				switch($mapping['type']){
				case 'entity':
					return;
				case 'query':
					$sql = $mapping['query'];
					$args = $query->args;
					if($query->page != -1) {
						// Adding the pagination parameters
						$sql = $sql.' limit ?,?';
						$args []= $query->page * $query->item_count;
						$args []= $query->item_count;
					}
					debug($sql);
					debug($args);
					$stmt = $db->prepare($sql);
					$stmt->bind_args($args);
					$stmt->execute();
					$result = array();
					while($row = $stmt->fetch_assoc()){
						$result []= $row;
					}
					$stmt->close();

					$sql = $mapping['count_query'];

					if($query->page != -1) {
						// Adding the pagination parameters
						$sql = $sql.' limit ?,?';
					}
					$stmt = $db->prepare($sql);
					$stmt->bind_args($args);
					$stmt->execute();
					$count = $stmt->fetch_assoc();
					$count = $count['count'];
					$stmt->close();
					$db->close();
					return new QueryResult($count, $result);
				}
			}
			return null;
		}
	}
?>
