<?php
use Illuminate\Support\Facades\Lang;


/**
 * Get formatted file size
 * @param $bytes
 * @return string
 */
function getFileSize($bytes)
{
    switch ($bytes):
        case ($bytes >= 1073741824):
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            break;
        case ($bytes >= 1048576):
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
            break;
        case ($bytes >= 1024):
            $bytes = number_format($bytes / 1024, 2) . ' KB';
            break;
        case ($bytes > 1):
            $bytes = $bytes . ' bytes';
            break;
        case ($bytes == 1):
            $bytes = $bytes . ' byte';
            break;
    endswitch;

    return $bytes;
}

/**
 * Get md5 file hash value
 *
 * @param $file
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
 * @return \App\Nrgi\Services\Contract\Identifier\ContractIdentifier
 */
function getContractIdentifier($contract_id)
{
    $ci = new \App\Nrgi\Services\Contract\Identifier\ContractIdentifier($contract_id);
    return $ci->generate();
}
