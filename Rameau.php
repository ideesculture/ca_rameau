<?php
	// A NOTER : l'utilisation de Rameau nécessite qu'un libellé de liste puisse avoir 0 caractères
	
	
//    const Rameau_SEARCH_TERM_QUERY = "http://data.bnf.fr/sparql?default-graph-uri=&query=PREFIX+skos%3A+%3Chttp%3A%2F%2Fwww.w3.org%2F2004%2F02%2Fskos%2Fcore%23%3E%0D%0APREFIX+foaf%3A+%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F%3E%0D%0ASELECT+DISTINCT+%3Fsujet+%3Flabel%0D%0AWHERE+%7B%0D%0A++%3Fsujet+a+skos%3AConcept+.%0D%0A++%3Fsujet+skos%3AprefLabel+%3Flabel+.%0D%0A++FILTER+%28regex%28%3Flabel%2C+%22###%22%2C+%22i%22%29%29%0D%0A++FILTER+NOT+EXISTS+%7B%0D%0A++%3Fsujet+foaf%3Afocus+%3FquelqueChose.%0D%0A++%7D%0D%0A%7D+%0D%0ALIMIT+100&format=json&timeout=0&should-sponge=";

/** ---------------------------------------------------------------------
 * app/lib/core/Plugins/InformationService/Wikipedia.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2015 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage InformationService
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */

/**
 *
 */

require_once __CA_LIB_DIR__ . "/Plugins/IWLPlugInformationService.php";
require_once __CA_LIB_DIR__ . "/Plugins/InformationService/BaseInformationServicePlugin.php";

global $g_information_service_settings_Rameau;
$g_information_service_settings_Rameau = array(
    'lang' => array(
        'formatType' => FT_TEXT,
        'displayType' => DT_FIELD,
        'default' => 'en',
        'width' => 30, 'height' => 1,
        'label' => _t('Rameau'),
        'description' => _t('...'),
    ),
);

class WLPlugInformationServiceRameau extends BaseInformationServicePlugin implements IWLPlugInformationService
{
    # ------------------------------------------------
    static $s_settings;
    # ------------------------------------------------
    /**
     *
     */
    public function __construct()
    {
        global $g_information_service_settings_Rameau;

        WLPlugInformationServiceRameau::$s_settings = $g_information_service_settings_Rameau;
        parent::__construct();
        $this->info['NAME'] = 'Rameau';

        $this->description = "Mots matière Rameau";
    }
    # ------------------------------------------------
    /**
     * Get all settings settings defined by this plugin as an array
     *
     * @return array
     */
    public function getAvailableSettings()
    {
        return WLPlugInformationServiceRameau::$s_settings;
    }
    # ------------------------------------------------
    # Data
    # ------------------------------------------------
    /**
     * Perform lookup on Wikipedia-based data service
     *
     * @param array $pa_settings Plugin settings values
     * @param string $ps_search The expression with which to query the remote data service
     * @param array $pa_options Lookup options (none defined yet)
     * @return array
     */
    public function lookup($pa_settings, $ps_search, $pa_options = null)
    {
        // support passing full wikipedia URLs
        if (isURL($ps_search)) {
            $ps_search = self::getPageTitleFromURI($ps_search);
        }
        $vs_lang = caGetOption('lang', $pa_settings, 'en');

        // readable version of get parameters
        $va_get_params = array(
            'action' => 'query',
            'generator' => 'search', // use search service as generator for page service
            'gsrsearch' => urlencode($ps_search),
            'gsrlimit' => 50, // max allowed by mediawiki
            'gsrwhat' => 'nearmatch', // search for near matches in titles
            'prop' => 'info',
            'inprop' => 'url',
            'format' => 'json',
        );

        $vs_Rameau_sparql = "https://data.idref.fr/sparql?query=PREFIX%20foaf%3A%20%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F%3E%20%0APREFIX%20owl%3A%20%3Chttp%3A%2F%2Fwww.w3.org%2F2002%2F07%2Fowl%23%3E%20%0APREFIX%20isni%3A%20%3Chttp%3A%2F%2Fisni.org%2Fontology%23%3E%20%0APREFIX%20bnf-onto%3A%20%3Chttp%3A%2F%2Fdata.bnf.fr%2Fontology%2Fbnf-onto%2F%3E%20%0APREFIX%20skos%3A%20%3Chttp%3A%2F%2Fwww.w3.org%2F2004%2F02%2Fskos%2Fcore%23%3E%20%0APREFIX%20rdfs%3A%20%3Chttp%3A%2F%2Fwww.w3.org%2F2000%2F01%2Frdf-schema%23%3E%20%0APREFIX%20bnf-onto%3A%20%3Chttp%3A%2F%2Fdata.bnf.fr%2Fontology%2Fbnf-onto%2F%3E%20%0A%0ASELECT%20DISTINCT%20%3Fsujet%20%3Flabel%0AWHERE%20%7B%0A%20%20%3Fsujet%20skos%3AprefLabel%20%3Flabel%20.%0A%20%20FILTER%20(regex(%3Flabel%2C%20%22%5C%5Cb###%5C%5Cb%22%2C%20%22i%22)).%0A%20%20FILTER%20NOT%20EXISTS%20%7B%0A%20%20%3Fsujet%20foaf%3Afocus%20%3FquelqueChose.%0A%20%20%7D%0A%7D%20%0ALIMIT%20500";

        $vs_content = caQueryExternalWebservice(
            //$vs_url = 'https://' . $vs_lang . '.wikipedia.org/w/api.php?' . caConcatGetParams($va_get_params)
            str_replace("###", urlencode($ps_search), $vs_Rameau_sparql)
        );

        $va_content = @json_decode($vs_content, true);
        //var_dump($vs_content);
        $vt_xml = new SimpleXMLElement($vs_content);
        $vedettes = [];
        foreach($vt_xml->results->result as $result) {
	        $vedette= [];
	        foreach($result->binding as $binding) {
		        //var_dump($binding->attributes()->name . "");
		        if(isset($binding->uri)) {
			    	$vedette[$binding->attributes()->name . ""] = $binding->uri."";    
		        } else {
			    	$vedette[$binding->attributes()->name . ""] = $binding->literal."";    
		        }
		        
	        }
	        $vedettes[]=$vedette;
        }

        foreach ($vedettes as $vedette) {
            $va_return['results'][] = array(
                'label' =>$vedette["label"], //. ' [' . $va_result['fullurl'] . ']',
                'url' => $vedette["sujet"],
                'idno' => str_replace(["http://www.idref.fr/","/id"], "", $vedette["sujet"]),
            );
        }

        return $va_return;
    }
    # ------------------------------------------------
    /**
     * Fetch details about a specific item from a Wikipedia-based data service for "more info" panel
     *
     * @param array $pa_settings Plugin settings values
     * @param string $ps_url The URL originally returned by the data service uniquely identifying the item
     * @return array An array of data from the data server defining the item.
     */
    public function getExtendedInformation($pa_settings, $ps_url)
    {
        $vs_display = "<p><a href='$ps_url' target='_blank'>$ps_url</a></p>";

        $va_info = "..."; //$this->getExtraInfo($pa_settings, $ps_url);

        $vs_display .= "<div style='float:right; margin: 10px 0px 10px 10px;'>...</div>";
        $vs_display .= "...";

        return array('display' => $vs_display);
    }
    # ------------------------------------------------
    public function getExtraInfo($pa_settings, $ps_url)
    {
        $vs_lang = caGetOption('lang', $pa_settings, 'en');

        // readable version of get parameters
        $va_get_params = array(
            'action' => 'query',
            'titles' => self::getPageTitleFromURI($ps_url),
            'prop' => 'pageimages|info|extracts',
            'inprop' => 'url',
            'piprop' => 'name|thumbnail',
            'pithumbsize' => '200px',
            'format' => 'json',
        );

        $vs_content = caQueryExternalWebservice(
            'https://' . $vs_lang . '.wikipedia.org/w/api.php?' . caConcatGetParams($va_get_params)
        );

        $va_content = @json_decode($vs_content, true);
        if (!is_array($va_content) || !isset($va_content['query']['pages'])) {
            return array();
        }

        // the top two levels are 'query' and 'pages'
        $va_results = $va_content['query']['pages'];

        if (sizeof($va_results) > 1) {
            Debug::msg('[Wikipedia] Found multiple results for page title ' . self::getPageTitleFromURI($ps_url));
        }

        if (sizeof($va_results) == 0) {
            Debug::msg('[Wikipedia] Couldnt find any results for page title ' . self::getPageTitleFromURI($ps_url));
            return null;
        }

        $va_result = array_shift($va_results);
        // try to extract the first paragraph (usually an abstract/summary of the article)
        $vs_abstract = preg_replace("/\s+<p><\/p>\s+<h2>.+$/ms", "", $va_result['extract']);

        return array(
            'image_thumbnail' => $va_result['thumbnail']['source'],
            'image_thumbnail_width' => $va_result['thumbnail']['width'],
            'image_thumbnail_height' => $va_result['thumbnail']['height'],
            'image_viewer_url' => $va_result['fullurl'] . '#/media/File:' . $va_result['pageimage'],
            'title' => $va_result['title'],
            'pageid' => $va_result['page_id'],
            'fullurl' => $va_result['fullurl'],
            'canonicalurl' => $va_result['canonicalurl'],
            'extract' => $va_result['extract'],
            'abstract' => $vs_abstract,
        );
    }
    # ------------------------------------------------
    private static function getPageTitleFromURI($ps_uri)
    {
        if (preg_match("/\/([^\/]+)$/", $ps_uri, $va_matches)) {
            return $va_matches[1];
        }

        return false;
    }
    # ------------------------------------------------
}
