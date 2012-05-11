<?php
/* Brainfuck interpreter by Max E. Katz

This is a Brainfuck interpreter which also has support for numeric arguments.
For instance, to add ten to the current cell on the data strip, one may write '10+' instead of '++++++++++'.  It is fully backward-compatible
with standard Brainfuck programs.

To use, create a new instance of the BrainfuckInterpreter class, then invoke the Interpret method, passing it the brainfuck code as a string argument.

Examples are included at the bottom of this document.

*/

// USAGE
// 
// Uncomment the following lines to see the interpreter in action.
// For more example programs, see http://esolangs.org/wiki/Brainfuck
// $bf = new BrainfuckInterpreter();
// $bf->Interpret('72+.29+.7+>2+[-<.>]<3+.67-.12-.55+.24+.3+.6-.8-.67-.23-.');	// 7+..



class BrainfuckInterpreter {
	public function __construct() {
		if(!defined('BI_MAX_DATA_LEN'))
			define('BI_MAX_DATA_LEN', 1024);
		if(!defined('BI_INIT_DATA_LEN'))
			define('BI_INIT_DATA_LEN', 1024);
		if(!defined('BI_LOOP_DATA_STRIP'))							// When the data strip's bounds are exceeded, do we loop to the other end?
			define('BI_LOOP_DATA_STRIP', TRUE);
		if(!defined('BI_LOOP_DATA_VAL'))							// When a point in the data exceeds 255 or drops below 0, do we loop around in an unsigned fashion?
			define('BI_LOOP_DATA_VAL', TRUE);
		if(!defined('BI_STDIN'))
			define('BI_STDIN', STDIN);
		if(!defined('BI_TICK_LIMIT'))								// Limit the number of instructions to execute.  Set to 0 for unlimited ticks.
			define('BI_TICK_LIMIT', 200000);
		if(!defined('BI_DEBUG_SHOWTICKS'))
			define('BI_DEBUG_SHOWTICKS', FALSE);
		if(!defined('BI_ENABLE_COUNT'))
			define('BI_ENABLE_COUNT', TRUE);
		if(!defined('BI_DEBUG'))
			define('BI_DEBUG', FALSE);
		if(!defined('BI_DEBUG_DUMP_FREQ'))
			define('BI_DEBUG_DUMP_FREQ', 3);
		if(!defined('BI_MAX_OUTPUT_LEN'))							// Set to 0 for no limit.
			define('BI_MAX_OUTPUT_LEN', 255);
	}
	
	
	public function Interpret($code, $input = NULL) {
		//
		// > 	increment pointer
		// <	decrement pointer
		// 
		// +	increment byte
		// -	decrement byte
		// 
		// ,	input char
		// .	output char
		// 
		// [	jz
		// ]	rnz
		// 
		//
		
		$codeLen = strlen($code);
		$codePtr = 0;
		$dataPtr = 0;
		$dataLen = BI_INIT_DATA_LEN;
		$data = str_repeat(chr(0), BI_INIT_DATA_LEN);
		$ticks = 0;
		$count = 1;
		$counting = FALSE;
		$output = '';
		$outputLen = 0;
		//$outputPtr = 0;
		$inputPtr = 0;
		$workingBuf = 0;
		
		// main loop
		while($codeLen - $codePtr) {
			switch($code[$codePtr]) {
				case '>':	$dataPtr += $count;
						if(BI_LOOP_DATA_STRIP)
							while($dataPtr > BI_MAX_DATA_LEN) $dataPtr -= BI_MAX_DATA_LEN;
						else $dataPtr = BI_MAX_DATA_LEN;						break;	// force the pointer to loop or stop at the maximum
							
				case '<':	$dataPtr -= $count;
						if(BI_LOOP_DATA_STRIP)
							while($dataPtr < 0) $dataPtr += BI_MAX_DATA_LEN;
						else $dataPtr = 0;								break;	// force the pointer to loop or stop at the start of the strip
				
				case '+':	$workingBuf = ord($data[$dataPtr]) + $count;						// convert the addition to an unsigned character.  wrap around on overflow as appropriate.
						while($workingBuf > 255) {								// note that the count is not being added to ticks properly.
							if(BI_LOOP_DATA_VAL)
								$workingBuf -= 256;
							else
								$workingBuf = 255;
						}
						$data[$dataPtr] = chr($workingBuf);
						$count = -1;									break;
				
				case '-':	$workingBuf = ord($data[$dataPtr]) - $count;						// convert the subtraction to an unsigned character.  wrap around on overflow as appropriate.
						while($workingBuf < 0) {
							if(BI_LOOP_DATA_VAL)
								$workingBuf += 256;
							else
								$workingBuf = 0;
						}
						$data[$dataPtr] = chr($workingBuf);
						$count = -1;									break;
				
				case ',':	if($input === NULL)
							while($count--) $data[$dataPtr] = fgetc(BI_STDIN);
						else
							while($count--) $data[$dataPtr] = $input[$inputPtr++];			break;
							
				case '.':	while(BI_MAX_OUTPUT_LEN && ($outputLen + $count) > BI_MAX_OUTPUT_LEN)
							$count = BI_MAX_OUTPUT_LEN - $outputLen;
						$outputLen += $count;
						$output .= ($workingBuf = str_repeat($data[$dataPtr], $count));
						echo $workingBuf;								break;
				
				case '[':	if(!ord($data[$dataPtr])) {
							$depth = 0;
							while($codePtr < $codeLen && ($code[++$codePtr] != ']' || $depth)) {
								if($code[$codePtr] == '[')
									$depth++;
								else if($code[$codePtr] == ']')
									$depth--;
							}
						}
							
																break;
				case ']':	//echo "<";
						$loopPtr = $codePtr;
						if(ord($data[$dataPtr])) {
							$depth = 0;
							while($loopPtr >= 0 && ($code[--$loopPtr] != '[' || $depth)) {
								if($code[$loopPtr] == ']')
									$depth++;
								else if($code[$loopPtr] == '[')
									$depth--;
							}
							if($loopPtr < 0)
								$codePtr++;
							else
								$codePtr = $loopPtr;
						} //echo ">";
																break;
				
				case '0': case '1': case '2': case '3': case '4':
				case '5': case '6': case '7': case '8': case '9':
						if(!BI_ENABLE_COUNT) {
							$count = 1;
							break;
						}
						if(!$counting) {
							$count = 0;
							$counting = TRUE;
						}
						$count *= 10;
						$count += ord($code[$codePtr]) - 48;						break;
				
				default:
						$counting = FALSE;
			}
			//echo $code[$codePtr];
			if(BI_DEBUG_SHOWTICKS)
				echo "tick.";
			
			if(BI_TICK_LIMIT && !(BI_TICK_LIMIT - (++$ticks))) {
				echo "Tick limit reached: ", $ticks, "\n";
				return FALSE;
			}
			
			// Reset the count.
			if(ord($code[$codePtr]) < 48 || ord($code[$codePtr]) > 57) {
				$counting = FALSE;
				$count = 1;
			}
			
			
			
			if(BI_DEBUG && !($ticks % BI_DEBUG_DUMP_FREQ)) {
				echo "Tick: ", $ticks, "\n";
				echo "Count: ", $count, "\n";
				echo "codePtr: ", $codePtr, " [", $code[$codePtr], "]\n";
				echo "dataPtr: ", $dataPtr, "\n";
				BrainfuckInterpreter::dumpData($data, 32);
				echo "\n";
			}
			
			$codePtr++;
		}
		
		//BrainfuckInterpreter::dumpData($data, 32);
		return $output;
	}
	
	protected function dumpData($data, $cols = 0) {
		echo "\n";
		$dataLen = strlen($data);
		for($i = 0; $i < $dataLen; $i++) {
			$val = ord($data[$i]);
			echo '[';
			if($val < 100)	echo ' ';
			if($val < 10)	echo ' ';
			if(!$val)	echo ' ';	// yes, this should be earlier in the loop, but i don't want to repeat the line-terminating code.
			else echo $val;
			echo ']';
			if($cols && !(($i + 1) % $cols)) echo "\n";
		}
		if($cols && ($i % $cols)) echo "\n";
	}
	
}

/*
$bf = new BrainfuckInterpreter();
$bf->Interpret(file_get_contents('fact.bf'));	// 7+..
 */
