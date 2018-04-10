<?php
$projectPath = __DIR__;
return [
    'composerJsonPath' => $projectPath . '/stub_composer.json',
    'vendorPath' => $projectPath . '/../vendor/',
    'scanDirectories' => [$projectPath . '/stubs/'],
    'requireDev' => false,
    'reportPath'=>$projectPath.'/reports/',
    'reportFormatter'=>function(array $report){
         return print_r($report, true);
    },
    'reportExtension' => 'txt'
];
