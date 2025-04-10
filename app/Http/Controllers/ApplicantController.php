<?php
namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index()
    {
        $applicants = Applicant::orderBy('last_name')->get();
        return view('applicants.index', compact('applicants'));
    }

    public function show(Applicant $applicant)
    {
        $applicant->load(['backgroundChecks' => function ($query) {
            $query->orderByDesc('created_at');
        }]);

        return view('applicants.show', [
            'applicant' => $applicant->toArray(),
        ]);
    }

    public function update(Applicant $applicant, Request $request)
    {
        $data = $request->validate([
            'status' => 'required|string',
        ]);
        // Prevent direct setting of "background check" via this method
        if ($data['status'] === 'background check') {
            return response()->json(['message' => 'Background check must be initiated via the proper workflow.'], 400);
        }
        // Update allowed statuses (e.g., applied -> stage 1, etc., or from background check review -> passed, etc.)
        $applicant->status = $data['status'];
        $applicant->save();
        return response()->json(['message' => 'Status updated', 'status' => $applicant->status]);
    }
}
