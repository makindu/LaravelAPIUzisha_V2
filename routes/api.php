<?php

use Illuminate\Http\Request;
use App\Models\requestHistory;
use Illuminate\Support\Facades\Route;
use App\Models\UnitOfMeasureController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\DebtsController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SpotsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ColorsController;
use App\Http\Controllers\FencesController;
use App\Http\Controllers\MoneysController;
use App\Http\Controllers\OwnersController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StylesController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\DefectsController;
use App\Http\Controllers\ReasonsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CautionsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\PosusersController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\SalariesController;
use App\Http\Controllers\ServantsController;
use App\Http\Controllers\LibrariesController;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\SafeguardController;
use App\Http\Controllers\VehiculesController;
use OpenApi\Annotations\AdditionalProperties;
use App\Http\Controllers\WekagroupsController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EnterprisesController;
use App\Http\Controllers\PointOfSaleController;
use App\Http\Controllers\DebtPaymentsController;
use App\Http\Controllers\DecisionTeamController;
use App\Http\Controllers\ExpendituresController;
use App\Http\Controllers\OtherEntriesController;
use App\Http\Controllers\RequestFilesController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\TicketOfficeController;
use App\Http\Controllers\DepositsUsersController;
use App\Http\Controllers\DocumentTypesController;
use App\Http\Controllers\RequestServedController;
use App\Http\Controllers\DetailsRequestController;
use App\Http\Controllers\FenceTicketingController;
use App\Http\Controllers\InvoiceDetailsController;
use App\Http\Controllers\InvoicesStatusController;
use App\Http\Controllers\RequestHistoryController;
use App\Http\Controllers\SelfReferencesController;
use App\Http\Controllers\TransfertstockController;
use App\Http\Controllers\AdvancesalariesController;
use App\Http\Controllers\MoneyConversionController;
use App\Http\Controllers\SharedlibrariesController;
use App\Http\Controllers\SubDepartementsController;
use App\Http\Controllers\AffectationUsersController;
use App\Http\Controllers\PressingServicesController;
use App\Http\Controllers\PricesCategoriesController;
use App\Http\Controllers\UsersPointOfSaleController;
use App\Http\Controllers\WekafirstentriesController;
use App\Http\Controllers\DepositControllerController;
use App\Http\Controllers\ProviderspaymentsController;
use App\Http\Controllers\RequestReferencesController;
use App\Http\Controllers\UsersTicketOfficeController;
use App\Http\Controllers\CustomerControllerController;
use App\Http\Controllers\ExpendituresLimitsController;
use App\Http\Controllers\ProviderControllerController;
use App\Http\Controllers\RequestapprovmentsController;
use App\Http\Controllers\ServicesControllerController;
use App\Http\Controllers\WekamemberaccountsController;
use App\Http\Controllers\EnterprisesinvoicesController;
use App\Http\Controllers\DecisionDecisionteamController;
use App\Http\Controllers\DetailsInvoicesStatusController;
use App\Http\Controllers\UzishafuelconsumptionController;
use App\Http\Controllers\ServicesadditionalfeesController;
use App\Http\Controllers\StockHistoryControllerController;
use App\Http\Controllers\UnitOfMeasureControllerController;
use App\Http\Controllers\UsersExpendituresLimitsController;
use App\Http\Controllers\ValidatedbydecisionteamController;
use App\Http\Controllers\AttemptactivationaccountController;
use App\Http\Controllers\DecisionChiefdepartmentsController;
use App\Http\Controllers\WekaAccountsTransactionsController;
use App\Http\Controllers\NbrdecisionteamValidationController;
use App\Http\Controllers\CategoriesCustomerControllerController;
use App\Http\Controllers\CategoriesServicesControllerController;
use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

/** Users getways */

//get all users and add new
Route::post('/cerouzisha/serditransactionsfeedback',[UsersController::class,'serditransactionsfeedback']);
Route::get('/storage/uploads/{filename}', function ($filename) {
    $path = storage_path("app/public/uploads/{$filename}");

    if (!file_exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return Response::make($file, 200, [
        'Content-Type' => $type,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'
    ]);
});
Route::ApiResource('/users',UsersController::class);
Route::get('/users/enterprise/{id}',[UsersController::class,'index']);
Route::get('/searchingusers',[UsersController::class,'searchingusers']);
Route::delete('/users/delete/{id}',[UsersController::class,'destroy2']);
Route::patch('/users/update/{id}',[UsersController::class,'update2']);
Route::post('/users/updatestatus',[UsersController::class,'changerStatus']);
Route::post('/users/updatepassword',[UsersController::class,'updatePassword']);
Route::get('/getuser',[UsersController::class,'getone']);
Route::get('/users/getbyid/{id}',[UsersController::class,'getuserbyId']);
Route::post('/users/dashboard/{id}',[UsersController::class,'dashboardBasedDateOperation']);
Route::post('/users/makeassuperadmin',[UsersController::class,'makeassuperadmin']);
Route::post('/users/resetpassword',[UsersController::class,'ifexistsemailadress']);
Route::post('/users/search',[UsersController::class,'agentsearch']);

//Funds
Route::get('/funds/mines/{id}', [FundsController::class,'mines']);

//reservations
Route::apiResource('/reservations',ReservationsController::class);
Route::get('/reservations/enterprise/{enterprise_id}',[ReservationsController::class,'index']);
Route::post('/reservations/filtered',[ReservationsController::class,'reservationsfilter']);
Route::post('/reservations/search',[ReservationsController::class,'searchreservations']);
Route::post('/reservations/changestatus',[ReservationsController::class,'changestatus']);

//connection or login
Route::post('/users/login',[UsersController::class,'login']);

//get access level for validations
Route::get('/getaccess/{id}',[UsersController::class,'getuseraccess']);

/** end of users getwayrs */

Route::resource('/affectation_users', AffectationUsersController::class);
Route::put('/affectation/update',[AffectationUsersController::class,'update2']);
Route::delete('/affectation/delete/{id}',[AffectationUsersController::class,'destroy2']);
Route::resource('/attemptactivationaccunt', AttemptactivationaccountController::class);

Route::resource('/comments', CommentsController::class);
Route::get('getByid_request/{id}',[CommentsController::class,'getCommentByIdRequest']);
Route::get('reference',[AffectationUsersController::class,'reference']);

Route::resource('/decision_chiefdepartments', DecisionChiefdepartmentsController::class);
Route::get('/decision_chief/single/{id}',[DecisionChiefdepartmentsController::class,'getsingledecision']);

Route::resource('/decision_decisionteam', DecisionDecisionteamController::class);

Route::resource('/decision_team', DecisionTeamController::class);
Route::patch('/decision_team/update/{id}',[DecisionTeamController::class,'update2']);
Route::delete('/decision_team/delete/{id}',[DecisionTeamController::class,'destroy2']);

Route::resource('/departments', DepartementController::class);
Route::put('/departments/update/{id}',[DepartementController::class,'update2']);
Route::get('/departments/findbyid/{id}',[DepartementController::class,'findbyid']);
Route::get('/departments/getusers/{id}',[DepartementController::class,'findusers']);
Route::delete('/department/delete/{id}',[DepartementController::class,'destroy2']);
Route::get('/departments/subdeparts/{id}',[DepartementController::class,'findsubdeparts']);

Route::resource('/details_request', DetailsRequestController::class);
Route::get('/request_details/{requestid}',[DetailsRequestController::class,'showforarequest']);

Route::apiResource('/funds', FundsController::class);
Route::get('funds/reset/{id}',[FundsController::class,'reset']);
Route::delete('/funds/delete/{id}',[FundsController::class,'destroy2']);
Route::patch('funds/update/{funds}',[FundsController::class,'update2']);
Route::post('/funds/requesthistories',[FundsController::class,'requesthistoriesbyagent']);
Route::get('/searchingrequesthistories',[RequestHistoryController::class,'searchingrequesthistories']);
Route::get('/funds/getOperationById/{id}',[RequestHistoryController::class,'getOperationById']);
Route::post('/funds/savemultiples',[RequestHistoryController::class,'savemultiple']);
Route::post('/funds/operations/update',[RequestHistoryController::class,'operationsupdate']);

Route::resource('/money_conversion', MoneyConversionController::class);
Route::get('/money_conversion/enterprise/{enterpriseId}', [MoneyConversionController::class,'index']);
Route::patch('/money_conversion/update/{id}',[MoneyConversionController::class,'updateMe']);
Route::delete('/money_conversion/delete/{id}',[MoneyConversionController::class,'delete']);

Route::resource('/money', MoneysController::class);
Route::get('/money/enterprise/{id}',[MoneysController::class,'index']);
Route::patch('/money/update/{id}',[MoneysController::class,'update2']);
Route::delete('/money/delete/{id}',[MoneysController::class,'destroy2']);

Route::resource('/nbrdecisionteam_validation', NbrdecisionteamValidationController::class);

Route::resource('/request_files', RequestFilesController::class);
Route::post('request_files/upload',[RequestFilesController::class,'getsfiles']);
Route::get('/request_files/single/{id}',[RequestFilesController::class,'getfilesbyrequest']);
// Route::get('/requests/decisionteamall',[RequestController::class,'validatedbydecisionteamall']);

Route::resource('/request_references', RequestReferencesController::class);
Route::get('/request_references/single/{id}',[RequestReferencesController::class,'getreferencesbyrequest']);

Route::resource('/request_served', RequestServedController::class);
Route::get('/request_served_by_id_Request/{id}',[RequestServedController::class,'getrequest_servedByIdRequest']);

Route::resource('/request_history',RequestHistoryController::class);
Route::get('/request_history/byfund/{fund}',[RequestHistoryController::class,'getbyfund']);
Route::patch('/request_history/{id}',[RequestHistoryController::class,'update']);

Route::resource('/request', RequestsController::class);
Route::post('/request/validation/{userid}', [RequestsController::class,'requestvalidation']);
Route::delete('/request_delete/{id}', [RequestsController::class,'deleteRequest']);
Route::put('/request_update/{id}', [RequestsController::class,'updateRequest']);
Route::get('/requests/user/{userid}',[RequestsController::class,'byuser']);
Route::get('/requests/bydepart/{departid}',[RequestsController::class,'bydepart']);
Route::get('/requests/files/depart/{departid}',[RequestsController::class,'filesbydepart']);
Route::get('/requests/requestservedbytub/{idtub}',[RequestsController::class,'requestservedbytub']);
Route::get('/validatedbychiefdepart/{userid}',[RequestsController::class,'validatedbychiefdepart']);
Route::get('/unvalidatedbychiefdepart/{userid}',[RequestsController::class,'unvalidatedbychiefdepart']);

//Request to validate
Route::post('/tovalidatebychiefdepart',[RequestsController::class,'tovalidatebychiefdepart']);
Route::post('/tovalidatebydecisionteam',[RequestsController::class,'tovalidatebydecisionteam']);

Route::get('/validatedbydecisionteam/{userid}',[RequestsController::class,'validatedbydecisionteam']);
Route::get('/unvalidatedbydecisionteam/{userid}',[RequestsController::class,'unvalidatedbydecisionteam']);

Route::get('/tobeserved/{userid}',[RequestsController::class,'tobeserved']);
Route::get('/tobeservedall',[RequestsController::class,'tobeservedall']);

Route::get('/alreadyserved/{userid}',[RequestsController::class,'alreadyserved']);
Route::get('/alreadyservedall',[RequestsController::class,'alreadyservedall']);

Route::resource('/self_references', SelfReferencesController::class);
Route::resource('/sud_departements', SubDepartementsController::class);
Route::resource('/validatedbydecisionteam', ValidatedbydecisionteamController::class);

Route::get('getByid/{id}',[FundsController::class,'getByid']);
Route::get('getByidMony/{id}',[MoneyConversionController::class,'getByid']);
Route::delete('deleteMe/{id}',[MoneyConversionController::class,'deleteMe']);

/**
 * CERO UZISHA API
 */
//Unif of measure
Route::apiResource('unitofmeasures',UnitOfMeasureControllerController::class);
Route::get('/unitofmeasures/enterprise/{enterprise_id}',[UnitOfMeasureControllerController::class,'index']);
Route::put('/unitofmeasures/update/{id}',[UnitOfMeasureControllerController::class,'update2']);
Route::patch('/unitofmeasures/update/{id}',[UnitOfMeasureControllerController::class,'update2']);
Route::get('/unitofmeasures/services/{uomid}',[UnitOfMeasureControllerController::class,'servicesbyuom']);
//Owners
Route::apiResource('owners',OwnersController::class);
//Enterprises (Entreprises)
Route::apiResource('enterprises',EnterprisesController::class);
Route::put('/enterprises/update/{id}',[EnterprisesController::class,'update2']);
Route::get('/enterprises/getinfos/{id}',[EnterprisesController::class,'getone']);
Route::post('/enterprises/resetdata',[EnterprisesController::class,'resetalldata']);
Route::post('/enterprises/resetdates',[EnterprisesController::class,'datesConsolidation']);
Route::put('/enterprises/updatestatus',[EnterprisesController::class,'updatestatus']);

//Enterprises invoices abonments
Route::apiResource('enterprisesinvoices',EnterprisesinvoicesController::class);
Route::get('/enterprises/myinvoices/{id}',[EnterprisesinvoicesController::class,'index']);

//Point of sale (Points des ventes)
Route::apiResource('pointofsales',PointOfSaleController::class);
Route::get('pointofsales/enterprise_id/{id}',[PointOfSaleController::class,'foraspecificEse']);
Route::patch('/pointofsales/update/{id}',[PointOfSaleController::class,'update2']);
Route::delete('/pointofsales/delete/{id}',[PointOfSaleController::class,'destroy2']);
Route::post('/pointofsales/affectdeposits',[PointOfSaleController::class,'affectDeposits']);
Route::get('/pointofsales/deposits/{posid}',[PointOfSaleController::class,'getdeposits']);
Route::get('/pointofsales/agents/{posid}',[PointOfSaleController::class,'getagents']);
Route::delete('/pointofsales/deposits/delete/{affectation_id}',[PointOfSaleController::class,'deleteposit']);
Route::post('/pointofsales/user/delete',[PointOfSaleController::class,'deleteuser']);
Route::apiResource('userspointofsale',UsersPointOfSaleController::class);

//point of sales users
Route::get('pointofsale/users/{id}',[PosusersController::class,'index']);
Route::post('pointofsale/users',[PosusersController::class,'store']);
Route::post('pointofsale/users/deleting',[PosusersController::class,'destroy']);
//Ticket offices (Guichets)
Route::apiResource('ticketoffices',TicketOfficeController::class);
Route::apiResource('usersticketoffice',UsersTicketOfficeController::class);

//Deposits (depots) and Stock Story (approvisionnements,destockages et historiques ventes:Entrees et sorties stocks)
Route::apiResource('deposits',DepositControllerController::class);
Route::get('/deposits/enterprise/{id}',[DepositControllerController::class,'index']);
Route::put('/deposits/update/{id}',[DepositControllerController::class,'update2']);
Route::patch('/deposits/update/{id}',[DepositControllerController::class,'update2']);
Route::delete('/deposits/delete/{id}',[DepositControllerController::class,'delete2']);
Route::post('/deposits/participants',[DepositControllerController::class,'participants']);
Route::post('/deposit/addservices',[DepositControllerController::class,'addservices']);
Route::post('/deposit/services/delete',[DepositControllerController::class,'withdrawServices']);
Route::post('/deposit/users',[DepositControllerController::class,'depositForUser']);
Route::get('/deposit/articlesdepositpaginated/{depositid}',[ServicesControllerController::class,'articlesdepositpaginated']);
Route::get('/deposit/all-articles-paginated/{userid}',[ServicesControllerController::class,'depositsandarticlespaginated']);
Route::post('/deposit/reset',[DepositControllerController::class,'reset']);
Route::post('/deposit/rollbackall',[DepositControllerController::class,'rollbackdepositquantities']);
Route::post('/deposits/generalstockvaluation',[DepositControllerController::class,'depositvaluationForUser']);

//Deposits users
Route::apiResource('depositsusers',DepositsUsersController::class);
Route::delete('/depositsusers/delete/{id}',[DepositsUsersController::class,'deleteaffectation']);
Route::put('/depositsusers/update/{id}',[DepositsUsersController::class,'updateaffectation']);

//Stock History
Route::apiResource('stockhistory',StockHistoryControllerController::class);
Route::get('/stockhistory/enterprise/{id}',[StockHistoryControllerController::class,'index']);
Route::get('/stockhistory/serviceid/{serviceid}',[StockHistoryControllerController::class,'getbyservice']);
Route::post('/stockhistory/byuser',[StockHistoryControllerController::class,'getbyuser']);
Route::post('/stockhistory/byuser/getbyuserbasedondateoperation',[StockHistoryControllerController::class,'getbyuserbasedondateoperation']);
Route::post('/stockhistory/byuser/grouped',[StockHistoryControllerController::class,'getbyusergrouped']);
Route::post('/stockhistory/byuser/byarticles',[StockHistoryControllerController::class,'articlesgetbyusergrouped']);
Route::post('/stockhistory/byuser/byarticlesbasedoperation',[StockHistoryControllerController::class,'articlesgetbyusergroupedbasedoperation']);
Route::post('/stockhistory/byuser/newreportstockhistory',[StockHistoryControllerController::class,'newReportStockHistorybasedondateoperation']);
Route::post('/stockhistory/expiration',[StockHistoryControllerController::class,'reportexpiration']);
Route::post('/stockhistory/fordeposit',[StockHistoryControllerController::class,'fordeposit']);
Route::post('/stockhistory/multipleservices',[StockHistoryControllerController::class,'multipleservices']);
Route::post('/stockhistory/multiplestore',[StockHistoryControllerController::class,'multiplestore']);
Route::post('/stockhistory/report/bydeposits',[StockHistoryControllerController::class,'reportbydeposits']);
Route::post('/stockhistory/report/bydepositsbasedonoperationdate',[StockHistoryControllerController::class,'reportbydepositsbasedondateoperation']);
Route::post('/stockhistory/report/reportstockgroupedbydates',[StockHistoryControllerController::class,'reportstockgroupedbydates']);
Route::post('/stockhistory/update/{id}',[StockHistoryControllerController::class,'update']);
Route::get('/searchingstockhistorybydoneby',[StockHistoryControllerController::class,'searchingstockhistorybydoneby']);
Route::get('/stockhistory/getStockHistoryById/{id}',[StockHistoryControllerController::class,'getStockHistoryById']);
//Transfert stock
Route::apiResource('transfertstock',TransfertstockController::class);
Route::get('/transfertstock/enterprise/{id}',[TransfertstockController::class,'index']);
Route::post('/transfertstock/validation',[TransfertstockController::class,'validation']);
Route::post('/transfertstock/cancel',[TransfertstockController::class,'canceling']);
Route::post('/transfertstock/status',[TransfertstockController::class,'statusChange']);
Route::post('/transfertstock/forauser',[TransfertstockController::class,'transfertforspecificUser']);

//request approvments
Route::apiResource('requestapprovments',RequestapprovmentsController::class);
Route::get('/requestapprovments/enterprise/{id}',[RequestapprovmentsController::class,'index']);
Route::post('/requestapprovments/validation',[RequestapprovmentsController::class,'validation']);
Route::post('/requestapprovments/cancel',[RequestapprovmentsController::class,'canceling']);
Route::post('/requestapprovments/multiplestore',[RequestapprovmentsController::class,'multiplestores']);

Route::apiResource('typesdocuments',DocumentTypesController::class);
Route::get('/typesdocuments/enterprise/{id}',[DocumentTypesController::class,'index']);

//Services and Articles
Route::apiResource('categoriesServices',CategoriesServicesControllerController::class);
Route::get('/categoriesServices/enterprise/{id}',[CategoriesServicesControllerController::class,'index']);
Route::put('/categoriesServices/update/{id}',[CategoriesServicesControllerController::class,'update2']);
Route::patch('/categoriesServices/update/{id}',[CategoriesServicesControllerController::class,'update2']);
Route::get('/categoriesServices/services/{categoryid}',[CategoriesServicesControllerController::class,'servicesbycategories']);

Route::apiResource('services',ServicesControllerController::class);
Route::get('/services/enterprise/{enterprise_id}',[ServicesControllerController::class,'index']);
Route::get('/services/enterprise/subservices/{enterprise_id}',[ServicesControllerController::class,'subserviceslist']);
Route::post('/services/list',[ServicesControllerController::class,'services_list']);
Route::get('/services/list/{user_id}',[ServicesControllerController::class,'services_list_paginated']);
Route::put('/services/update/{id}',[ServicesControllerController::class,'update2']);
Route::patch('/services/update/{id}',[ServicesControllerController::class,'update2']);
Route::delete('/services/delete/{id}',[ServicesControllerController::class,'destroy2']);
Route::get('/servicestosell/{userid}',[ServicesControllerController::class,'give_to_seller']);
Route::get('/services/myarticles/{userid}',[ServicesControllerController::class,'myarticles']);
Route::get('/services/depositarticles/{depositid}',[ServicesControllerController::class,'depositarticles']);
Route::get('/services/depositall/{depositid}',[ServicesControllerController::class,'depositall']);
Route::post('/services/importation',[ServicesControllerController::class,'importation']);
Route::post('/services/enterprise/deleteall',[ServicesControllerController::class,'resetallservices']);
Route::post('/services/periodicstockhistory',[ServicesControllerController::class,'periodicstockhistory']);
Route::post('/services/periodicsell',[ServicesControllerController::class,'periodicsell']);
Route::get('/filterservicesbytypes',[ServicesControllerController::class,'servicesbytypes']);
// Route::get('/filterservicesbytypes',function(Request $request){
//     return $request->query();
//     $type=$request->query('typesent');
//     $enterprise=$request->query('enterprise_id');

//     return response()->json([
//         'typesent'=>$type,
//         'enterprise'=>$enterprise
//     ]);
// });
Route::post('/services/groupedbytypes',[ServicesControllerController::class,'servicesgroupedbytypes']);

Route::apiResource('pricescategories',PricesCategoriesController::class);
Route::put('/pricescategories/update/{id}',[PricesCategoriesController::class,'update2']);
Route::patch('/pricescategories/update/{id}',[PricesCategoriesController::class,'update2']);
Route::delete('/pricescategories/delete/{id}',[PricesCategoriesController::class,'deletepricing']);
Route::get('/pricescategories/service/{id}',[PricesCategoriesController::class,'foraservice']);

//Customers
Route::apiResource('categoriescustomers',CategoriesCustomerControllerController::class);
Route::get('/categoriescustomers/enterprise/{enterprise_id}',[CategoriesCustomerControllerController::class,'index']);
Route::put('/categoriescustomers/update/{id}',[CategoriesCustomerControllerController::class,'update2']);
Route::patch('/categoriescustomers/update/{id}',[CategoriesCustomerControllerController::class,'update2']);
Route::delete('/categoriescustomers/delete/{id}',[CategoriesCustomerControllerController::class,'destroy2']);

Route::apiResource('customers',CustomerControllerController::class);
Route::get('/customers/enterprise/{id}',[CustomerControllerController::class,'index']);
Route::get('/anonymous/customers/enterprise/{id}',[CustomerControllerController::class,'anonymous']);
Route::get('/customers/byid/{id}',[CustomerControllerController::class,'getcustomerbyId']);
Route::put('/customers/update/{id}',[CustomerControllerController::class,'update2']);
Route::patch('/customers/update/{id}',[CustomerControllerController::class,'update2']);
Route::delete('/customers/delete/{id}',[CustomerControllerController::class,'delete']);
Route::post('/customers/uuid',[CustomerControllerController::class,'getbyuuid']);
Route::post('/customers/importation',[CustomerControllerController::class,'importation']);

//PROVIDERS
Route::apiResource('providers',ProviderControllerController::class);
Route::get('/providers/enterprise/{id}',[ProviderControllerController::class,'index']);
Route::get('/providers/financialsituation/{enterpriseid}',[ProviderControllerController::class,'financialsituation']);
Route::get('/providers/debtbyprovider/{providerid}',[ProviderControllerController::class,'debtsprovider']);
Route::put('/providers/update/{id}',[ProviderControllerController::class,'update2']);
Route::patch('/providers/update/{id}',[ProviderControllerController::class,'update2']);
Route::delete('/providers/delete/{id}',[ProviderControllerController::class,'delete']);
Route::get('/providers/stockhistory/{id}',[ProviderControllerController::class,'stockhistory']);
Route::post('/providers/periodicstockhistory',[ProviderControllerController::class,'periodicstockhistory']);
Route::get('/providers/stockhistory/cash/{id}',[ProviderControllerController::class,'cashstockhistory']);
Route::get('/providers/stockhistory/debt/{id}',[ProviderControllerController::class,'debtstockhistory']);
Route::post('/providers/importation',[ProviderControllerController::class,'importation']);

Route::post('/providers/debt/payment',[ProviderspaymentsController::class,'store']);
Route::post('/providers/paymentslistbyprovider',[ProviderspaymentsController::class,'paymentsbyprovider']);

//tables
Route::apiResource('tables',TablesController::class);
Route::get('/tables/enterprise/{id}',[TablesController::class,'index']);
Route::get('/tables/sales/{id}',[TablesController::class,'sales']);
Route::get('/tables/servants/{id}',[TablesController::class,'servants']);
Route::put('/tables/update/{id}',[TablesController::class,'update2']);
Route::patch('/tables/update/{id}',[TablesController::class,'update2']);
Route::delete('/tables/delete/{id}',[TablesController::class,'delete2']);

//servants
Route::apiResource('servants',ServantsController::class);
Route::get('/servants/enterprise/{id}',[ServantsController::class,'index']);
Route::delete('/servants/delete/{id}',[ServantsController::class,'delete']);
Route::put('/servants/update/{id}',[ServantsController::class,'update2']);
Route::patch('/servants/update/{id}',[ServantsController::class,'update2']);
Route::get('/servants/sales/{id}',[ServantsController::class,'getsales']);

//Cautions, Bonus and Points
Route::apiResource('cautions',CautionsController::class);
Route::get('/cautions/enterprise/{id}',[CautionsController::class,'index']);
Route::get('/cautions/customer/{id}',[CautionsController::class,'foracustomer']);
Route::post('/cautions/customer/filtered',[CautionsController::class,'FilteredCautionsForACustomer']);

Route::apiResource('bonus',BonusController::class);
Route::get('/bonus/enterprise/{id}',[BonusController::class,'index']);
Route::get('/bonus/customer/{id}',[BonusController::class,'foracustomer']);

Route::apiResource('points',PointsController::class);
Route::get('/points/enterprise/{id}',[PointsController::class,'index']);
Route::get('/points/customer/{id}',[PointsController::class,'foracustomer']);
//Fences (Clotures)
Route::apiResource('fences',FencesController::class);
Route::get('/fences/enterprise/{id}',[FencesController::class,'index']);
Route::post('/fences/dataforfencing/',[FencesController::class,'dataforfencing']);
Route::post('/fences/dataforfencingbasedondate',[FencesController::class,'dataforfencingbasedondate']);
Route::post('/fences/subtotaldataforfencingbasedondate',[FencesController::class,'dataforfencingsumerized']);
Route::delete('/fences/delete/{id}',[FencesController::class,'delete2']);
Route::get('/fences/show/{id}',[FencesController::class,'getone']);

Route::apiResource('fenceticketing',FenceTicketingController::class);
//Invoices (Factures)
Route::apiResource('invoices',InvoicesController::class);
Route::post('/newinvoice/mobile',[InvoicesController::class,'storemobile']);
Route::post('/newinvoice/garage',[InvoicesController::class,'storegarage']);
Route::get('/invoices/enterprise/{id}',[InvoicesController::class,'index']);
Route::get('/invoices/getinvoicebyid/{id}',[InvoicesController::class,'getinvoicebyid']);
Route::get('/invoices/customer/{id}',[InvoicesController::class,'foracustomer']);
Route::post('/invoices/filteredcustomer',[InvoicesController::class,'forACustomerFiltered']);
Route::post('/invoices/reportbyuser',[InvoicesController::class,'reportUserSelling']);
Route::post('/invoices/newreportbyuser',[InvoicesController::class,'reportUserSelling2']);
Route::post('/invoices/reportfilteredbydatesoperations',[InvoicesController::class,'reportUserSelling2basedondatesoperations']);
Route::post('/invoices/reportUserSellingwithoutdetails',[InvoicesController::class,'reportUserSellingwithoutdetailsbasedoperationdates']);
Route::post('/invoices/sellsreportgroupedbydates',[InvoicesController::class,'sellsreportgroupedbydates']);
Route::post('/invoices/reportUserSelling2filteredbytva',[InvoicesController::class,'reportUserSelling2filteredbytva']);
Route::post('/invoices/reportbyuser/grouped',[InvoicesController::class,'reportUserSellingGroupByArticle']);
Route::get('/invoices/comptecourant/{customerid}',[InvoicesController::class,'comptecourant']);
Route::post('/invoices/users',[InvoicesController::class,'foraspecificuser']);
// Route::post('/invoices/searchingforaspecificuser',[InvoicesController::class,'searchingforaspecificuser']);
Route::get('/searchinginvoices',[InvoicesController::class,'searchingforaspecificuser']);
Route::patch('/invoices/cancel',[InvoicesController::class,'cancelling']);
Route::post('/invoices/cancel',[InvoicesController::class,'cancelling']);
Route::get('/orders/enterprise/{id}',[InvoicesController::class,'enterpriseorders']);
Route::get('/orders/users/{userid}',[InvoicesController::class,'userorders']);

Route::apiResource('invoicedetails',InvoiceDetailsController::class);

Route::apiResource('debts',DebtsController::class);
Route::get('/debts/enterprise/{enterprise_id}',[DebtsController::class,'index']);
Route::get('/searchdebtsbydoneby',[DebtsController::class,'searchdebtsbydoneby']);
Route::post('/debts/searchingbyidoruuid',[DebtsController::class,'searchingbyidoruuid']);
Route::post('/reports/credits',[DebtsController::class,'debtsgroupedbycustomerbasedodateoperation']);
Route::post('/reports/credits/debtsfilteredbycriteria',[DebtsController::class,'debtsfilteredbycriteria']);
Route::post('/debts/customer',[DebtsController::class,'compteCourant']);
Route::post('/debts/customer/filteredcomptecourantcustomer',[DebtsController::class,'FilteredcompteCourant']);
Route::post('/debts/payment',[DebtsController::class,'payment_debt']);
Route::get('/debts/getDebtById/{id}',[DebtsController::class,'getDebtById']);
Route::post('/debts/debt/payments',[DebtsController::class,'getPayments']);

Route::apiResource('payments',DebtPaymentsController::class);
Route::get('/searchpaymentsdebtsbydoneby',[DebtPaymentsController::class,'searchpaymentsdebtsbydoneby']);
Route::get('/payments/getbyid/{id}',[DebtPaymentsController::class,'getpaymentbyid']);
//Financial mouvements
Route::apiResource('accounts',AccountsController::class);
Route::put('accounts/update/{id}',[AccountsController::class,'update2']);
Route::patch('accounts/update/{id}',[AccountsController::class,'update2']);
Route::get('accounts/enterprise/{enterprise_id}',[AccountsController::class,'index']);
Route::get('accounts/getone/{account_id}',[AccountsController::class,'showone']);
Route::delete('accounts/delete/{id}',[AccountsController::class,'delete']);
Route::post('/accounts/importation',[AccountsController::class,'importation']);

Route::apiResource('expenditures',ExpendituresController::class);
Route::post('/expenditures/doneby',[ExpendituresController::class,'doneby']);
Route::get('/expenditures/getexpenditurebyid/{id}',[ExpendituresController::class,'getexpenditurebyid']);
Route::get('/searchexpendituresdoneby',[ExpendituresController::class,'searchdoneby']);
Route::post('/expenditures/byaccount',[ExpendituresController::class,'byaccount']);
Route::delete('/expenditures/delete/{id}',[ExpendituresController::class,'delete']);

Route::apiResource('otherentries',OtherEntriesController::class);
Route::get('/otherentries/enterprise/{enterpriseid}',[OtherEntriesController::class,'index']);
Route::get('/otherentries/account/{accountid}',[OtherEntriesController::class,'byaccount']);
Route::get('/otherentries/update/{id}',[OtherEntriesController::class,'update2']);
Route::get('/otherentries/delete/{id}',[OtherEntriesController::class,'delete']);
Route::get('/otherentries/doneby/{id}',[OtherEntriesController::class,'doneby']);
Route::get('/searchotherentriesdoneby',[OtherEntriesController::class,'searchdoneby']);
Route::get('/otherentries/getotherentrybyid/{id}',[OtherEntriesController::class,'getotherentrybyid']);
Route::post('/otherentries/dailyreport',[OtherEntriesController::class,'doneby']);

/**
 * Safeguards
 */
Route::apiResource('safeguards',SafeguardController::class);
Route::post('/safeguards/invoices',[SafeguardController::class,'invoicesSafeguard']);

/**
 * Roles
 */
Route::resource('/role',RolesController::class);
Route::delete('/role/delete/{id}',[RolesController::class,'destroy2']);
Route::post('/role/owner',[RolesController::class,'ruleForOwner']);
Route::get('/role/enterprise/{id}',[RolesController::class,'index']);
Route::get('/role/permissions/{id}',[RolesController::class,'gerpermissions']);
Route::get('/role/specificroleuser/{id}',[RolesController::class,'specificRoleUser']);

/**
 * Expenditures limits
 */
Route::resource('/limitsexpenditures',ExpendituresLimitsController::class);
Route::get('/limitsexpenditures/enterprise/{id}',[ExpendituresLimitsController::class,'index']);
Route::put('/limitsexpenditures/update/{id}',[ExpendituresLimitsController::class,'update2']);
Route::delete('/limitsexpenditures/delete/{id}',[ExpendituresLimitsController::class,'destroy2']);
Route::get('/limitsexpenditures/users/{id}',[ExpendituresLimitsController::class,'getUsersForOne']);
Route::get('/limitsexpenditures/user/{id}',[ExpendituresLimitsController::class,'getforaspecificuser']);

/**
 * Expenditures Limits with Users
 */
Route::resource('/limitsusers',UsersExpendituresLimitsController::class);
Route::delete('/limitsusers/delete/{id}',[UsersExpendituresLimitsController::class,'destroy2']);

/**
 * reports for uzisha stock
 */
//v1
Route::post('/reports/cashbook',[InvoicesController::class,'cashbook']);
Route::post('/reports/invoices/reportbyarticles',[InvoicesController::class,'reportbyarticles']);
Route::post('/reports/invoices/reportbydepositsarticles',[InvoicesController::class,'reportbydepositsarticles']);
Route::post('/reports/invoices/reportbyagents',[InvoicesController::class,'reportbyagents']);
Route::post('/reports/invoices/creditsByCutomers',[DebtsController::class,'creditsByCutomers']);

//v2
Route::post('/reports/cashbook/cashbookbasedondateoperations',[InvoicesController::class,'cashbookbasedondateoperations']);
Route::post('/reports/invoices/reportbyarticlesbasedondates',[InvoicesController::class,'reportbyarticlesbasedondates']);
Route::post('/reports/invoices/reportbyarticlesbasedondateoperation',[InvoicesController::class,'reportbyarticlesbasedondateoperation']);
Route::post('/reports/invoices/reportbydepositsarticlesbasedonoperation',[InvoicesController::class,'reportbydepositsarticlesbasedondateoperation']);
Route::post('/reports/invoices/reportbyagentsbasedondateoperations',[InvoicesController::class,'reportbyagentsbasedondateoperations']);
Route::post('/reports/invoices/creditsByCutomersbasedondate',[DebtsController::class,'creditsByCutomersbasedondate']);
Route::post('/reports/invoices/paymentsbycutomersbasedondate',[DebtPaymentsController::class,'paymentsbycutomersbasedondate']);
Route::post('/reports/invoices/reportpaymentsbydates',[DebtPaymentsController::class,'reportpaymentsbydates']);
Route::post('/reports/invoices/groupreportbyprices',[InvoicesController::class,'groupreportbyprices']);

/**
 * Pressings
 */

//Services
Route::post('/pressing/services',[PressingServicesController::class,'services_list']);
Route::post('/pressing/services/new',[PressingServicesController::class,'store']);
Route::put('/pressing/services/update/{id}',[ServicesControllerController::class,'update2']);
Route::patch('/pressing/services/update/{id}',[ServicesControllerController::class,'update2']);
Route::delete('/pressing/services/delete/{id}',[ServicesControllerController::class,'destroy2']);

//Colors
Route::apiResource('pressing/colors',ColorsController::class);
Route::get('/pressing/colors/enterprise/{enterpriseid}',[ColorsController::class,'index']);
Route::put('/pressing/colors/update/{id}',[ColorsController::class,'update2']);
Route::patch('/pressing/colors/update/{id}',[ColorsController::class,'update2']);
Route::delete('/pressing/colors/delete/{id}',[ColorsController::class,'destroy2']);

//Defects
Route::apiResource('/pressing/defects',DefectsController::class);
Route::get('/pressing/defects/enterprise/{enterpriseid}',[DefectsController::class,'index']);
Route::put('/pressing/defects/update/{id}',[DefectsController::class,'update2']);
Route::patch('/pressing/defects/update/{id}',[DefectsController::class,'update2']);
Route::delete('/pressing/defects/delete/{id}',[DefectsController::class,'destroy2']);

//Spots
Route::apiResource('/pressing/spots',SpotsController::class);
Route::get('/pressing/spots/enterprise/{enterpriseid}',[SpotsController::class,'index']);
Route::put('/pressing/spots/update/{id}',[SpotsController::class,'update2']);
Route::patch('/pressing/spots/update/{id}',[SpotsController::class,'update2']);
Route::delete('/pressing/spots/delete/{id}',[SpotsController::class,'destroy2']);

//Styles
Route::apiResource('/pressing/styles',StylesController::class);
Route::get('/pressing/styles/enterprise/{enterpriseid}',[StylesController::class,'index']);
Route::put('/pressing/styles/update/{id}',[StylesController::class,'update2']);
Route::patch('/pressing/styles/update/{id}',[StylesController::class,'update2']);
Route::delete('/pressing/styles/delete/{id}',[StylesController::class,'destroy2']);

//materials
Route::apiResource('pressing/materials',MaterialsController::class);
Route::get('/pressing/materials/enterprise/{enterpriseid}',[MaterialsController::class,'index']);
Route::put('/pressing/materials/update/{id}',[MaterialsController::class,'update2']);
Route::patch('/pressing/materials/update/{id}',[MaterialsController::class,'update2']);
Route::delete('/pressing/materials/delete/{id}',[MaterialsController::class,'destroy2']);

//reasons
Route::apiResource('pressing/reasons',ReasonsController::class);
Route::get('/pressing/reasons/enterprise/{enterpriseid}',[ReasonsController::class,'index']);
Route::put('/pressing/reasons/update/{id}',[ReasonsController::class,'update2']);
Route::patch('/pressing/reasons/update/{id}',[ReasonsController::class,'update2']);
Route::delete('/pressing/reasons/delete/{id}',[ReasonsController::class,'destroy2']);

//additional services fees
Route::apiResource('additionalfees',ServicesadditionalfeesController::class);
Route::get('/pressing/additionalfees/enterprise/{enterpriseid}',[ServicesadditionalfeesController::class,'index']);

//Customers
Route::get('/pressing/customers/enterprise/{id}',[CustomerControllerController::class,'index']);
Route::post('/pressing/customers/new',[CustomerControllerController::class,'store']);
Route::put('/pressing/customers/update/{id}',[CustomerControllerController::class,'update2']);
Route::patch('/pressing/customers/update/{id}',[CustomerControllerController::class,'update2']);
Route::delete('/pressing/customers/delete/{id}',[CustomerControllerController::class,'delete']);

//Orders
Route::post('/pressing/orders/enterprise',[InvoicesController::class,'pressingOrders']);
Route::post('/pressing/orders/new',[InvoicesController::class,'storeorder']);
Route::put('/pressing/orders/update',[InvoicesController::class,'updateorder']);
//update order status
Route::post('/pressing/order/status/new',[InvoicesStatusController::class,'store']);
Route::get('/pressing/order/status/{id}',[InvoicesStatusController::class,'statusForAspecificInvoice']);
Route::post('/pressing/orders/statisticbystatus',[InvoicesStatusController::class,'statisticbyinvoices']);
Route::post('/pressing/orders/statisticdetailsbystatus',[InvoicesStatusController::class,'statisticByDetailsInvoices']);

//Update detail's order status
Route::post('/pressing/order-detail/status/new',[DetailsInvoicesStatusController::class,'store']);

//Status
Route::get('/pressing/status/enterprise/{id}',[StatusController::class,'index']);
Route::post('/pressing/status/new',[StatusController::class,'store']);
Route::put('/pressing/status/update/{id}',[StatusController::class,'update2']);
Route::patch('/pressing/status/update/{id}',[StatusController::class,'update2']);
Route::delete('/pressing/status/delete/{id}',[StatusController::class,'destroy2']);

//Pressing fencing
Route::post('/pressing/fences/new',[FencesController::class,'store']);
Route::get('/pressing/fences/enterprise/{id}',[FencesController::class,'index']);
Route::post('/pressing/fences/dataforfencing',[FencesController::class,'dataforfencing']);

//Pressing expenditures
Route::post('/pressing/expenditures',[ExpendituresController::class,'store']);
Route::post('/pressing/expenditures/doneby',[ExpendituresController::class,'doneby']);
Route::post('/pressing/expenditures/delete/{id}',[ExpendituresController::class,'delete']);

//Pressing accounts
Route::post('/pressing/accounts/new',[AccountsController::class,'store']);
Route::get('/pressing/accounts/enterprise/{enterprise_id}',[AccountsController::class,'index']);
Route::put('/pressing/accounts/update/{id}',[AccountsController::class,'update2']);
Route::patch('pressing/accounts/update/{id}',[AccountsController::class,'update2']);

/**
 * invoices
 */
Route::post('/pressing/invoices/reportbyuser',[InvoicesController::class,'reportUserSelling']);
Route::post('/pressing/invoices/newreportbyuser',[InvoicesController::class,'reportUserSelling2basedondatesoperations']);

//Pressing otherentries
Route::post('/pressing/otherentries',[OtherEntriesController::class,'store']);
Route::get('/pressing/otherentries/update/{id}',[OtherEntriesController::class,'update2']);
Route::post('/pressing/otherentries/doneby',[OtherEntriesController::class,'doneby']);

/**
 * SEARCHING METHODS
 */
//Services
Route::get('/services/search/enterprise/{enterprise_id}',[ServicesControllerController::class,'search']);
Route::post('/services/searchbyword/enterprise',[ServicesControllerController::class,'searchinarticlesbyname']);
Route::post('/services/searchbycodebar/enterprise',[ServicesControllerController::class,'searchbycodebar']);
Route::post('/services/updateAll',[ServicesControllerController::class,'updateallservices']);
Route::post('/services/availablesunavailablesservices',[ServicesControllerController::class,'availablesunavailablesservices']);
Route::post('/services/financialsummarybyservice',[ServicesControllerController::class,'financialsummarybyservice']);

//DEPOSIT AND SERVICES
Route::post('/deposit/services/searchbywords',[ServicesControllerController::class,'searchinarticlesdeposit']);
Route::post('/deposit/services/searchbybarcode',[ServicesControllerController::class,'searchinarticlesbybarcode']);
Route::post('/deposit/services/searchbycategorieandeposit',[ServicesControllerController::class,'searchbycategorieandeposit']);

//CUSTOMERS
Route::get('/customers/search/enterprise/{id}',[CustomerControllerController::class,'search']);
Route::get('/searchingcustomersbypagination',[CustomerControllerController::class,'searchingcustomersbypagination']);
Route::post('/customers/search-words',[CustomerControllerController::class,'searchbywords']);

//vehicules
Route::apiResource('vehicules',VehiculesController::class);
Route::get('/vehicules/enterprise/{id}',[VehiculesController::class,'index']);
Route::post('/vehicules/search-words',[VehiculesController::class,'searchbywords']);

//tests methods
Route::get('/testwithdrawadjust',[InvoicesController::class,'testwithdrawadjust']);

/**
 * API WEKA AKIBA END-POINTS
 */
Route::get('/weka/allaccounts/{id}',[WekamemberaccountsController::class,'allaccounts']);
Route::get('/weka/member/accounts/{id}',[WekamemberaccountsController::class,'membersaccounts']);
Route::post('/weka/import/members',[UsersController::class,'wekaimportmembers']);
Route::get('/weka/users/enterprise/{id}',[UsersController::class,'wekamemberslist']);
Route::get('/weka/memberspaginated/{enterpriseid}',[UsersController::class,'wekamemberslistpaginated']);
Route::get('/weka/members-to-validated/enterprise/{id}',[UsersController::class,'wekamemberstovalidate']);
Route::post('/weka/members/lookup',[UsersController::class,'wekamemberslookup']);

Route::post('/weka/financedashboard/{userid}',[UsersController::class,'wekafinancedashboard']);

Route::post('/weka/transactions/new',[WekaAccountsTransactionsController::class,'store']);
Route::post('/weka/transactions/syncing',[WekaAccountsTransactionsController::class,'syncing']);
Route::post('/weka/transactions',[WekaAccountsTransactionsController::class,'index']);
Route::post('/weka/mobile-dashboard',[WekaAccountsTransactionsController::class,'dashboardmobileatwekaakiba']);
Route::post('/weka/transactions/update',[WekaAccountsTransactionsController::class,'updatetransactions']);

Route::post('/weka/members/newmember',[UsersController::class,'newwekamember']);
Route::put('/weka/members/update/{id}',[UsersController::class,'updatewekamember']);

Route::post('/weka/firstentries',[WekafirstentriesController::class,'index']);
Route::post('/weka/firstentries/new',[WekafirstentriesController::class,'store']);
Route::post('/weka/firstentries/delete',[WekafirstentriesController::class,'destroy']);
Route::post('/weka/firstentries/update',[WekafirstentriesController::class,'update']);

Route::post('/weka/members-validation',[UsersController::class,'members_validation']);
Route::post('/weka/usersbytypes/enterprise',[UsersController::class,'usersbytypes']);
Route::post('/users/membertocollectors',[UsersController::class,'membertocollectors']);


Route::post('/weka/groups',[WekagroupsController::class,'index']);
Route::post('/weka/groupswithmembers',[WekagroupsController::class,'groupandmembers']);
Route::post('/weka/groupes/new',[WekagroupsController::class,'store']);
Route::post('/weka/groups/update/{id}',[WekagroupsController::class,'update']);
Route::post('/weka/groups/addmembers',[WekagroupsController::class,'addmembers']);
Route::get('/weka/groups/members/{id}',[WekagroupsController::class,'getmembers']);
Route::post('/weka/groups/removemembers',[WekagroupsController::class,'removemembers']);
Route::post('/weka/groups/memberslevel',[WekagroupsController::class,'memberslevel']);
Route::post('/weka/groups/memberslookup',[WekagroupsController::class,'memberslookup']);

/**
 * POSITIONS
 * 
 */
Route::post('/weka/positions/new',[PositionsController::class,'store']);
Route::post('/weka/positions/list',[PositionsController::class,'index']);

/**
 * SALARIES
 */
Route::post('/weka/salaries/new',[SalariesController::class,'store']);
Route::post('/weka/salaries/list',[SalariesController::class,'index']);
Route::post('/weka/salaries/update/{id}',[SalariesController::class,'update']);
Route::post('/weka/salaries/employeespayslips',[SalariesController::class,'employeesalairies']);
Route::post('/weka/salaries/delete',[SalariesController::class,'deletesalary']);

/**
 * ADVANCES SALARIES
 */
Route::post('/weka/advancesalaries/new',[AdvancesalariesController::class,'store']);
Route::post('/weka/advancesalaries/byagent',[AdvancesalariesController::class,'index']);
Route::post('/weka/advancesalaries/update',[AdvancesalariesController::class,'update']);
Route::post('/weka/advancesalaries/delete',[AdvancesalariesController::class,'deleteadvance']);

/**
 * Weka Expenditures
 */
Route::post('/weka/expenditures/update',[ExpendituresController::class,'expendituresupdate']);

/**
 * CERUBU MODULES
 */
Route::post('/testcerubu',[UzishafuelconsumptionController::class,'store']);
Route::post('/testcerubu/report',[UzishafuelconsumptionController::class,'index']);

/**
 * Librairies routes
 */
Route::post('/libraries',[LibrariesController::class,'store']);
Route::post('/libraries/enterprise',[LibrariesController::class,'index']);
Route::get('/libraries/storage/enterprise/{enterprise_id}',[LibrariesController::class,'getstorage']);
Route::post('/libraries/share',[SharedlibrariesController::class,'store']);
Route::post('/libraries/delete',[LibrariesController::class,'deletemultiple']);
Route::post('/libraries/getfile',[LibrariesController::class,'getfilebyname']);
Route::get('/libraries/image/{filename}',[LibrariesController::class,'getfile']);

/**
 * Seeder run offline
 */
Route::post('/enterprises/run-seeders',[EnterprisesController::class,'run_seeders']);

/**
 * Plans routes
 */
Route::get('/plans',[PlansController::class,'index']);

/**
 * updatind sold when uploading exel file
 */
Route::post('/soldeUpdating', [WekamemberaccountsController::class, 'AccountUpdateSold'] );




