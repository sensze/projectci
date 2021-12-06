<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }
    public function index()
    {
        $data['title'] = 'Profile';
        $data['user'] = $this->db->get_where('user_table', ['user_nrp' => $this->session->userdata('nrp')])->row_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function edit()
    {
        $data['title'] = 'Edit Profil';
        $data['user'] = $this->db->get_where('user_table', ['user_nrp' => $this->session->userdata('nrp')])->row_array();

        $this->form_validation->set_rules('name', 'Full Name', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $name = $this->input->post('name');
            $nrp = $this->input->post('nrp');

            //cek jika ada gambar yang di upload
            $upload_image = $_FILES['image']['name'];

            if ($upload_image) {
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']     = '2048';
                $config['upload_path'] = './assets/img/profile/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    $old_image = $data['user']['user_image'];
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile/' . $old_image);
                    }

                    $new_image = $this->upload->data('file_name');
                    $this->db->set('user_image', $new_image);
                } else {
                    echo $this->upload->display_errors();
                }
            }

            $this->db->set('user_nama', $name);
            $this->db->where('user_nrp', $nrp);
            $this->db->update('user_table');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Your profile has been updated !
           </div>');
            redirect('user');
        }
    }
    public function changepassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user_table', ['user_nrp' => $this->session->userdata('nrp')])->row_array();


        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('templates/footer');
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if (!password_verify($current_password, $data['user']['user_password'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
           Wrong password !
           </div>');
                redirect('user/changepassword');
            } else {
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Password cannot be the same as current password !
                    </div>');
                    redirect('user/changepassword');
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    $this->db->set('user_password', $password_hash);
                    $this->db->where('user_nrp', $this->session->userdata('nrp'));
                    $this->db->update('user_table');

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                    Password changed !
                    </div>');
                    redirect('user/changepassword');
                }
            }
        }
    }

    public function daftarKP()
    {
        $data['title'] = 'Pengajuan Tempat Kerja Praktik';
        $data['user'] = $this->db->get_where('user_table', ['user_nrp' => $this->session->userdata('nrp')])->row_array();

        $this->form_validation->set_rules('judul', 'Judul', 'required|trim');
        $this->form_validation->set_rules('tempat', 'tempat', 'required|trim');
        $this->form_validation->set_rules('alamat', 'Alamat KP', 'required|trim');
        $this->form_validation->set_rules('waktu', 'waktu mulai', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/daftarKP', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'judul' => htmlspecialchars($this->input->post('judul', true)),
                'tempat' => htmlspecialchars($this->input->post('tempat', true)),
                'alamat_kp' => htmlspecialchars($this->input->post('alamat', true)),
                'waktu_mulai' => htmlspecialchars($this->input->post('waktu', true)),
                'NRP' => htmlspecialchars($this->session->userdata('nrp'))
            ];
            $this->db->insert('daftarkp', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Pengajuan tempat KP berhasil
          </div>');
            redirect('user/daftarKP');
        }
    }

    public function laporanKP()
    {
        $data['title'] = 'Monitoring Harian Kerja Praktik';
        $data['title2'] = 'Data Kerja Praktik';
        $data['user'] = $this->db->get_where('user_table', ['user_nrp' => $this->session->userdata('nrp')])->row_array();
        $data['userKP'] = $this->db->get_where('daftarkp', ['NRP' => $this->session->userdata('nrp')])->row_array();

        $this->form_validation->set_rules('tanggal', 'tanggal', 'required|trim');
        $this->form_validation->set_rules('jam_mulai', 'jam_mulai', 'required|trim');
        $this->form_validation->set_rules('jam_selesai', 'jam_selesai', 'required|trim');
        $this->form_validation->set_rules('materi', 'materi', 'required|trim');
        $this->form_validation->set_rules('progres', 'progres', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/dataKP', $data);
            $this->load->view('user/laporanKP', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'tanggal' => htmlspecialchars($this->input->post('tanggal', true)),
                'jam_mulai' => htmlspecialchars($this->input->post('jam_mulai', true)),
                'jam_selesai' => htmlspecialchars($this->input->post('jam_selesai', true)),
                'materi' => htmlspecialchars($this->input->post('materi', true)),
                'progres' => htmlspecialchars($this->input->post('progres', true)),
                'NRP' => htmlspecialchars($this->session->userdata('nrp'))
            ];
            $this->db->insert('laporan_kp', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
           Laporan KP anda sudah diterima
          </div>');
            redirect('user/laporanKP');
        }
    }
}
