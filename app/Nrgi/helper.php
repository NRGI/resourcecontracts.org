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
