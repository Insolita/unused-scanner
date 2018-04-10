<?php
$projectPath = __DIR__;
return [
    'composerJsonPath' => $projectPath . '/stub_composer.json',
    'vendorPath' => $projectPath . '/../vendor/',
    'scanDirectories' => [$projectPath . '/stubs/'],
    'requireDev' => false,
    'reportPath'=>$projectPath.'/reports/'
];
