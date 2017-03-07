<?php
class Crudoyqueso_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function bases()
	{
		$query = $this->db->query('SHOW DATABASES');
		return $query->result_array();
	}

	public function tablas($base)
	{
		$query = $this->db->query('SHOW FULL TABLES FROM ' . $base );
		return $query->result_array();
	}

	public function campos($base,$tabla)
	{
		$query = $this->db->query('SHOW FULL COLUMNS FROM '. $base . '.' . $tabla );
		return $query->result_array();
	}

	public function primary($base,$tabla)
	{
		$query = $this->db->query('SHOW KEYS FROM '. $base . '.' . $tabla .' WHERE Key_name = "PRIMARY"');
		$result = $query->row_array();
		return $result['Column_name'];
	}

	public function campos_mostrar($base,$tabla)
	{
		$query = $this->db->query('SHOW FULL COLUMNS FROM '. $base . '.' . $tabla .' WHERE Comment LIKE "%mostrar%"');
		$result = $query->result_array();
		return $result;
	}


	public function procesos($base,$tabla)
	{
		$query = $this->db->query(' SHOW CREATE TABLE '. $base . '.' . $tabla );
		return $query->result_array();
	}

	public function claves($base,$tabla)
	{
	$query = $this->db->query('

	SELECT
	  TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
	FROM
	  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
	WHERE
		REFERENCED_TABLE_NAME IS NOT NULL
		AND
		TABLE_NAME = "'.$tabla.'"
	;
	');
		return $query->result_array();
	}

	public function consulta($consulta){
		$query = $this->db->query($consulta);
		echo '<pre>';
		print_r($query->result_array());
		echo '</pre>';
	}

}?>
