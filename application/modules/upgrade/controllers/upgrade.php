<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Upgrade extends Controller
{

	/**
	 * Constructor method
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('upgrade');
		$this->load->library('upgrade_lib');
	}
	
	/**
	 * Index method
	 * 
	 * @access public
	 * @return void
	 */
	public function index()
	{
		// Prevent upgrade from being ran twice
		$this->settings->item('version') == CMS_VERSION and show_error(lang('upgrade.messages.cant_upgrade'));

		$data['current_version'] = $this->settings->item('version');
		$data['target_version'] = CMS_VERSION;

		$data['page_output'] = $this->load->view('main',$data,TRUE);
		
		$this->load->view('global',$data);
	}

	public function process()
	{
		$data['page_output'] = $this->load->view('process','',TRUE);

		$this->load->view('global',$data);
	}

	public function complete()
	{
		$data['page_output'] = $this->load->view('complete','',TRUE);

		$this->load->view('global',$data);
	}

	public function do_upgrade()
	{
		$this->upgrade_lib->upgrade();
	}

}

/* End of file upgrade.php */
/* Location: ./upgrade/controllers/upgrade.php */