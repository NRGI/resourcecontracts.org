<?php
$files = files('ethiopian-contracts/json');
$exel  = [];

foreach ($files as $file) {

    $data                                  = json_decode(file_get_contents($file), 1);
    $formatedData['contract_title']        = urldecode(pathinfo($data['contract_name'], PATHINFO_FILENAME));
    $formatedData['file_name']             = urldecode($data['file_name']);
    $formatedData['pdf_url']             = $data['pdf_url'];
    $formatedData['m_country']             = $data['metadata']['country'];
    $formatedData['m_resource']            = $data['metadata']['resource'];
    $formatedData['m_type_of_contract']    = $data['metadata']['type_of_contract'];
    $formatedData['m_signature_year']      = $data['metadata']['signature_year'];
    $formatedData['a_signature_year']      = $data['annotation']['signature_year'][0];
    $formatedData['m_signature_date']      = $data['metadata']['signature_date'];
    $formatedData['a_signature_date']      = $data['annotation']['signature_date'][0];
    $formatedData['a_government_entities'] = $data['annotation']['government_entities'][0];
    $formatedData['a_company']             = implode('--', array_filter($data['annotation']['company']));
    $formatedData['a_type_of_contract']    = $data['annotation']['type_of_contract'][0];
    $formatedData['m_project_title']       = isset($data['metadata']['project_title']) ? $data['metadata']['project_title'] : "";

    $formatedData['a_government_entities']     = $data['annotation']['government_entities'][0];
    $formatedData['a_license_concession_name'] = implode(
        '--',
        array_filter($data['annotation']['license_concession_name'])
    );
    $formatedData['m_license_concession_name'] = isset($data['metadata']['license_concession_name']) ? $data['metadata']['license_concession_name'] : "";;

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
