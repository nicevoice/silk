<?php

use \silk\test\TestSuite;

class SilkTestTask extends SilkTask {

	/* interchange methods to task can be run from console as well as commandline */
	
	public function __construct()
	{
		$this->addOption('system', array(
			'long_name' => '--system',
			'description' => 'Runs the Silk Framework tests',
			'action' => 'StoreTrue',
			'final' => false
			)
		);
		
		$this->addArgument('args', array(
			'multiple' => true,
			'optional' => true,
			)
		);
		
		return parent::__construct(array(
			'name' => 'Test Task',
			'description' => "A Test Task for Tests and/or Testing",
			'version' => '0.0.2'
			)
		);
	}

	public function run($argc, $argv)
	{ 
		try
		{
			$result = $this->parse($argc, $argv);

			if ($result->options['system'] == true)
			{
				echo "\nRunning Silk System tests.\n\n";
				define('SILK_TEST_DIR', join_path(SILK_LIB_DIR, 'test'));
				$test_suite = new OurTestSuite(join_path(SILK_LIB_DIR, 'test'));
			}
			else
			{
				echo "\nRunning Application tests.\n\n";
				define('SILK_TEST_DIR', join_path(ROOT_DIR, 'test'));
				$test_suite = new OurTestSuite(join_path(ROOT_DIR, 'test'));
			}
		}
		catch (Exception $exc)
		{
			$this->displayError($exc->getMessage());
		}
	}
}

class OurTestSuite extends TestSuite
{
	function __construct($path = '')
	{
		parent::__construct();

		if ($path != '' && is_dir($path))
		{
			$pattern = '/test\..*php$/';
			$dirs = array($path);

			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objects as $name => $it)
			{
				if ($it->isFile() && basename($name) != '.' && basename($name) != '..')
				{
					echo "adding file: " . $it->getPathname() . "\n";
					$this->addTestFile($it->getPathname());
				}
			}

			//$this->run();
			$result = PHPUnit_TextUI_TestRunner::run($this);
		}
	}
}

# vim:ts=4 sw=4 noet
