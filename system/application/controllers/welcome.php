<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
	}
	
	function index()
	{
		  
		
//		$this->output->cache(1);
		
		  $this->load->model('test_model');
	    $this->test_model->test();
	    
	    $this->load->view('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */