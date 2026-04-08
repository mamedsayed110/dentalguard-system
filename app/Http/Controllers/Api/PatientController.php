<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // قائمة المرضى الخاصة بالدكتور فقط
    public function index()
    {
        return response()->json(
            Patient::where('doctor_id', auth()->id())
                ->latest()
                ->paginate(50)
        );
    }

    // إنشاء مريض
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'age'        => 'nullable|integer|min:0|max:120',
            'identifier' => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:2000'
        ]);

        $data['doctor_id'] = auth()->id();

        $patient = Patient::create($data);

        $this->log('CREATE_PATIENT', ['patient_id' => $patient->id]);

        return response()->json($patient, 201);
    }

    // عرض مريض
    public function show($id)
    {
        $patient = Patient::where('doctor_id', auth()->id())
            ->findOrFail($id);

        return response()->json($patient);
    }

    // تحديث مريض
    public function update(Request $request, $id)
    {
        $patient = Patient::where('doctor_id', auth()->id())
            ->findOrFail($id);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'age'        => 'nullable|integer|min:0|max:120',
            'identifier' => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:2000'
        ]);

        $patient->update($data);

        $this->log('UPDATE_PATIENT', ['patient_id' => $patient->id]);

        return response()->json($patient);
    }

    // حذف مريض
    public function destroy($id)
    {
        $patient = Patient::where('doctor_id', auth()->id())
            ->findOrFail($id);

        $this->log('DELETE_PATIENT', [
            'patient_id' => $patient->id,
            'name' => $patient->name
        ]);

        $patient->delete();

        return response()->json(['deleted' => true]);
    }

    // تسجيل الأحداث
    private function log($action, array $meta = [])
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'ip'      => request()->ip(),
            'meta'    => $meta
        ]);
    }
    /**
 * عرض الأطباء الخاصين بالمريض
 */
public function myDoctors()
{
    $patient = auth()->user();
    
    if (!$patient->isPatient()) {
        return response()->json(['error' => 'Not a patient'], 403);
    }

    // الطبيب الرئيسي
    $doctor = User::find($patient->doctor_id);

    if (!$doctor) {
        return response()->json(['doctors' => []]);
    }

    return response()->json([
        'doctors' => [[
            'id' => $doctor->id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phone' => $doctor->phone
        ]]
    ]);
}
}
