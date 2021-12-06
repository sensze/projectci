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
                'is_active' => 0,
                'date_created' => time()
            ];

            //token
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $this->input->post('email', true),
                'token' => $token,
                'date_created' => time()
            ];


            $this->db->insert('user_table', $data);
            $this->db->insert('user_token', $user_token);

            $this->_sendEmail($token, 'verify');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Congratulation ! Your account has been created ! Please activate your account !
          </div>');
            redirect('auth');
        }
    }

    private function _sendEmail($token, $type)
    {
        $config = [
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'simpmkp.ver@gmail.com',  // Email gmail
            'smtp_pass' => 'simpmkpversurabaya',  // Password gmail
            'smtp_port' => 465,
            'newline' => "\r\n"
        ];
        $this->load->library('email', $config);

        $this->email->from('simpmkp.ver@gmail.com', 'SIM-PMKP Verification System');
        $this->email->to($this->input->post('email'));

        if ($type == 'verify') {

            $this->email->subject('SIM - PMKP Account Verification');
            $this->email->message('Click this link to verify your account : <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
        } else if ($type == 'forgot') {
            $this->email->subject('Reset Password');
            $this->email->message('Click this link to reset your password : <a href="' . base_url() . 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
        }

        if ($this->email->send()) {
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
        }
    }

    public function verify()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user_table', ['user_email' => $email])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
            if ($user_token) {
                if (time() - $user_token['date_created'] < 86400) {
                    $this->db->set('is_active', 1);
                    $this->db->where('user_email', $email);
                    $this->db->update('user_table');

                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           ' . $email . ' has been activated ! Please login !
          </div>');
                    redirect('auth');
                } else {

                    $this->db->delete('user_table', ['email' => $email]);
                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Account activation failed ! Token expired !
          </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Account activation failed ! Token invalid !
          </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Account activation failed ! Wrong email !
          </div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('user_nrp');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           You have been logged out !
          </div>');
        redirect('auth');
    }
    public function blocked()
    {
        $this->load->view('auth/blocked');
    }

    public function forgotpassword()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Forgot Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/forgot-password.php');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email');
            $this->db->get_where('user_table', ['user_email' => $email, 'is_active' => 1])->row_array();

            if ($user) {
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $this->input->post('email', true),
                    'token' => $token,
                    'date_created' => time()
                ];
                $this->db->insert('user_token', $user_token);
                $this->_sendEmail($token, 'forgot');
                $this->session->set_flashdata('message', '<div class="alert alert-succes" role="alert">
           Please check your email !
          </div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Email is not registered or activated !
          </div>');
                redirect('auth/forgotpassword');
            }
        }
    }

    public function resetpassword()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user_table', ['user_email' => $email])->row_array();
        if ($user) {
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Reset password failed ! Wrong email !
          </div>');
            redirect('auth/forgotpassword');
        }
    }
    
    
}
