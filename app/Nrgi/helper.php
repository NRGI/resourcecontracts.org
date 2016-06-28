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
 * @param String
 *
 * @return string
 */
function _l($key)
{
    if (Lang::has($key)) {
        return Lang::get($key);
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
 * @param $code
 *
 * @return null
 */
function getLanguageName($code)
{
    $lang = trans('codelist/language');
    $lang = $lang['major'] + $lang['minor'];
    $code = strtolower($code);

    return isset($lang[$code]) ? $lang[$code] : null;
}

/**
 * Get Open Contracting identifier
 *
 * @param $identifier
 * @param $iso_code
 *
 * @return \App\Nrgi\Services\Contract\Identifier\ContractIdentifier
 */
function getContractIdentifier($identifier, $iso_code)
{
    $ci = new \App\Nrgi\Services\Contract\Identifier\ContractIdentifier($identifier, $iso_code);

    return $ci->generate();
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
        $status = '<span class="label pull-right" style="background-color: darkgray">('.$count.') '.trans(
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
