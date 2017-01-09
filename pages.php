<?php

// generate data for pages

require_once(dirname(__FILE__) . '/ris.php');

$data = array();

//----------------------------------------------------------------------------------------
function import($reference)
{
	global $data;

	//if ($reference->volume < 55) return;
	if (!in_array($reference->volume, array(45,46,47,48,49,50,51,52,53,54))) return;
	
	if (isset($reference->volume)
		&& isset($reference->issue)
		&& isset($reference->spage)
		&& isset($reference->year)
		&& isset($reference->issn)
		)
	{
		if (!isset($data[$reference->volume]))
		{
			$data[$reference->volume] = array();
		}
		if (!isset($data[$reference->volume][$reference->issue]))
		{
			$data[$reference->volume][$reference->issue] = array();
		}
		$data[$reference->volume][$reference->issue][] = preg_replace('/^0+/', '', $reference->spage);
	}
	
	
}


$filename = '';
if ($argc < 2)
{
	echo "Usage: pages.php <RIS file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'import');

//print_r($data);

foreach ($data as $volume => $issues)
{
	foreach ($issues as $issue => $pages)
	{
		sort($pages);
		
		foreach ($pages as $page)
		{
			echo join("\t", array($volume, $issue, $page)) . "\n";
		}
	}
}


?>