<?php
use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Length\Loc;
use Hal\Metrics\Design\Component\MaintenabilityIndex\MaintenabilityIndex;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;
use Hal\Component\Result\ResultSet;
use Hal\Component\Token\Tokenizer;
use Hal\Component\Token\TokenType;

if (! isset($autoloader)) {
    $autoloader = require __DIR__ . '/../../vendor/autoload.php';
}
$binary = __DIR__ . '/../../vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64';
if (false !== stripos(php_uname(), 'windows')) {
    $binary = __DIR__ . '/../../vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage.exe';
}
if (!file_exists($binary)) {
    die(sprintf("Binary %s not found", $binary));
}

/*
 * Generates metrics as json
 */
$folders = explode(',', $argv[3]);
$files   = array();
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        $folder = rtrim($folder, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.(php)$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $files[] = $file[0];
        }
    }
}

$results  = array();
foreach ($files as $filename) {
    // calculates
    $halstead = new Halstead(new Tokenizer(), new TokenType());
    $rHalstead = $halstead->calculate($filename);

    $loc = new Loc(new Tokenizer());
    $rLoc = $loc->calculate($filename);

    $mcCabe = new McCabe(new Tokenizer());
    $rMcCabe = $mcCabe->calculate($filename);

    $maintenability = new MaintenabilityIndex();
    $rMaintenability = $maintenability->calculate($rHalstead, $rLoc, $rMcCabe);

    // formats
    $resultSet = new ResultSet($filename);
    $resultSet
        ->setLoc($rLoc)
        ->setHalstead($rHalstead)
        ->setMcCabe($rMcCabe)
        ->setMaintenabilityIndex($rMaintenability);

    $results[$resultSet->getFilename()] = $resultSet->asArray();
}
file_put_contents(__DIR__ . '/../../build/bubbles/results.json', json_encode($results));

/*
 * Generates output image as jpg (used by Jenkins)
 */
$generator = new \Knp\Snappy\Image($binary);
$generator->setOption('javascript-delay', 1000);
$outImage = isset($argv[2]) ? $argv[2] : __DIR__ . '/../../build/pdepend/metrics.jpg';
$generator->generate(
    isset($argv[1]) ? $argv[1] : __DIR__ . '/../../build/bubbles/index.html',
    $outImage,
    array(),
    true
);
echo sprintf("Bubble chart generated in %s", $outImage);
