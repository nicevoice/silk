<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
// The MIT License
// 
// Copyright (c) 2008-2010 Ted Kulp
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

define('CACHE_SECONDS', 300);
define('ADODB_OUTP', 'adodb_outp');

use \silk\core\EventManager;
use \silk\performance\Profiler;

/**
 * Singleton class to represent a connection to the database.
 *
 * Adodb Data Dictionary Manual: http://phplens.com/lens/adodb/docs-datadict.htm
 * @author Ted Kulp
 * @since 1.0
 **/
class SilkDatabase extends \silk\core\Object
{
	private static $instance = NULL;

	public static function get_instance($dsn = null, $debug = false)
	{
		if (self::$instance == NULL)
		{
			SilkDatabase::connect($dsn, $debug);
			EventManager::send_event('silk:database:connection:opened');
			EventManager::register_event_handler('silk:core:application:shutdown_now', array(get_called_class(), 'close'), true);
		}
		return self::$instance;
	}
	
	public static function close()
	{
		if (self::$instance !== null)
		{
			if (self::$instance->IsConnected())
			{
				self::$instance->Close();
				EventManager::send_event('silk:database:connection:closed');
			}
		}
	}
	
	public static function get_prefix()
	{
		$prefix = self::get_instance()->prefix;
		return $prefix;
	}
	
	public static function connect($dsn = null, $debug = false, $die = true, $prefix = null, $make_global = true)
	{
		if ($dsn == null)
		{
			$config = get('config');
			
			//Setup the database connection
			//if (!isset($config['database']['dsn']))
				//throw new SilkDatabaseException("No database information found in the configuration file.");
			
			$dsn = $config['database']['dsn'];
			$debug = $config['debug'];
			$prefix = $config['database']['prefix'];
		}
		
		$dbinstance = null;

		//Globals are teh lame
		global $ADODB_CACHE_DIR;
		$ADODB_CACHE_DIR = join_path(ROOT_DIR, 'tmp', 'cache');

		require_once(join_path(SILK_LIB_DIR, 'adodb','adodb-exceptions.inc.php'));
        require_once(join_path(SILK_LIB_DIR, 'adodb','adodb.inc.php'));

		try
		{
			$dbinstance = ADONewConnection($dsn);
			$dbinstance->fnExecute = 'pre_parse_query';
			$dbinstance->fnCacheExecute = 'count_cached_execs';
			$dbinstance->prefix = $prefix;
		}
		catch (Exception $e)
		{
			if ($die)
			{
				echo "<strong>Database Connection Failed</strong><br />";
				echo "Error: {$dbinstance->_errorMsg}<br />";
				echo "Function Performed: {$e->fn}<br />";
				echo "Host/DB: {$e->host}/{$e->database}<br />";
				die();
			}
			else
			{
				return null;
			}
		}

		$dbinstance->SetFetchMode(ADODB_FETCH_ASSOC);
		$dbinstance->debug = ($debug ? 1 : false);
		
		if (!$debug)
			$dbinstance->cacheSecs = CACHE_SECONDS;
		else
			$dbinstance->cacheSecs = 0;
		
		if (isset($dbms))
		{
			if ($dbms == 'sqlite')
			{
				$dbinstance->Execute("PRAGMA short_column_names = 1;");
				sqlite_create_function($dbinstance->_connectionID,'now','time',0);
			}
			else
			{
				try
				{
					$dbinstance->Execute("SET NAMES 'utf8'");
				}
				catch (Exception $e)
				{
					//Ignore for now
				}
			}
		}
	
		if ($make_global)
		{
			self::$instance = $dbinstance;
		}

		return $dbinstance;
	}
	
	public static function disable_caching()
	{
		self::get_instance()->cacheSecs = 0;
	}
	
	public static function enable_caching()
	{
		self::get_instance()->cacheSecs = CACHE_SECONDS;
	}

	public static function get_xml_schema()
	{
		$db = self::get_instance();
		$ado = new adoSchema($db);
		$ado->SetPrefix(self::get_prefix(), FALSE);
		return $ado;
	}
	
	public static function query_count()
	{
		if (method_exists(self::$instance, 'query_count'))
		{
			return self::$instance->query_count();
		}
		else
		{
			global $EXECS;
			global $CACHED;
			return $EXECS + $CACHED;
		}
	}
	
	public static function cached_query_count()
	{
		if (method_exists(self::$instance, 'cached_query_count'))
		{
			return self::$instance->cached_query_count();
		}
		else
		{
			global $CACHED;
			return $CACHED;
		}
	}

	/**
	 * Detects existence of table by trying to select from $tableName, suppressing the error output. 
	 * @param $tableName Table you'd like to check the existence of.
	 * @param $silent false if you would like the function to throw the exceptions causing a return of 'false', rather than suppress them. Default is true. 
	 * Can assist in debugging db connection if you know the table does in fact exist.
	 * @return true if table exists, false if it does not. 
	 * Will also return false if there is any another error generated by attempting selection from this table.table
	 * Use $silent  = true if you suspect the latter is the case. 
	 * @author Tim Oxley
	*/
	public static function table_exists($table, $silent = true)
	{
		$db = self::get_instance();
		if ($silent)
		{
			$saveErrHandlers = $db->IgnoreErrors();
		}
		// TODO: Test other adodb methods to see which one is fastest for checking existence
		// eg MetaTables, CreateTableSQL etc,
		$result = $db->Execute("SELECT * FROM ".self::get_prefix().$table);
		if ($silent)
		{
			$db->IgnoreErrors($saveErrHandlers);
		}

		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Creates a new table definition in the database, or modifies schema of table if the table already exists. 
	 * If tryToChangeTable is false and table exists, php will throw an error. If true and table exists, 
	 * will try to update the table schema. If table does not exist, will try create the table, regardless of this value.
	 * Returns the result of trying to execute these functions.
	 * @param $table String - Name of the table to create. Framework prefix added automatically.
	 * @param $fields array - Adodb field definitions. See CreateTableSQL $fldarray syntax in Adodb Data Dictionary Manual.
	 * @param $changeTable Boolean - Try to change table if true or try to create. Compare CreateTableSQL/ChangeTableSQL in Adodb Data Dictionary Manual.
	 * @return 0 if there's an execution problem, 1 if executed all but with error, returns 2 if executed successfully.
	 * @author Ted Kulp, Tim Oxley
	*/
	public static function create_table($table, $fields, $changeTable = true)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$taboptarray = array('mysql' => 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
		//Pick command to execute	
		if ($changeTable)
		{
			$cmd = 'ChangeTableSQL';
		}
		else
		{
			$cmd = 'CreateTableSQL';
		}
		$sqlarray = $dbdict->$cmd(self::get_prefix().$table, $fields, $taboptarray);
		
		return $dbdict->ExecuteSQLArray($sqlarray);
	}

	/**
	 * Wrapper around create_table but forces the changeTable option. change_table is a more clear description.
	 * @see create_table
	 * @author Tim Oxley
	*/
	public static function change_table($table, $fields)
	{
		return self::create_table($table, $fields, true);
	}
	
	public static function create_index($table, $name, $field)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->CreateIndexSQL(self::get_prefix().$name, self::get_prefix().$table, $field);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function add_column($table, $fields)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->AddColumnSQL(self::get_prefix().$table, $fields);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function alter_column($table, $fields)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->AlterColumnSQL(self::get_prefix().$table, $fields);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function drop_table($table)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->DropTableSQL(self::get_prefix().$table);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function drop_index($table, $name)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->DropIndexSQL(self::get_prefix().$name, self::get_prefix().$table);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function drop_column($table, $fields)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->DropColumnSQL(self::get_prefix().$table, $fields);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
	
	public static function rename_table($table, $new_table)
	{
		$db = self::get_instance();
		$dbdict = NewDataDictionary($db);
		$sqlarray = $dbdict->RenameTableSQL(self::get_prefix().$table, self::get_prefix().$new_table);
		return $dbdict->ExecuteSQLArray($sqlarray);
	}
}

// Global functions outside of class

function adodb_outp($msg, $newline = true)
{
	$msg = str_replace('<hr />', '', $msg);
	$msg = str_replace('<hr>', '', $msg);
	$msg = str_replace("\r\n", '', $msg);
	$msg = str_replace("\r", '', $msg);
	$msg = str_replace("\n", '', $msg);
	$msg = html_entity_decode($msg, ENT_COMPAT, 'UTF-8');

	Profiler::get_instance()->mark($msg);
}

//TODO: Clean me up.  Globals?  Yuck!
function pre_parse_query($db, &$sql, $inputarray)
{
	$sql = strtr($sql, array('{' => SilkDatabase::get_prefix(), '}' => ''));

	global $EXECS;

	if (!is_array($inputarray))
		$EXECS++;
	else if (is_array(reset($inputarray)))
		$EXECS += sizeof($inputarray);
	else
		$EXECS++;

	$null = null;
	return $null;
}

//TODO: You too, slacker!
function count_cached_execs($db, $secs2cache, $sql, $inputarray)
{
	Profiler::get_instance()->mark('CACHED:' . $sql);

	global $CACHED; $CACHED++;
}

# vim:ts=4 sw=4 noet
?>
