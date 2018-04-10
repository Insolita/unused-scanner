<?php
$projectPath = getcwd();

/**
 * Array of full directories path for scan;
 * Scan will be recursive
 * Put directories with most intensive imports in top of list for more quick result
 * @see http://api.symfony.com/4.0/Symfony/Component/Finder/Finder.html#method_in
**/
$scanDirectories = [
    $projectPath . '/config/',
    $projectPath . '/Acme/',
    $projectPath . '/controllers/',
    $projectPath . '/console/',
    $projectPath . '/models/',
    $projectPath . '/modules/',
    $projectPath . '/services/',
];

$scanFiles = [
    $projectPath . '/autocomplete.php',
];
/**
 * Names relative to ones of scanDirectories
 *
 * @see http://api.symfony.com/4.0/Symfony/Component/Finder/Finder.html#method_exclude
 **/
$excludeDirectories = [
    'runtime',
    'storage/logs',
];
return [
    'composerJsonPath' => $projectPath . '/composer.json', //required
    'vendorPath' => $projectPath . '/vendor/',             //required
    'scanDirectories' => $scanDirectories,                 //required
    'excludeDirectories' => $excludeDirectories,           //optional
    'scanFiles' => $scanFiles,                             //optional
    'extensions' => ['*.php'],                             //optional
    'requireDev' => false,   //optional, Check composer require-dev section, default false
    'customMatch'=> function($definition, $packageName, \Symfony\Component\Finder\SplFileInfo $file){  //optional
         //custom logic, should return boolean: true if $definition presented in $fileContent, otherwise false
         return false;
    }
];
