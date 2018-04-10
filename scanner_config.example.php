<?php
/**
 *  Set $projectPath = getcwd(); if your put it under project root
**/
$projectPath = __DIR__ . '/my/project/';


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
    },
    /**
     * Report mode options
     * Report mode enabled, when reportPath value is valid directory path
     * !!!Note!!! The scanning time and memory usage will be increased when report mode enabled,
     * it sensitive especially for big projects and  when requireDev option enabled
     **/
    'reportPath' => null, //path in directory, where usage report will be stores;
    //optional, by default, result formatted as json
    'reportFormatter'=>function(array $report):string{ return print_r($report, false);},
    //optional, by default - json, set own, if use custom formatter
    'reportExtension'=>null,
];
