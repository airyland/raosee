<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test_model
 *
 * @author Administrator
 */
class test_model extends Model {
    function  __construct() {
        parent::Model();
    }
    function test(){

		/*
		CREATE TABLE IF NOT EXISTS `test` (
		  `ID` int(20) NOT NULL auto_increment,
		  `key` varchar(255) NOT NULL,
		  PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
		
		*/
      $this->load->database();
      $this->db->cache_on();
      $query =  $this->db->query('SELECT * FROM test');
      $this->db->cache_delete_all();
      
//    foreach ($query->result() as $row)
//		{
//			   echo $row->ID;
//			   echo $row->key;
//		}
    }

 
}
?>
