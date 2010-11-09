<?php
	defined( 'uservice' ) or die( 'You should not see this.' );

	class Event {
		public $header;
		public $message;
		public $source;

		public function __construct($message, $header, $source) {
			$this->message = $message;
			$this->header = $header;
			$this->source = $source;
		}
	}

	class EventDispatcher {
		private static $instance;
		private $handlers = array();

		private function __construct() {
			// Nothing here.
		}

		public static function get_instance() {
			if(!isset(self::$instance)) {
				self::$instance = new EventDispatcher();
			}
			return self::$instance;
		}

		public function register_handler($name, $handler) {
			if(!isset($this->handlers[$name])) {
				$this->handlers[$name] = array();
			}
			$this->handlers[$name] []= $handler;
		}

		public function dispatch($name, $message, $source = null, $header = array()) {
			$e = new Event($message, $header, $source);
			if(isset($this->handlers[$name])) {
				foreach($this->handlers[$name] as $handler) {
					$handler($e);
				}
			}
			if(isset($this->handlers['*'])) {
				foreach($this->handlers['*'] as $handler) {
					$handler($e);
				}
			}
		}
	}
?>
