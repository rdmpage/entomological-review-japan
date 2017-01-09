<?php

require_once(dirname(__FILE__) . '/ris.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Inject XMP metadata into PDF
 *
 * We inject XMP metadata using Exiftools
 *
 * @param reference Reference 
 * @param pdf_filename Full path of PDF file to process
 * @param tags Tags to add to PDF
 *
 */
function pdf_add_xmp ($reference, $pdf_filename)
{
	global $config;
	
	// URL
	if (isset($reference->url))
	{
		$command = "exiftool" .  " -XMP:URL=" . escapeshellarg($reference->url) . " " . $pdf_filename;	
		system($command);
	}
	
	// Mendeley will overwrite XMP-derived metadata with CrossRef metadata if we include this
	if (isset($reference->doi))
	{
		$command = "exiftool" .  " -XMP:DOI=" . escapeshellarg($reference->doi) . " " . $pdf_filename;
		system($command);
	}
	
	// Title and authors
	$command = "exiftool" .  " -XMP:Title=" . escapeshellarg($reference->title) . " " . $pdf_filename;
	system($command);
	
	foreach ($reference->authors as $a)
	{
		$command = "exiftool" .  " -XMP:Creator+=" . escapeshellarg($a) . " " . $pdf_filename;
		system($command);
	}
	
	// Article
	if ($reference->genre == 'article')
	{
		$command = "exiftool" .  " -XMP:AggregationType=journal " . $pdf_filename;
		system($command);
		$command = "exiftool" .  " -XMP:PublicationName=" . escapeshellarg($reference->secondary_title) . " " . $pdf_filename;
		system($command);
		
		if (isset($reference->issn))
		{
			$command = "exiftool" .  " -XMP:ISSN=" . escapeshellarg($reference->issn) . " " . $pdf_filename;
			system($command);
		}
				
		$command = "exiftool" .  " -XMP:Volume=" . escapeshellarg($reference->volume) . " " . $pdf_filename;
		system($command);
		if (isset($reference->issue))
		{
			$command = "exiftool" .  " -XMP:Number=" . escapeshellarg($reference->issue) . " " . $pdf_filename;
			system($command);
		}
		$command = "exiftool" .  " -XMP:StartingPage=" . escapeshellarg($reference->spage) . " " . $pdf_filename;
		system($command);
		if (isset($reference->epage))
		{
			$command = "exiftool" .  " -XMP:EndingPage=" . escapeshellarg($reference->epage) . " " . $pdf_filename;
			system($command);
			$command = "exiftool" .  " -XMP:PageRange+=" . escapeshellarg($reference->spage. '-' . $reference->epage) . " " . $pdf_filename;
			system($command);
		}
	}
	
	$command = "exiftool" .  " -XMP:CoverDate=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
	system($command);
	$command = "exiftool" .  " -XMP:Date=" . escapeshellarg(str_replace("-", ":", $reference->date)) . " " . $pdf_filename;
	system($command);
	
	// cleanup
	if (file_exists($pdf_filename . '_original'))
	{
		unlink($pdf_filename . '_original');
	}
	
}	


$start = array(
	1 => array (1 => 1, 2=> 21),



	//5 => array (1 => 1, 2=> 201),

	//44 => array (1 => 1, 2=> 63), plates complicate things
	//45 => array (1 => 1, 2=> 81),
	//46 => array (1 => 1, 2=> 97),

	/*
	// broken 52 => array (1 => -1, 2=> 47),
	53 => array (1 => -1, 2=> 47),
	54 => array (1 => -1, 2=> 77),	
	
	55 => array (1 => -1, 2=> 69),
	56 => array (1 => -1, 2=> 39),
	57 => array (1 => -1, 2=> 107),
	58 => array (1 => -1, 2=> 105),
	59 => array (1 => -1, 2=> 133),

	60 => array (1 => -1, 2=> 99)
	61 => array (1 => 1, 2=> 97),
	62 => array (1 => 1, 2=> 117),
	63 => array (1 => 1, 2=> 71),
	64 => array (1 => 1, 2=> 115),
	
	65 => array (1 => 1, 2=> 201)
	*/
	
	);

//----------------------------------------------------------------------------------------
function import($reference)
{
	global $start;
	
	$force = true;
	$force = false;
	
	print_r($reference);
	
	if (isset($reference->volume)
		&& isset($reference->issue)
		&& isset($reference->spage)
		&& isset($reference->year)
		&& isset($reference->issn)
		)
	{
		if (isset($start[$reference->volume]))
		{
			$pdf_filename = 'pdfs/ERJ' . $reference->volume . '(' . $reference->issue . ')' . $reference->year . '.pdf';
	
			//$article_pdf = $reference->spage . '.pdf';
			//$article_pdf = 'output/' . 'S' . $reference->issn . '(' . substr($reference->year, 2, 2) . ')'  . str_pad($reference->volume, 2, '0', STR_PAD_LEFT) . str_pad($reference->spage, 4, '0', STR_PAD_LEFT) . '.pdf';
			$article_pdf = 'output/' . 'S' . $reference->issn . $reference->year . str_pad($reference->volume, 4, '0', STR_PAD_LEFT) . str_pad($reference->spage, 5, '0', STR_PAD_LEFT) . '.pdf';
			
			if (file_exists($article_pdf) && !$force)
			{
			}
			else
			{		
				$from = $reference->spage - $start[$reference->volume][$reference->issue] + 1;
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


?>