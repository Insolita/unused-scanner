<?php
$projectPath = __DIR__;
return [
    'composerJsonPath' => $projectPath . '/stub_composer.json',
    'vendorPath' => $projectPath . '/../vendor/',
    'scanDirectories' => [$projectPath . '/stubs/', $projectPath.'/not_existed/', $projectPath.'/missing'],
    'requireDev' => true
];
