<?php
defined('BASEPATH') or exit('No direct script access allowed');

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
|	https://codeigniter.com/user_guide/general/routing.html
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

$route['default_controller'] = 'login';
$route['no_access/([^/]+)'] = 'no_access/index/$1';
$route['no_access/([^/]+)/([^/]+)'] = 'no_access/index/$1/$2';
$route['sales/index/([^/]+)'] = 'sales/manage/$1';
$route['sales/index/([^/]+)/([^/]+)'] = 'sales/manage/$1/$2';
$route['sales/index/([^/]+)/([^/]+)/([^/]+)'] = 'sales/manage/$1/$2/$3';
$route['reports/(summary_:any)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/summary_:any'] = 'reports/date_input';
$route['reports/(graphical_:any)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/graphical_:any'] = 'reports/date_input';
$route['reports/(inventory_:any)/([^/]+)'] = 'reports/$1/$2';
$route['reports/inventory_summary'] = 'reports/inventory_summary_input';
$route['reports/(inventory_summary)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2';

//$route['items/category_apply_vat/(:any)'] = 'items/category_apply_vat/$i';

$route['reports/(detailed_sales)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/detailed_sales'] = 'reports/date_input_sales';
$route['reports/(detailed_receivings)/([^/]+)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4/$5';
$route['reports/detailed_receivings'] = 'reports/date_input_recv';

//for product specific reports
$route['reports/(detailed_product_sales)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/detailed_product_sales'] = 'reports/date_product_input_sales';

//item_inventory_report
$route['reports/(item_inventory_report)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/item_inventory_report'] = 'reports/date_item_inventory_input';

//fpor product specific reports
$route['reports/(detailed_product_receivings)/([^/]+)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4/$5';
$route['reports/detailed_product_receivings'] = 'reports/date_product_input_recv';

//expiry items
// $route['reports/(date_expiry_items)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3';
$route['reports/(date_expiry_items)/([^/]+)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4/$5';
$route['reports/expiry_items'] = 'reports/date_expiry_items';


//expired items
$route['reports/(date_expired_items)/([^/]+)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4/$5';
$route['reports/expired_items'] = 'reports/date_expired_items';

//price list report
$route['reports/(price_list)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/price_list'] = 'reports/date_input_price_list';

//stock value report
$route['reports/(stock_value)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/stock_value'] = 'reports/date_input_stock_value';

//all items report
$route['reports/(all_items)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/all_items'] = 'reports/date_input_all_items';

//vat / tax report
$route['reports/(vat_tax)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/vat_tax'] = 'reports/date_input_vat_tax';

//markup report
$route['reports/(markup_report)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/markup_report'] = 'reports/date_input_markup_report';

//sales markup report
$route['reports/(sales_markup_report)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/sales_markup_report'] = 'reports/date_input_sales_markup_report';

//out of stock report
$route['reports/(out_of_stock)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/out_of_stock'] = 'reports/date_input_out_of_stock';

$route['reports/(detailed_transfers)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/detailed_transfers'] = 'reports/date_input_trans';
$route['reports/irecharge_sales'] = 'reports/date_input_ir';

$route['reports/(specific_:any)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/specific_customer'] = 'reports/specific_customer_input';
$route['reports/specific_expiry'] = 'reports/date_input_recv1';
$route['reports/specific_employee'] = 'reports/specific_employee_input';
$route['reports/specific_discount'] = 'reports/specific_discount_input';
$route['reports/out_:any'] = 'reports/show_out_of_stock_page';
$route['roles/'] = 'roles/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
