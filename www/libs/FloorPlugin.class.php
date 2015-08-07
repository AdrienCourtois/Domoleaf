<?php
	class FloorPlugin{
		public $api, $api_result, $link;
		
		public function __CONSTRUCT(){
			$this->api = new Api;
			$this->api_result = $this->api->send_request();
			$this->link = Link::get_link('mastercommand');
		}
		
		public function floor_exist($id){
			if (!isset($id) || empty($id) || !is_numeric($id)){ return false; }
			
			$sql = 'SELECT *
		        FROM user_floor
		        WHERE user_id= :user_id AND floor_id = :floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':user_id', $this->api->getId(), PDO::PARAM_INT);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$test = $req->rowCount();
			
			if ($test == 0){ return false; }
			
			return true;
		}
		
		public function getDevices($id, $done){
			if (!$this->floor_exist($id)){ return false; }
			$add = ($done) ? 'AND pos_x_icon NOT LIKE \'%/0\'' : 'AND pos_x_icon LIKE \'%/0\'';
			
			$sql = 'SELECT room_id
					FROM room
					WHERE floor=:floor';
			$req = $this->link->prepare($sql);
			$req->bindValue(':floor', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			$result = array(); 
			
			while ($do = $req->fetch(PDO::FETCH_OBJ)) {
				$this_room_id = $do->room_id;
				
				$sql_device = 'SELECT *
					FROM room_device
					WHERE room_id=:room_id '.$add;
				$req_device = $this->link->prepare($sql_device);
				$req_device->bindValue(':room_id', $do->room_id, PDO::PARAM_INT);
				$req_device->execute() or die (error_log(serialize($req_device->errorInfo())));
				
				if ($req_device->rowCount() > 0){
					$result[] = $req_device->fetch(PDO::FETCH_OBJ);
				}
			}
			
			return $result; 
		}
		
		public function getFloorBackground($id){
			if (!$this->floor_exist($id)){ return false; }
			
			$sql = 'SELECT floor_background_url
					FROM floor
					WHERE floor_id=:floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do = $req->fetch(PDO::FETCH_OBJ);
			
			if (empty($do->floor_background_url) || is_null($do->floor_background_url)){
				return null;
			} 
			
			return $do->floor_background_url;
		}
		
		public function getFloorName($id){
			if (!$this->floor_exist($id)){ return false; }
			
			$sql = 'SELECT floor_name
					FROM floor
					WHERE floor_id=:floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$do = $req->fetch(PDO::FETCH_OBJ);
			
			if (empty($do->floor_name) || is_null($do->floor_name)){
				return null;
			} 
			
			return $do->floor_name;
		}
		
		public function testIfRightEditFloor($id){
			if (!is_numeric($id)){ return array('error'=>_('The specified ID doesn\'t exist.')); }
			if (!$this->api->is_co()){ return array('error'=>_('You must be logged on to do that.')); }
			
			$user_id = $this->api->getId();
	
			$sql = 'SELECT * FROM user_floor WHERE user_id = :user_id AND floor_id = :floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			$r = $req->rowCount();
			
			if ($r == 0){ return array('error'=>_('This floor doesn\'t belong to you.')); }
			
			return array("success"=>true);
		}
		
		public function updateFloorToNull($id){
			$sql = 'UPDATE floor
		        SET floor_background_url = :floor_background_url
		        WHERE floor_id=:floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':floor_background_url', null);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			return true;
		}
		
		public function updateFloorBackground($id, $name){
			$sql = 'UPDATE floor
		        SET floor_background_url= :floor_background_url
		        WHERE floor_id=:floor_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':floor_background_url', $name, PDO::PARAM_STR);
			$req->bindValue(':floor_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			return true;
		}
		
		public function testRightRoomDevice($id){
			if (!is_numeric($id)){ return array("error"=>_('An error occured, please reload the page.')); }
			
			$user_id = $this->api->getId();
			
			$sql = 'SELECT room_id FROM room_device WHERE room_device_id = :room_device_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':room_device_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			if ($req->rowCount() == 0){ return array('error'=>_('This device doesn\'t exist.')); }
			
			$do = $req->fetch(PDO::FETCH_OBJ);
	
			$sql = 'SELECT * FROM user_room WHERE user_id = :user_id AND room_id = :room_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$req->bindValue(':room_id', $do->room_id, PDO::PARAM_INT);
			$req->execute() or die(error_log(serialize($req->errorIngo())));
			
			if ($req->rowCount() == 0){ return array('error' => _('This device doesn\'t belong to you.')); }
			
			return array('success'=>true);
		}
		
		public function updateRoomDevicePosition($id, $x,$y){
			$sql = 'UPDATE room_device
		       SET pos_x_icon= :pos_x_icon , pos_y_icon = :pos_y_icon
		       WHERE room_device_id=:room_device_id';
			$req = $this->link->prepare($sql);
			$req->bindValue(':pos_x_icon', $x, PDO::PARAM_STR);
			$req->bindValue(':pos_y_icon', $y, PDO::PARAM_STR);
			$req->bindValue(':room_device_id', $id, PDO::PARAM_INT);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			
			return true;
		}
		
		public function queryGetDevices($id){
			$list = array();
		
			$sql = 'SELECT room_device_id, room_device.protocol_id, room_id, 
						   room_device.device_id, room_device.name, addr, plus1, 
						   plus2, plus3, device.application_id
					FROM room_device
					JOIN device ON room_device.device_id = device.device_id  WHERE room_device_id = :id
					ORDER BY name';
			$req = $this->link->prepare($sql);
			$req->bindValue(':id', $id, PDO::PARAM_INT);
			$req->execute() or die(error_log(serialize($req->errorIngo())));
			
			while ($do = $req->fetch(PDO::FETCH_OBJ)) {
				$list = array(
					'application_id'=> $do->application_id,
					'room_device_id'=> $do->room_device_id,
					'room_id'       => $do->room_id,
					'device_id'     => $do->device_id,
					'protocol_id'   => $do->protocol_id,
					'name'          => $do->name,
					'addr'          => $do->addr,
					'plus1'         => $do->plus1,
					'plus2'         => $do->plus2,
					'plus3'         => $do->plus3,
					'device_opt'    => array()
				);
			}
			
			$user_id = $this->api->getId();
			$user = new User($user_id);
			
			$sql = 'SELECT room_device.room_device_id, room_device.room_id, 
						   optiondef.hidden_arg, room_device.device_id, 
						   optiondef.option_id, room_device_option.addr,
						   if(optiondef.name'.$user->getLanguage().' = "", optiondef.name, optiondef.name'.$user->getLanguage().') as name,
						   room_device_option.addr_plus, room_device_option.valeur
					FROM room_device
					JOIN room_device_option ON room_device_option.room_device_id = room_device.room_device_id
					JOIN optiondef ON room_device_option.option_id = optiondef.option_id
					WHERE room_device_option.status = 1 AND room_device.room_device_id = '.$id;
			$req = $this->link->prepare($sql);
			$req->execute() or die (error_log(serialize($req->errorInfo())));
			while($do = $req->fetch(PDO::FETCH_OBJ)) {
				if($do->hidden_arg & 4) {
					$list['device_opt'][$do->option_id] = array(
						'option_id'=> $do->option_id,
						'name'     => $do->name,
						'addr'     => $do->addr,
						'addr_plus'=> $do->addr_plus,
						'valeur'   => $do->valeur
					);
				}
			}
			
			return $list;
		}
	}