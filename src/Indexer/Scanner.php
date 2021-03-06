<?php
/**
 * This file is part of JsonDb library.
 *
 * @author John Wilson
 * @copyright 2016 John Wilson
 * 
 */

namespace IBT\JsonDB\Indexer;

/**
* Scanner class walks the JSON object and indexes it values.
* 
* ```php
* $s = new Scanner();
* $j = '{"name":"jason bourne", "category":"agent"}';
* $s->scan(json_decode($j));
* ```
*/
class Scanner {

	/**
	* Indexes a JSON string.
	*
	* @param array $json JSON value
	* @return array Path
	*/
	public function scan($json) {
		$it = new \RecursiveArrayIterator($json); // call global namespace class
		$path = ""; // set object root path
		$depth = 0; // set path depth
		$result = array(); // array for indexes
		$fn = array($this, "walk"); // iterator callback function

		iterator_apply($it, $fn, array($it, $path, $depth, &$result));

		return $result;
	}

	/**
	* Indexes a JSON string.
	*
	* @param \RecursiveArrayIterator $it Iterator
	* @param string $path current JSON path
	* @param int $depth current JSON depth
	* @param array $result Reference to array containing scanned indexes
	*/
	private function walk($it, $path, $depth, &$result) {
	    while ($it->valid()) {

	        $key = $path . $it->key();
	        $value = $it->current();
	        $typ = Scanner::jsonType(gettype($value));
	        if($typ == "array" || $typ == "object") {
	            $value = json_encode($value);
	        }

	        $result[] = new Index($typ, $value, $key, $depth);

	        if($it->hasChildren()) {
	            $c = $it->getChildren();            
	            $sub_path = $key . ".";
	            $depth++;
	            $this->walk($c, $sub_path, $depth, $result);
	        }
	        $it->next();
	    }
	}

	/**
	* Returns the equivalent JSON type.
	*
	* @param string $typ
	* @return string
    * @throws Exception if the JSON type isn't supported
	*/
	public static function jsonType($typ) {
		switch ($typ) {
			case 'integer':
			case 'double':
				return 'float';
			case 'NULL':
				return 'null';
			case 'boolean':
			case 'string':
			case 'array':
			case 'object':
				return $typ;
			default:
				throw new Exception("Unknown JSON type ${typ}");
		}
	}
}