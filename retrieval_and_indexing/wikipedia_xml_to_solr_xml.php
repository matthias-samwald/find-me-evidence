<?php
require_once('./lib/chunk/class.chunk.php');
require_once('./lib/wikipedia_parser/wikiParser.class.php');

$mediawiki_converter = new wikiParser();

$start = microtime(true);
$processed_entries = 0;
$successfully_processed_entries = 0;

// Iterate through XML files in the directory..
$handle = opendir('wikipedia');
while (false !== ($file = readdir($handle))){
	$extension = strtolower(substr(strrchr($file, '.'), 1));
	if($extension == "xml"){

		$file = new Chunk($file, array('element' => 'page', 'path' => './wikipedia/', 'chunkSize' => 3000));

		while ($xml_string = $file->read()) {
			$processed_entries++;
			// print mb_detect_encoding($xml);
			
			try {
				$article = new SimpleXMLElement($xml_string);
			}
			catch (Exception $e) {
    			echo 'Caught exception: ',  $e->getMessage(), "\n";
    			continue;
			}

			// Fetch article title
			$article_title = $article->title;
			$url_id = urlencode(str_replace(" ", "_" , $article_title));
			
			// Check if this is actually a redirect
			$redirect = $article->redirect['title'];
			if ($redirect != "")  {
				print "Skipping $article_title because it is a redirect to $redirect \n";
				continue;
			}

			// Fetch Wikipedia article code
			$wikipedia_code = $article->revision->text;
			if ($wikipedia_code == "") continue;
			
			// Get raw text (convert to HTML, then strip HTML tags)
			$article_text_without_wiki_markup = strip_tags($mediawiki_converter->parse($wikipedia_code));
			
			// Strip left-over wiki syntax that was failed to be eliminated in prior steps: {{...}}
			$article_text_without_wiki_markup = preg_replace("/\{\{([^\}]+)\}\}/", "", $article_text_without_wiki_markup);
			
			// Replace abbreviations with expanded forms
			// Currently disabled because it does not work with (long) Wikipedia article code
			/*
			$abbreviation_expander_output = trim(shell_exec('java ExtractAbbrev "' . addslashes($article_text_without_wiki_markup) . '"'));
			$abbreviation_expander_output_lines = explode("\n", $abbreviation_expander_output);
			foreach ($abbreviation_expander_output_lines as $line) {
				if (trim($line) == "") continue;
				$abbreviation_mapping = explode("\t", $line);
				$wikipedia_code = str_replace(" " . $abbreviation_mapping[0], " " . $abbreviation_mapping[1], $article_text_without_wiki_markup);
			}
			*/
			
			// Fetch timestamp (represent it as "date created")
			$date_created = 	$article->revision->timestamp;
			
			$output_file = fopen("wikipedia_solr_xml_output/wikipedia_" . $url_id. ".xml", "w");
			fputs($output_file, "<add><doc>\n");

			fputs($output_file, "<field name='title'>" . htmlspecialchars($article_title) . "</field>\n");
			fputs($output_file, "<field name='body'>" . htmlspecialchars($article_text_without_wiki_markup) . "</field>\n");
			fputs($output_file, "<field name='data_source_name'>Wikipedia</field>\n");
			fputs($output_file, "<field name='dateCreated'>" . $date_created . "</field>\n");
			fputs($output_file, "<field name='id'>http://en.wikipedia.org/wiki/" . $url_id . "</field>\n");
			fputs($output_file, "<field name='mimeType'>text/plain</field>\n");

			fputs($output_file, "</doc></add>");
			fclose($output_file);

			$successfully_processed_entries++;
			print(" " . $successfully_processed_entries . "\n");
			// if ($successfully_processed_entries == 10) break;
		}

	}
}

print "\n Processed $successfully_processed_entries out of $processed_entries records successfully. ";
$end = (microtime(true) - $start) ;
print "Completed in {$end} seconds.";

?>