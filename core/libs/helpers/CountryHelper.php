<?php


class CountryHelper {

	
	
	
	
	
	
	
	
	
	/**
	 * Converts a lang code to the corresponding ISO 3166 country
	 * 
	 * @param string $langCode
	 */
	static function langToCountry ( $langCode )
	{
		switch ( $langCode )
		{
			case 'af' 		: return 'za';
			case 'sq' 		: return 'al';
			case 'ar-dz' 	: return 'dz';
			case 'de' 		: return 'de';
			case 'de-at' 	: return 'at';
			case 'de-li' 	: return 'li';
			case 'de-lu' 	: return 'lu';
			case 'de-ch' 	: return 'ch';
			case 'en' 		: return 'gb';
			case 'en-us' 	: return 'us';
			case 'en-ie' 	: return 'ie';
			case 'en-za' 	: return 'za';
			case 'en-bz' 	: return 'bz';
			case 'en-gb' 	: return 'gb';
			case 'ar-sa' 	: return 'sa';
			case 'ar-bh' 	: return 'bh';
			case 'ar-ae' 	: return 'ae';
			case 'en-au' 	: return 'au';
			case 'nl-be' 	: return 'be';
			case 'be' 		: return 'by';
			case 'bg' 		: return 'bg';
			case 'en-ca' 	: return 'ca';
			case 'ca' 		: return 'es';
			case 'zh' 		: return 'cn';
			case 'zh-hk' 	: return 'hk';
			case 'zh-cn' 	: return 'cn';
			case 'zh-sg' 	: return 'sg';
			case 'zh-tw' 	: return 'tw';
			case 'ko' 		: return 'kr';
			case 'hr' 		: return 'hr';
			case 'da' 		: return 'da';
			case 'ar-eg' 	: return 'eg';
			case 'es' 		: return 'es';
			case 'es-ar' 	: return 'ar';
			case 'es-bo' 	: return 'bo';
			case 'es-cl' 	: return 'cl';
			case 'es-co' 	: return 'co';
			case 'es-cr' 	: return 'cr';
			case 'es-sv' 	: return 'sv';
			case 'es-ec' 	: return 'ec';
			case 'es-gt' 	: return 'gt';
			case 'es-hn' 	: return 'hn';
			case 'es-mx' 	: return 'mx';
			case 'es-ni' 	: return 'ni';
			case 'es-pa' 	: return 'pa';
			case 'es-py' 	: return 'py';
			case 'es-pe' 	: return 'pe';
			case 'es-pr' 	: return 'pr';
			case 'en-tt' 	: return 'tt';
			case 'es-uy' 	: return 'uy';
			case 'es-ve' 	: return 've';
			case 'et' 		: return 'ee';
			case 'sx' 		: return 'ee';
			case 'fo' 		: return 'fo';
			case 'fi' 		: return 'fi';
			case 'fr' 		: return 'fr';
			case 'fr-fr' 	: return 'fr';
			case 'fr-be' 	: return 'be';
			case 'fr-ca' 	: return 'ca';
			case 'fr-lu' 	: return 'lu';
			case 'fr-ch' 	: return 'ch';
			case 'gd' 		: return 'gb';
			case 'el' 		: return 'gr';
			case 'he' 		: return 'il';
			case 'nl' 		: return 'nl';
			case 'hu' 		: return 'hu';
			case 'in' 		: return 'id';
			case 'hi' 		: return 'in';
			case 'fa' 		: return 'ir';
			case 'ar-iq' 	: return 'iq';
			case 'is' 		: return 'is';
			case 'it' 		: return 'it';
			case 'it-ch' 	: return 'ch';
			case 'en-jm' 	: return 'jm';
			case 'ja' 		: return 'jp';
			case 'ar-jo' 	: return 'jo';
			case 'ar-kw' 	: return 'kw';
			case 'lv' 		: return 'lv';
			case 'ar-lb' 	: return 'lb';
			case 'lt' 		: return 'lt';
			case 'ar-ly' 	: return 'ly';
			case 'mk' 		: return 'mk';
			case 'ms' 		: return 'my';
			case 'mt' 		: return 'mt';
			case 'ar-ma' 	: return 'ma';
			case 'en-nz' 	: return 'nz';
			case 'no' 		: return 'no';
			case 'ar-om' 	: return 'om';
			case 'pl' 		: return 'pl';
			case 'pt' 		: return 'pt';
			case 'pt-br' 	: return 'br';
			case 'ar-qa' 	: return 'qa';
			case 'ro' 		: return 'ro';
			case 'ro-mo' 	: return 'ro';
			case 'ru' 		: return 'ru';
			case 'ru-mo' 	: return 'md';
			case 'sr' 		: return 'rs';
			case 'sk' 		: return 'sk';
			case 'sl' 		: return 'si';
			case 'sv' 		: return 'se';
			case 'sv-fi' 	: return 'fi';
			case 'ar-sy' 	: return 'sy';
			case 'th' 		: return 'th';
			case 'ts' 		: return 'za';
			case 'tn' 		: return 'za';
			case 'ar-tn' 	: return 'tn';
			case 'tr' 		: return 'tr';
			case 'uk' 		: return 'ua';
			case 'vi' 		: return 'vn';
		}
		return false ;
	}
	
	
}



?>