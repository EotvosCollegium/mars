<?php

namespace App\Http\Controllers\Secretariat;

use App\Models\Role;
use App\Models\User;
use App\Models\Semester;
use App\Models\ImportItem;
use App\Models\PrinterConfiguration;
use App\Console\Commands;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Dormitory\Printing\PrintJobController;
use App\Utils\LatexHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('document.any');

        // add semesters for the certificate feature
        $data = $this->printingBladeData();
        $data['semesters'] = Semester::withWhereHas('communityServices', function ($query) use ($request) {
            $query->where('approver_id', $request->user()->id)
                ->orWhere('requester_id', $request->user()->id);
        })->get();
        $data['showApprove'] = true;

        return view(
            'secretariat.document.index',
            $data
        );
    }

    public function downloadRegisterStatement()
    {
        Gate::authorize('document.register-statement');

        $result = $this->generateRegisterStatement();
        return $this->downloadDocument($result);
    }

    public function printRegisterStatement()
    {
        Gate::authorize('document.register-statement');
        $this->authorize('use', $this->printingConfiguration());

        $result = $this->generateRegisterStatement();
        return $this->printDocument($result);
    }

    /** Departure statement */

    public function downloadDepartureStatement()
    {
        Gate::authorize('document.departure-statement');

        $result = $this->generateDepartureStatement();
        return $this->downloadDocument($result);
    }

    public function printDepartureStatement()
    {
        Gate::authorize('document.departure-statement');
        $this->authorize('use', $this->printingConfiguration());

        $result = $this->generateDepartureStatement();
        return $this->printDocument($result);
    }

    public function downloadImport()
    {
        Gate::authorize('document.import-license');

        $result = $this->generateImport();
        return $this->downloadDocument($result);
    }

    public function printImport()
    {
        Gate::authorize('document.import-license');
        $this->authorize('use', $this->printingConfiguration());

        $result = $this->generateImport();
        return $this->printDocument($result);
    }

    public function showImport()
    {
        Gate::authorize('document.import-license');

        return view(
            'secretariat.document.import',
            array_merge(
                [
                    'items' => user()->importItems,
                ],
                $this->printingBladeData()
            )
        );
    }

    public function addImport(Request $request)
    {
        Gate::authorize('document.import-license');

        ImportItem::create([
            'user_id' => user()->id,
            'name' => $request->item,
            'serial_number' => $request->serial_number ?? null,
        ]);
        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function removeImport(Request $request)
    {
        Gate::authorize('document.import-license');

        ImportItem::findOrFail($request->id)->delete();
        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /** Status certificate */

    public function downloadStatusCertificate()
    {
        Gate::authorize('document.status-certificate');

        $result = $this->generateStatusCertificate(user());
        return $this->downloadDocument($result);
    }

    public function showStatusCertificate($id)
    {
        Gate::authorize('document.status-certificate.viewAny');

        $user = User::findOrFail($id);
        $result = $this->generateStatusCertificate($user);
        return $this->downloadDocument($result);
    }

    public function requestStatusCertificate()
    {
        Gate::authorize('document.status-certificate');

        $url = route('documents.status-cert.show', ['id' => user()->id]);
        $secretaries = User::withRole(Role::SECRETARY)->get();
        foreach ($secretaries as $recipient) {
            Mail::to($recipient)->queue(new \App\Mail\StateCertificateRequest($recipient->name, user()->name, $url));
        }

        return redirect()->back()->with('message', "Sikeres igénylés. Az igazolást hamarosan megtalálhatod a titkárságon.");
    }

    public function printStatusCertificate()
    {
        Gate::authorize('document.status-certificate');
        $this->authorize('use', $this->printingConfiguration());

        $result = $this->generateStatusCertificate(user());
        return $this->printDocument($result);
    }

    /** Private helper functions */


    private function downloadDocument($result)
    {
        if (!$result['success']) {
            return $result['redirect'];
        }
        $document = $result['pdf'];
        return response()->download($document);
    }

    /** Private helper functions */


    private function printDocument($result)
    {
        if (!$result['success']) {
            return $result['redirect'];
        }
        $document = $result['pdf'];
        return PrintJobController::printDocument(
            $this->printingConfiguration(),
            1,
            false,
            $document,
            "Generated by system"
        );
    }

    private function printingConfiguration()
    {
        return PrinterConfiguration::find(config('document.printer_configuration_id'));
    }

    private function printingAvailable(): bool
    {
        return $this->printingConfiguration() != null
        && user()->can("use", $this->printingConfiguration());
    }

    private function printingBladeData()
    {
        if ($this->printingAvailable()) {
            return [
                'printing_conf_name_hun' => $this->printingConfiguration()->description_hun,
                'printing_conf_name_eng' => $this->printingConfiguration()->description_eng,
                'current_balance' => user()->printAccount->balance,
                'printing_available' => $this->printingAvailable(),
            ];
        } else {
            return [
                'printing_available' => false,
            ];
        }
    }

    private function generateStatement($template_name)
    {
        $user = user();

        if (!$user->hasPersonalInformation()) {
            return [
                'success' => false,
                'redirect' => back()->withInput()->with('error', __('document.missing_personal_info')),
            ];
        }
        $info = $user->personalInformation;

        $pdf = LatexHelper::generatePDF(
            $template_name,
            [ 'name' => $user->name,
                'address' => $info->getAddress(),
                'phone' => $info->phone_number,
                'email' => $user->email,
                'place_and_of_birth' => $info->getPlaceAndDateOfBirth(),
                'mothers_name' => $info->mothers_name,
                'date' => date("Y.m.d"),
            ]
        );
        return ['success' => true, 'pdf' => $pdf];
    }

    private function generateRegisterStatement()
    {
        return $this->generateStatement('latex.register-statement');
    }

    private function generateDepartureStatement()
    {
        return $this->generateStatement('latex.departure-statement');
    }

    private function generateImport()
    {
        $user = user();
        $items = $user->importItems;

        if ($items->isEmpty()) {
            return [
                'success' => false,
                'redirect' => back()->withInput()->with('error', "Még nem adtad meg a tárgyakat, amiket behoznál."),
            ];
        }

        $pdf = LatexHelper::generatePDF(
            'latex.import',
            [
                'name' => $user->name,
                'items' => $items,
                'date' => date("Y.m.d"),
            ]
        );
        return ['success' => true, 'pdf' => $pdf];
    }

    private function generateStatusCertificate($user)
    {
        if (!$user->hasPersonalInformation()) {
            return [
                'success' => false,
                'redirect' => back()->withInput()->with('error', "A személyes adataid hiányoznak a dokumentum kitöltéséhez. Kérj segítséget egy rendszergazdától."),
            ];
        }

        if (!$user->hasEducationalInformation()) {
            return [
                'success' => false,
                'redirect' => back()->withInput()->with('error', "A tanulmányi adataid hiányoznak a dokumentum kitöltéséhez. Kérj segítséget egy rendszergazdától"),
            ];
        }
        $personalInfo = $user->personalInformation;
        $educationalInfo = $user->educationalInformation;

        $pdf = LatexHelper::generatePDF(
            'latex.status-cert',
            [ 'name' => $user->name,
                'address' => $user->zip_code . ' ' . $personalInfo->getAddress(),
                'place_and_date_of_birth' => $personalInfo->getPlaceAndDateOfBirth(),
                'mothers_name' => $personalInfo->mothers_name,
                'neptun' => $educationalInfo->neptun,
                'from' => $educationalInfo->year_of_acceptance,
                'until' => Semester::current()->getEndDate()->format('Y.m.d.'), // TODO: check active semesters
                // TODO: add status
            ]
        );

        return ['success' => true, 'pdf' => $pdf];
    }
}
