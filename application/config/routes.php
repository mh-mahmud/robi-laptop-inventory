<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "parent_controller";
$route['confirmation'] = "parent_controller/show_confirmation";
$route['test'] = "test_controller/test";
$route['test_2'] = "test_controller/test_2";

# user authentication
$route['login'] = 'parent_controller/login';
$route['logout'] = 'parent_controller/logout';
$route['change_password'] = 'parent_controller/change_password';
$route['change_password/:any'] = 'parent_controller/change_password';
$route['forgot_password'] = 'parent_controller/forgot_password';
$route['user_login_time_update'] = 'parent_controller/user_login_time_update';


# user management
$route['create_user']='master_controller/create_user';
$route['manage_user']='master_controller/manage_user';
$route['manage_user/:any']='master_controller/manage_user';
$route['create_role']='master_controller/create_role';
$route['manage_role']='master_controller/manage_role';
$route['manage_role/:any']='master_controller/manage_role';

# configuration
$route['create_option']='master_controller/create_option';
$route['manage_option']='master_controller/manage_option';
$route['manage_option/:any']='master_controller/manage_option';

# employee
$route['create_employee'] = 'operation_controller/create_employee';
$route['upload_employee_batch'] = 'operation_controller/upload_employee_batch';
$route['manage_employee'] = 'operation_controller/manage_employee';
$route['manage_employee/:any'] = 'operation_controller/manage_employee';

# purchase
$route['create_purchase'] = 'operation_controller/create_purchase';
$route['upload_purchase_batch'] = 'operation_controller/upload_purchase_batch';
$route['upload_purchase_batch_without_gr_date'] = 'operation_controller/upload_purchase_batch_without_gr_date';
$route['upload_purchase_gr_date'] = 'operation_controller/upload_purchase_gr_date';
$route['manage_purchase'] = 'operation_controller/manage_purchase';
$route['manage_purchase/:any'] = 'operation_controller/manage_purchase';

# laptop operations
$route['assign_laptop'] = 'operation_controller/assign_laptop';
$route['assign_laptop_batch'] = 'operation_controller/assign_laptop_batch';
$route['manage_assign_laptop'] = 'operation_controller/manage_assign_laptop';
$route['manage_assign_laptop/:any'] = 'operation_controller/manage_assign_laptop';

$route['broken_laptop'] = 'operation_controller/broken_fault_laptop';
$route['manage_broken_fault_laptop']	=	'operation_controller/manage_broken_fault_laptop';
$route['manage_broken_fault_laptop/:any']	=	'operation_controller/manage_broken_fault_laptop';

$route['lost_laptop']	=	'operation_controller/lost_laptop';
$route['manage_lost_laptop']	=	'operation_controller/manage_lost_laptop';
$route['manage_lost_laptop/:any']	=	'operation_controller/manage_lost_laptop';

$route['noc_with_personalized']	=	'operation_controller/noc_with_personalized';
$route['manage_noc_with_personalized']	=	'operation_controller/manage_noc_with_personalized';
$route['manage_noc_with_personalized/:any']	=	'operation_controller/manage_noc_with_personalized';

$route['noc_without_personalized']	=	'operation_controller/noc_without_personalized';
$route['manage_noc_without_personalized']	=	'operation_controller/manage_noc_without_personalized';
$route['manage_noc_without_personalized/:any']	=	'operation_controller/manage_noc_without_personalized';

$route['service_laptop']	=	'operation_controller/service_laptop';
$route['manage_service_laptop']	=	'operation_controller/manage_service_laptop';
$route['manage_service_laptop/:any']	=	'operation_controller/manage_service_laptop';
$route['return_laptop']	=	'operation_controller/return_laptop';

$route['send_to_replace_laptop']	=	'operation_controller/send_to_replace_laptop';
$route['manage_on_replacing_laptop']	=	'operation_controller/manage_on_replacing_laptop';
$route['manage_replaced_laptop']	=	'operation_controller/manage_replace_laptop';
$route['manage_replaced_laptop/:any']	=	'operation_controller/manage_replace_laptop';

$route['replace_laptop']	=	'operation_controller/replace_laptop';
$route['manage_replace_laptop']	=	'operation_controller/manage_on_replacing_laptop';
$route['manage_replace_laptop/:any']	=	'operation_controller/manage_on_replacing_laptop';


# report purchase laptop
$route['report_purchase_laptop']	=	'report_controller/report_purchase_laptop';
$route['report_purchase_laptop/:any']	=	'report_controller/report_purchase_laptop';

# report available laptop
$route['report_available_laptop']	=	'report_controller/report_available_laptop';
$route['report_available_laptop/:any']	=	'report_controller/report_available_laptop';

# report lost laptop
$route['report_lost_laptop']	=	'report_controller/report_lost_laptop';
$route['report_lost_laptop/:any']	=	'report_controller/report_lost_laptop';

# report_assign_laptop
$route['report_assign_laptop']	=	'report_controller/report_assign_laptop';
$route['report_assign_laptop/:any']	=	'report_controller/report_assign_laptop';

# report_broken_fault_laptop
$route['report_broken_fault_laptop']	=	'report_controller/report_broken_fault_laptop';
$route['report_broken_fault_laptop/:any']	=	'report_controller/report_broken_fault_laptop';

# report_noc_with_laptop
$route['report_noc_with_laptop']	=	'report_controller/report_noc_with_laptop';
$route['report_noc_with_laptop/:any']	=	'report_controller/report_noc_with_laptop';

# report_noc_with_laptop
$route['report_noc_without_laptop']	=	'report_controller/report_noc_without_laptop';
$route['report_noc_without_laptop/:any']	=	'report_controller/report_noc_without_laptop';

#stock report
$route['distribution_report']	=	'report_controller/distribution_report';
$route['distribution_report/:any']	=	'report_controller/distribution_report';

#po details
$route['po_details']	=	'report_controller/po_details';
$route['po_details/:any']	=	'report_controller/po_details';

#stock report
$route['stock_report']	=	'report_controller/stock_report';
$route['stock_report/:any']	=	'report_controller/stock_report';

#report_service_laptop
$route['report_service_laptop']	=	'report_controller/report_service_laptop';
$route['report_service_laptop/:any']	=	'report_controller/report_service_laptop';

#report_eol_of_laptop
$route['report_eol_of_laptop']	=	'report_controller/report_eol_of_laptop';
$route['report_eol_of_laptop/:any']	=	'report_controller/report_eol_of_laptop';

#report_eol_of_assign_laptop
$route['report_eol_of_assign_laptop']	=	'report_controller/report_eol_of_assign_laptop';
$route['report_eol_of_assign_laptop/:any']	=	'report_controller/report_eol_of_assign_laptop';

$route['lost_laptop_batch'] = 'temp_controller/lost_laptop_batch';
$route['noc_with_laptop_batch'] = 'temp_controller/noc_with_laptop_batch';
$route['noc_without_laptop_batch'] = 'temp_controller/noc_without_laptop_batch';
$route['faulty_laptop_batch'] = 'temp_controller/noc_without_laptop_batch';



#$route['test'] = "parent_controller/test";

/* End of file routes.php */
/* Location: ./application/config/routes.php */