<?php
$files = files('ethiopian-contracts/json');
$exel  = [];

foreach ($files as $file) {

    $data = json_decode(file_get_contents($file), 1);

    $formatedData['contract_title']        = str_replace('_', ' ', $data['contract_name']);
    $formatedData['file_name']             = str_replace('_', ' ', $data['file_name']);
    $formatedData['m_country']             = $data['m_country'];
    $formatedData['m_resource']            = $data['m_resource'];
    $formatedData['m_type_of_contract']    = $data['m_type_of_contract'];
    $formatedData['m_signature_year']      = $data['m_signature_year'];
    $formatedData['a_signature_year']      = $data['a_signature_year'][0];
    $formatedData['m_signature_date']      = $data['m_signature_date'];
    $formatedData['a_signature_date']      = $data['a_signature_date'][0];
    $formatedData['a_government_entities'] = $data['a_government_entities'][0];
    $formatedData['a_company']             = implode('--', array_filter($data['a_company']));
    $formatedData['a_type_of_contract']    = $data['a_type_of_contract'][0];
    $formatedData['m_project_title']       = isset($data['m_project_title']) ? $data['m_project_title'] : "";

    $formatedData['a_government_entities']     = $data['a_government_entities'][0];
    $formatedData['a_license_concession_name'] = implode('--', array_filter($data['a_license_concession_name']));
    $formatedData['m_license_concession_name'] = isset($data['m_license_concession_name']) ? $data['m_license_concession_name'] : "";;

    $exel [] = $formatedData;
}

download_send_headers("data_export_" . date("Y-m-d") . ".csv");

echo array2csv($exel);
die();

/**
 * Get an array of all files in a directory.
 *
 * @param  string $directory
 * @return array
 */
function files($directory)
{
    $glob = glob($directory . '/*');

    if ($glob === false) {
        return array();
    }

    // To get the appropriate files, we'll simply glob the directory and filter
    // out any "files" that are not truly files so we do not end up with any
    // directories in our list, but only true files within the directory.
    return array_filter(
        $glob,
        function ($file) {
            return filetype($file) == 'file';
        }
    );
}

function formatArray($array)
{
    $string = '';
    foreach ($array as $val) {
        $string = $val . "-->";
    }

    return $string;
}

function array2csv(array &$array)
{
    if (count($array) == 0) {
        return null;
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));


    foreach ($array as $row) {
        fputcsv($df, $row);
    }
    fclose($df);

    return ob_get_clean();
}


function download_send_headers($filename)
{
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    header('Content-type: text/csv; charset=UTF-8');

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
//    header("Content-Transfer-Encoding: binary");
}
