<?php

require_once(dirname(__FILE__) . '/ris.php');

	

//----------------------------------------------------------------------------------------

// load page map

$data = array();

$pdfs = array();
$sql = '';

$count = 0;

$filename = 'pages.tsv';
$file_handle = fopen($filename, "r");
		
while (!feof($file_handle)) 
{
	$row = trim(fgets($file_handle));
	
	if ($count != 0)
	{
	
		$parts = explode("\t", $row);
	
		if (!isset($data[$parts[0]]))
		{
			$data[$parts[0]] = array();
		}
		if (!isset($data[$parts[0]][$parts[1]]))
		{
			$data[$parts[0]][$parts[1]] = array();
		}
		$data[$parts[0]][$parts[1]][$parts[2]] = $parts[3];
	}
	
	$count++;
}

print_r($data);	



//----------------------------------------------------------------------------------------
function import($reference)
{
	global $data;
	global $pdfs;
	global $sql;
	
	$force = true;
	$force = false;
	
	$reference->spage = preg_replace('/^0+/', '', $reference->spage);
	$reference->epage = preg_replace('/^0+/', '', $reference->epage);
	
	print_r($reference);
	
	if (isset($reference->volume)
		&& isset($reference->issue)
		&& isset($reference->spage)
		&& isset($reference->year)
		&& isset($reference->issn)
		)
	{
		if (isset($data[$reference->volume]))
		{
			$pdf_filename = 'pdfs/ERJ' . $reference->volume . '(' . $reference->issue . ')' . $reference->year . '.pdf';
	
			//$article_pdf = $reference->spage . '.pdf';
			//$article_pdf = 'output/' . 'S' . $reference->issn . '(' . substr($reference->year, 2, 2) . ')'  . str_pad($reference->volume, 2, '0', STR_PAD_LEFT) . str_pad($reference->spage, 4, '0', STR_PAD_LEFT) . '.pdf';
			$article_pdf = 'output/' . 'S' . $reference->issn . $reference->year . str_pad($reference->volume, 4, '0', STR_PAD_LEFT) . str_pad($reference->spage, 5, '0', STR_PAD_LEFT) . '.pdf';
			
			
			$pdfs[] = str_replace('output/', '', $article_pdf);
			
			$sql .= "UPDATE names SET pdf='http://bionames.org/archive/issn/0286-9810/" . str_replace('output/', '', $article_pdf) . "' WHERE issn='0286-9810' AND volume=" . $reference->volume . " AND spage=" . $reference->spage . ";\n";
			
			if (file_exists($article_pdf) && !$force)
			{
			}
			else
			{		
				$from = $data[$reference->volume][$reference->issue][$reference->spage];
				if (isset($reference->epage))
				{
					$to = $from + ($reference->epage - $reference->spage);
				}
				else
				{
					$to = $from;
				}
	
				$command = 'gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER '
					. ' -dFirstPage=' . $from . ' -dLastPage=' . $to
					. ' -sOutputFile=\'' . $article_pdf . '\' \'' .  $pdf_filename . '\'';
	
				echo $command . "\n";

				system($command);
		
				// pdf_add_xmp($reference, $article_pdf);
			}
		}
	}	
	
	
}


$filename = '';
if ($argc < 2)
{
	echo "Usage: extract.php <RIS file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'import');


// PDF dump
foreach ($pdfs as $pdf)
{
	echo "issn/0286-9810/" . $pdf . "\thttp://bionames.org/archive/issn/0286-9810/" . $pdf . "\n";
}

echo $sql;




?>