<?php namespace UsamaNoman\PHPEntitiesParser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PHPEntitiesParser {
 
  	public function __construct($timezone="America/Los_Angeles")
  	{
  		date_default_timezone_set($timezone);
  	}

  	public function DateTimeParser($str){
  		$de = new DateExtraction();
  		$dates=[];
  		$date= $de->parse($str);
  		$parts= date_parse($date[key($date)]);
  		if($parts['year']<=1970){
  			foreach($this->TriWords(explode(" ", $str)) as $StrPart){
  				// previous to PHP 5.1.0 you would compare with -1, instead of false
  				if (($timestamp = strtotime($StrPart)) !== false) {
  				  $dates[$StrPart] =date("Y-m-d H:i:s", $timestamp);
  				}
  			}
  		}else{
  		  $dates[key($date)]= $date[key($date)];
  		}
  		return array_unique($dates);
  	}

  	public function EmailsParser($str){
  		$matches = array(); //create array
  		$pattern = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/i'; //regex for pattern of e-mail address
  		preg_match_all($pattern, $str, $matches); //find matching pattern
  		return $matches[0];
  	}


  	public function FirstEmailParser($str){
  		$matches = array(); //create array
  		$pattern = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/i'; //regex for pattern of e-mail address
  		preg_match($pattern, $str, $matches); //find matching pattern
  		return $matches;
  	}

  	private function TriWords($words){
  		$biwords=[];
  		for($i=1;$i<count($words);$i++){
  			$biwords[]=$words[$i-1]." ".$words[$i];
  		}
  		$triwords=[];
		for($i=2;$i<count($words);$i++){
  			$triwords[]=$words[$i-2]." ".$words[$i-1]." ".$words[$i];
  		}
  		return array_merge($words,$biwords,$triwords);
  	}

  	public function LocationParser($str){
  		$words= $this->RemoveStopwords($str);
  		
  		$client = new Client(['base_uri' => 'http://maps.googleapis.com/maps/api/geocode/']);
  		
  		$Locations=[];
  		$AllPotentialLocations= $this->TriWords($words);
  		foreach($AllPotentialLocations as $PotentialLocation){
  			try{
  				$response = $client->request('GET', 'json?address='.$PotentialLocation.'&sensor=true');
	  			$results= json_decode($response->getBody()->getContents(),true)['results'];
	  			if(count($results)>0){
	  				$Locations[$PotentialLocation]=$results[0]['formatted_address'];
	  			}
	  		}catch (RequestException $e) {
			    echo Psr7\str($e->getRequest());
			    if ($e->hasResponse()) {
			        echo Psr7\str($e->getResponse());
			    }
			}
  		}
  		return array_unique($Locations);
  	}

  	private function RemoveStopwords($str){
  		$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst",   "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "de",  "down", "due", "during", "each", "eg",  "either", "else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few",  "fill", "find", "fire",  "for", "former", "formerly", "forty", "found",  "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein","see","seem","sees","seems", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once" , "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same",  "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "un", "under", "until","unless","email","address" ,"up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

  		$stopwords=array_merge($stopwords,$this->EmailsParser(strtolower($str)));
  		$words=explode(" ", strtolower($str));
  		$i=0;
  		foreach($words as $word){
  			if(in_array(preg_replace("/(?![.=$'€%-])\p{P}/u", "", trim($word)), $stopwords) || in_array(trim($word), $stopwords)){
  				unset($words[$i]);
  			}
  			$i++;
  		}

  		return array_values($words);
  	}
 
 	private function FourWords($words){
  		$triWords=$this->TriWords($words);
  		$fourWords=[];
		for($i=3;$i<count($words);$i++){
  			$fourWords[]=$words[$i-3]." ".$words[$i-2]." ".$words[$i-1]." ".$words[$i];
  		}
  		return array_merge($triWords,$fourWords);
  	}

 	public function NumberParser($str){
 		$str=strtolower("one thousand and fifty two but three");
 		$numbers=[];
 		$i=0;
 		foreach (explode(" ", $str) as  $value) {
 			if(isset($this->NumberTranslations()[$value])){
 				if(!isset($numbers[$i])){
 					$numbers[$i]='';
 				}
 				$numbers[$i].=' '.$value;
 			}else{
 				$i++;
 			}

 		}
 		return  $this->WordsToNumber($numbers);
 	}

 	private function NumberTranslations(){
 		return array(
 		    'zero'      => '0',
 		    'a'         => '1',
 		    'one'       => '1',
 		    'two'       => '2',
 		    'three'     => '3',
 		    'four'      => '4',
 		    'five'      => '5',
 		    'six'       => '6',
 		    'seven'     => '7',
 		    'eight'     => '8',
 		    'nine'      => '9',
 		    'ten'       => '10',
 		    'eleven'    => '11',
 		    'twelve'    => '12',
 		    'thirteen'  => '13',
 		    'fourteen'  => '14',
 		    'fifteen'   => '15',
 		    'sixteen'   => '16',
 		    'seventeen' => '17',
 		    'eighteen'  => '18',
 		    'nineteen'  => '19',
 		    'twenty'    => '20',
 		    'thirty'    => '30',
 		    'forty'     => '40',
 		    'fourty'    => '40', // common misspelling
 		    'fifty'     => '50',
 		    'sixty'     => '60',
 		    'seventy'   => '70',
 		    'eighty'    => '80',
 		    'ninety'    => '90',
 		    'hundred'   => '100',
 		    'thousand'  => '1000',
 		    'lakh'		=> '100000',
 		    'million'   => '1000000',
 		    'crore'		=> '10000000',
 		    'billion'   => '1000000000',
 		    'and'       => '',
 		);
 	}

 	/**
 	 * Convert a string such as "one hundred thousand" to 100000.00.
 	 *
 	 * @param string $data The numeric string.
 	 *
 	 * @return float or false on error
 	 */
 	private function WordsToNumber($numberArrays) {
 	    // Replace all number words with an equivalent numeric value
 		$translations=[];
 		foreach ($numberArrays as $key => $data) {

	 	    $data2 = strtr(
	 	        trim($data),
	 	        $this->NumberTranslations()
	 	    );

	 	    // Coerce all tokens to numbers
	 	    $parts = array_map(
	 	        function ($val) {
	 	            return floatval($val);
	 	        },
	 	        preg_split('/[\s-]+/', $data2)
	 	    );
	 	    $stack = new \SplStack(); // Current work stack
	 	    $sum   = 0; // Running total
	 	    $last  = null;

	 	    foreach ($parts as $part) {
	 	        if (!$stack->isEmpty()) {
	 	            // We're part way through a phrase
	 	            if ($stack->top() > $part) {
	 	                // Decreasing step, e.g. from hundreds to ones
	 	                if ($last >= 1000) {
	 	                    // If we drop from more than 1000 then we've finished the phrase
	 	                    $sum += $stack->pop();
	 	                    // This is the first element of a new phrase
	 	                    $stack->push($part);
	 	                } else {
	 	                    // Drop down from less than 1000, just addition
	 	                    // e.g. "seventy one" -> "70 1" -> "70 + 1"
	 	                    $stack->push($stack->pop() + $part);
	 	                }
	 	            } else {
	 	                // Increasing step, e.g ones to hundreds
	 	                $stack->push($stack->pop() * $part);
	 	            }
	 	        } else {
	 	            // This is the first element of a new phrase
	 	            $stack->push($part);
	 	        }

	 	        // Store the last processed part
	 	        $last = $part;
	 	    }

	 	    $translations[trim($data)]=$sum + $stack->pop();
	 	}
	 	return $translations;
 	}
}