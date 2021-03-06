<?php

/**#@+
 * @version 0.1.3
 * @since 0.1.3
 */

/**
 * @package POT
 * @author Wrzasq <wrzasq@gmail.com>
 * @copyright 2007 (C) by Wrzasq
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License, Version 3
 */

/**
 * Base class for all database drivers.
 * 
 * <p>
 * It defines additional rotines required by database driver for POT using default SQL standard-compliant method.
 * </p>
 * 
 * @package POT
 */
abstract class OTS_Base_DB extends PDO implements IOTS_DB
{
/**
 * Tables prefix.
 * 
 * @var string
 */
    private $prefix = '';

/**
 * Query counter
 *
 * @var int
 */
    private $queries = 0;

/**
 * Query-quoted field name.
 * 
 * @param string $name Field name.
 * @return string Quoted name.
 */
    public function fieldName($name)
    {
        return '"' . $name . '"';
    }

	public function fieldNames()
	{
		$ret = '';

		$argc = func_num_args();
		$argv = func_get_args();
		for($i = 0; $i < $argc; $i++) {
			$ret .= $this->fieldName($argv[$i]) . ',';
		}

		$ret[strlen($ret) - 1] = '';
		return $ret;
	}

/**
 * Query-quoted table name.
 * 
 * @param string $name Table name.
 * @return string Quoted name.
 */
    public function tableName($name)
    {
        return $this->fieldName($this->prefix . $name);
    }

/**
 * @param stirng $string String to be quoted.
 * @return string Quoted string.
 * @deprecated 0.0.5 Use PDO::quote().
 * @version 0.0.7
 */
    public function SQLquote($string)
    {
        return parent::quote($string, PDO_PARAM_STR);
    }

/**
 * @param string $query SQL query.
 * @return PDOStatement|bool Query results.
 * @deprecated 0.0.5 Use PDO::query().
 */
    public function SQLquery($query)
    {
        return query($query);
    }

    public function query($query)
    {
        $this->queries++;
		//echo $query . PHP_EOL;
        return parent::query($query);
    }

	public function select($table, $data)
	{
		$fields = array_keys($data);
		$values = array_values($data);
		$query = 'SELECT * FROM ' . $this->tableName($table) . ' WHERE (';
		for ($i = 0; $i < count($fields); $i++)
			$query.= $this->fieldName($fields[$i]).' = '.$this->quote($values[$i]).' AND ';
		$query = substr($query, 0, strlen($query)-4);
		$query.=');';

		$query = $this->query($query);
		if($query->rowCount() != 1) return false;
		return $query->fetch();
	}

	public function insert($table, $data)
	{
		$fields = array_keys($data);
		$values = array_values($data);
		$query = 'INSERT INTO ' . $this->tableName($table) . ' (';
		foreach ($fields as $field)
			$query.= $this->fieldName($field).',';

		$query = substr($query, 0, strlen($query) - 1);
		$query .= ') VALUES (';
		foreach ($values as $value)
			if ($value === null)
				$query .= 'NULL,';
			else
				$query .= $this->quote($value).',';

		$query = substr($query, 0, strlen($query) - 1);
		$query .= ')';
		$this->query($query);
		return true;
	}

	public function replace($table, $data)
	{
		$fields = array_keys($data);
		$values = array_values($data);
		$query = 'REPLACE INTO '.$this->tableName($table).' (';
		foreach ($fields as $field)
			$query.= $this->fieldName($field).',';

		$query = substr($query, 0, strlen($query) - 1);
		$query.= ') VALUES (';
		foreach ($values as $value)
			if ($value === null)
				$query.= 'NULL,';
			else
				$query.= $this->quote($value).',';

		$query = substr($query, 0, strlen($query) - 1);
		$query .= ')';

		$this->query($query);
		return true;
	}

	public function update($table, $data, $where, $limit = 1)
	{
		$fields = array_keys($data);
		$values = array_values($data);

		$query = 'UPDATE '.$this->tableName($table).' SET ';
		for ($i = 0; $i < count($fields); $i++)
			$query.= $this->fieldName($fields[$i]).' = '.$this->quote($values[$i]).', ';

		$query = substr($query, 0, strlen($query)-2);
		$query.=' WHERE (';
		$fields = array_keys($where);
		$values = array_values($where);

		for ($i = 0; $i < count($fields); $i++)
			$query.= $this->fieldName($fields[$i]).' = '.$this->quote($values[$i]).' AND ';

		$query = substr($query, 0, strlen($query)-4);
		if (isset($limit))
			$query .=') LIMIT '.$limit.';';
		else
			$query .=');';

		$this->query($query);
		return true;
	}

	public function delete($table, $data, $limit = 1)
	{
		$fields = array_keys($data);
		$values = array_values($data);

		$query = 'DELETE FROM ' . $this->tableName($table) . ' WHERE (';
		for ($i = 0; $i < count($fields); $i++)
			$query .= $this->fieldName($fields[$i]) . ' = ' . $this->quote($values[$i]) . ' AND ';

		$query = substr($query, 0, strlen($query) - 4);
		if ($limit > 0)
			$query.=') LIMIT '.$limit.';';
		else
			$query.=');';

		$this->query($query);
		return true;
	}
/**
 * LIMIT/OFFSET clause for queries.
 * 
 * @param int|bool $limit Limit of rows to be affected by query (false if no limit).
 * @param int|bool $offset Number of rows to be skipped before applying query effects (false if no offset).
 * @return string LIMIT/OFFSET SQL clause for query.
 */
    public function limit($limit = false, $offset = false)
    {
        // by default this is empty part
        $sql = '';

        if($limit !== false)
        {
            $sql = ' LIMIT ' . $limit;

            // OFFSET has no effect if there is no LIMIT
            if($offset !== false)
            {
                $sql .= ' OFFSET ' . $offset;
            }
        }

        return $sql;
    }

	public function queries() {
		return $this->queries;
	}
}

/**#@-*/

?>
