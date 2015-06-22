<?php
	
	function getAnonName() {
		//doesn't read in whole file to save processing power
		$filename = "animals.txt";
		$filesize = filesize($filename);
		$begin_index = rand(0, $filesize);
		$file_handle = fopen($filename, "r");
		$animal_name = "";
		fseek($file_handle, $begin_index);//jumps to random place in document
		
		//finds the most recent comma, going back one byte (one character) at a time.
		while(fgetc($file_handle) !== ','){
			fseek($file_handle, -2, SEEK_CUR); //have to go back two bytes because fgetc move forward one byte
		}
		//reads until next comma or end of file, constructing animal name
		
		$char = fgetc($file_handle);
		while($char!==',' && !feof($file_handle)){
			$animal_name .= $char;
			$char = fgetc($file_handle);
		}
		fclose($file_handle);
		
		print($animal_name);
	}

	getAnonName();
?>