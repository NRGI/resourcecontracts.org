<?php
use Illuminate\Support\Facades\Lang;

/**
 * Get formatted file size
 *
 * @param $bytes
 *
 * @return string
 */
function getFileSize($bytes)
{
    switch ($bytes):
        case ($bytes >= 1073741824):
            $bytes = number_format($bytes / 1073741824, 2).' GB';
            break;
        case ($bytes >= 1048576):
            $bytes = number_format($bytes / 1048576, 2).' MB';
            break;
        case ($bytes >= 1024):
            $bytes = number_format($bytes / 1024, 2).' KB';
            break;
        case ($bytes > 1):
            $bytes = $bytes.' bytes';
            break;
        case ($bytes == 1):
            $bytes = $bytes.' byte';
            break;
    endswitch;

    return $bytes;
}

/**
 * Get md5 file hash value
 *
 * @param $file
 *
 * @return string
 */
function getFileHash($file)
{
    return hash_file('md5', $file);
}

/**
 * get language
 *
 * @param        $key
 * @param string $locale
 *
 * @return string
 *
 */
function _l($key, $locale = 'en')
{
    if (Lang::has($key)) {
        return trans($key, [], null, $locale);
    }
    $array = explode('.', $key);

    return end($array);
}


/**
 * Get S3 file url
 *
 * @param string $fileName
 *
 * @return mixed
 */
function getS3FileURL($fileName = '')
{
    return \Storage::disk('s3')
                   ->getDriver()
                   ->getAdapter()
                   ->getClient()
                   ->getObjectUrl(env('AWS_BUCKET'), $fileName);
}

/**
 * Get Language Name by code
 *
 * @param        $code
 * @param string $locale
 *
 * @return null
 */
function getLanguageName($code, $locale = 'en')
{
    $lang = trans('codelist/language', [], null, $locale);
    $lang = $lang['major'] + $lang['minor'];
    $code = strtolower($code);

    return isset($lang[$code]) ? $lang[$code] : null;
}

/**
 * Generate random number
 *
 * @param $length
 *
 * @return string
 */
function str_random_number($length)
{
    $number = '';

    for ($i = 0; $i < $length; $i++) {
        $number .= mt_rand(0, 9);
    }

    return $number;
}

/**
 * This function returns hit url based upon env variable Mturk Sandbox.
 *
 * @param $hitID
 *
 * @return string
 */
function hit_url($hitID)
{
    $subDomain = env('MTURK_SANDBOX') ? 'requestersandbox' : 'requester';

    return sprintf("https://%s.mturk.com/mturk/manageHIT?HITId=%s", $subDomain, $hitID);
}

/**
 * Trim array values
 *
 * @param $value
 *
 * @return array|string
 */
function trimArray($value)
{
    if (!is_array($value)) {
        return trim($value);
    }

    return array_map('trimArray', $value);
}

/**
 * Show discussion count and link
 *
 * @param        $discussions
 * @param        $discussion_status
 * @param        $contract_id
 * @param        $key
 * @param string $type
 *
 * @return string
 */
function discussion($discussions, $discussion_status, $contract_id, $key, $type = 'metadata')
{
    $count             = isset($discussions[$key]) ? $discussions[$key] : 0;
    $discussion_status = (isset($discussion_status[$key]) && $discussion_status[$key] == 1) ? true : false;

    if ($discussion_status == 1) {
        $status = '<span class="label label-success">('.$count.') '.trans('contract.resolved').'</span>';
    } else {
        $status = '<span class="label label-red pull-right">('.$count.') '.trans('contract.open').'</span>';
    }
    if ($count == 0) {
        $status = '<span class="label pull-right" style="background-color: #999">('.$count.') '.trans(
                'contract.open'
            ).'</span>';
    }

    return sprintf(
        '<a href="#" data-url="%s" data-loading="false" class="key-%s contract-discussion pull-right">%s</a>',
        route('contract.discussion', ['id' => $contract_id, 'type' => $type, 'key' => $key]),
        $key,
        $status
    );
}

/**
 * Get Language Url
 *
 * @param $code
 *
 * @return string
 */
function lang_url($code)
{
    $query = ['lang' => $code];

    return count(\Request::query()) > 0
        ? \Request::url().'/?'.http_build_query(array_merge(\Request::query(), $query))
        : \Request::fullUrl().'?'.http_build_query($query);
}

/**
 * Trans Array List
 *
 * @param array $codeList
 * @param       $path
 *
 * @return array
 */
function trans_array(array $codeList, $path)
{
    foreach ($codeList as $key => $code) {
        $codeList[$key] = _l($path.'.'.$code);
    }

    return $codeList;
}

/**
 * Get Category name by key
 *
 * @param string $key
 * @param bool   $lang
 *
 * @return string
 */
function getCategoryName($key = '', $lang = false)
{
    if ($lang) {
        $categories = trans('codelist/annotation.annotation_category');
    } else {
        $categories = config('annotation.category');
    }

    return array_key_exists($key, $categories) ? $categories[$key] : $key;
}

/**
 * Get Category name by key
 *
 * @param string $key
 * @param bool   $lang
 *
 * @return string
 */
function getCategoryClusterName($key = '', $lang = false)
{
    if ($lang) {
        $categories = trans('codelist/annotation.cluster');
    } else {
        $categories = config('annotation.cluster');
    }

    return array_key_exists($key, $categories) ? $categories[$key] : $key;
}

/**
 * Translate date string
 *
 * @param $date
 *
 * @return string
 */
function translate_date($date)
{
    $lang_date = trans('date');
    $date      = str_replace(array_keys($lang_date), array_values($lang_date), $date);

    return $date;
}

/**
 * Get Formatted id for contract name
 *
 * @param $id
 *
 * @return string
 */
function formatIdRorName($id)
{
    return str_pad($id, 4, "0", STR_PAD_LEFT);
}

/**
 * MTurk Helper functions
 */

/**
 * Get Lang Text for mTurk page
 *
 * @param $lang_text
 * @param $lang
 *
 * @return string
 */
function get_lang_text($lang_text, $lang)
{
    $lang_key = array_keys($lang_text);
    if (in_array($lang, $lang_key)) {
        return $lang_text[$lang];
    }

    return $lang_text['en'];
}

/**
 * Get disclaimer page for a language
 *
 * @param string $lang
 *
 * @return string
 */
function disclaimer($lang = 'en')
{
    $lang_text = [
        'en' => '<strong>DISCLAIMER</strong> - IF YOU ARE NOT A FRENCH SPEAKER PLEASE DO NOT COMPLETE TASKS FOR FRENCH LANGUAGE DOCUMENTS. DUE TO ERRORS BY PREVIOUS TRANSCRIBERS, WE ARE NOT IN A POSITION TO PAY FOR TRANSCRIPTIONS OF FRENCH LANGUAGE DOCUMENTS BY NON-FRENCH SPEAKERS.',
        'fr' => '<strong>AVERTISSEMENT</strong> - SI VOUS N’ETES PAS FRANCOPHONE, NOUS VOUS PRIONS DE PAS TRAVAILLER SUR DES DOCUMENTS EN LANGUE FRANCAISE. EN RAISON D’ERREURS COMMISES PAR  DE PRECEDENTS TRANSCRIPTEURS, NOUS NE SOMMES PAS EN MESURE DE PAYER POUR DES TRANSCRIPTS DE DOCUMENTS EN LANGUE FRANCAISE PAR DES NON FRANCOPHONES.',
    ];

    return get_lang_text($lang_text, $lang);
}

/**
 * Get instructions text
 *
 * @param string $lang
 *
 * @return string
 */
function other_instructions($lang = 'en')
{
    $lang_text = [
        'en' => '<h4>Other instructions for transcribers: </h4>
		            <ol>
						<li>Original letters and accents should be preserved (for example for French language contracts, the letters / symbols “œ” and “æ” and the following accents should be transcribed: à ç è é ê ö).</li>
						<li>Original symbols should be transcribed where possible (for example: ° as used in “N°”, or in coordinates like “53°14’477’’).</li>
						<li>For non-transcribed objects such as maps, photos or illegible signatures, please provide a bracketed entry (for example: [map], [photo] or [signature]).</li>
						<li>If you can read the handwritten signature, or any other handwriting, please transcribe it</li>
						<li>Tables should be transcribed with pipes ( | ) as separators (for example: “Royalties | 10% | 12% | 15%”).</li>
						<li>Please include any typed page numbers or other document references that appear on the page.</li>
						<li>Please include a single space only between each Article of the contract.</li>
		            </ol>',
        'fr' => '<h4>Autres instructions pour les transcripteurs:</h4>
					<ol>
						<li>Les lettres et accents originaux doivent être présevrés (par exemple pour les contrats en langue française, les lettres / symboles “œ” et “æ” ainsi que les accents suivants doivent être retranscrits: à ç è é ê ö).</li>
						<li>Les symboles originaux doivent, dans la mesure du possible, être retranscrits (par exemple: ° utilisé dans “N°”, ou dans des coordonnées comme “53°14’477”).</li>
						<li>Pour les objets non retranscrits tels les cartes, photos ou des signatures illisibles, nous vous prions de bien vouloir utiliser des crochets (par exemple: [carte], [photo], [signature])</li>
						<li>Si vous pouvez lire la signature manuscrite, ou toute autre écriture, nous vous prions de la/les retranscrire.</li>
						<li>Les tableaux doivent être retranscrits avec des tubes ( | ) comme séparateurs (par exemple: “Redevances  | 10%  | 12%  | 15 % “).</li>
						<li>Nous vous prions d’inclure tout numéro de pages dactylographiés ou autres références à des documents qui apparaissent sur la page</li>
						<li>Nous vous prions d’utiliser un interligne simple seulement entre les articles du contrat.
					</ol>',
    ];

    return get_lang_text($lang_text, $lang);
}

/**
 * Show transcript language
 *
 * @param string $lang
 *
 * @return string
 */
function show_language($lang = 'en')
{
    $langConfig = trans('codelist/language', [], null, 'en');
    $lang_list  = $langConfig['major'] + $langConfig['minor'];
    $language   = 'English';

    if (array_key_exists($lang, $lang_list)) {
        $language = $lang_list[$lang];
    }

    return sprintf(" in <strong>%s</strong>", $language);
}