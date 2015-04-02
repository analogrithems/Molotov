<?php

include_once('./bootstrap.php');

use \Phalcon\Db\Column;
use \Phalcon\Db\Index;
use Molotov\Modules\Auth\Models\User;
use Molotov\Modules\Auth\Models\Group;
use Molotov\Modules\Auth\Models\Role;
use Molotov\Modules\Auth\Models\UserGroups;

class MolotovInstaller {
	public $di;
	public $first_user = 'admin@example.com';
	public $password = 'P@ssw0rd';
	public $first_group = 'Acme';
	public $db_driver = 'Mysql';
	public $db_args = array();
	public $db_list = array();
	protected $db;
	
	public function __construct(){
		$this->di = \Phalcon\DI::getDefault();
		$this->db_list = array(
			'Sqlite' => array(
				'dbname'=>APP_ROOT_DIR.'data/molotov.db'
			),
			'Mysql'=> array(
				"hostname" => "localhost",
				"dbname" => "molotov",
				"username" => "molotov",
				"password" => "secret",
				"charset" => 'utf8'
			),
			'Postgresql'=> array(
				"hostname" => "localhost",
				"dbname" => "molotov",
				"username" => "molotov",
				"password" => "secret",
				"charset" => 'utf8'
			)
		);
		if( !$this->di->get('db') ){
			die("No Database configured in Config/Config.php");
		}

	}
	
	public function install(){
		$this->install_tables();
		$this->clearTables();
		$this->install_default_data();
		$this->saveConfig();
	}
	
	public function install_default_data(){
		$this->createCapabilities();
		
		if($this->first_group){
			$group = new Group();
			$group->name = $this->first_group;
			$group->save();
			
			if($group->id){
				//add the user
				$passwordHasher = new \Hautelook\Phpass\PasswordHash(8, true);
				$user = new User();
				$user->display_name = 'Super Admin';
				$user->email = $this->first_user;
				$user->password = $passwordHasher->HashPassword($this->password);
				$user->group_id = $group->id;
				$user->enabled = 1;
				$user->created = date('Y-m-d H:i:s');
				$user->save();
				

				$userGroup = new UserGroups();
				$userGroup->user_id = $user->id;
				$userGroup->group_id = $group->id;
				$userGroup->setRole('administrator');
				$userGroup->save();

			}
		}
	}
	
	public function install_tables(){
		
		#This script creates the default tables for the application
		/*
		 * doesn't work for sqlite
		$this->db->createTable(
		    "user",
		    null,
		    array(
		       "columns" => array(
		            new Column("id",
		                array(
		                    "type"          => Column::TYPE_INTEGER,
		                    "size"          => 10,
		                    "notNull"       => true,
		                    'unsigned' 		=> TRUE,
		                    "autoIncrement" => true
		                )
		            ),
		            new Column("display_name",
		                array(
		                    "type"    => Column::TYPE_VARCHAR,
		                    "size"    => 255,
		                    "notNull" => true,
		                )
		            ),
		            new Column("email",
		                array(
		                    "type"    => Column::TYPE_VARCHAR,
		                    "size"    => 255,
		                    "notNull" => true,
		                )
		            ),
		            new Column("password",
		                array(
		                    "type"    => Column::TYPE_VARCHAR,
		                    "size"    => 255,
		                    "notNull" => true,
		                )
		            ),
		            new Column("group_id",
		                array(
		                    "type"    => Column::TYPE_INTEGER,
		                    "size"    => 11,
		                    "notNull" => true,
		                )
		            ),
		            new Column("enabled",
		                array(
		                    "type"    => Column::TYPE_INTEGER,
		                    "size"    => 4,
		                    "notNull" => true,
		                )
		            ),
		            new Column("created",
		                array(
		                    "type"    => Column::TYPE_DATETIME,
		                    "notNull" => true,
		                )
		            ),
		        ),
				"indexes" => array(
					new Index("PRIMARY", array("id")),
					new Index("email", array("email")),
				),
               "options" => ('Sqlite' != $this->db_driver) ? array(
                    "TABLE_TYPE"      => "BASE TABLE",
                    "ENGINE"          => "InnoDB",
                    "TABLE_COLLATION" => "utf8_general_ci",
                ) : array()
		    )
		);
		*/
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `user_groups` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) unsigned NOT NULL,
		  `group_id` int(10) unsigned NOT NULL,
		  `role_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `user` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `display_name` varchar(255) NOT NULL,
		  `email` varchar(255) NOT NULL,
		  `password` varchar(50) NOT NULL,
		  `group_id` int(11) NOT NULL,
		  `enabled` tinyint(4) NOT NULL,
		  `created` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `sessions` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) unsigned NOT NULL,
		  `token` varchar(64) NOT NULL,
		  `session` text NOT NULL,
		  `ip` varchar(15) NOT NULL,
		  `created` datetime NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `role_capabilities` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `role_id` int(10) unsigned NOT NULL,
		  `capability_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `role` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `group_id` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `groups` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `emailactivations` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) unsigned NOT NULL,
		  `activation_key` varchar(50) NOT NULL,
		  `type` enum('verify','passwordreset','signup') NOT NULL,
		  `used` tinyint(4) NOT NULL,
		  `created` datetime NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		$sql_install[] = "CREATE TABLE IF NOT EXISTS `capability` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `capability` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		
		foreach($sql_install as $s){
			$this->db->execute($s);
		}
	}
	
	public function clearTables(){
		$sql = array(
			'TRUNCATE TABLE capability',
			'TRUNCATE TABLE emailactivations',
			'TRUNCATE TABLE groups',
			'TRUNCATE TABLE role',
			'TRUNCATE TABLE role_capabilities',
			'TRUNCATE TABLE sessions',
			'TRUNCATE TABLE user',
			'TRUNCATE TABLE user_groups'
		);
		foreach($sql as $s){
			$this->db->execute($s);
		}
	}
	
	public function get_db_settings_cli(){
    
	    //get db driver
	    print "Which database would you like to use?  The following are supported: [".join(', ',array_keys($this->db_list))."]\n";
	    $temp_db =  false;
	    while( !$temp_db || !array_key_exists($temp_db, $this->db_list)){
		    $temp_db =  $this->readline("DB Driver [".$this->db_driver."]: ");
		    if(!$temp_db){
			    $temp_db = $this->db_driver;
		    }
	    }
		$this->db_driver = $temp_db;
		
		//get db args
		foreach($this->db_list[$this->db_driver] as $idx=>$value){
			$tmp_value = $this->readline("DB {$idx} [".$value."]: ");
			if($tmp_value){
				$this->db_args[$idx] = $tmp_value;
			}else{
				$this->db_args[$idx] = $value;			
			}
		}
	}
	
	public function get_admin_account_cli(){
	    //get username
	    $temp_first_user =  $this->readline("Admin Email [".$this->first_user."]: ");
	    if($temp_first_user && strlen($temp_first_user) > 1){
			$this->first_user = $temp_first_user;
	    }
	    
	    //get password
	    $temp_password =  $this->readline("Admin Password [".$this->password."]: ");
	    if($temp_password && strlen($temp_password) > 1){
			$this->password = $temp_password;
	    }
	    
	    //get group
	    $temp_first_group =  $this->readline("Admin Group [".$this->first_group."]: ");
	    if($temp_first_group && strlen($temp_first_group) > 1){
			$this->first_group = $temp_first_group;
	    }
	}
	
	public function test_db_creds(){
		try{
			switch($this->db_driver){
				case 'Mysql':
					$this->db = new \Phalcon\Db\Adapter\Pdo\Mysql($this->db_args);
					break;
				case 'Postgresql':
					$this->db = new \Phalcon\Db\Adapter\Pdo\Postgresql($this->db_args);
					break;
				default:
					if( isset($this->db_args['dbname']) &&  !file_exists($this->db_args['dbname']) ){
						if( !file_exists(dirname($this->db_args['dbname']))){
							mkdir(dirname($this->db_args['dbname']),0750,true);
						}
					}
					$this->db = new \Phalcon\Db\Adapter\Pdo\Sqlite($this->db_args);
					break;			
			}
		}catch (\Exception $e) {
		    return false;
		}
		
		return $this->db;
	}
	
	public function saveConfig(){
		file_put_contents(APP_ROOT_DIR.'Config/Config.php', $this->generateConfig());
	}
	
	public function generateConfig(){
		$c = '';
		$config = include(APP_ROOT_DIR.'Config/Config.php.sample');
		$config['db']['driver'] = $this->db_driver;
		$config['db']['creds'] = $this->db_args;

		$c .= "<?php\n/*\n";
		$c .= "* Movotov Base Settings\n";
		$c .= "*/\nreturn ".var_export($config,true).";\n";
		
		return $c;
	}
	
	public function dump_config(){
		print "Test:\n".$this->generateConfig();
	}
	
	public function readline( $prompt = '' ){
	    echo $prompt;
	    return rtrim( fgets( STDIN ), "\n" );
	}

	/**
	 * a handful of things need to be done when a group is created such as building out the default roles and capabilities
	 */	
	public function createCapabilities(){
		$this->db->execute("TRUNCATE table capability");
		//create default roles for this new group
		include_once(AUTH_MODULE_DIR.'/data/default_roles.php');
		$_caps = array();
		foreach($default_roles as $name=>$caps){
			foreach($caps as $cap){
				if( !in_array($cap, $_caps)){
					$_caps[] = $cap;
				}
			}
		}
		foreach($_caps as $cap){
			//insert into database
			$this->db->execute("INSERT into capability (`capability`) VALUES ('{$cap}')");
		}
	}
}

if (php_sapi_name() == "cli") {
	

    // In cli-mode
	$installer = new MolotovInstaller();
    print "Ok, it's time to install Molotov, lets get a few pieces of information first.\n";
    $installer->get_admin_account_cli();

	do{
	    $installer->get_db_settings_cli();
	}while( !$installer->test_db_creds() && print "Invalid database settings, try again!\n");

	$installer->install();//create tables, add first user, write settings

} else {
    // Not in cli-mode, render a view
}



