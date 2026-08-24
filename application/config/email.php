<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Konfigurasi SMTP untuk pengiriman email notifikasi.
| Digunakan oleh library Notifier (application/libraries/Notifier.php).
| Ganti dengan kredensial SMTP perusahaan (Google Workspace, Office365, SendGrid SMTP, dll).
*/

$config['protocol']    = 'smtp';
$config['smtp_host']   = 'smtp.gmail.com';
$config['smtp_port']   = 587;
$config['smtp_crypto'] = 'tls';
$config['smtp_user']   = 'jdi.arjuna@gmail.com';
$config['smtp_pass']   = 'gtrotcrhceusrkmk';
$config['smtp_timeout'] = 30;

$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['newline']     = "\r\n";
$config['wordwrap']    = TRUE;

// Pengirim default yang ditampilkan pada email notifikasi
$config['from_email']  = 'jdi.arjuna@gmail.com';
$config['from_name']   = 'Sistem Update Harga';
