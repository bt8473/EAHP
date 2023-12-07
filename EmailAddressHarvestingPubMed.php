<?php

/**
 * @author    Brendan Thomas
 * @version   1.0
 *
 * This script illustrates the technique for e-mail address harvesting 
 * from PubMed discussed in:
 * Thomas B. "E-mail Address Harvesting on PubMed - A Call for Responsible
 * Handling of E-mail Addresses." Mayo Clinic Proceedings. 2011. 86(4):362.
 * 
 */

class PubMedQuery {
    
    private $query;
    private $searchParameters;
    private $baseSearchURL = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?';
    private $searchURL;
    private $fetchParameters;
    private $baseFetchURL = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?';
    private $fetchURL;
    private $searchResults;
    private $fetchResults;
    private $matches =  array();
    private $matchRegex = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/';
    private $emailAddresses;

    public function __construct($query) {
        $this->query = $query;
    }
    
    public function setSearchParameters() {
        $this->searchParameters = array(
            'db'         => 'pubmed',
            'term'       => $this->query,
            'retmode'    => 'xml',
            'retstart'   => '0',
            'retmax'     => '1000',
            'usehistory' => 'y'
        );
    }
        
    public function getSearchParameters() {
        return $this->searchParameters; 
    } 

    public function setFetchParameters() {
        $this->fetchParameters = array(
            'db'        => 'pubmed',
            'retmax'    => '1000',
            'query_key' => (string) $this->searchResults->QueryKey,
            'WebEnv'    => (string) $this->searchResults->WebEnv
        );
    }
   
    public function getFetchParameters() {
        return $this->fetchParameters; 
    } 
    
    public function setSearchURL() {
        $this->searchURL = $this->baseSearchURL . http_build_query($this->getSearchParameters());
    }

    public function getSearchURL() {
        return $this->searchURL; 
    }
 
    public function setFetchURL() {
        $this->fetchURL = $this->baseFetchURL . http_build_query($this->getFetchParameters());
    }

    public function getFetchURL() {
        return $this->fetchURL; 
    }
           
    public function setSearchResults() {
        $this->setSearchParameters();  
        $this->setSearchURL();
        $this->searchResults = simplexml_load_file($this->getSearchURL());
    }

    public function getSearchResults() {
        $this->setFetchParameters();
        $this->setFetchURL();
        return file_get_contents($this->getFetchURL()); 
    }

    public function setEmailAddresses() {
        preg_match_all($this->matchRegex, $this->getSearchResults(), $this->matches);
        $this->emailAddresses = array_unique(array_values($this->matches[0]));
    }

    public function getEmailAddresses() {
        $this->setSearchResults();
        $this->getSearchResults();
        $this->setEmailAddresses();
        return $this->emailAddresses;
    }
}

//Example using search term "psoriasis"
$query  = new PubMedQuery('psoriasis');
echo implode('<br />', $query->getEmailAddresses());

?>