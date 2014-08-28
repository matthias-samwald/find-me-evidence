<?php
include_once ('config.php');
include_once ('functions.php');

$xml = get_solr ( $_GET ["id"] );

$title = xpath ( $xml, "doc/arr[@name='title']/str" );

?>


<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/themes/default/jquery.mobile-1.3.2.min.css">
<link rel="stylesheet" href="_assets/css/jqm-demos.css">
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
<link href="bricoleur.css" rel="stylesheet" type="text/css">
<script src="js/jquery.js"></script>
<script src="_assets/js/index.js"></script>
<script src="js/jquery.mobile-1.3.2.min.js"></script>
<title>FindMeEvidence: <?php print $title ?></title>
</head>
<body>
	<div data-role="page" id="main" data-theme="d">
		<div data-role="header" class="jqm-header" style="text-align: center; padding: 3px">
			<img src="images/findmeevidence-logo.png" alt="FindMeEvidence" />
		</div>
		<div data-role="content">
			<h1><?php print $title ?></h1>
			<p>
				<span class="data_source_name"><?php print xpath($xml, "doc/str[@name='data_source_name']"); ?></span>
				- <span><?php print substr(xpath($xml, "doc/date[@name='dateCreated']"), 0, 10) ?></span>
                               <?php
                               $authors = xpath($xml, "doc/arr[@name='author']/str", true);
                               $authorsString = implode(", ", $authors);
                               if ($authorsString != ""){
                                print(" - <span>" . $authorsString . "</span>");
                               }
                               ?>
			</p>
			<p style="line-height: 150%"><?php print xpath($xml, "doc/arr[@name='body']/str"); ?></p>

                <?php
                $id = xpath($xml, "doc/str[@name='id']");
                $persid = (string)xpath($xml, "doc/str[@name='persid']");
                $category = xpath($xml, "doc/arr[@name='category']/str");
                $citedin_count = xpath($xml, "doc/int[@name='citedin_count']");
//                $pdf_link = getPdfLink(substr($id, 35));
                $pmcid = (string)xpath($xml, "doc/str[@name='pmcid']");
                $date_release = (string)xpath($xml, "doc/date[@name='dateRelease']");

                switch ($category) {
                    case "Pubmed":                        
                        
                        $showPubReader = false;                        
                        if ($pmcid !== ""){                          
                            if ($date_release !== ""){
                                $time_now = time();
                                $timestamp_release = strtotime($date_release);
                                if ($timestamp_release < $time_now) {
                                    $showPubReader = true;
                                }
                            } else {                        
                                $showPubReader = true;
                            }
                        }
                        
                        if ($pmcid === "" || !$showPubReader){
                            echo '<p><a href="' . $id . '">View in PubMed</a></p>';
                        }                        
                        if ($showPubReader){
                            echo '<p><a href="http://www.ncbi.nlm.nih.gov/pmc/articles/' . $pmcid . '/?report=reader">PMC Fulltext</a></p>';
                        }
                        
                        if ($persid !== "") {
                            echo '<p><a href="http://dx.doi.org/' . $persid . '">View (via DOI)</a></p>';
                        }
//                        if ($pdf_link !== "") {
//                            echo '<p><img src="images/OA-icon.gif" alt=OA /> <a href="' . $pdf_link . '">PDF</a></p>';
//                        }
                        break;

                    default:
                        echo '<a href="' . $id . '">' . $id . '</a>';
                        break;
                }
                ?>                     

			<p>
				<a href="index.php" data-role="button" data-icon="back"
					data-rel="back">Go back</a>
			</p>
		</div>
		<div data-role="footer">
			<h4>The FindMeEvidence service comes without any warranty. Visit <a href="https://github.com/matthias-samwald/find-me-evidence">project website</a> for more information.</h4>
		</div>
	</div>
</body>