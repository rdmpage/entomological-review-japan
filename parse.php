<?php

require_once(dirname(__FILE__) . '/simplehtmldom_1_5/simple_html_dom.php');

//----------------------------------------------------------------------------------------
function clean_title(&$reference)
{
	$reference->title = preg_replace('/\s\s+/', ' ', $reference->title);
	$reference->title = preg_replace('/\.$/', ' ', $reference->title);
	$reference->title = preg_replace('/&nbsp;/', ' ', $reference->title);
	$reference->title = preg_replace('/&amp;/', '&', $reference->title);
	
	
	/*
	if (preg_match('/By /', $reference->title))
	{
		echo $reference->title . "\n";
		if (preg_match('/[\x{3000}]By\s+([A-Z][a-z]+(\s+[A-Z]+)+)?(\s+&\s+([A-Z][a-z]+(\s+[A-Z]+)+)?)?(\s+\d+(\-\d+)?)?$/u', $reference->title, $m))
		{
			print_r($m);
		}
		exit();
		
	}
	*/

	// [\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]
	//if (preg_match_all('/(\p{Han}+)/u', $reference->title, $m, PREG_OFFSET_CAPTURE))

	if (preg_match_all('/([\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]+)/u', $reference->title, $m, PREG_OFFSET_CAPTURE))
	{
		//echo $reference->title;
		//print_r($m);
		
		$from = $m[0][0][1];
		$to = $m[0][count($m[0])-1][1] + mb_strlen($m[0][count($m[0])-1][0]);
		
		//echo $from . ' ' . $to;
		
		$reference->alternate_title = mb_substr($reference->title, $from, $to);
		$reference->title = str_replace($reference->alternate_title, '', $reference->title);
		$reference->title = preg_replace('/^\s+/', '', $reference->title);
		
		//print_r($reference);
		
		//exit();
		
	}
	$reference->title = preg_replace('/\s+$/', '', $reference->title);
	$reference->title = preg_replace('/[\x{3000}]$/u', '', $reference->title);
	// to do: handle multilingual titles
	$reference->title = preg_replace('/[\x{3000}]By\s+([A-Z][a-z]+(\s+[A-Z]+)+)?(\s+&\s+([A-Z][a-z]+(\s+[A-Z]+)+)?)?(\s+\d+(\-\d+)?)?$/u', '', $reference->title);
	//$reference->title = preg_replace('/　By\s+([A-Z][a-z]+(\s+[A-Z]+)+)?(\s+\d+(\-\d+)?)?$/u', '', $reference->title);
	$reference->title = preg_replace('/^[\x{3000}]/u', '', $reference->title);
	
	
}

//--------------------------------------------------------------------------------------------------
function reference2ris($reference)
{
	$ris = '';
	
	if (isset($reference->genre))
	{
		switch ($reference->genre)
		{
			case 'article':
				$ris .= "TY  - JOUR\n";
				break;
				
			case 'book':
				$ris .= "TY  - BOOK\n";
				break;

			case 'chapter':
				$ris .= "TY  - CHAP\n";
				break;
				
			default:
				$ris .= "TY  - GEN\n";
				break;
		}

	}
	else
	{
		
		if (isset($reference->secondary_title) || isset($reference->issn))
		{
			$ris .= "TY  - JOUR\n";
		}
		else
		{
			$ris .= "TY  - GEN\n";
		}
	}	
	
	if (isset($reference->id))
	{
		$ris .=  "ID  - " . $reference->id . "\n";
	}
	if (isset($reference->publisher_id))
	{
		$ris .=  "ID  - " . $reference->publisher_id . "\n";
	}
	
	if (isset($reference->authors))
	{
		foreach ($reference->authors as $a)
		{
			if (is_object($a))
			{
				$ris .= "AU  - ";
				if (isset($a->forename))
				{
					$ris .= trim($a->forename);
				}
				if (isset($a->lastname))
				{
					$ris .= ' ' . trim($a->lastname);
				}
				if (isset($a->surname))
				{
					$ris .= ' ' . trim($a->surname);
				}
				$ris .= "\n";
			
			}
			else
			{
				//$a = preg_replace('/\.([A-Z])/u', ". $1", $a);
				//$a = preg_replace('/\s\s/u', " ", $a);
				$ris .= "AU  - " . trim($a) . "\n";	
			}
		}
	}
	
	if (isset($reference->atitle))
	{
		$ris .=  "TI  - " . strip_tags($reference->atitle) . "\n";
		$ris .=  "JF  - " . strip_tags($reference->title) . "\n";
	}
	else
	{
		$reference->title = str_replace('&quot;',"'", $reference->title);
		if ($reference->title != '')
		{
			$ris .=  "TI  - " . strip_tags($reference->title) . "\n";
		}
		else
		{
			// If no main title but we have alternate title, make that the main title
			if (isset($reference->alternate_title))
			{
				$ris .=  "T1  - " . $reference->alternate_title . "\n";		
			}			
		}
	}
	
	if (isset($reference->alternate_title))
	{
		$ris .=  "T2  - " . $reference->alternate_title . "\n";		
	}
	
	if (isset($reference->secondary_title)) 
	{
		switch ($reference->genre)
		{
			case 'chap':
				$ris .=  "T2  - " . $reference->secondary_title . "\n";
				break;
			
			default:
				$ris .=  "JF  - " . $reference->secondary_title . "\n";
				break;
		}
	}
	
	if (isset($reference->issn))
	{
		$ris .=  "SN  - " . $reference->issn . "\n";
	}
	
	
	if (isset($reference->secondary_authors))
	{
		foreach ($reference->secondary_authors as $a)
		{
			$ris .= "ED  - " . trim($a) . "\n";	
		}	
	}	
	if (isset($reference->volume)) $ris .=  "VL  - " . $reference->volume . "\n";
	if (isset($reference->issue) && ($reference->issue != ''))
	{
		$ris .=  "IS  - " . $reference->issue . "\n";
	}
	if (isset($reference->spage)) $ris .=  "SP  - " . $reference->spage . "\n";
	if (isset($reference->epage)) $ris .=  "EP  - " . $reference->epage . "\n";
	
	if (isset($reference->date))
	{
		$ris .=  "Y1  - " . str_replace("-", "/", $reference->date) . "\n";
	}
	else
	{
		$ris .=  "Y1  - " . $reference->year . "///\n";
	}
	if (isset($reference->url))
	{
		if (preg_match('/dx.doi.org/', $reference->url))
		{
		}
		elseif (preg_match('/biostor.org/', $reference->url))
		{
		}
		else
		{
			$ris .=  "UR  - " . $reference->url . "\n";
		}
	}
	
	if (isset( $reference->pdf))
	{
		$ris .=  "L1  - " . $reference->pdf . "\n";
	}
	if (isset( $reference->doi))
	{
		$ris .=  "UR  - http://dx.doi.org/" . $reference->doi . "\n";
		// Ingenta
		$ris .= 'M3  - ' . $reference->doi . "\n"; 
		// Mendeley 0.9.9.2
		$ris .=  "DO  - " . $reference->doi . "\n";
	}
	if (isset( $reference->hdl))
	{
		$ris .=  "UR  - http://hdl.handle.net/" . $reference->hdl . "\n";
	}
	if (isset( $reference->biostor))
	{
		$ris .=  "UR  - http://biostor.org/reference/" . $reference->biostor . "\n";
	}

	if (isset( $reference->pmid))
	{
		$ris .=  "UR  - http://www.ncbi.nlm.nih.gov/pubmed/" . $reference->pmid . "\n";
	}
	if (isset( $reference->pmc))
	{
		$ris .=  "UR  - http://www.ncbi.nlm.nih.gov/pmc/articles/PMC" . $reference->pmc . "\n";
	}



	if (isset($reference->abstract))
	{
		$ris .=  "N2  - " . $reference->abstract . "\n";
	}
	
	if (isset($reference->publisher))
	{
		$ris .=  "PB  - " . $reference->publisher . "\n";
	}
	if (isset($reference->publoc))
	{
		$ris .=  "CY  - " . $reference->publoc . "\n";
	}
	
	if (isset($reference->notes))
	{
		$ris .=  "N1  - " . $reference->notes . "\n";
	}
	
	
	if (isset($reference->keywords))
	{
		foreach ($reference->keywords as $keyword)
		{
			$ris .=  "KW  - " . $keyword . "\n";
		}
	}
	
	if (isset($reference->thumbnail))
	{
		$ris .=  "L4  - " . $reference->thumbnail . "\n";
	}	

	
	
	$ris .=  "ER  - \n";
	$ris .=  "\n";
	
	return $ris;
}
$h = file_get_contents('Entomological Review of Japan.html');

//echo mb_detect_encoding($h);
//exit();

$h = mb_convert_encoding($h, 'UTF-8', 'SJIS');

$h = str_replace("<tr", "CUTME", $h);

$rows = explode("CUTME", $h);

//print_r($rows);
//exit();

$current_volume = '';
$current_issue = '';

foreach ($rows as $row)
{
	$row = '<tr' . $row;

	$html = str_get_html($row);

	$cells = $html->find('td');
	
	$reference = new stdclass;
	$reference->secondary_title = 'Entomological Review of Japan';
	$reference->issn = '0286-9810';
	
	$text = array();
	foreach ($cells as $cell)
	{
		$text[] = $cell->plaintext;
	}
	$reference->notes = join(" ", $text);
	$reference->notes = preg_replace('/\s\s+/u', ' ', $reference->notes);
	$reference->notes = preg_replace('/^\s+/u', '', $reference->notes);
	
	if (count($cells) == 5)
	{
		if (preg_match('/Vol.\s*(?<volume>\d+),\s*No.\s*(?<issue>\d+)/', trim($cells[3]->plaintext), $m))
		{
			$current_volume = $m['volume'];
			$current_issue = $m['issue'];
		}

		if (preg_match('/(?<spage>\d+)\-(?<epage>\d+)/', trim($cells[4]->plaintext), $m))
		{
			$reference->spage = $m['spage'];
			$reference->epage = $m['epage'];
		}
		if (preg_match('/^(?<spage>\d+)$/', trim($cells[4]->plaintext), $m))
		{
			$reference->spage = $m['spage'];
		}
	}
	else
	{
		if (preg_match('/(?<spage>\d+)\-(?<epage>\d+)/', trim($cells[3]->plaintext), $m))
		{
			$reference->spage = $m['spage'];
			$reference->epage = $m['epage'];
		}
		if (preg_match('/^(?<spage>\d+)$/', trim($cells[3]->plaintext), $m))
		{
			$reference->spage = $m['spage'];
		}
	}
	
	$authorstring = trim($cells[0]->plaintext);
	
	
	$authorstring = preg_replace('/[\x0d][\x0a]\s*/u', ' ', $authorstring);
	//echo "|$authorstring|\n";
	
	
	$authorstring = preg_replace('/&nbsp;/u', ' ', $authorstring);
	$authorstring = preg_replace('/\s+(and)?&amp;\s+/u', '|', $authorstring);
	$authorstring = preg_replace('/\s+and\s+/u', '|', $authorstring);
	$authorstring = preg_replace('/,\s*/u', '|', $authorstring);
	
	$reference->authors = explode("|", $authorstring);
	
	
	$reference->year = trim($cells[1]->plaintext);
	$reference->title = trim($cells[2]->plaintext);
	
	clean_title($reference);
	
	
	$reference->volume = $current_volume;
	$reference->issue = $current_issue;
	
	
	
	
	//print_r($reference);
	
	echo reference2ris($reference);


}

?>
		
