<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\{
    CourseController,
    ProfessionalFamilyController,
    FormativeActionController,
    ModuleController,
    SectorController,
    TeacherController,
    EntityController,
    CenterController,
    InscriptionController,
    UserController,
    StudentController,
    DocumentController,
    NoteController,
    ProjectController,
    CompanyController,
    CompanyDocumentController,
    CompanyContactController,
    InternshipController,
    InternshipDocumentController,
};

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

//Redirect de / a /login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->prefix('dashboard')->group(function () {

    Route::get('/settings', function () {
        return Inertia::render('Dashboard/Settings');
    })->name('dashboard.settings');

    Route::get('/entities', [EntityController::class, 'index'])->name('dashboard.entities');

    // Inscriptions
    Route::get('/inscriptions', [InscriptionController::class, 'index'])->name('dashboard.inscriptions');
    Route::post('/inscriptions/load-items', [InscriptionController::class, 'loadItems'])->name('dashboard.inscriptions.load-items');
    Route::post('/inscriptions', [InscriptionController::class, 'store'])->name('dashboard.inscriptions.store');
    Route::put('/inscriptions/{inscription}', [InscriptionController::class, 'update'])->name('dashboard.inscriptions.update');
    Route::delete('/inscriptions/{inscription}', [InscriptionController::class, 'destroy'])->name('dashboard.inscriptions.destroy');
    Route::delete('/inscriptions/{inscription}/permanent', [InscriptionController::class, 'destroyPermanent'])->name('dashboard.inscriptions.destroyPermanent');
    Route::post('/inscriptions/{id}/restore', [InscriptionController::class, 'restore'])->name('dashboard.inscriptions.restore');
    Route::get('/inscriptions/export-excel', [InscriptionController::class, 'exportExcel'])->name('dashboard.inscriptions.exportExcel');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('dashboard.courses');
    Route::post('/courses/load-items', [CourseController::class, 'loadItems'])->name('dashboard.courses.load-items');
    Route::post('/courses', [CourseController::class, 'store'])->name('dashboard.courses.store');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('dashboard.courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('dashboard.courses.destroy');
    Route::delete('/courses/{course}/permanent', [CourseController::class, 'destroyPermanent'])->name('dashboard.courses.destroyPermanent');
    Route::post('/courses/{id}/restore', [CourseController::class, 'restore'])->name('dashboard.courses.restore');
    Route::get('/courses/export-excel', [CourseController::class, 'exportExcel'])->name('dashboard.courses.exportExcel');
    Route::post('/courses/{id}/add-module', [CourseController::class, 'addModule'])->name('dashboard.courses.add-module');
    Route::post('/courses/{id}/remove-module', [CourseController::class, 'removeModule'])->name('dashboard.courses.remove-module');

    Route::post('/courses/load-autocomplete-items', [CourseController::class, 'loadAutocompleteItems'])->name('dashboard.courses.load-autocomplete-items');

    // Projects
    Route::get('/projects/all', [ProjectController::class, 'all'])->name('dashboard.projects.all');
    Route::post('/projects/load-items', [ProjectController::class, 'loadItems'])->name('dashboard.projects.load-items');
    Route::post('/projects', [ProjectController::class, 'store'])->name('dashboard.projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('dashboard.projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('dashboard.projects.destroy');
    Route::delete('/projects/{project}/permanent', [ProjectController::class, 'destroyPermanent'])->name('dashboard.projects.destroyPermanent');
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->name('dashboard.projects.restore');
    Route::get('/projects/export-excel', [ProjectController::class, 'exportExcel'])->name('dashboard.projects.exportExcel');

    // ProfessionalFamily
    Route::post('/professional-families/load-items', [ProfessionalFamilyController::class, 'loadItems'])->name('dashboard.professional-families.load-items');
    Route::post('/professional-families', [ProfessionalFamilyController::class, 'store'])->name('dashboard.professional-families.store');
    Route::put('/professional-families/{professionalFamily}', [ProfessionalFamilyController::class, 'update'])->name('dashboard.professional-families.update');
    Route::delete('/professional-families/{professionalFamily}', [ProfessionalFamilyController::class, 'destroy'])->name('dashboard.professional-families.destroy');
    Route::delete('/professional-families/{professionalFamily}/permanent', [ProfessionalFamilyController::class, 'destroyPermanent'])->name('dashboard.professional-families.destroyPermanent');
    Route::post('/professional-families/{id}/restore', [ProfessionalFamilyController::class, 'restore'])->name('dashboard.professional-families.restore');
    Route::get('/professional-families/export-excel', [ProfessionalFamilyController::class, 'exportExcel'])->name('dashboard.professional-families.exportExcel');

    // FormativeAction
    Route::get('/formative-actions', [FormativeActionController::class, 'index'])->name('dashboard.formative-actions');
    Route::get('/formative-actions/project/{project}', [FormativeActionController::class, 'getFormativeActionsByProject'])->name('dashboard.formative-actions.getFormativeActionsByProject');
    Route::get('/formative-actions/{formativeAction}', [FormativeActionController::class, 'getFormativeAction'])->name('dashboard.formative-actions.getFormativeAction');
    Route::post('/formative-actions/load-items', [FormativeActionController::class, 'loadItems'])->name('dashboard.formative-actions.load-items');
    Route::post('/formative-actions', [FormativeActionController::class, 'store'])->name('dashboard.formative-actions.store');
    Route::put('/formative-actions/{formativeAction}', [FormativeActionController::class, 'update'])->name('dashboard.formative-actions.update');
    Route::delete('/formative-actions/{formativeAction}', [FormativeActionController::class, 'destroy'])->name('dashboard.formative-actions.destroy');
    Route::delete('/formative-actions/{formativeAction}/permanent', [FormativeActionController::class, 'destroyPermanent'])->name('dashboard.formative-actions.destroyPermanent');
    Route::post('/formative-actions/{id}/restore', [FormativeActionController::class, 'restore'])->name('dashboard.formative-actions.restore');
    Route::get('/formative-actions/export-excel', [FormativeActionController::class, 'exportExcel'])->name('dashboard.formative-actions.exportExcel');
    Route::post('/formative-actions/{id}/add-user', [FormativeActionController::class, 'addUser'])->name('dashboard.formative-actions.add-user');
    Route::post('/formative-actions/{id}/remove-user', [FormativeActionController::class, 'removeUser'])->name('dashboard.formative-actions.remove-user');
    Route::post('/formative-actions/{id}/add-module', [FormativeActionController::class, 'addModule'])->name('dashboard.formative-actions.add-module');
    Route::post('/formative-actions/{id}/remove-module', [FormativeActionController::class, 'removeModule'])->name('dashboard.formative-actions.remove-module');

    // Modules
    Route::get('/modules', [ModuleController::class, 'index'])->name('dashboard.modules');
    Route::post('/modules/load-items', [ModuleController::class, 'loadItems'])->name('dashboard.modules.load-items');
    Route::post('/modules', [ModuleController::class, 'store'])->name('dashboard.modules.store');
    Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('dashboard.modules.update');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('dashboard.modules.destroy');
    Route::delete('/modules/{module}/permanent', [ModuleController::class, 'destroyPermanent'])->name('dashboard.modules.destroyPermanent');
    Route::post('/modules/{id}/restore', [ModuleController::class, 'restore'])->name('dashboard.modules.restore');
    Route::get('/modules/export-excel', [ModuleController::class, 'exportExcel'])->name('dashboard.modules.exportExcel');

    Route::post('/modules/load-autocomplete-items', [ModuleController::class, 'loadAutocompleteItems'])->name('dashboard.modules.load-autocomplete-items');

    // Sector
    Route::post('/sectors/load-items', [SectorController::class, 'loadItems'])->name('dashboard.sectors.load-items');
    Route::post('/sectors', [SectorController::class, 'store'])->name('dashboard.sectors.store');
    Route::put('/sectors/{sector}', [SectorController::class, 'update'])->name('dashboard.sectors.update');
    Route::delete('/sectors/{sector}', [SectorController::class, 'destroy'])->name('dashboard.sectors.destroy');
    Route::delete('/sectors/{sector}/permanent', [SectorController::class, 'destroyPermanent'])->name('dashboard.sectors.destroyPermanent');
    Route::post('/sectors/{id}/restore', [SectorController::class, 'restore'])->name('dashboard.sectors.restore');
    Route::get('/sectors/export-excel', [SectorController::class, 'exportExcel'])->name('dashboard.sectors.exportExcel');

    // Teacher
    Route::get('/teachers', [TeacherController::class, 'index'])->name('dashboard.teachers');
    Route::post('/teachers/load-items', [TeacherController::class, 'loadItems'])->name('dashboard.teachers.load-items');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('dashboard.teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('dashboard.teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('dashboard.teachers.destroy');
    Route::delete('/teachers/{teacher}/permanent', [TeacherController::class, 'destroyPermanent'])->name('dashboard.teachers.destroyPermanent');
    Route::post('/teachers/{id}/restore', [TeacherController::class, 'restore'])->name('dashboard.teachers.restore');
    Route::get('/teachers/export-excel', [TeacherController::class, 'exportExcel'])->name('dashboard.teachers.exportExcel');

    // Entities
    Route::post('/entities/load-items', [EntityController::class, 'loadItems'])->name('dashboard.entities.load-items');
    Route::post('/entities', [EntityController::class, 'store'])->name('dashboard.entities.store');
    Route::put('/entities/{entity}', [EntityController::class, 'update'])->name('dashboard.entities.update');
    Route::delete('/entities/{entity}', [EntityController::class, 'destroy'])->name('dashboard.entities.destroy');
    Route::delete('/entities/{entity}/permanent', [EntityController::class, 'destroyPermanent'])->name('dashboard.entities.destroyPermanent');
    Route::post('/entities/{id}/restore', [EntityController::class, 'restore'])->name('dashboard.entities.restore');
    Route::get('/entities/export-excel', [EntityController::class, 'exportExcel'])->name('dashboard.entities.exportExcel');

    // Centers
    Route::post('/centers/load-items', [CenterController::class, 'loadItems'])->name('dashboard.centers.load-items');
    Route::post('/centers', [CenterController::class, 'store'])->name('dashboard.centers.store');
    Route::put('/centers/{center}', [CenterController::class, 'update'])->name('dashboard.centers.update');
    Route::delete('/centers/{center}', [CenterController::class, 'destroy'])->name('dashboard.centers.destroy');
    Route::delete('/centers/{center}/permanent', [CenterController::class, 'destroyPermanent'])->name('dashboard.centers.destroyPermanent');
    Route::post('/centers/{id}/restore', [CenterController::class, 'restore'])->name('dashboard.centers.restore');
    Route::get('/centers/export-excel', [CenterController::class, 'exportExcel'])->name('dashboard.centers.exportExcel');
    Route::post('/centers/{id}/add-user', [CenterController::class, 'addUser'])->name('dashboard.centers.add-user');
    Route::post('/centers/{id}/remove-user', [CenterController::class, 'removeUser'])->name('dashboard.centers.remove-user');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('dashboard.students');
    Route::post('/students/add-diploma', [StudentController::class, 'addDiploma'])->name('dashboard.students.add-diploma');
    Route::get('/students/download-diploma/{students_id}/{formative_actions_id}', [StudentController::class, 'downloadDiploma'])->name('dashboard.students.download-diploma');
    Route::post('/students/delete-diploma/{students_id}/{formative_actions_id}', [StudentController::class, 'deleteDiploma'])->name('dashboard.students.delete-diploma');
    Route::post('/students/load-items', [StudentController::class, 'loadItems'])->name('dashboard.students.load-items');
    Route::post('/students/{id}/add-formative-action', [StudentController::class, 'addFormativeAction'])->name('dashboard.students.add-formative-action');
    Route::post('/students/{id}/remove-formative-action', [StudentController::class, 'removeFormativeAction'])->name('dashboard.students.remove-formative-action');
    Route::post('/students/{id}/change-module-status', [StudentController::class, 'changeModuleStatus'])->name('dashboard.students.change-module-status');
    Route::post('/students/{id}/change-formative-action-status', [StudentController::class, 'changeFormativeActionStatus'])->name('dashboard.students.change-formative-action-status');
    Route::post('/students', [StudentController::class, 'store'])->name('dashboard.students.store');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('dashboard.students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('dashboard.students.destroy');
    Route::delete('/students/{student}/permanent', [StudentController::class, 'destroyPermanent'])->name('dashboard.students.destroyPermanent');
    Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('dashboard.students.restore');
    Route::get('/students/export-excel', [StudentController::class, 'exportExcel'])->name('dashboard.students.exportExcel');
    Route::post('/students/load-autocomplete-items', [StudentController::class, 'loadAutocompleteItems'])->name('dashboard.students.load-autocomplete-items');
    Route::get('/students/{student}/export-pdf', [StudentController::class, 'exportToPdf'])->name('dashboard.students.export-pdf');

    // Documents
    Route::post('/documents', [DocumentController::class, 'store'])->name('dashboard.documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('dashboard.documents.update');
    Route::delete('/documents/{document}/permanent', [DocumentController::class, 'destroy'])->name('dashboard.documents.destroyPermanent');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('dashboard.documents.download');

    // Notes
    Route::post('/notes', [NoteController::class, 'store'])->name('dashboard.notes.store');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('dashboard.notes.update');
    Route::delete('/notes/{note}/permanent', [NoteController::class, 'destroy'])->name('dashboard.notes.destroyPermanent');

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->name('dashboard.companies');
    Route::post('/companies/load-items', [CompanyController::class, 'loadItems'])->name('dashboard.companies.load-items');
    Route::post('/companies', [CompanyController::class, 'store'])->name('dashboard.companies.store');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('dashboard.companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('dashboard.companies.destroy');
    Route::delete('/companies/{company}/permanent', [CompanyController::class, 'destroyPermanent'])->name('dashboard.companies.destroyPermanent');
    Route::post('/companies/{id}/restore', [CompanyController::class, 'restore'])->name('dashboard.companies.restore');
    Route::get('/companies/export-excel', [CompanyController::class, 'exportExcel'])->name('dashboard.companies.exportExcel');

    // Company Documents
    Route::post('/companyDocuments', [CompanyDocumentController::class, 'store'])->name('dashboard.companyDocuments.store');
    Route::put('/companyDocuments/{companyDocument}', [CompanyDocumentController::class, 'update'])->name('dashboard.companyDocuments.update');
    Route::delete('/companyDocuments/{companyDocument}/permanent', [CompanyDocumentController::class, 'destroy'])->name('dashboard.companyDocuments.destroyPermanent');
    Route::get('/companyDocuments/{companyDocument}/download', [CompanyDocumentController::class, 'download'])->name('dashboard.companyDocuments.download');

    // Company Contacts
    Route::post('/companyContacts', [CompanyContactController::class, 'store'])->name('dashboard.companyContacts.store');
    Route::put('/companyContacts/{companyContact}', [CompanyContactController::class, 'update'])->name('dashboard.companyContacts.update');
    Route::delete('/companyContacts/{companyContact}/permanent', [CompanyContactController::class, 'destroy'])->name('dashboard.companyContacts.destroyPermanent');

    // Internships
    Route::post('/internships', [InternshipController::class, 'store'])->name('dashboard.internships.store');
    Route::put('/internships/{internship}', [InternshipController::class, 'update'])->name('dashboard.internships.update');
    Route::delete('/internships/{internship}/permanent', [InternshipController::class, 'destroyPermanent'])->name('dashboard.internships.destroyPermanent');
    Route::get('/internships/export-excel', [InternshipController::class, 'exportExcel'])->name('dashboard.internships.exportExcel');

    // Internship Documents
    Route::post('/internshipDocuments', [InternshipDocumentController::class, 'store'])->name('dashboard.internshipDocuments.store');
    Route::put('/internshipDocuments/{internshipDocument}', [InternshipDocumentController::class, 'update'])->name('dashboard.internshipDocuments.update');
    Route::delete('/internshipDocuments/{internshipDocument}/permanent', [InternshipDocumentController::class, 'destroy'])->name('dashboard.internshipDocuments.destroyPermanent');
    Route::get('/internshipDocuments/{internshipDocument}/download', [InternshipDocumentController::class, 'download'])->name('dashboard.internshipDocuments.download');


    Route::middleware('admin')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('dashboard.users');
        Route::post('/users/load-items', [UserController::class, 'loadItems'])->name('dashboard.users.load-items');
        Route::post('/users/{id}/add-location-web', [UserController::class, 'addLocationWeb'])->name('dashboard.users.add-location-web');
        Route::post('/users/{id}/remove-location-web', [UserController::class, 'removeLocationWeb'])->name('dashboard.users.remove-location-web');
        Route::post('/users', [UserController::class, 'store'])->name('dashboard.users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('dashboard.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('dashboard.users.destroy');
        Route::delete('/users/{id}/permanent', [UserController::class, 'destroyPermanent'])->name('dashboard.users.destroyPermanent');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('dashboard.users.restore');
    });
});

require __DIR__ . '/auth.php';
