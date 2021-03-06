<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('silk')
             ->setDependsOnPHPVersion('5.3')
             ->setChannel('pear.silkframework.com')
             ->setSummary('PHP5 Framework filled with awesome')
             ->setDescription('TODO: Longer description of your PEAR package')
             ->setReleaseVersion('0.0.1dev' . strftime('%Y%m%d'))
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1dev' . strftime('%Y%m%d'))
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Ted Kulp', 'tedkulp', 'ted@tedkulp.com')
             ->addGitFiles()
             ->addFilesRegex('/vendor\/phake/')
             ->addExcludeFilesRegex('/vendor\/phake$/')
             ->addFilesRegex('/vendor\/rack/')
             ->addExcludeFilesRegex('/vendor\/rack$/')
             ->addFilesRegex('/vendor\/htmldocparser/')
             ->addExcludeFilesRegex('/vendor\/htmldomparser$/')
             ->addFilesRegex('/vendor\/doctrine/')
             ->addExcludeFilesRegex('/vendor\/doctrine[^\/]*$/')
             ->addExcludeFilesRegex('/\.git.*/')
             ->addExcludeFilesRegex('/\.gitignore/')
             ->addExcludeFilesRegex('/\.gitmodules/')
             ->addExcludeFilesRegex('/vendor\/.*\/tests/')
             ->addExecutable('silk')
             ;

$fileObj = $spec->getFile('index.php');
$fileObj->addReplaceTask('pear-config', '@php_bin@', 'php_bin');
$fileObj->addReplaceTask('pear-config', '@bin_dir@', 'bin_dir');
$fileObj->addReplaceTask('pear-config', '@pear_directory@', 'php_dir');
