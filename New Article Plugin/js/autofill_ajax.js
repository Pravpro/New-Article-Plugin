 /* This file allows the Plugin to retrieve all the information about a specific article 
  * using the pmid entered. Uses the WP Pubmed Reflist Plugin files to process the necessary info.
  * 
  * Dependency: WP Pubmed Reflist Plugin
  */ 

jQuery(document).ready(function($) {	
	// To make the $ work for jQuery.
	
	function convertMonthToNumber(month) {
		return new Date(Date.parse(month +" 1, 2012")).getMonth()+1;
	}
	
	$(document).on( 'click', '#autofill_btn', function() {
		
		var pmid = $('#PMID').val();
		
		$.ajax({
			type : 'post',
			url : ajaxurl,
			data : {
				'action' : 'pmid_autofill',
				'pubmedID': pmid
			},
			success : function( response ) {
			// Set all respective fields with appropriate data
				var pubInfo = JSON.parse(response);
				
				// Set Date Completed
				var dateCompleted = pubInfo.DateCompleted;
				$("#date_completed").val(dateCompleted);
				
				// Set Date Revised
				var dateRevised = pubInfo.DateRevised;
				$("#date_revised").val(dateRevised);
				
				// Set the article URL
				var articleURL = "https://www.ncbi.nlm.nih.gov/pubmed/" + pmid;
				$("#article_url").val(articleURL);
				
				// Set Article Title
				var articleTitle = pubInfo.Title;
				$("#article_title").val(articleTitle);
				
				// Set Article Authors
				var articleAuthors = pubInfo.Authors;
				$("#article_authors").val(articleAuthors);
				
				//Set the article date
				var articleDate = pubInfo.ArticleDate;
				$("#article_date").val(articleDate);
				
				// Set the pagination
				var pages = pubInfo.Pages;
				$("#pages").val(pages);
				
				// Set the Article Abstract
				var abstract = pubInfo.Abstract;
				$("#abstract").val(abstract);
				
				// Set the Journal Volume
				var volume = pubInfo.Volume;
				$("#journal_volume").val(volume);
				
				// Set Article Affiliation
				var affiliation = pubInfo.Affiliations;
				$("#article_affiliation").val(affiliation);
				
				// Set the Journal Issue
				var issue = pubInfo.Issue;
				$("#issue").val(issue);
				
				// Set the Journal Title
				var journalTitle = pubInfo.JournalTitle
				$("#journal_title").val(journalTitle)
				
				// Set the Journal Year
				var year = pubInfo.JournalYear;
				$("#year").val(year);
				
				// Set the Journal citation
				var journal_citation = pubInfo.JournalCitation;
				$("#journal_citation").val(journal_citation);
				
				// Set the Journal Abbreviation 
				var journalAb = pubInfo.JournalAb;
				$("#journal_ab").val(journalAb);
				
				// Set the Journal Day
				var journal_day = pubInfo.JournalDay;
				$("#journal_day").val(journal_day);
				
				// Set the Journal Month
				var journal_month = pubInfo.JournalMonth;
				var journal_month_num = convertMonthToNumber(journal_month);
				if (journal_month_num.toString().length == 1) {
					journal_month_num = '0' + journal_month_num;
				}
				$("#journal_month").val(journal_month_num);
				
				// Set the Journal Date if there is one
				if(journal_day != '' && journal_month != '' && year != '') {
					$("#journal_date").val(year + '-' + journal_month_num + '-' + journal_day);
				} else if (journal_day == '' && journal_month != '' && year != '') {
					$("#journal_date").val(year + '-' + journal_month_num + '-01');
				} else if (journal_day == '' && journal_month == '' && year != '') {
					$("#journal_date").val(year + '-01-01');
				}
				
				
			}
		});

		return false;
	});
});
