<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


class Upgrade_lib
{
	private $ci;

	private $versions = array('0.9.8-rc1', '0.9.8-rc2', '0.9.8', '0.9.8.1');

	function __construct()
	{
		$this->ci =& get_instance();
	}
	
	
	function upgrade()
	{

		$this->ci->ion_auth->logout();

  		$this->ci->load->database();
  		$this->ci->load->dbforge();


		$db_version = $this->ci->settings->item('version');
		$file_version = CMS_VERSION;

		list($base_db_version) = explode('-', $db_version);

		if(!$db_version)
		{
			show_error('We have no idea what version you are using, which means it must be v0.9.8-rc1 or before.
				Please manually upgrade everything to v0.9.8-rc1 then you can use this upgrade script past that. Look at /docs/UPGRADE to see how.');
		}

		// Upgrade is already done
  		if($db_version == $file_version)
  		{
  			show_error('Looks like the upgrade is already complete, you are already running '.$db_version.'.');
  		}

		// File version is not supported
  		if(!in_array($file_version, $this->versions))
  		{
  			show_error('The upgrade script does not support version '.$file_version.'.');
  		}

		// DB is ahead of files
		else if( $base_db_version > $file_version )
		{
			show_error('The database is expecting '.$db_version.' but the version of PyroCMS you are using is '.$file_version.'. Try downloading a newer version from ' . anchor('http://pyrocms.com/') . '.');
		}


		while($db_version != $file_version)
  		{
	  		// Find the next version
	  		$pos = array_search($db_version, $this->versions) + 1;
	  		$next_version = $this->versions[$pos];

  			// Run the method to upgrade that specific version
	  		$function = 'upgrade_' . preg_replace('/[^0-9a-z]/i', '', $next_version);

	  		if($this->$function() !== TRUE)
	  		{
	  			show_error('There was an error upgrading to "'.$next_version.'"');
	  		}

	  		$this->ci->settings->set_item('version', $next_version);

			$this->output("Upgraded to " . $next_version);

	  		$db_version = $next_version;

  		}
		

		// Have to do a javascript redirect
		echo '<script type="text/javascript">self.parent.location="' . site_url('upgrade/complete') . '";</script>';
		@ob_flush();
		@flush();

	}


	function upgrade_0981()
	{
		$this->output('Adding comments_enabled field to pages table...');
		//add display_name to profiles table
		$this->ci->dbforge->add_column('pages', array(
			'comments_enabled' => array(
				'type' 	  	=> 'INT',
				'constraint' => 1,
				'default' => 0,
				'null' 		=> FALSE
			)
        ));

		$this->output('Clearing the page cache...');
		$this->ci->cache->delete_all('pages_m');

		$this->output('Adding theme_layout field to page_layouts table...');
		//add display_name to profiles table
		$this->ci->dbforge->add_column('page_layouts', array(
			'theme_layout' => array(
				'type' 	  	=> 'VARCHAR',
				'constraint' => '100',
				'null' 		=> TRUE
			)
        ));

		$this->output('Adding display_name field to profiles table...');
		$this->ci->dbforge->add_column('profiles', array(
			'display_name' => array(
				'type' 	  	=> 'VARCHAR',
				'constraint' => '100',
				'null' 		=> TRUE
			)
        ));

        //get the profiles
        $this->ci->db->select('profiles.id, users.id as user_id, profiles.first_name, profiles.last_name');
		$this->ci->db->join('profiles', 'profiles.user_id = users.id', 'left');
		$profile_result = $this->ci->db->get('users')->result_array();

		//insert the display names into profiles
		foreach ($profile_result as $profile_data)
		{
			$this->output('Inserting user ' . $profile_data['user_id'] . ' display_name into profiles table...');

			$data = array('display_name' => $profile_data['first_name'].' '.$profile_data['last_name']);
			$this->ci->db->where('id', $profile_data['id']);
			$this->ci->db->update('profiles', $data);
		}

		$this->output("Changing Forum Tables Collation...");
		$this->ci->db->query("ALTER TABLE  `forums` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$this->ci->db->query("ALTER TABLE  `forum_posts` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$this->ci->db->query("ALTER TABLE  `forum_categories` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$this->ci->db->query("ALTER TABLE  `forum_subscriptions` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

		$this->output("Changing Forum Table Column Collation...");
		$this->ci->db->query("ALTER TABLE  `forums` CHANGE  `title`  `title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
		$this->ci->db->query("ALTER TABLE  `forums` CHANGE  `description`  `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  ''");

		$this->ci->db->query("ALTER TABLE  `forum_categories` CHANGE  `title`  `title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  ''");

		$this->ci->db->query("ALTER TABLE  `forum_posts` CHANGE  `content`  `content` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
		$this->ci->db->query("ALTER TABLE  `forum_posts` CHANGE  `title`  `title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  ''");


		return TRUE;
	}


	function upgrade_098()
	{
		//rename the users columns
		$this->output('Renaming photo description to captions...');
		$this->ci->dbforge->modify_column('photos', array(
			'description' => array(
				'name' 	  => 'caption',
				'type' 	  => 'VARCHAR',
				'constraint' => 100,
			)
		));

		return TRUE;
	}

 	// Upgrade
 	function upgrade_098rc2()
	{
		$this->output('Moving existing "photo" comments to photo-album comments.');
		$this->ci->db->where('module', 'photos');
		$this->ci->db->update('comments', array('module' => 'photos-album'));

		// Create a "unsorted" widget area
		$this->output('Adding unsorted widget area.');
		$this->ci->db->insert('widget_areas', array('slug' => 'unsorted', 'title' => 'Unsorted'));

		$this->output('Adding ip_address to comments.');
		$this->ci->dbforge->add_column('comments', array(
			'ip_address' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '15'
			)
		));

		//if the groups tables doesnt exist lets do some magic
		if ( $this->ci->db->table_exists('groups'))
		{
			show_error('The table "groups" already exists.');
		}

		$this->ci->db->select(array('users.id as user_id, users.first_name, users.last_name, users.lang, profiles.bio, profiles.dob, profiles.gender, profiles.phone, profiles.mobile, profiles.address_line1, profiles.address_line2, profiles.address_line3, profiles.postcode, profiles.msn_handle, profiles.aim_handle, profiles.yim_handle, profiles.gtalk_handle, profiles.gravatar, profiles.updated_on'));
		$this->ci->db->join('profiles', 'profiles.user_id = users.id', 'left');
		$profile_result = $this->ci->db->get('users')->result_array();

		//drop the profiles table
		$this->output('Dropping the profiles table.');
		$this->ci->dbforge->drop_table('profiles');

		//create the meta table
		$this->ci->dbforge->add_field('id');
		$profiles_fields = array(
			'user_id' => array(
				'type' 	  	  => 'INT',
				'constraint' 	  => 11,
				'unsigned' 	  => TRUE,
				'auto_increment' => FALSE,
				'null' => FALSE,
			),
				'first_name' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '50',
				'null' => FALSE,
			),
				'last_name' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '50',
				'null' => FALSE,
			),
				'company' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => FALSE,
			),
			'lang' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '2',
				'null' => FALSE,
				'default' => 'en',
			),
			'bio' => array(
				'type' 	  => 'text',
				'null' => TRUE,
			),
			'dob' => array(
				'type' 	  => 'INT',
				'constraint' => '11',
				'null' => TRUE,
			),

			'gender' => array(
				'type' 	  => "set('m','f','')",
				'null' => TRUE,
			),
			'phone' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'mobile' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'address_line1' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
			),
			'address_line2' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
			),
			'address_line3' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
			),
			'postcode' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'msn_handle' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE,
			),
			'yim_handle' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE,
			),
			'aim_handle' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE,
			),
			'gtalk_handle' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE,
			),
			'gravatar' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE,
			),
			'updated_on' => array(
				'type' 	  => 'INT',
				'constraint' => '11',
				'unsigned' 	  => TRUE,
				'auto_increment' => FALSE,
			),
		);

		$this->ci->dbforge->add_field($profiles_fields);
		$this->output('Creating profiles table...');
		$this->ci->dbforge->create_table('profiles');

		//insert the profile data
		foreach ($profile_result as $profile_data)
		{
			$this->output('Inserting user ' . $profile_data['user_id'] . ' into profiles table...');

			$this->ci->db->insert('profiles', $profile_data);
		}

			$this->ci->db->select(array('id, role'));
			$role_user_query = $this->ci->db->get('users');

			$role_user_result = $role_user_query->result_array();

			//update roles to group_id
			$this->output('Converting roles to group_ids');
			foreach ($role_user_result as $role)
			{
				$role_query = $this->ci->db->select(array('id'))->where('abbrev', $role['role'])->get('permission_roles');
				$current_role = $role_query->row_array();

				$this->ci->db->where('id', $role['id'])->update('users', array('role' => $current_role['id']));
			}

			//rename permission_roles table
			$this->output('Renaming permission_roles to groups ');
			$this->ci->dbforge->rename_table('permission_roles', 'groups');

			//add new groups field
			$this->output('Adding columns to groups table ');
			$this->ci->dbforge->add_column('groups', array(
				'description' => array(
					'type' 	  	=> 'VARCHAR',
					'constraint' => 100,
					'null' 		=> TRUE,
				  ),
			));

			//rename the groups columns
			$this->output('Renaming the groups columns ');
			$this->ci->dbforge->modify_column('groups', array(
				'abbrev' => array(
					'name' 	  => 'name',
					'type' 	  => 'VARCHAR',
					'constraint' => '100',
				)
			));

			//rename the users columns
			$this->output('Renaming the users columns ');
			$this->ci->dbforge->modify_column('users', array(
				'is_active' => array(
					'name' 	  => 'active',
					'type' 	  => 'INT',
					'constraint' => '1',
				),
				'ip' => array(
					'name' 	   => 'ip_address',
					'type' 	   => 'VARCHAR',
					'constraint' => '16',
				 ),
				'activation_code' => array(
					'name' 	    => 'activation_code',
					'type' 	    => 'VARCHAR',
					'constraint' => '40',
					'null' 	    => TRUE
				),
				'role' => array(
					'name' 	     => 'group_id',
					'type' 	     => 'INT',
					'constraint' => '11',
					'null' 	     => TRUE,
				)
			));

			// add new users fields
			$this->output('Adding columns to users table ');

		$this->ci->dbforge->add_column('users', array(
			'username' => array(
				'type' 	  	=> 'VARCHAR',
				'constraint' => 20,
				'null' 		=> TRUE,
			),
			'forgotten_password_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 40,
				'null' 		=> TRUE
			),
			'remember_code' => array(
				'type' 	  => 'VARCHAR',
				'constraint' => 40,
				'null' 	  => TRUE
			)
        ));

		//removing columns from users table
		$this->output('Removing columns from users table ');
		$this->ci->dbforge->drop_column('users', 'first_name');
		$this->ci->dbforge->drop_column('users', 'last_name');
		$this->ci->dbforge->drop_column('users', 'lang');

		$this->output('Creating forum_categories table...');

		$this->ci->db->query("CREATE TABLE `forum_categories` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `title` varchar(100) NOT NULL,
		  `permission` mediumint(2) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT='Splits forums into categories'");

		$this->output('Creating forum_posts table...');
		$this->ci->db->query("CREATE TABLE `forum_posts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `forum_id` int(11) NOT NULL DEFAULT '0',
		  `author_id` int(11) NOT NULL DEFAULT '0',
		  `parent_id` int(11) NOT NULL DEFAULT '0',
		  `title` varchar(100) NOT NULL DEFAULT '',
		  `content` text NOT NULL,
		  `type` tinyint(1) NOT NULL DEFAULT '0',
		  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
		  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
		  `created_on` int(11) NOT NULL DEFAULT '0',
		  `updated_on` int(11) NOT NULL DEFAULT '0',
		  `view_count` int(11) NOT NULL DEFAULT '0',
		  `sticky` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

		$this->output('Creating forum_subscriptions table...');
		$this->ci->db->query("CREATE TABLE `forum_subscriptions` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `topic_id` int(11) NOT NULL DEFAULT '0',
		  `user_id` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

		$this->output('Creating forums table...');
		$this->ci->db->query("CREATE TABLE `forums` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `title` varchar(100) NOT NULL DEFAULT '',
		  `description` varchar(255) NOT NULL DEFAULT '',
		  `category_id` int(11) NOT NULL DEFAULT '0',
		  `permission` int(2) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT='Forums are the containers for threads and topics.'");


		return TRUE;
	}


	public function output($message)
	{
		echo '<li style="font: 14px Helvetica,Verdana; list-style: none; margin: 3px 0;">' . $message . '</li>';
		@ob_flush();
		@flush();
	}
}

/* End of file upgrade_lib.php */
/* Location: ./upgrade/libraries/upgrade_lib.php */