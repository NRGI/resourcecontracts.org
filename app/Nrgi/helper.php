<?php
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
 * Get Pdf Path
 * @param $contract_id
 * @param $page_no
 * @return string
 */
function getPdfUrl($contract_id, $page_no)
{
    $path = trim(url(config('nrgi.pdf_storage_url')), '/');
    return sprintf('%s/%s/pages/%s.pdf', $path, $contract_id, $page_no);
}