<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        $data['title'] = 'Login Page';
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        if($this->form_validation->run() == false) {
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        }else {
            $this->_login();
        }
    }
    private function _login() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $user = $this->db->get_where('customer', ['email' => $email])->row_array();

        // jika usernya ada
        if($user){
            // jika usernya aktif/sudah teraktivasi
            if($user['is_active'] == 1){
                //cek password
                // if(password_verify($password, $user['password'])){
                    if($password == $user['password']){
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    redirect('user');
                }else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password salah!</div>');
                    redirect('auth');
                }
            }else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email belum teraktivasi!</div>');
                redirect('auth');
            }

        }else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email belum terdaftar!</div>');
            redirect('auth');
        }
    }
    public function registration() {
        $data['title'] = 'Account Registration';
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[customer.email]', 
        [
            'is_unique' => 'Email sudah terdaftar!'
        ]); 
        $this->form_validation->set_rules('telepon', 'Telepon', 'required|trim|numeric');
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[6]|matches[password2]', [
            'min_length' => 'Password terlalu pendek!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]', 
        [
            'matches' => 'Password tidak cocok!',
        ]
    );

        if($this->form_validation->run() == false) {
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        }else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'no_telp' => $this->input->post('telepon', true),
                // password hash untuk men-enkripsi password
                // 'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'password' => $this->input->post('password1'),
                
                // role_id = 2 karena customer dan admin = 1
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];
            // Data masukkan ke database
            $this->db->insert('customer', $data);
            $this->session->set_flashdata('message', 
            '<div class="alert alert-success" role="alert">
                Selamat! Akun anda telah dibuat. Silahkan login.
            </div>');
            redirect('auth');
        }
    } 
    public function logout() {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Anda telah logout!</div>');
        redirect('auth');
    }
}