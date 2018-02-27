<php
 class model extends CI_Model {
 
 public function __construct() {
   $this->load->database();
  }
  
  public function set() {
   $data =array(
	'name' => $this->input->post('title'),
	'width'=> $this->input->post('image_width'),
	'height'=> $this->input->post('image_height'),
	'time' => $_SERVER['REQUEST_TIME'] 
	'id'=> uniqid()
	);
   return $this->db->insert('testdb',$data);
  }

  public function update {
   $data = arry(
	'name' => $this->input->post('title'),
	'size'=> $this->input->post('file_size')
	);
    return $this->db->insert('testdb',$data);
 }
}
