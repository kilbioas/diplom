<?php
class User {
	private $data;
	private $permission = array();

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		
		if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND status = '1'");

			if ($user_query->num_rows) {
				$this->data["user_id"] = $user_query->row['user_id'];
				$this->data["username"] = $user_query->row['username'];
				$this->db->query("UPDATE " . DB_PREFIX . "user SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE user_id = '" . (int)$this->session->data['user_id'] . "'");
				$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");
				$permissions = unserialize($user_group_query->row['permission']);
				if (is_array($permissions)) {
					foreach ($permissions as $key => $value)
						$this->permission[$key] = $value;
				}
				$friesnds = $this->db->query("SELECT `friend_id` FROM `" . DB_PREFIX . "user_friend` WHERE `user_id`=" . $this->get("user_id"));
				foreach($friesnds->rows as $friend)
					$this->data["friends"][] = $friend;
			} else
				$this->signout();
		}
	}

	public function signIn($username, $password) {
		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE username = '" . $this->db->escape($username) . "' AND `password`='" . $this->db->escape(md5($password)) . "' AND status = '1'");
		if ($user_query->num_rows) {
			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->data["user_id"] = $user_query->row['user_id'];
			$this->data["username"] = $user_query->row['username'];
			$this->data["ip"] = $user_query->row['ip'];
			$this->data["email"] = $user_query->row['email'];
			$this->data["created_at"] = $user_query->row['created_at'];
			$this->data["updated_at"] = $user_query->row['updated_at'];

			$user_group_query = $this->db->query("SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");
			$permissions = unserialize($user_group_query->row['permission']);
			if (is_array($permissions)) {
				foreach ($permissions as $key => $value)
					$this->permission[$key] = $value;
			}
			$friesnds = $this->db->query("SELECT `friend_id` FROM `" . DB_PREFIX . "user_friend` WHERE `user_id`=" . $this->get("user_id"));
			foreach($friesnds->rows as $friend)
				$this->data["friends"][] = $friend;
			return true;
		} else
			return false;
	}

	public function signout() {
		unset($this->session->data['user_id']);
		$this->data = array();
		session_destroy();
	}

	public function hasPermission($key, $value) {
		return (isset($this->permission[$key]) && in_array($value, $this->permission[$key])) ? true : false;
	}

	public function signedIn() {
		return !empty($this->data);
	}

	public function get($key) {
		return array_key_exists($key, $this->data) ? $this->data[$key] : false;
	}
}
?>