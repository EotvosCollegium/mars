<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\ApplicationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Dormitory\FaultController;
use App\Http\Controllers\Dormitory\PrintController;
use App\Http\Controllers\Dormitory\RoomAssignmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Network\AdminCheckoutController;
use App\Http\Controllers\Network\InternetController;
use App\Http\Controllers\Network\RouterController;
use App\Http\Controllers\Secretariat\DocumentController;
use App\Http\Controllers\Secretariat\PermissionController;
use App\Http\Controllers\Secretariat\RegistrationsController;
use App\Http\Controllers\Secretariat\SemesterController;
use App\Http\Controllers\Secretariat\UserController;
use App\Http\Controllers\StudentsCouncil\EconomicController;
use App\Http\Controllers\StudentsCouncil\EpistolaController;
use App\Http\Controllers\StudentsCouncil\MrAndMissController;
use App\Http\Controllers\StudentsCouncil\CommunityServiceController;
use App\Http\Controllers\Dormitory\RoomController;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', [HomeController::class, 'welcome'])->name('index');
Route::get('/verification', [HomeController::class, 'verification'])->name('verification');
Route::get('/privacy_policy', [HomeController::class, 'privacyPolicy'])->name('privacy_policy');
Route::get('/img/{filename}', [HomeController::class, 'getPicture']);
Route::get('/setlocale/{locale}', [HomeController::class, 'setLocale'])->name('setlocale');

Auth::routes();

Route::get('/register/guest', [RegisterController::class, 'showTenantRegistrationForm'])->name('register.guest');

Route::middleware(['auth', 'only_hungarian'])->group(function () {
    Route::get('/application', [ApplicationController::class, 'showApplicationForm'])->name('application');
    Route::post('/application', [ApplicationController::class, 'storeApplicationForm'])->name('application.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/user/update_password', [UserController::class, 'updatePassword'])->name('users.update.password');
});

Route::middleware(['auth', 'log', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/home/edit', [HomeController::class, 'editNews'])->name('home.edit');
    Route::post('/color/{mode}', [HomeController::class, 'colorMode'])->name('set-color-mode');

    Route::post('/report_bug', [HomeController::class, 'reportBug'])->name('reportbug');
    Route::get('/report_bug', [HomeController::class, 'indexReportBug'])->name('index_reportbug');

    /** User related routes */
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/personal_information', [UserController::class, 'updatePersonalInformation'])->name('users.update.personal');
    Route::post('/users/{user}/educational_information', [UserController::class, 'updateEducationalInformation'])->name('users.update.educational');
    Route::post('/users/{user}/roles/{role}', [UserController::class, 'addRole'])->name('users.roles.add');
    Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.delete');


    /** Localization */
    Route::get('/localizations', [LocaleController::class, 'index'])->name('localizations');
    Route::post('/localizations/add', [LocaleController::class, 'add'])->name('localizations.add');
    Route::middleware(['can:viewAny,App\Models\LocalizationContribution'])->group(function () {
        Route::get('/localizations/admin', [LocaleController::class, 'indexAdmin'])->name('localizations.admin');
        Route::post('/localizations/admin/approve', [LocaleController::class, 'approve'])->name('localizations.approve');
        Route::post('/localizations/admin/approve_all', [LocaleController::class, 'approveAll'])->name('localizations.approve_all');
        Route::post('/localizations/admin/delete', [LocaleController::class, 'delete'])->name('localizations.delete');
    });

    /** Printing */
    Route::get('/print', [PrintController::class, 'index'])->name('print');
    Route::post('/print/no-paper', [PrintController::class, 'noPaper'])->name('print.no_paper');
    Route::post('/print/added-paper', [PrintController::class, 'addedPaper'])->name('print.added_paper');
    Route::get('/print/free_pages/list', [PrintController::class, 'listFreePages'])->name('print.free_pages.list');
    Route::get('/print/print_jobs/list', [PrintController::class, 'listPrintJobs'])->name('print.print_jobs.list');
    Route::get('/print/free_pages/list/all', [PrintController::class, 'listAllFreePages'])->name('print.free_pages.list.all');
    Route::get('/print/print_jobs/list/all', [PrintController::class, 'listAllPrintJobs'])->name('print.print_jobs.list.all');
    Route::post('/print/transfer_balance', [PrintController::class, 'transferBalance'])->name('print.transfer-balance');
    Route::post('/print/print_jobs/{id}/cancel', [PrintController::class, 'cancelPrintJob'])->name('print.print_jobs.cancel');
    Route::put('/print/print', [PrintController::class, 'print'])->name('print.print');
    Route::middleware(['can:modify,App\Models\PrintAccount'])->group(function () {
        Route::get('/print/account_history', [PrintController::class, 'listPrintAccountHistory'])->name('print.account_history');
        Route::get('/print/manage', [PrintController::class, 'admin'])->name('print.manage');
        Route::post('/print/modify_balance', [PrintController::class, 'modifyBalance'])->name('print.modify');
    });
    Route::post('/print/add_free_pages', [PrintController::class, 'addFreePages'])->name('print.free_pages')->middleware('can:create,App\Models\FreePages');

    /** Internet */
    Route::get('/internet', [InternetController::class, 'index'])->name('internet');
    Route::get('/internet/mac_addresses/users', [InternetController::class, 'getUsersMacAddresses'])->name('internet.mac_addresses.users');
    Route::get('/internet/admin/mac_addresses/all', [InternetController::class, 'getUsersMacAddressesAdmin'])->name('internet.admin.mac_addresses.all');
    Route::get('/internet/admin/internet_accesses/all', [InternetController::class, 'getInternetAccessesAdmin'])->name('internet.admin.internet_accesses.all');
    Route::get('/internet/admin/wifi_connections/all', [InternetController::class, 'getWifiConnectionsAdmin'])->name('internet.admin.wifi_connections.all');
    Route::get('/internet/admin/{user}/wifi_connections/approve', [InternetController::class, 'approveWifiConnections'])->name('admin.internet.wifi_connections.approve');
    Route::get('/internet/admin', [InternetController::class, 'admin'])->name('internet.admin');
    Route::post('/internet/mac_addresses/add', [InternetController::class, 'addMacAddress'])->name('internet.mac_addresses.add');
    Route::post('/internet/mac_addresses/{id}/edit', [InternetController::class, 'editMacAddress'])->name('internet.mac_addresses.edit');
    Route::post('/internet/mac_addresses/{id}/delete', [InternetController::class, 'deleteMacAddress'])->name('internet.mac_addresses.delete');
    Route::post('/internet/wifi_password/reset', [InternetController::class, 'resetWifiPassword'])->name('internet.wifi_password.reset');
    Route::post('/internet/internet_accesses/{id}/edit', [InternetController::class, 'editInternetAccess'])->name('internet.internet_accesses.edit');

    /** Admin Checkout **/
    Route::get('/network/admin/checkout', [AdminCheckoutController::class, 'showCheckout'])->name('admin.checkout');
    Route::post('network/admin/checkout/print_to_checkout', [AdminCheckoutController::class, 'printToCheckout'])->name('admin.checkout.to_checkout');
    Route::post('/network/admin/checkout/transaction/add', [AdminCheckoutController::class, 'addTransaction'])->name('admin.checkout.transaction.add');
    Route::get('/network/admin/checkout/transaction/delete/{transaction}', [EconomicController::class, 'deleteTransaction'])->name('admin.checkout.transaction.delete');

    /** Routers */
    Route::get('/routers', [RouterController::class, 'index'])->name('routers');
    Route::get('/routers/create', [RouterController::class, 'create'])->name('routers.create');
    Route::post('/routers/create', [RouterController::class, 'store'])->name('routers.store');
    Route::get('/routers/{ip}', [RouterController::class, 'view'])->name('routers.view');
    Route::get('/routers/{ip}/edit', [RouterController::class, 'edit'])->name('routers.edit');
    Route::post('/routers/{ip}/edit', [RouterController::class, 'update'])->name('routers.update');
    Route::post('/routers/{ip}/delete', [RouterController::class, 'delete'])->name('routers.delete');

    /** Registration handling */
    Route::middleware(['can:registration.handle'])->group(function () {
        Route::get('/secretariat/registrations', [RegistrationsController::class, 'index'])->name('secretariat.registrations');
        Route::get('/secretariat/registrations/accept/{id}', [RegistrationsController::class, 'accept'])->name('secretariat.registrations.accept');
        Route::get('/secretariat/registrations/reject/{id}', [RegistrationsController::class, 'reject'])->name('secretariat.registrations.reject');
        Route::post('/secretariat/registrations/invite', [RegistrationsController::class, 'invite'])->name('secretariat.registrations.invite');
    });
    /** Application handling */
    Route::get('/applications', [ApplicationController::class, 'showApplications'])->name('applications');
    Route::post('/applications', [ApplicationController::class, 'editApplication'])->name('applications.edit');
    Route::post('/applications/finalize', [ApplicationController::class, 'finalizeApplicationProcess'])->name('applications.finalize');

    /** Faults */
    Route::get('/faults', [FaultController::class, 'index'])->name('faults');
    Route::get('/faults/table', [FaultController::class, 'GetFaults'])->name('faults.table');
    Route::post('/faults/add', [FaultController::class, 'addFault'])->name('faults.add');
    Route::post('/faults/update', [FaultController::class, 'updateStatus'])->name('faults.update');

    /** Rooms */
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');
    Route::put('/rooms/{room}/capacity', [RoomController::class, 'updateRoomCapacity'])->name('rooms.update-capacity');
    Route::put('/rooms/update', [RoomController::class, 'updateResidents'])->name('rooms.update');
    Route::get('/rooms/modify', [RoomController::class, 'modify'])->name('rooms.modify');

    /** Status update form */
    Route::get('/secretariat/status-update', [SemesterController::class, 'showStatusUpdate'])->name('secretariat.status-update.show');
    Route::post('/secretariat/status-update', [SemesterController::class, 'updateStatus'])->name('secretariat.status-update.update');

    /** Documents */
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
    Route::get('/documents/register-statement/download', [DocumentController::class, 'downloadRegisterStatement'])->name('documents.register-statement.download');
    Route::get('/documents/register-statement/print', [DocumentController::class, 'printRegisterStatement'])->name('documents.register-statement.print');
    Route::get('/documents/import/show', [DocumentController::class, 'showImport'])->name('documents.import.show');
    Route::post('/documents/import/add', [DocumentController::class, 'addImport'])->name('documents.import.add');
    Route::post('/documents/import/remove', [DocumentController::class, 'removeImport'])->name('documents.import.remove');
    Route::get('/documents/import/download', [DocumentController::class, 'downloadImport'])->name('documents.import.download');
    Route::get('/documents/import/print', [DocumentController::class, 'printImport'])->name('documents.import.print');
    Route::get('/documents/status-cert/download', [DocumentController::class, 'downloadStatusCertificate'])->name('documents.status-cert.download');
    Route::get('/documents/status-cert/request', [DocumentController::class, 'requestStatusCertificate'])->name('documents.status-cert.request');
    Route::get('/documents/status-cert/{id}/show', [DocumentController::class, 'showStatusCertificate'])->name('documents.status-cert.show');

    /** Students' Council */
    Route::get('/economic_committee', [EconomicController::class, 'index'])->name('economic_committee');
    Route::post('/economic_committee/transaction/add', [EconomicController::class, 'addTransaction'])->name('economic_committee.transaction.add');
    Route::get('/economic_committee/transaction/delete/{transaction}', [EconomicController::class, 'deleteTransaction'])->name('economic_committee.transaction.delete');
    Route::get('/economic_committee/kktnetreg', [EconomicController::class, 'indexKKTNetreg'])->name('kktnetreg');
    Route::post('/economic_committee/kktnetreg/pay', [EconomicController::class, 'payKKTNetreg'])->name('kktnetreg.pay');
    Route::get('/economic_committee/calculate_workshop_balance', [EconomicController::class, 'calculateWorkshopBalance'])->name('economic_committee.workshop_balance');
    Route::put('/economic_committee/workshop_balance/{workshop_balance}', [EconomicController::class, 'modifyWorkshopBalance'])->name('economic_committee.workshop_balance.update');
    Route::post('/economic_committee/kktnetreg/to_checkout', [EconomicController::class, 'KKTNetregToCheckout'])->name('economic_committee.to_checkout');

    Route::get('/communication_committee/epistola', [EpistolaController::class, 'index'])->name('epistola');
    Route::get('/communication_committee/epistola/new', [EpistolaController::class, 'new'])->name('epistola.new');
    Route::get('/communication_committee/epistola/edit/{epistola}', [EpistolaController::class, 'edit'])->name('epistola.edit');
    Route::post('/communication_committee/epistola/update_or_create', [EpistolaController::class, 'updateOrCreate'])->name('epistola.update_or_create');
    Route::get('/communication_committee/epistola/restore/{epistola}', [EpistolaController::class, 'restore'])->name('epistola.restore');
    Route::post('/communication_committee/epistola/mark_as_sent/{epistola}', [EpistolaController::class, 'markAsSent'])->name('epistola.mark_as_sent');
    Route::post('/communication_committee/epistola/delete/{epistola}', [EpistolaController::class, 'delete'])->name('epistola.delete');
    Route::get('/communication_committee/epistola/preview', [EpistolaController::class, 'preview'])->name('epistola.preview');
    Route::get('/communication_committee/epistola/send', [EpistolaController::class, 'send'])->name('epistola.send');

    Route::get('/community_committee/mr_and_miss/vote', [MrAndMissController::class, 'indexVote'])->name('mr_and_miss.vote');
    Route::post('/community_committee/mr_and_miss/vote', [MrAndMissController::class, 'saveVote'])->name('mr_and_miss.vote.save');
    Route::post('/community_committee/mr_and_miss/vote/custom', [MrAndMissController::class, 'customVote'])->name('mr_and_miss.vote.custom');
    Route::get('/community_committee/mr_and_miss/categories', [MrAndMissController::class, 'indexCategories'])->name('mr_and_miss.categories');
    Route::post('/community_committee/mr_and_miss/categories', [MrAndMissController::class, 'createCategory'])->name('mr_and_miss.categories.create');
    Route::post('/community_committee/mr_and_miss/categories/create', [MrAndMissController::class, 'editCategories'])->name('mr_and_miss.categories.edit');
    Route::get('/community_committee/mr_and_miss/results', [MrAndMissController::class, 'indexResults'])->name('mr_and_miss.results');

    Route::get('/community_service', [CommunityServiceController::class, 'index'])->name('community_service');
    Route::post('/community_service/approve/{community_service}', [CommunityServiceController::class, 'approve'])->name('community_service.approve');
    Route::post('/community_service/reject/{community_service}', [CommunityServiceController::class, 'reject'])->name('community_service.reject');
    Route::post('/community_service/create', [CommunityServiceController::class, 'create'])->name('community_service.create');
    Route::get('/community_service/search', [CommunityServiceController::class, 'search'])->name('community_service.search');
});
