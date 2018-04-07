<?php
$projectPath = __DIR__.'/my/project/';
$scanDirectories = [
    $projectPath . '/Acme/',
    $projectPath . '/controllers/',
    $projectPath . '/config/',
    $projectPath . '/console/',
    $projectPath . '/models/',
    $projectPath . '/modules/',
    $projectPath . '/services/',
];
$scanFiles = [
    $projectPath.'/autocomplete.php'
];
return [
    'composerJsonPath' => $projectPath . '/composer.json',
    'vendorPath' => $projectPath . '/vendor/',
    'scanDirectories' => $scanDirectories,  //Directories will be scanned recursive
    'scanFiles' => $scanFiles,   //Array of files that should be scanned
    'requireDev'=>false   //Check composer require-dev section
];
?>