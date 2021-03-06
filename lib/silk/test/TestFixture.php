<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
#CMS - CMS Made Simple
#(c)2004-2010 by Ted Kulp (ted@cmsmadesimple.org)
#This project's homepage is: http://cmsmadesimple.org
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace silk\test;

use \silk\core\Object;
use \silk\database\Database;

class TestFixture extends Object
{
	function __construct()
	{
		parent::__construct();
	}

	function setUp()
	{
		$table = isset($this->table) ? $this->table : '';
		if (isset($this->model))
		{
			$the_model = $this->model;
			$table = $the_model::getTableName();
			$the_model::migrate();
		}

		if ($table != '' && isset($this->records))
		{
			$pdo = Database::getDatabase();
			$is_mongo = Database::isMongoDb();
			foreach($this->records as $one_record)
			{
				if (is_array($one_record))
				{
					if ($is_mongo)
					{
						$col = $pdo->selectCollection($table);
						if ($col)
						{
							if ($one_record['id'])
							{
								$one_record['_id'] = $one_record['id'];
								unset($one_record['id']);
							}
							$col->insert($one_record);
						}
					}
					else
					{
						$query = "INSERT INTO " . $table . " (" . implode(", ", array_keys($one_record)) . ") VALUES (" . implode(',', array_fill(0, count($one_record), '?')) . ")";
						$pdo->executeUpdate($query, array_values($one_record));
					}
				}
			}
		}
	}

	function tearDown()
	{
		if (isset($this->model))
		{
			$the_model = $this->model;
			$the_model::dropTable();
		}
	}
}

# vim:ts=4 sw=4 noet
