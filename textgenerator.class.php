<?php

/**
* TextGenerator
*
* Generate georgian words and sentences
*
* @author		Levan Velijanashvili <stichoza@gmail.com>
* @link			http://stichoza.com/
* @copyright	2013 Stichoza
* @license		http://www.opensource.org/licenses/mit-license.php MIT
* @version		2.1.26
*/

class TextGenerator {

	/**
	* Probability of certain functionalities
	* Large number = less probability
	* n	- 1/n+1
	* 0	- 1
	* 1	- 1/2
	* inf	- 1/inf
	* 
	* @access	private
	*/
	private $probability = array(
		"punctuation" =>	5,
		"prefixes" =>		4,
		"suffixes" =>		6,
		"letter_skip" =>	8,
	);


	/**
	* Array of processed chars
	* 
	* @access	private
	*/
	private $preparedData = array();


	/**
	* Basic array of characters
	* This array is deleted at the end of construction
	* 
	* @access	private
	*/
	private $characterBase = array(
		"punctuation" => array(
			"middle" => array(
				"," =>	8,
				" -" =>	1
			),
			"ending" => array(
				"." =>	25,
				"..." =>3,
				"?" =>	2,
				"!" =>	2,
				"?!" =>	1
			)
		),
		"letters" => array(
			"vowels" => array(
				"ა" =>	6,
				"ე" =>	3,
				"ი" =>	4,
				"ო" =>	5,
				"უ" =>	1
			),
			"consonants" => array(
				"ბ" =>	37,		"გ" =>	28,		"დ" =>	56,
				"ვ" =>	46,		"ზ" =>	11,		"თ" =>	31,
				"კ" =>	17,		"ლ" =>	47,		"მ" =>	64,
				"ნ" =>	48,		"პ" =>	4,		"ჟ" =>	1,
				"რ" =>	67,		"ს" =>	77,		"ტ" =>	13,
				"ფ" =>	10,		"ქ" =>	9,		"ღ" =>	7,
				"ყ" =>	10,		"შ" =>	18,		"ჩ" =>	5,
				"ც" =>	19,		"ძ" =>	7,		"წ" =>	10,
				"ჭ" =>	3,		"ხ" =>	25,		"ჯ" =>	3,
				"ჰ" =>	2
			)
		),
		"groups" => array(
			"prefixes" => array(
				"მი" =>		3,
				"მო" =>		3,
				"მიმო" =>	1,
				"და" =>		4,
				"ჩა" =>		4,
				"შე" =>		6,
				"გა" =>		4,
				"გადმო" =>	1,
				"წა" =>		3
			),
			"suffixes" => array(
				"მა" =>		5,
				"ით" =>		6,
				"ქნა" =>		1,
				"ები" =>		10,
				"ოვა" =>		3,
				"-მეთქი" =>	1,
				"-თქო" =>	1
			)
		)
	);


	/**
	* Class constructor
	* 
	* @access	public
	*/
	public function __construct() {
		try {
			$this->prepareData($this->characterBase);
		} catch (Exception $e) {
			echo "Caught Exception: " . $e.getMessage();
		}
		unset($this->characterBase);
	}


	/**
	* Prepare characterBase[] and push to preparedArray[]
	* 
	* @access	private
	* @param	array	$array	Array to iterate
	* @return	void
	* @throws
	*
	*/
	private function prepareData($array) {
		foreach ($array as $key => $value) {
			if (is_array($value) && !TextGenerator::has_array($value)) {
				try {
					$this->preparedData[$key] = $this->prepareArray($value);
				} catch (Exception $e) {
					echo "Exception in first loop: " . $e.getMessage();
				}
			} elseif (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					try {
						$this->preparedData[$key][$key2] = $this->prepareArray($value[$key2]);
					} catch (Exception $e) {
						echo "Exception in inner loop: " . $e.getMessage();
					}
				}
			} else {
				throw new Exception('Expected array.');
			}
		}
	}


	/**
	* Return prepared array (keys multiplied by it's priority value)
	* 
	* @param	array	$array	Array to prepare
	* @return	array	Prepared array
	*/
	private function prepareArray($array) {
		if (!is_array($array)) {
			throw new Exception('Expected array.');
			return null;
		}
		$preparedArray = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				throw new Exception('Array contains sub-array.');
				continue;
			}
			for ($i=0; $i < $value; $i++) {
				$preparedArray[] = $key;
			}
		}
		return $preparedArray;
	}


	/**
	* Generate random word
	*
	* @param	integer	$length	Word length
	* @param	boolean	$pre	Use prefixes
	* @param	boolean	$suf	Use suffixes
	* @param	boolean	$strict	Generate in strict mode (no length incrementing)
	* @return	string	Generated word
	*/
	public function generateWord($length, $pre = true, $suf = true, $strict = false) {
		$word = "";
		$offset = ($strict) ? 0 : rand(0, 1);
		if (($offset && $length%2!=0) || (!$offset && $length%2==0)) $length++;
		for ($i=0; $i<$length; $i++) {
			$isVowel = (($i+$offset)%2 == 0);
			if ($isVowel && !rand(0, $this->probability["letter_skip"]) && !$strict && $length > 4)
				continue;
			$letters = $isVowel ? $this->preparedData["letters"]["vowels"]
				: $this->preparedData["letters"]["consonants"];
			$word .= $letters[rand(0, count($letters)-1)];
		}
		if (!rand(0, $this->probability["suffixes"]) && $suf)
			$word .= $this->preparedData["groups"]["suffixes"][rand(0,
				count($this->preparedData["groups"]["suffixes"])-1)];
		if (!rand(0, $this->probability["prefixes"]) && $pre)
			$word = $this->preparedData["groups"]["prefixes"][rand(0,
				count($this->preparedData["groups"]["prefixes"])-1)] . $word;
		return $word;
	}


	/**
	* Generate sentence, string containig random words
	*
	* @param	integer	$n		Number of words in sentence
	* @param	boolean	$pre	Use prefixes
	* @param	boolean	$suf	Use suffixes
	* @return	string	Generated sentence
	*/
	public function generateSentence($n, $pre, $suf) {
		$sentence = "";
		for ($i=0; $i<$n; $i++) {
			$sentence .= $this->generateWord(rand(3, 8), $pre, $suf);
			if ($i == $n-1) {
				$sentence .= $this->preparedData["punctuation"]["ending"][rand(0,
					count($this->preparedData["punctuation"]["ending"])-1)];
				break;
			} elseif (!rand(0, $this->probability["punctuation"])) {
				$sentence .= $this->preparedData["punctuation"]["middle"][rand(0,
					count($this->preparedData["punctuation"]["middle"])-1)];
			}
			$sentence .= " ";
		}
		return $sentence;
	}


	/**
	* Check if array is multidimensional
	*
	* @param	array	$a	Array to chech for sub-arrays
	* @return	boolean	If the array is multidimensional
	*/
	public static function has_array($a) {
		foreach ($a as $v) {
			if (is_array($v)) return true;
		}
		return false;
	}

}
?>
