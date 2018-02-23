<?php
/* This file is borrowed from WP Pubmed Reflist Plugin for wordpress and is under the
 * GPL2 license. This file has been modified where indicated and is being used as part of
 * this plugin to handle a Pubmed XML object returned from one of ncbi's API.
 * 
 * We or the original author of this file is not liable for any warranty on the code.
 */

/*
Convert SimpleXML objects returned by pubmed Efetch
*/

class PMIDeFetch{

	var $pmidObj;
	var $error_msg;
	
	function __construct($pmidObj){		
		$this->pmidObj = $pmidObj;

	}
	
	function article(){
		if (!is_object($this->pmidObj)) return false;
		return $this->pmidObj->MedlineCitation->Article;
	}

	function date_completed() {
		$date = $this->pmidObj->MedlineCitation->DateCompleted;
		$date_str = $date->Year . "-" . $date->Month . "-" . $date->Day;
		return $date_str;
	}
	
	function date_revised() {
		$date = $this->pmidObj->MedlineCitation->DateRevised;
		$date_str = $date->Year . "-" . $date->Month . "-" . $date->Day;
		return $date_str;
	}
	
	/*
	return authors as an array.
	*/
	function authors(){
		$authors = array();
		$tags = array('LastName', 'Initials', 'Suffix', 'CollectiveName');
		if(is_object($this->article())){
			if(is_object($this->article()->AuthorList->Author)){
			foreach($this->article()->AuthorList->Author as $auth){
				foreach ($tags as $tag){
					$$tag = '';
					if(isset($auth->$tag)) $$tag = (string)$auth->$tag;
				}
				$cite_name = 'Author unknown';
				if($LastName != "") $cite_name = trim("$LastName, $Initials $Suffix");
				if($CollectiveName != "") $cite_name = "$CollectiveName";
				
				$authors[] = array(
					'Last' => $LastName,
					'Initials' => $Initials,
					'CollectiveName' => $CollectiveName,
					'Cite_name' => $cite_name
				);
			}
			}else{
				$authors[0] = array(
						'Last' => 'Anon.',
						'Initials' => '',
						'CollectiveName' => '',
						'Cite_name' => 'Anon.'			
				); 
			}
		}
		return $authors;
	}

	function title(){
		if (!$this->article()){
			return ($this->error_msg);
		}
		return (string)$this->article()->ArticleTitle;
	}
	
	// Modified funciton
	function journal_abbreviation(){
		return (string)$this->article()->Journal->ISOAbbreviation;
	}
	
	// Added function
	function journal_title(){
		return (string)$this->article()->Journal->Title;
	}
	
	// Added function
	function journal_citation() {
		$citation = $this->journal_abbreviation() ." ";
		
		$date = (string)$this->journal_date()->MedlineDate;
		if($date == '') {
			$date = (string)$this->journal_date()->Year . " " . (string)$this->journal_date()->Month . " " . (string)$this->journal_date()->Day;
		}
		$date = trim($date);
		
		$citation .= $date . "; ";
		$citation .= $this->volume() . "(" . $this->issue() . ")";
		$citation .= ":" . $this->pages();
		
		return $citation;
	}
	
	// Added function
	function affiliations() {
		return (string)$this->article()->AuthorList->Author->AffiliationInfo->Affiliation;
	}

	function volume(){
		return (string)$this->article()->Journal->JournalIssue->Volume;
	}
	function issue(){
		return (string)$this->article()->Journal->JournalIssue->Issue;
	}

	function pmid(){
		return (string)$this->pmidObj->MedlineCitation->PMID;
	}

	function pages(){
		return (string)$this->article()->Pagination->MedlinePgn;
	}

	// Modified function
	function journal_date(){
		return $this->article()->Journal->JournalIssue->PubDate;
	}
	
	// Added function
	function article_date() {
		$date = $this->article()->ArticleDate;
		$article_date_str = '';
		if($date) {
			$article_date_str .= $date->Year . "-" . $date->Month . "-" . $date->Day;
		}
		return $article_date_str;
	}
	
	/*
	return abstract text as string. Can't use abstract for the method name because it is a reserved word.
	*/
	function abstract_text(){
		return (string)$this->article()->Abstract->AbstractText;
	}
	
	function pubmed_data(){
		return $this->pmidObj->PubmedData;
	}
	
	function xrefs(){
		if (!is_object($this->pmidObj)) return false;
		$arr = array();
		if(is_object ($this->pubmed_data())){
			$xrefs = $this->pubmed_data()->ArticleIdList;
			foreach ($xrefs->ArticleId as $xref){
				$arr[(string)$xref->attributes()] = (string)$xref;
			}
		}
		return $arr;
	}
	
	function mesh(){
		$arr = array();
		if (is_object($this->pmidObj->PubmedArticle->MedlineCitation->MeshHeadingList)){
			$mesh_list = $this->pmidObj->PubmedArticle->MedlineCitation->MeshHeadingList; #print_r($mesh_list);
			foreach($mesh_list->MeshHeading as $mesh_item){
				
				$base_heading = (string)$mesh_item->DescriptorName;
				switch ($mesh_item->QualifierName->count()){
					case 0:
						$arr[] = "$base_heading";
						break;				
					case 1:
						$arr[] = "$base_heading/".(string)$mesh_item->QualifierName;
						break;
					default:
						foreach ($mesh_item->QualifierName as $qualifier){
							$arr[] = "$base_heading/$qualifier";
						}
				}			
			}
		}
		return $arr;
	}
	function epub(){
		$epub = '';
		#echo "<pre>".print_r($this->pmidObj->PubmedData, true)."</pre>";
		if(is_object($this->pmidObj->PubmedData->History)){
			#echo __METHOD__."<br>";
			foreach($this->pmidObj->PubmedData->History as $pubMedDate){
				foreach($pubMedDate as $item){
					if((string)$item->attributes() == 'epublish'){
						$epub = "Epub ".$item->Year.'/'.$item->Month.'/'.$item->Day;
					}
				}
			}
		}
		return $epub;
	}
	
	function citation(){
		$authorlist = array();
		foreach($this->authors() as $auth){
			$authorlist[] = $auth['Cite_name'];
		}
		return array(
			'PMID'    		=> $this->pmid(), //not needed
			'ArticleDate'	=> $this->article_date(), //done
			'DateCompleted' => $this->date_completed(), //done
			'DateRevised'	=> $this->date_revised(), //done
			'Authors' 		=> implode(', ', $authorlist), //done
			'AuthorList' 	=> $authorlist, //not needed
			'JournalYear'   => (string)$this->journal_date()->Year, //done
			'JournalMonth'  => (string)$this->journal_date()->Month, //done
			'JournalDay'	=> (string)$this->journal_date()->Day, //done
			'JournalCitation'=> $this->journal_citation(), //done
			'Title'    		=> $this->title(), //done
			'JournalAb' 	=> $this->journal_abbreviation(), //done
			'JournalTitle'	=> $this->journal_title(), //done
			'Volume'   		=> $this->volume(), //done
			'Issue'   		=> $this->issue(), //done
			'Pages'   		=> $this->pages(), //done
			'Abstract' 		=> $this->abstract_text(), //done
			'Affiliations'	=> $this->affiliations(), //done
			'xrefs' 		=> $this->xrefs(), //
			'EPub'    		=> $this->epub() //not needed?
		);
	}
	
	function dump(){
		print_r($this->pmidObj);
	}
}