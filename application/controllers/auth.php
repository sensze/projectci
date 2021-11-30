<?php
defined('BASEPATH') or exit('No direct script access allowed');

class auth extends CI_Controller
{
    //Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    //Login page lek gasalah wkwk
    public function index()
    {
        $this->form_validation->set_rules('nrp', 'Nrp', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login.php');
            $this->load->view('templates/auth_footer');
        } else {
            //validasi success
            $this->_login();
        }
    }

    //login 
    private function _login()
    {
        $nrp = $this->input->post('nrp');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user_table', ['user_nrp' => $nrp])->row_array();

        //user ada
        if ($user) {
            //jika user aktif
            if ($user['is_active'] == 1) {
                if (password_verify($password, $user['user_password'])) {
                    $data = [
                        'nrp' => $user['user_nrp'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Wrong password !
               </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                This NRP has not been activated ! Contact your administrator !
               </div>');
                redirect('auth');
            }
        } else {
            //tidak ada user dengan email itu
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            NRP is not registered !
           </div>');
            redirect('auth');
        }
    }
    //Registration
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
                'user_password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];
            $this->db->insert('user_table', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Congratulation ! Your account has been created ! Please Login
          </div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           You have been logged out !
          </div>');
        redirect('auth');
    }
}
