<?php

$router->get('/', 'Auth\AuthController@getLogin');
$router->get('home', 'Dashboard\DashboardController@index');
$router->controllers(
    [
        'auth'     => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
    ]
);

$router->get('/site/login', 'Auth\AuthController@siteLogin');

$router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

$router->get(
    'g',
    function () {
        readFromJson();
    }
);

function readFromJson()
{
    $files          = File::files(public_path('ethiopian-contracts/json'));
    $category_excel = [];
    foreach ($files as $file) {
        $contract = json_decode(file_get_contents($file), 1);
        foreach ($contract['annotations'] as $annotation) {
            $category_excel[] = $annotation['category'];
        }
    }
    $category_excel = array_map('trim', $category_excel);
    asort($category_excel);
    $annotation_from_files = array_unique($category_excel);
    $annotation_category   = trans("codelist/annotation.annotation_category");
    $found                 = [];
    $not_found             = [];
    foreach ($annotation_from_files as $ann_cat) {
        if (array_search($ann_cat, $annotation_category)) {
            $found[] = $ann_cat;
        } else {
            $not_found[] = $ann_cat;
        }
    }

    array_to_csv_download($not_found, 'not_found.csv');

    //dd($not_found);
}

function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",")
{
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f    = fopen('php://memory', 'w');
    $data = [];
    // loop over the input array
    foreach ($array as $line) {
        $data[]["d"] = $line;
        // generate csv lines from the inner arrays
    }
    foreach ($data as $line) {
        fputcsv($f, $line, $delimiter);

    }
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}
