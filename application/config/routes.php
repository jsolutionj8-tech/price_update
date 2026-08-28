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
$route['products/search']              = 'products/search';

// Kategori barang (master data)
$route['categories']                   = 'categories/index';
$route['categories/create']            = 'categories/create';
$route['categories/store']             = 'categories/store';
$route['categories/edit/(:num)']       = 'categories/edit/$1';
$route['categories/update/(:num)']     = 'categories/update/$1';
$route['categories/delete/(:num)']     = 'categories/delete/$1';
$route['categories/activate/(:num)']   = 'categories/activate/$1';

// Vendor (master data)
$route['vendors']                      = 'vendors/index';
$route['vendors/create']               = 'vendors/create';
$route['vendors/store']                = 'vendors/store';
$route['vendors/edit/(:num)']          = 'vendors/edit/$1';
$route['vendors/update/(:num)']        = 'vendors/update/$1';
$route['vendors/delete/(:num)']        = 'vendors/delete/$1';
$route['vendors/activate/(:num)']      = 'vendors/activate/$1';

// Kompetitor (master data)
$route['competitors']                  = 'competitors/index';
$route['competitors/create']           = 'competitors/create';
$route['competitors/store']            = 'competitors/store';
$route['competitors/edit/(:num)']      = 'competitors/edit/$1';
$route['competitors/update/(:num)']    = 'competitors/update/$1';
$route['competitors/delete/(:num)']    = 'competitors/delete/$1';
$route['competitors/activate/(:num)']  = 'competitors/activate/$1';

// Biaya (master data)
$route['costs']                        = 'costs/index';
$route['costs/create']                 = 'costs/create';
$route['costs/store']                  = 'costs/store';
$route['costs/edit/(:num)']            = 'costs/edit/$1';
$route['costs/update/(:num)']          = 'costs/update/$1';
$route['costs/delete/(:num)']          = 'costs/delete/$1';

// Marketplace / kanal penjualan (master data)
$route['marketplaces']                 = 'marketplaces/index';
$route['marketplaces/create']          = 'marketplaces/create';
$route['marketplaces/store']           = 'marketplaces/store';
$route['marketplaces/edit/(:num)']     = 'marketplaces/edit/$1';
$route['marketplaces/update/(:num)']   = 'marketplaces/update/$1';
$route['marketplaces/delete/(:num)']   = 'marketplaces/delete/$1';
$route['marketplaces/activate/(:num)'] = 'marketplaces/activate/$1';

// Update Harga (modul inti)
$route['price-update']                 = 'price_update/index';
$route['price-update/form/(:num)']     = 'price_update/form/$1';
$route['price-update/calculate']       = 'price_update/calculate';
$route['price-update/save']            = 'price_update/save';
$route['price-update/add-vendor/(:num)'] = 'price_update/add_vendor/$1';
$route['price-update/preview-email']   = 'price_update/preview_email';
$route['price-update/send-pending']    = 'price_update/send_pending';

// Harga kompetitor
$route['competitor-price']                    = 'competitor_price/index';
$route['competitor-price/create']             = 'competitor_price/create';
$route['competitor-price/store']              = 'competitor_price/store';
$route['competitor-price/edit/(:num)']        = 'competitor_price/edit/$1';
$route['competitor-price/update/(:num)']      = 'competitor_price/update/$1';
$route['competitor-price/delete/(:num)']      = 'competitor_price/delete/$1';

// Riwayat perubahan harga
$route['price-history']                = 'price_history/index';
$route['price-history/detail/(:num)']  = 'price_history/detail/$1';
$route['price-history/resend/(:num)']  = 'price_history/resend/$1';
$route['price-history/export']         = 'price_history/export';
$route['price-history/export-pdf']     = 'price_history/export_pdf';

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

// Hak akses (menu per role)
$route['access-control']               = 'access_control/index';
$route['access-control/update']        = 'access_control/update';

// Import / export
$route['reports/export']               = 'reports/export';
$route['reports/import']               = 'reports/import';
$route['reports/do_import']            = 'reports/do_import';

// CLI (dipanggil via cron, lihat dokumentasi)
$route['cli/send_email_queue']         = 'cli/send_email_queue';
