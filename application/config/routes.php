<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/* =========================================================
 | Routing Aplikasi Update Harga Produk & Notifikasi Email
 | =======================================================*/

// Auth
$route['login']                        = 'auth/login';
$route['logout']                       = 'auth/logout';
$route['auth/do_login']                = 'auth/do_login';

// Dashboard
$route['dashboard']                    = 'dashboard/index';

// Produk (master data)
$route['products']                     = 'products/index';
$route['products/create']              = 'products/create';
$route['products/store']               = 'products/store';
$route['products/edit/(:num)']         = 'products/edit/$1';
$route['products/update/(:num)']       = 'products/update/$1';
$route['products/delete/(:num)']       = 'products/delete/$1';

// Update Harga (modul inti)
$route['price-update']                 = 'price_update/index';
$route['price-update/form/(:num)']     = 'price_update/form/$1';
$route['price-update/calculate']       = 'price_update/calculate';
$route['price-update/save']            = 'price_update/save';
$route['price-update/add-vendor/(:num)'] = 'price_update/add_vendor/$1';
$route['price-update/preview-email']   = 'price_update/preview_email';

// Harga kompetitor
$route['competitor-price']             = 'competitor_price/index';
$route['competitor-price/save']        = 'competitor_price/save';

// Riwayat perubahan harga
$route['price-history']                = 'price_history/index';
$route['price-history/detail/(:num)']  = 'price_history/detail/$1';
$route['price-history/resend/(:num)']  = 'price_history/resend/$1';

// User & role
$route['users']                        = 'users/index';
$route['users/create']                 = 'users/create';
$route['users/store']                  = 'users/store';
$route['users/edit/(:num)']            = 'users/edit/$1';
$route['users/update/(:num)']          = 'users/update/$1';
$route['users/delete/(:num)']          = 'users/delete/$1';

// Grup notifikasi
$route['notification-groups']              = 'notification_groups/index';
$route['notification-groups/create']       = 'notification_groups/create';
$route['notification-groups/store']        = 'notification_groups/store';
$route['notification-groups/edit/(:num)']  = 'notification_groups/edit/$1';
$route['notification-groups/update/(:num)'] = 'notification_groups/update/$1';
$route['notification-groups/delete/(:num)'] = 'notification_groups/delete/$1';

// Import / export
$route['reports/export']               = 'reports/export';
$route['reports/import']               = 'reports/import';
$route['reports/do_import']            = 'reports/do_import';

// CLI (dipanggil via cron, lihat dokumentasi)
$route['cli/send_email_queue']         = 'cli/send_email_queue';
