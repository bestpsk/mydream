<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;
use app\middleware\JwtMiddleware;
use app\middleware\CorsMiddleware;
use app\middleware\TenantMiddleware;

// 认证相关路由
Route::post('/api/auth/login', 'app\controller\AuthController@login')->middleware([CorsMiddleware::class]);
Route::post('/api/auth/refresh-token', 'app\controller\AuthController@refreshToken')->middleware([CorsMiddleware::class]);
Route::get('/api/auth/user-info', 'app\controller\AuthController@getUserInfo')->middleware([JwtMiddleware::class, CorsMiddleware::class]);
Route::get('/api/auth/user-stores', 'app\controller\AuthController@getUserStores')->middleware([JwtMiddleware::class, CorsMiddleware::class]);

// 充值卡管理相关路由
Route::group('/api/recharge-card', function () {
    Route::get('/list', 'app\controller\RechargeCardController@getList')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::get('/detail/{id}', 'app\controller\RechargeCardController@getDetail')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::post('/add', 'app\controller\RechargeCardController@add')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::put('/update/{id}', 'app\controller\RechargeCardController@update')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::delete('/delete/{id}', 'app\controller\RechargeCardController@delete')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
});

// 充值卡配赠相关路由
Route::group('/api/recharge-gift', function () {
    Route::get('/projects/{rechargeId}', 'app\controller\RechargeGiftController@getGiftProjects')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::post('/add-project', 'app\controller\RechargeGiftController@addGiftProject')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::put('/update-project/{id}', 'app\controller\RechargeGiftController@updateGiftProject')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::delete('/delete-project/{id}', 'app\controller\RechargeGiftController@deleteGiftProject')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::get('/products/{rechargeId}', 'app\controller\RechargeGiftController@getGiftProducts')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::post('/add-product', 'app\controller\RechargeGiftController@addGiftProduct')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::put('/update-product/{id}', 'app\controller\RechargeGiftController@updateGiftProduct')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
    Route::delete('/delete-product/{id}', 'app\controller\RechargeGiftController@deleteGiftProduct')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
});

// OPTIONS 请求处理
Route::options('/api/auth/login', 'app\controller\AuthController@login');
Route::options('/api/auth/refresh-token', 'app\controller\AuthController@refreshToken');
Route::options('/api/auth/user-info', 'app\controller\AuthController@getUserInfo');
Route::options('/api/auth/user-stores', 'app\controller\AuthController@getUserStores');

// 权限相关路由
Route::get('/api/permission/routes', 'app\controller\PermissionController@getRoutes')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::get('/api/permission/permissions', 'app\controller\PermissionController@getPermissions')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::get('/api/permission/menus', 'app\controller\PermissionController@getMenus')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::get('/api/permission/menu-permissions', 'app\controller\PermissionController@getAllMenuPermissions')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::post('/api/permission/add', 'app\controller\PermissionController@addPermission')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::put('/api/permission/update/{id}', 'app\controller\PermissionController@updatePermission')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);
Route::delete('/api/permission/delete/{id}', 'app\controller\PermissionController@deletePermission')->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

/**
 * 企业管理相关路由
 * 需要JwtMiddleware中间件进行身份验证
 */
Route::group('/api/enterprise', function () {
    /**
     * 公司管理
     */
    Route::get('/company', 'app\controller\EnterpriseController@getCompanies');
    Route::post('/company', 'app\controller\EnterpriseController@addCompany');
    Route::put('/company/{id}', 'app\controller\EnterpriseController@updateCompany');
    Route::delete('/company/{id}', 'app\controller\EnterpriseController@deleteCompany');
    
    /**
     * 门店管理
     */
    Route::get('/store', 'app\controller\EnterpriseController@getStores');
    Route::post('/store', 'app\controller\EnterpriseController@addStore');
    Route::put('/store/{id}', 'app\controller\EnterpriseController@updateStore');
    Route::delete('/store/{id}', 'app\controller\EnterpriseController@deleteStore');
    
    /**
     * 部门管理
     */
    Route::get('/department', 'app\controller\EnterpriseController@getDepartments');
    Route::post('/department', 'app\controller\EnterpriseController@addDepartment');
    Route::put('/department/{id}', 'app\controller\EnterpriseController@updateDepartment');
    Route::delete('/department/{id}', 'app\controller\EnterpriseController@deleteDepartment');
    
    /**
     * 职位管理
     */
    Route::get('/position', 'app\controller\EnterpriseController@getPositions');
    Route::post('/position', 'app\controller\EnterpriseController@addPosition');
    Route::put('/position/{id}', 'app\controller\EnterpriseController@updatePosition');
    Route::delete('/position/{id}', 'app\controller\EnterpriseController@deletePosition');
    
    /**
     * 员工管理
     */
    Route::get('/employee', 'app\controller\EnterpriseController@getEmployees');
    Route::post('/employee', 'app\controller\EnterpriseController@addEmployee');
    Route::put('/employee/{id}', 'app\controller\EnterpriseController@updateEmployee');
    Route::delete('/employee/{id}', 'app\controller\EnterpriseController@deleteEmployee');
    
    /**
     * 床位管理
     */
    Route::get('/bedroom', 'app\controller\EnterpriseController@getBedrooms');
    Route::post('/bedroom', 'app\controller\EnterpriseController@addBedroom');
    Route::put('/bedroom/{id}', 'app\controller\EnterpriseController@updateBedroom');
    Route::delete('/bedroom/{id}', 'app\controller\EnterpriseController@deleteBedroom');
})->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

/**
 * 预约管理相关路由
 * 需要JwtMiddleware中间件进行身份验证
 */
Route::group('/api/appointment', function () {
    /**
     * 预约管理
     */
    Route::get('/get-appointments', 'app\controller\AppointmentController@getAppointments');
    Route::post('/add-appointment', 'app\controller\AppointmentController@addAppointment');
    Route::put('/update-appointment/{id}', 'app\controller\AppointmentController@updateAppointment');
    Route::delete('/delete-appointment/{id}', 'app\controller\AppointmentController@deleteAppointment');
    Route::get('/get-appointment-detail/{id}', 'app\controller\AppointmentController@getAppointmentDetail');
    Route::get('/get-available-time-slots', 'app\controller\AppointmentController@getAvailableTimeSlots');
    Route::get('/get-customer-projects', 'app\controller\AppointmentController@getCustomerProjects');
    Route::get('/get-departments', 'app\controller\AppointmentController@getDepartments');
    Route::get('/get-customers', 'app\controller\AppointmentController@getCustomers');
    Route::get('/get-employees', 'app\controller\AppointmentController@getEmployees');
    Route::get('/get-rooms', 'app\controller\AppointmentController@getRooms');
    Route::get('/get-projects', 'app\controller\AppointmentController@getProjects');
    Route::get('/get-employee-projects', 'app\controller\AppointmentController@getEmployeeProjects');
    Route::put('/update-appointment-status/{id}', 'app\controller\AppointmentController@updateAppointmentStatus');
})->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

/**
 * 卡项管理相关路由
 * 需要JwtMiddleware中间件进行身份验证
 */
Route::group('/api/card-item', function () {
    /**
     * 供应商管理
     */
    Route::get('/get-suppliers', 'app\controller\CardItemController@getSuppliers');
    Route::post('/add-supplier', 'app\controller\CardItemController@addSupplier');
    Route::put('/update-supplier/{id}', 'app\controller\CardItemController@updateSupplier');
    Route::delete('/delete-supplier/{id}', 'app\controller\CardItemController@deleteSupplier');
    
    /**
     * 项目分类管理
     */
    Route::get('/get-categories', 'app\controller\CardItemController@getCategories');
    Route::post('/add-category', 'app\controller\CardItemController@addCategory');
    Route::put('/update-category/{id}', 'app\controller\CardItemController@updateCategory');
    Route::delete('/delete-category/{id}', 'app\controller\CardItemController@deleteCategory');
    
    /**
     * 核心业务部门管理
     */
    Route::get('/get-core-departments', 'app\controller\CardItemController@getCoreDepartments');
    
    /**
     * 项目管理
     */
    Route::get('/get-projects', 'app\controller\CardItemController@getProjects');
    Route::post('/add-project', 'app\controller\CardItemController@addProject');
    Route::put('/update-project/{id}', 'app\controller\CardItemController@updateProject');
    Route::delete('/delete-project/{id}', 'app\controller\CardItemController@deleteProject');
    Route::get('/get-project-ingredients/{projectId}', 'app\controller\CardItemController@getProjectIngredients');
    Route::get('/get-project-sub-projects/{projectId}', 'app\controller\CardItemController@getProjectSubProjects');
    Route::get('/search-projects-by-pinyin', 'app\controller\CardItemController@searchProjectsByPinyin');
    
    /**
     * 充值卡管理
     */
    Route::get('/get-recharge-cards', 'app\controller\CardItemController@getRechargeCards');
    Route::post('/add-recharge-card', 'app\controller\CardItemController@addRechargeCard');
    Route::put('/update-recharge-card/{id}', 'app\controller\CardItemController@updateRechargeCard');
    Route::delete('/delete-recharge-card/{id}', 'app\controller\CardItemController@deleteRechargeCard');
    
    /**
     * 套餐卡管理
     */
    Route::get('/get-package-cards', 'app\controller\CardItemController@getPackageCards');
    Route::get('/get-package-card-detail/{id}', 'app\controller\CardItemController@getPackageCardDetail');
    Route::post('/add-package-card', 'app\controller\CardItemController@addPackageCard');
    Route::put('/update-package-card/{id}', 'app\controller\CardItemController@updatePackageCard');
    Route::delete('/delete-package-card/{id}', 'app\controller\CardItemController@deletePackageCard');
    
    /**
     * 时效卡管理
     */
    Route::get('/get-time-cards', 'app\controller\CardItemController@getTimeCards');
    Route::get('/get-time-card-detail/{id}', 'app\controller\CardItemController@getTimeCardDetail');
    Route::post('/add-time-card', 'app\controller\CardItemController@addTimeCard');
    Route::put('/update-time-card/{id}', 'app\controller\CardItemController@updateTimeCard');
    Route::delete('/delete-time-card/{id}', 'app\controller\CardItemController@deleteTimeCard');
    Route::post('/copy-time-card/{id}', 'app\controller\CardItemController@copyTimeCard');
    Route::put('/toggle-time-card-status/{id}', 'app\controller\CardItemController@toggleTimeCardStatus');
    Route::put('/batch-time-card-status', 'app\controller\CardItemController@batchTimeCardStatus');
    
    /**
     * 产品管理
     */
    Route::get('/get-products', 'app\controller\CardItemController@getProducts');
    Route::post('/add-product', 'app\controller\CardItemController@addProduct');
    Route::put('/update-product/{id}', 'app\controller\CardItemController@updateProduct');
    Route::delete('/delete-product/{id}', 'app\controller\CardItemController@deleteProduct');
    
    /**
     * 产品分类管理
     */
    Route::get('/get-product-categories', 'app\controller\CardItemController@getProductCategories');
    Route::post('/add-product-category', 'app\controller\CardItemController@addProductCategory');
    Route::put('/update-product-category/{id}', 'app\controller\CardItemController@updateProductCategory');
    Route::delete('/delete-product-category/{id}', 'app\controller\CardItemController@deleteProductCategory');
})->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

/**
 * 客户管理相关路由
 * 需要JwtMiddleware中间件进行身份验证
 */
Route::group('/api/customer', function () {
    /**
     * 客户管理
     */
    Route::get('/list', 'app\controller\CustomerController@list');
    Route::get('/detail/{id}', 'app\controller\CustomerController@detail');
    Route::post('/add', 'app\controller\CustomerController@add');
    Route::put('/edit/{id}', 'app\controller\CustomerController@edit');
    Route::delete('/delete/{id}', 'app\controller\CustomerController@delete');
    Route::get('/core-departments', 'app\controller\CustomerController@coreDepartments');
    Route::get('/employees', 'app\controller\CustomerController@employees');
    Route::get('/get-superior', 'app\controller\CustomerController@getSuperior');
})->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

/**
 * 角色菜单管理相关路由
 * 需要JwtMiddleware中间件进行身份验证
 */
Route::group('/api/role-menu', function () {
    /**
     * 角色管理
     */
    Route::get('/role', 'app\controller\RoleMenuController@getRoles');
    Route::post('/role', 'app\controller\RoleMenuController@addRole');
    Route::put('/role/{id}', 'app\controller\RoleMenuController@updateRole');
    Route::delete('/role/{id}', 'app\controller\RoleMenuController@deleteRole');
    Route::get('/role/{id}/permissions', 'app\controller\RoleMenuController@getRolePermissions');
    Route::put('/role/{id}/permissions', 'app\controller\RoleMenuController@updateRolePermissions');
    
    /**
     * 菜单管理
     */
    Route::get('/menu', 'app\controller\RoleMenuController@getMenus');
    Route::post('/menu', 'app\controller\RoleMenuController@addMenu');
    Route::put('/menu/{id}', 'app\controller\RoleMenuController@updateMenu');
    Route::delete('/menu/{id}', 'app\controller\RoleMenuController@deleteMenu');
    
    /**
     * 菜单权限管理
     */
    Route::get('/menu/{menuId}/permissions', 'app\controller\RoleMenuController@getMenuPermissions');
    Route::post('/menu/{menuId}/permissions', 'app\controller\RoleMenuController@addMenuPermission');
    Route::put('/menu/{menuId}/permissions/{permissionId}', 'app\controller\RoleMenuController@updateMenuPermission');
    Route::delete('/menu/{menuId}/permissions/{permissionId}', 'app\controller\RoleMenuController@deleteMenuPermission');
})->middleware([JwtMiddleware::class, TenantMiddleware::class, CorsMiddleware::class]);

