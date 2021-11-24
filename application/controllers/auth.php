<?php
defined('BASEPATH') or exit('No direct script access allowed');

class auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        $this->form_validation->set_rules('nrp', 'Nrp', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login.php');
            $this->load->view('templates/auth_footer');
        }
    }
    public function registration()
    {
        //Validation
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user_table.user_email]', [
            'is_unique' => 'This email has already registered !'
        ]);
        $this->form_validation->set_rules('nrp', 'Nrp', 'required|trim|is_unique[user_table.user_nrp]', [
            'is_unique' => 'This NRP has already registered !'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password dont match!',
            'min_length' => 'Password too short !'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'SIM - Pendaftaran Monitoring Kerja Praktik';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration.php');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'user_nama' => htmlspecialchars($this->input->post('name', true)),
                'user_email' => htmlspecialchars($this->input->post('email', true)),
                'user_nrp' => htmlspecialchars($this->input->post('nrp', true)),
                'user_image' => 'default.jpg',
                'user_password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];
            $this->db->insert('user_table', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Congratulation ! Your account has been created ! Please Login
          </div>');
            redirect('auth/login.php');
        }
    }
}
