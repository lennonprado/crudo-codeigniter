<?php
die("Para proteger al mundo me he desactivado ;) ");
class Crudoyqueso extends CI_Controller {

	var $base;

	function __construct() {
	        parent::__construct();
					$this->load->helper(array('form', 'url','file'));
					$this->load->model('crudoyqueso_model');
  }

	function index()
	{
			$bases = $this->crudoyqueso_model->bases();
			$tables_name = 'Tables_in_' . $this->db->database;
		 	echo '<h1>Seleccione una base </h1>';
			for($i = 0; $i < count($bases); $i++)
			{
					echo '<a href="';
					echo  base_url();
					echo 'crudoyqueso/tablas/';
					echo $bases[$i]['Database'];
					echo '"> ';
					echo $bases[$i]['Database'];
					echo  '</a><br>';
			}
	}

	function tablas($base)
	{
			$this->base = $base;
			$tablas = $this->crudoyqueso_model->tablas($base);
			$tables_name = 'Tables_in_' . $base;
		 	echo '<h1>Seleccione una tabla de '.$base.'</h1>';
			for($i = 0; $i < count($tablas); $i++)
			{
					echo '<a href="';
					echo  base_url();
					echo 'crudoyqueso/campos/' . $base . '/';
					echo $tablas[$i][$tables_name];
					echo '"> ';
					echo $tablas[$i][$tables_name];
					echo  '</a><br>';
			}
	}


	function campos($base,$tabla)
	{
		$campos = $this->crudoyqueso_model->campos($base,$tabla);
		$claves = $this->crudoyqueso_model->claves($base,$tabla);
		/* return Example
				array(5) {
		    ["TABLE_NAME"]=>string(11) "cons_turnos"
		    ["COLUMN_NAME"]=>string(17) "turno_id_paciente"
		    ["CONSTRAINT_NAME"]=>string(18) "cons_turnos_ibfk_1"
		    ["REFERENCED_TABLE_NAME"]=>string(9) "pacientes"
		    ["REFERENCED_COLUMN_NAME"]=>string(14) "paciente_ficha"
				["REFERENCED_FIELDS"]=valores seprados por comas
		  }
		*/
		for($i=0; $i<count($claves);$i++){
				$nuevos_campos=$this->crudoyqueso_model->campos_mostrar($base,$claves[$i]["REFERENCED_TABLE_NAME"]);
				/* Return Example
				array(9) {
				  ["Field"]=>				    string(15) "paciente_nombre"
				  ["Type"]=>				    string(12) "varchar(255)"
				  ["Collation"]=>				string(15) "utf8_general_ci"
				  ["Null"]=>				    string(2) "NO"
				  ["Key"]=>				      string(0) ""
				  ["Default"]=>				  string(0) ""
				  ["Extra"]=>				    string(0) ""
				  ["Privileges"]=>      string(31) "select,insert,update,references"
				  ["Comment"]=>				  string(15) "mostrar|externa"
				}
				*/

				if(count($nuevos_campos)>0){
					$claves[$i]["REFERENCED_FIELDS"]=$claves[$i]["REFERENCED_COLUMN_NAME"];

					for($j=0;$j<count($nuevos_campos);$j++){
							$claves[$i]["REFERENCED_FIELDS"]=$claves[$i]["REFERENCED_FIELDS"] . ',' .$nuevos_campos[$j]["Field"];
					}
				}
		}
		// generate controllers
		$this->controlador($tabla,$campos,$claves);
		// generate models
		$this->modelo($tabla,$campos,$claves);
		// generate list views
		$this->vista_list($tabla,$campos);
		// generate form views
		$this->vista_form($tabla,$campos,$claves);
}


	function modelo($tabla,$campos,$claves){
		//name of model and controller
		$name = str_ireplace('_','',$tabla);
		// primary key
		$primary = $this->crudoyqueso_model->primary($this->base,$tabla);
		// file content
		$content = "<?php
		class ".ucfirst($name)."_model extends CI_Model
		{
			// New element
			function add(\$registros){
				\$this->db->insert('".$tabla."',\$registros);
				return \$this->db->insert_id();
			}

			// Edit element
			function edit(\$id,\$registros){
				\$this->db->where('".$primary."',\$id);
				\$this->db->update('".$tabla."',\$registros);
				return \$this->db->_error_number();
			}

			// Del element
			function delete(\$id){
				\$this->db->where('".$primary."',\$id);
				\$this->db->delete('".$tabla."');
			}

			// List of elements
		function get(){
			//\$this->db->limit();
			\$this->db->order_by('".$primary."','DESC');
			//\$this->db->where('field',\$val)
			\$this->db->from('".$tabla."');
			\$query = \$this->db->get();
			return \$query->result_array();
		}

			function element(\$id)
			{
				\$this->db->where('".$primary."',\$id);
				\$this->db->from('".$tabla."');
				\$query = \$this->db->get();
				\$element = \$query->result_array();
				if(count(\$element)==1){return(\$element[0]);}
				else {return NULL;}
			}
			";
			for($i=0;$i<count($claves);$i++){
				$content .= "
			function ".$claves[$i]["REFERENCED_TABLE_NAME"]."()
			{
				\$this->db->select('".$claves[$i]["REFERENCED_FIELDS"]."');
				\$this->db->from('".$claves[$i]["REFERENCED_TABLE_NAME"]."');
				\$query = \$this->db->get();
				return \$query->result_array();
			}
					";
			}
			$content .="
		}
		/* End of file ".$tabla.".php */
		/* Location: ./application/models/".$tabla."_model.php */";

		$this->escribir_archivo($content,'models',str_ireplace('_','',$tabla).'_model');

	}
	function controlador($tabla,$campos,$claves){

		$name = str_ireplace('_','',$tabla);

		$content = "<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
		class " . ucfirst($name) ." extends CI_Controller {


			function __construct() {
							parent::__construct();
							\$this->load->helper(array('form','url','file'));
							\$this->load->model('".$name."_model');
			}

			public function index()
			{
				\$data['titulo']='".$name."';
				\$data['elements'] = \$this->".$name."_model->get();
				\$data['body_template']=\$this->load->view('list_".$name."',\$data,TRUE);
				\$this->load->view('index',\$data,FALSE);
			}
			public function add(){
				if(!empty(\$_POST)){
					\$data['msj'] = \$this->".$name."_model->add(\$_POST);
					redirect(BASEURL);
				}
				\$data['titulo']='".$name."';
				";
				for($i=0;$i<count($claves);$i++){
					$content .= "
				\$data['".$claves[$i]["REFERENCED_TABLE_NAME"]."']=\$this->".$name."_model->".$claves[$i]["REFERENCED_TABLE_NAME"]."();";
				}
				$content .= "
				\$data['body_template']=\$this->load->view('edit_".$name."',\$data,TRUE);
				\$this->load->view('index',\$data,FALSE);
			}

			public function edit(\$id){
				if(!empty(\$_POST)){
					\$data['msj'] = \$this->".$name."_model->edit(\$id,\$_POST);
					redirect(BASEURL);
				}
				\$data['element'] = \$this->".$name."_model->element(\$id);";
				for($i=0;$i<count($claves);$i++){
					$content .= "
				\$data['".$claves[$i]["REFERENCED_TABLE_NAME"]."']=\$this->".$name."_model->".$claves[$i]["REFERENCED_TABLE_NAME"]."();";
				}
				$content .= "
				\$data['titulo']='".$name."';
				\$data['body_template']=\$this->load->view('edit_".$name."',\$data,TRUE);
				\$this->load->view('index',\$data,FALSE);
			}

			public function delete(\$id){
					\$this->".$name."_model->delete(\$id);
					redirect(BASEURL);
			}
		}

		/* End of file ".$tabla.".php */
		/* Location: ./application/controllers/".$tabla.".php */";

		$this->escribir_archivo($content,'controllers',str_ireplace('_','',$tabla));
	}


	function vista_list($tabla,$campos){

		$name = str_ireplace('_','',$tabla);

		$content = "
		<table class=\"table\">
		<thead>
		<tr>
		";

		for ($i=0; $i < count($campos) ; $i++) {
			$content .= '<th>'. ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])) .'</th>
			';
		}
		$content .="<th>Acciones</th>";
		$content .="</tr>
		</thead>
		<tbody>";
		$content .="<?php
		for(\$i=0;\$i<count(\$elements);\$i++){
			?>
			<tr>";
			for($i=0;$i<count($campos);$i++){
					$content.="<td><?=\$elements[\$i]['".$campos[$i]['Field']."']?></td>
					";
			}
		$content.="<td><a href=\"<?=base_url('".$name."')?>/edit/<?=\$elements[\$i]['".$campos[0]["Field"]."']?>\"><i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i></a></td>";

		$content .="</tr>
			<?php	} ?>";

		$content .=	"</tbody>
		</table>
		<hr>
		<p align=\"right\"><a href=\"<?=base_url('".$name."')?>/add/\" class=\"btn btn-warning\" role=\"button\">Agregar</a></p>

		";
		$this->escribir_archivo($content,'views','list_'.str_ireplace('_','',$tabla));
	}

	function vista_form($tabla,$campos,$claves){

		$content='<form action="" method="post" name="form">';
		$name = str_ireplace('_','',$tabla);
		for ($i=0; $i < count($campos) ; $i++) {
			// entro solo cuando tengo que mostrar el campo en el formulario o que no sea el primario
			if((strpos($campos[$i]["Comment"],"noMostrar") === FALSE)&&((strpos($campos[$i]["Key"],"PRI") === FALSE))){
				// comparo nombre de campo con todas las claves,
				//si existe tengo que imprimirel select
				//sino continuar y verificar que tipo de dato es
				$columnas = $this->array__column($claves,'COLUMN_NAME');
				// columnas trae las columnas que son foraneas
				$posicion = array_search($campos[$i]['Field'], $columnas);
				// posicion trae lo siguiente: bool(false) o int(0), int(1), int(2), etc
				if($posicion !== FALSE){
					$camposMostrables = explode(",",$claves[$posicion]["REFERENCED_FIELDS"]);
					// elementos tiene el nombre de la variable que le paso a la vista con los valores del select
					$elementos = $claves[$posicion]["REFERENCED_TABLE_NAME"];
					$content.='
					<div class="form-group">
						<label for="'.$campos[$i]['Field'].'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])).'</label>
						<select class="form-control" id="'.$claves[$posicion]["COLUMN_NAME"].'" name="'.$claves[$posicion]["COLUMN_NAME"].'">
						<?php
						for($i=0;$i < count($'.$elementos.'); $i++){
							echo \'<option value="\'.$'.$elementos.'[$i]["'.$camposMostrables[0].'"].\'">\';
							echo  $' . $elementos . '[$i]["' . $camposMostrables[1] . '"];
							echo \'</option>\';
						}
						?></select></div>';
				}
				else
				{
						if(strpos($campos[$i]['Type'],'varchar')!==FALSE){
							$content.='
							<div class="form-group">
							<label for="'.$campos[$i]['Field'].'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])).'</label>
							<input class="form-control" name="'.$campos[$i]['Field'].'" id="'.$campos[$i]['Field'].'" type="text" value="<?php if(isset($element["'.$campos[$i]['Field'].'"])) echo $element["'.$campos[$i]['Field'].'"];?>">
							</div>
						';
						} // fin tipo varchar
						elseif(strpos($campos[$i]['Type'],'int')!==FALSE){
							$content.='
							<div class="form-group">
							<label for="'.$campos[$i]['Field'].'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])).'</label>
							<input class="form-control" name="'.$campos[$i]['Field'].'" id="'.$campos[$i]['Field'].'" type="number" value="<?php if(isset($element["'.$campos[$i]['Field'].'"])) echo $element["'.$campos[$i]['Field'].'"];?>">
						</div>
						';
						}// fin tipo int
						elseif(strpos($campos[$i]['Type'],'text')!==FALSE){
							$content.='
							<div class="form-group">
							<label for="'.$campos[$i]['Field'].'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])).'</label>
							<textarea class="form-control" name="'.$campos[$i]['Field'].'" id="'.$campos[$i]['Field'].'"><?php if(isset($element["'.$campos[$i]['Field'].'"])) echo $element["'.$campos[$i]['Field'].'"];?></textarea>
						</div>
						';
						} // fin tipo text
						elseif(strpos($campos[$i]['Type'],'date')!==FALSE){
							$content.='
							<div class="form-group">
							<label for="'.$campos[$i]['Field'].'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])).'</label>
							<input class="form-control" name="'.$campos[$i]['Field'].'" id="'.$campos[$i]['Field'].'" type="date" value="<?php if(isset($element["'.$campos[$i]['Field'].'"])) echo $element["'.$campos[$i]['Field'].'"];?>">
						</div>
						';
						}// fin tipo date
						elseif( $campos[$i]['Default']=="CURRENT_TIMESTAMP" ){
								$content.='
								<div class="form-group">
								<label for="'.$campos[$i]['Field'] .'">'.ucfirst(str_replace( "_" , " ", $campos[$i]['Field'])) .'</label>
								<input class="form-control" name="'.$campos[$i]['Field'].'" id="'.$campos[$i]['Field'].'" type="datetime" value="">
								</div>
								';
						}

						else {
							# code...
							$content .= $campos[$i]['Field'] . ' - ' . $campos[$i]['Type'] . '<br>';
						}
				}
			}
		}

		$content .= '<input class="btn btn-warning" type="submit" value="Submit">
		</form>';


		$this->escribir_archivo($content,'views','edit_'.$name);


	}

	function escribir_archivo($data,$folder,$nombre){
			$ruta =  realpath('../bp/') . '/application/' . $folder . '/';
			chmod($ruta, 0777);
			if ( ! write_file($ruta . $nombre .'.php', $data))
			{
				return FALSE;
			}
			else
			{
					return TRUE;
			}
			chmod($ruta, 0775);
	}

	function array__column($array,$column_name){
		return array_map(function($element) use($column_name){return $element[$column_name];}, $array);
	}

}
