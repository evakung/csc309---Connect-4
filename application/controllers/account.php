<?php

class Account extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    }
        
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	

    		$protected = array('updatePasswordForm','updatePassword','index','logout');
    		
    		if (in_array($method,$protected) && !isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
          
    
    function loginForm() {
    		$this->load->view('account/loginForm');
    }
    
    function login() {
    		$this->load->library('form_validation');
    		$this->form_validation->set_rules('username', 'Username', 'required');
    		$this->form_validation->set_rules('password', 'Password', 'required');

    		if ($this->form_validation->run() == FALSE)
    		{
    			$this->load->view('account/loginForm');
    		}
    		else
    		{
    			$login = $this->input->post('username');
    			$clearPassword = $this->input->post('password');
    			 
    			$this->load->model('user_model');
    		
    			$user = $this->user_model->get($login);

    			 
    				if (isset($user) && $user->comparePassword($clearPassword)) {
    					
    					if ($user->user_status_id == User::OFFLINE){
    					$_SESSION['user'] = $user;
    					$data['user']=$user;
    				
    					$this->user_model->updateStatus($user->id, User::AVAILABLE);
    				
    					redirect('arcade/index', 'refresh'); //redirect to the main application page
    					}
    					else{
    						$data['errorMsg']='This account is logged in already!';
    						$this->load->view('account/loginForm', $data);
    					}
    				}
    				else {   			
						$data['errorMsg']='Incorrect username or password!';
 						$this->load->view('account/loginForm',$data);
 					}
    			
 			
    		}
    }
    
    function gameLobby(){
    	$this->load->model('user_model');
    	
    	$user = $_SESSION['user'];
    	$this->user_model->updateStatus($user->id, User::AVAILABLE);
    	
    	redirect('arcade/index', 'refresh');
    	
    }

    function logout() {
		$user = $_SESSION['user'];
    	$this->load->model('user_model');
	    $this->user_model->updateStatus($user->id, User::OFFLINE);
    	session_destroy();
    	redirect('account/index', 'refresh'); //Then we redirect to the index page again
    }

    function newForm() {
	    	$this->load->view('account/newForm');
    }
    
    function createNew() {
    		$this->load->library('form_validation');
    	    $this->form_validation->set_rules('username', 'Username', 'required|is_unique[user.login]');
	    	$this->form_validation->set_rules('password', 'Password', 'required');
	    	$this->form_validation->set_rules('first', 'First', "required");
	    	$this->form_validation->set_rules('last', 'last', "required");
	    	$this->form_validation->set_rules('email', 'Email', "required|is_unique[user.email]");
	    	
	    	include_once $_SERVER['DOCUMENT_ROOT'] . '/securimage/securimage.php';  //Securimage source code
	    	$securimage = new Securimage(); //creates a new Securimage object that is responsible for creating, managing and validating captcha codes
	    	
	    	if ($securimage->check($_POST['captcha_code']) == false) {
	    		// the code was incorrect
	    		// you should handle the error so that the form processor doesn't continue
	    	
	    		// or you can use the following code if there is no validation or you do not know how
	    	?>
	    	<head>
				<style type="text/css">
					*{
						font-family: "Century Gothic", CenturyGothic, Geneva, AppleGothic, sans-serif;
						text-align:center;
						color: red;

					}
					body{
						background-image: url('http://backgrounds.picaboo.com/download/15/55/155f32631fd64f138687514eb46b2e8a/butterfly2.jpg'); 
						background-attachment: fixed;
					}
					.blue{
						color: blue;
					}
					input {
						display: block;
						margin: 0px auto;
					}
				</style> 
			</head> 
	
	<?php	
	    		echo "<b>Error: Your captcha input was incorrect</b><br><br>";
	    		echo "<i>Please go <a href='javascript:history.go(-1)'><span class='blue'>back</span></a> and re-enter the correct captcha code.<i>";
	    		exit;
	    	}
	    	
	    
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/newForm');
	    	}
	    	else  
	    	{
	    		$user = new User();
	    		 
	    		$user->login = $this->input->post('username');
	    		$user->first = $this->input->post('first');
	    		$user->last = $this->input->post('last');
	    		$clearPassword = $this->input->post('password');
	    		$user->encryptPassword($clearPassword);
	    		$user->email = $this->input->post('email');
	    		
	    		$this->load->model('user_model');
	    		 
	    		
	    		$error = $this->user_model->insert($user);
	    		
	    		$this->load->view('account/loginForm');
	    	}
    }

    
    function updatePasswordForm() {
	    	$this->load->view('account/updatePasswordForm');
    }
    
    function updatePassword() {
	    	$this->load->library('form_validation');
	    	$this->form_validation->set_rules('oldPassword', 'Old Password', 'required');
	    	$this->form_validation->set_rules('newPassword', 'New Password', 'required');
	    	 
	    	 
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/updatePasswordForm');
	    	}
	    	else
	    	{
	    		$user = $_SESSION['user'];
	    		
	    		$oldPassword = $this->input->post('oldPassword');
	    		$newPassword = $this->input->post('newPassword');
	    		 
	    		if ($user->comparePassword($oldPassword)) {
	    			$user->encryptPassword($newPassword);
	    			$this->load->model('user_model');
	    			$this->user_model->updatePassword($user);
	    			redirect('arcade/index', 'refresh'); //Then we redirect to the index page again
	    		}
	    		else {
	    			$data['errorMsg']="Incorrect password!";
	    			$this->load->view('account/updatePasswordForm',$data);
	    		}
	    	}
    }
    
    function recoverPasswordForm() {
    		$this->load->view('account/recoverPasswordForm');
    }
    
    function recoverPassword() {
	    	$this->load->library('form_validation');
	    	$this->form_validation->set_rules('email', 'email', 'required');
	    	
	    	if ($this->form_validation->run() == FALSE)
	    	{
	    		$this->load->view('account/recoverPasswordForm');
	    	}
	    	else
	    	{ 
	    		$email = $this->input->post('email');
	    		$this->load->model('user_model');
	    		$user = $this->user_model->getFromEmail($email);

	    		if (isset($user)) {
	    			$newPassword = $user->initPassword();
	    			$this->user_model->updatePassword($user);
	    			
	    			$this->load->library('email');
	    		
	    			$config['protocol']    = 'smtp';
	    			$config['smtp_host']    = 'ssl://smtp.gmail.com';
	    			$config['smtp_port']    = '465';
	    			$config['smtp_timeout'] = '7';
	    			$config['smtp_user']    = 'your gmail user name';
	    			$config['smtp_pass']    = 'your gmail password';
	    			$config['charset']    = 'utf-8';
	    			$config['newline']    = "\r\n";
	    			$config['mailtype'] = 'text'; // or html
	    			$config['validation'] = TRUE; // bool whether to validate email or not
	    			
		    	  	$this->email->initialize($config);
	    			
	    			$this->email->from('csc309Login@cs.toronto.edu', 'Login App');
	    			$this->email->to($user->email);
	    			
	    			$this->email->subject('Password recovery');
	    			$this->email->message("Your new password is $newPassword");
	    			
	    			$result = $this->email->send();
	    			
	    			//$data['errorMsg'] = $this->email->print_debugger();	
	    			
	    			//$this->load->view('emailPage',$data);
	    			$this->load->view('account/emailPage');
	    			
	    		}
	    		else {
	    			$data['errorMsg']="No record exists for this email!";
	    			$this->load->view('account/recoverPasswordForm',$data);
	    		}
	    	}
    }    
 }

