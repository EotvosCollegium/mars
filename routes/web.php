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
use App\Http\Controllers\Dormitory\RoomController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Network\AdminCheckoutController;
use App\Http\Controllers\Network\AdminInternetController;
use App\Http\Controllers\Network\InternetController;
use App\Http\Controllers\Network\MacAddressController;
use App\Http\Controllers\Network\RouterController;
use App\Http\Controllers\IssuesController;
use App\Http\Controllers\Secretariat\DocumentController;
use App\Http\Controllers\Secretariat\GuestsController;
use App\Http\Controllers\Secretariat\InvitationController;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Http\Controllers\Secretariat\UserController;
use App\Http\Controllers\StudentsCouncil\CommunityServiceController;
use App\Http\Controllers\StudentsCouncil\EconomicController;
use App\Http\Controllers\StudentsCouncil\EpistolaController;
use App\Http\Controllers\StudentsCouncil\GeneralAssemblyController;
use App\Http\Controllers\StudentsCouncil\GeneralAssemblyPresenceCheckController;
use App\Http\Controllers\StudentsCouncil\GeneralAssemblyQuestionController;
use App\Http\Controllers\StudentsCouncil\MrAndMissController;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\OnlyHungarian;
use App\Http\Middleware\EnsureVerified;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * This file contains the routes of the application.
 *
 * We encourage the usage of typical CRUD routes (crate, read, update, delete):
 * Verb      URI                    Function    Route Name          Comment
 * GET       /photos                index       photos.index        Display a list of all photos
 * GET       /photos/create         create      photos.create       Display a form to create a new photo
 * POST      /photos                store       photos.store        Create a new photo
 * GET       /photos/{photo}        show        photos.show         Display a specific photo
 * GET       /photos/{photo}/edit   edit        photos.edit         Display a form to edit a specific photo
 * PUT/PATCH /photos/{photo}        update      photos.update       Update a specific photo
 * DELETE    /photos/{photo}        destroy     photos.destroy      Delete a specific photo
 *
 * See more: https://laravel.com/docs/10.x/controllers#resource-controllers
 *
 */

Route::get('/', [HomeController::class, 'welcome'])->name('index');
Route::get('/verification', [HomeController::class, 'verification'])->name('verification');
Route::get('/privacy_policy', [HomeController::class, 'privacyPolicy'])->name('privacy_policy');
Route::get('/img/{filename}', [HomeController::class, 'getPicture']);
Route::get('/setlocale/{locale}', [HomeController::class, 'setLocale'])->name('setlocale');

Auth::routes(); //check \Laravel\Ui\AuthRouteMethods

Route::get('/register/guest', [RegisterController::class, 'showTenantRegistrationForm'])->name('register.guest');

Route::middleware([Authenticate::class, LogRequests::class, OnlyHungarian::class])->group(function () {
    Route::get('/application', [ApplicationController::class, 'showApplicationForm'])->name('application');
});
Route::middleware([Authenticate::class, LogRequests::class])->group(function () {
    /** Routes that needs to be accessed during the application process */
    Route::post('/application', [ApplicationController::class, 'storeApplicationForm'])->name('application.store');
    Route::post('/users/{user}/profile_picture', [UserController::class, 'storeProfilePicture'])->name('users.update.profile-picture');
    Route::delete('/users/{user}/profile_picture', [UserController::class, 'deleteProfilePicture'])->name('users.delete.profile-picture');
    Route::post('/users/{user}/personal_information', [UserController::class, 'updatePersonalInformation'])->name('users.update.personal');
    Route::post('/users/{user}/educational_information', [UserController::class, 'updateEducationalInformation'])->name('users.update.educational');
    Route::post('/users/{user}/alfonso', [UserController::class, 'updateAlfonsoStatus'])->name('users.update.alfonso');
    Route::post('/users/{user}/language_exam', [UserController::class, 'uploadLanguageExam'])->name('users.language_exams.upload');
    Route::post('/application/finalize', [ApplicationController::class, 'finalizeApplicationProcess'])->name('application.finalize');
});

Route::middleware([Authenticate::class, LogRequests::class, EnsureVerified::class])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/home/edit', [HomeController::class, 'editNews'])->name('home.edit');

    /** Issue reporting */
    Route::get('/issues', [IssuesController::class, 'create'])->name('issues.create');
    Route::post('/issues/create', [IssuesController::class, 'store'])->name('issues.store');

    /** User related routes */
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/tenant_until', [UserController::class, 'updateTenantUntil'])->name('users.update.tenant_until');
    Route::post('/users/{user}/roles/{role}', [UserController::class, 'addRole'])->name('users.roles.add');
    Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.delete');
    Route::post('/users/update_password', [UserController::class, 'updatePassword'])->name('users.update.password')->withoutMiddleware(LogRequests::class);
    Route::get('/users/tenant_update/show', [UserController::class, 'showTenantUpdate'])->name('users.tenant-update.show');
    Route::post('/users/tenant_update/applicant', [UserController::class, 'tenantToApplicant'])->name('users.tenant-update.to-applicant');

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

    Route::prefix('internet')->name('internet.')->group(function () {
        Route::get('/', [InternetController::class, 'index'])->name('index');
        Route::post('/reset', [InternetController::class, 'resetWifiPassword'])->name('password.reset');
        Route::post('/report', [InternetController::class, 'reportFault'])->name('report_fault');
        Route::resource('mac_addresses', MacAddressController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::get('/admin', [AdminInternetController::class, 'index'])->name('admin.index');
        Route::get('/admin/wifi_connections', [AdminInternetController::class, 'indexWifi'])->name('wifi_connections.index');
        Route::get('/admin/internet_accesses', [AdminInternetController::class, 'indexInternetAccesses'])->name('internet_accesses.index');
        Route::post('/admin/{internet_access}/extend', [AdminInternetController::class, 'extend'])->name('internet_accesses.extend');
        Route::post('/admin/{internet_access}/revoke', [AdminInternetController::class, 'revoke'])->name('internet_accesses.revoke');
    });

    /** Admin Checkout **/
    Route::get('/network/admin/checkout', [AdminCheckoutController::class, 'showCheckout'])->name('admin.checkout');
    Route::post('/network/admin/checkout/mark_as_paid/{user}', [AdminCheckoutController::class, 'markAsPaid'])->name('admin.checkout.pay');
    Route::post('/network/admin/checkout/to_checkout', [AdminCheckoutController::class, 'toCheckout'])->name('admin.checkout.to_checkout');
    Route::post('/network/admin/checkout/expense/add', [AdminCheckoutController::class, 'addExpense'])->name('admin.checkout.expense.add');
    Route::post('/network/admin/checkout/income/add', [AdminCheckoutController::class, 'addIncome'])->name('admin.checkout.income.add');
    Route::get('/network/admin/checkout/transaction/delete/{transaction}', [EconomicController::class, 'deleteTransaction'])->name('admin.checkout.transaction.delete');

    /** Routers */
    Route::get('/routers', [RouterController::class, 'index'])->name('routers');
    Route::get('/routers/create', [RouterController::class, 'create'])->name('routers.create');
    Route::post('/routers/create', [RouterController::class, 'store'])->name('routers.store');
    Route::get('/routers/{router}', [RouterController::class, 'view'])->name('routers.view');
    Route::get('/routers/{router}/edit', [RouterController::class, 'edit'])->name('routers.edit');
    Route::post('/routers/{router}/edit', [RouterController::class, 'update'])->name('routers.update');
    Route::post('/routers/{router}/delete', [RouterController::class, 'delete'])->name('routers.delete');

    /** Registration handling */
    Route::get('/secretariat/registrations', [GuestsController::class, 'index'])->name('secretariat.registrations');
    Route::get('/secretariat/registrations/accept/{id}', [GuestsController::class, 'accept'])->name('secretariat.registrations.accept');
    Route::get('/secretariat/registrations/reject/{id}', [GuestsController::class, 'reject'])->name('secretariat.registrations.reject');
    Route::post('/secretariat/invite', [InvitationController::class, 'store'])->name('secretariat.invite');

    /** Application handling */
    Route::get('/applications', [ApplicationController::class, 'showApplications'])->name('applications');
    Route::post('/applications', [ApplicationController::class, 'editApplication'])->name('applications.edit');
    Route::get('/applications/export', [ApplicationController::class, 'exportApplications'])->name('applications.export');

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

    /** Evaluation form */
    Route::get('/secretariat/evaluation', [SemesterEvaluationController::class, 'show'])->name('secretariat.evaluation.show');
    Route::post('/secretariat/evaluation', [SemesterEvaluationController::class, 'store'])->name('secretariat.evaluation.store');

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
    Route::post('/economic_committee/expense/add', [EconomicController::class, 'addExpense'])->name('economic_committee.expense.add');
    Route::post('/economic_committee/income/add', [EconomicController::class, 'addIncome'])->name('economic_committee.income.add');
    Route::get('/economic_committee/transaction/delete/{transaction}', [EconomicController::class, 'deleteTransaction'])->name('economic_committee.transaction.delete');
    Route::post('/economic_committee/mark_as_paid/{user}', [EconomicController::class, 'markAsPaid'])->name('economic_committee.pay');
    Route::post('/economic_committee/to_checkout', [EconomicController::class, 'toCheckout'])->name('economic_committee.to_checkout');

    Route::get('/economic_committee/kktnetreg', [EconomicController::class, 'indexKKTNetreg'])->name('kktnetreg');
    Route::post('/economic_committee/kktnetreg/pay', [EconomicController::class, 'payKKTNetreg'])->name('kktnetreg.pay');
    Route::get('/economic_committee/calculate_workshop_balance', [EconomicController::class, 'calculateWorkshopBalance'])->name('economic_committee.workshop_balance');
    Route::put('/economic_committee/workshop_balance/{workshop_balance}', [EconomicController::class, 'modifyWorkshopBalance'])->name('economic_committee.workshop_balance.update');


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

    /** voting */
    Route::get("/general_assemblies", [GeneralAssemblyController::class, 'index'])->name('general_assemblies.index');
    Route::get("/general_assemblies/create", [GeneralAssemblyController::class, 'create'])->name('general_assemblies.create');
    Route::post("/general_assemblies", [GeneralAssemblyController::class, 'store'])->name('general_assemblies.store');
    Route::get("/general_assemblies/{general_assembly}", [GeneralAssemblyController::class, 'show'])->name('general_assemblies.show');
    Route::get("/general_assemblies/{general_assembly}/code", [GeneralAssemblyController::class, 'showCode'])->name('general_assemblies.show_code');
    Route::post('/general_assemblies/{general_assembly}/open', [GeneralAssemblyController::class, 'openAssembly'])->name('general_assemblies.open');
    Route::post('/general_assemblies/{general_assembly}/close', [GeneralAssemblyController::class, 'closeAssembly'])->name('general_assemblies.close');

    Route::get('/general_assemblies/{general_assembly}/questions/create', [GeneralAssemblyQuestionController::class, 'create'])->name('general_assemblies.questions.create');
    Route::post('/general_assemblies/{general_assembly}/questions', [GeneralAssemblyQuestionController::class, 'store'])->name('general_assemblies.questions.store');
    Route::get('/general_assemblies/{general_assembly}/questions/{question}', [GeneralAssemblyQuestionController::class, 'show'])->name('general_assemblies.questions.show');
    Route::post('/general_assemblies/{general_assembly}/questions/{question}/open', [GeneralAssemblyQuestionController::class, 'openQuestion'])->name('general_assemblies.questions.open');
    Route::post('/general_assemblies/{general_assembly}/questions/{question}/close', [GeneralAssemblyQuestionController::class, 'closeQuestion'])->name('general_assemblies.questions.close');
    Route::post('/general_assemblies/{general_assembly}/questions/{question}/votes', [GeneralAssemblyQuestionController::class, 'saveVote'])->name('general_assemblies.questions.votes.store')->withoutMiddleware(LogRequests::class);

    Route::get('/general_assemblies/{general_assembly}/presence_checks/create', [GeneralAssemblyPresenceCheckController::class, 'create'])->name('general_assemblies.presence_checks.create');
    Route::post('/general_assemblies/{general_assembly}/presence_checks', [GeneralAssemblyPresenceCheckController::class, 'store'])->name('general_assemblies.presence_checks.store');
    Route::get('/general_assemblies/{general_assembly}/presence_checks/{presence_check}', [GeneralAssemblyPresenceCheckController::class, 'show'])->name('general_assemblies.presence_checks.show');
    Route::post('/general_assemblies/{general_assembly}/presence_checks/{presence_check}/close', [GeneralAssemblyPresenceCheckController::class, 'closePresenceCheck'])->name('general_assemblies.presence_checks.close');
    Route::post('/general_assemblies/{general_assembly}/presence_checks/{presence_check}/sign_presence', [GeneralAssemblyPresenceCheckController::class, 'signPresence'])->name('general_assemblies.presence_checks.presence.store')->withoutMiddleware(LogRequests::class);
});
