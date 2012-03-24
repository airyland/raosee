<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Database Cache Class
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_Cache {

	var $CI;
	var $db;	// allows passing of db object so that multiple database connections and returned db objects can be supported

	/**
	 * Constructor
	 *
	 * Grabs the CI super object instance so we can access it.
	 *
	 */	
	function CI_DB_Cache(&$db)
	{
		// Assign the main CI object to $this->CI
		// and load the file helper since we use it a lot
		$this->CI =& get_instance();
		$this->db =& $db;
		//--change--
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache Directory Path
	 *
	 * @access	public
	 * @param	string	the path to the cache directory
	 * @return	bool
	 */		
	function check_path($path = '')
	{
		if ($path == '')
		{
			if ($this->db->cachedir == '')
			{
				return $this->db->cache_off();
			}
		
			$path = $this->db->cachedir;
		}
	
		// Add a trailing slash to the path if needed
		//--change--
		
		$this->db->cachedir = $path;
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Retrieve a cached query
	 *
	 * The URI being requested will become the name of the cache sub-folder.
	 * An MD5 hash of the SQL statement will become the cache file name
	 *
	 * @access	public
	 * @return	string
	 */
	function read($sql)
	{
		if ( ! $this->check_path())
		{
			return $this->db->cache_off();
		}

		$segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		
		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
	
		$file_name = $segment_one.'-'.$segment_two.'-'.md5($sql);		
		
		if($this->db->cache_method == "storage" ){
			$storage = new SaeStorage();
		  $cachedata = $storage->read( $this->db->cachedir , $file_name  );
			if( $storage->errno() != 0){
		     return FALSE;
		  }
			return unserialize($cachedata);			
		}else{
			$mmc=memcache_init();
			if($mmc){
				 $cachedata = memcache_get( $mmc,$file_name) ;
				 if(!$cachedata){
				 	return unserialize($cachedata);		
				 }else{
				 	return false;
				}
				 
			}else{
			  return false;
			} 			
			
		}
	}	

	// --------------------------------------------------------------------

	/**
	 * Write a query to a cache file
	 *
	 * @access	public
	 * @return	bool
	 */
	 
	 //--chang--
	function write($sql, $object)
	{
		if ( ! $this->check_path())
		{
			return $this->db->cache_off();
		}

		$segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		
		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
	
		$file_name = $segment_one.'-'.$segment_two.'-'.md5($sql);
		
		if($this->db->cache_method == "storage" ){
			$storage = new SaeStorage();
		  $storage->write( $this->db->cachedir , $file_name , serialize($object) );
		  if( $storage->errno() != 0){
		     return FALSE;
		  }		  
		}else{
			$mmc=memcache_init();
			if($mmc){
				 $catalog_data = memcache_get( $mmc,$this->db->cachedir);
				 $catalog;
				 if( $catalog_data == false ){
				  	$catalog = array();
				 }else{
				   	$catalog = unserialize($catalog_data);
				 } 
				 if(  ! array_key_exists($file_name,$catalog) ){
				 	    $catalog[$file_name] = "";
				 	    memcache_set( $mmc,$this->db->cachedir,serialize($catalog) );
				 }
			 	 memcache_set( $mmc,$file_name,serialize($object) ) ;
			}else{
			  return false;
			} 
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache files within a particular directory
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete($segment_one = '', $segment_two = '')
	{	
		if ($segment_one == '')
		{
			$segment_one  = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		}
		
		if ($segment_two == '')
		{
			$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
		}
		
		$file_name = $segment_one.'-'.$segment_two.'-'.'*';
	  if($this->db->cache_method == "storage" ){
			$storage = new SaeStorage();
			$file_list =  $storage->getList($this->db->cachedir,$file_name);
			for($i=0; $i < count($file_list) ;$i++ ){
	  		$storage->delete($this->db->cachedir,$file_list[$i]);
			}
		}else{
			$mmc=memcache_init();
			if($mmc){
				 $catalog_data = memcache_get( $mmc,$this->db->cachedir);
				 $catalog;
				 if( $catalog_data == false ){
            return false;
				 }else{
				   	$catalog = unserialize($catalog_data);
				 } 
				 if( array_key_exists($file_name,$catalog) ){
				    unset( $catalog[$file_name] );
				    memcache_set( $mmc,$this->db->cachedir,serialize($catalog) );
				}
				 memcache_delete( $mmc,$file_name );
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete all existing cache files
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete_all()
	{
		if($this->db->cache_method == "storage" ){
		  $storage = new SaeStorage();
			$file_list =  $storage->getList($this->db->cachedir);
			for($i=0; $i < count($file_list) ;$i++ ){
	  		$storage->delete($this->db->cachedir,$file_list[$i]);
			}			
			if(count($file_list) == 100){
		    delete_all();
		  }
		}else{
			$mmc=memcache_init();
			if($mmc){
				 $catalog_data = memcache_get( $mmc,$this->db->cachedir);
				 $catalog;
				 if( $catalog_data == false ){
            return false;
				 }else{		 	
				   	$catalog = unserialize($catalog_data); 	
				 } 
				 foreach ($catalog as $key=>$value){
	          memcache_delete( $mmc,$key);
				 }
				 $catalog = array();
				 memcache_set( $mmc,$this->db->cachedir,serialize($catalog) );
			} 
		}
	}

}


/* End of file DB_cache.php */
/* Location: ./system/database/DB_cache.php */