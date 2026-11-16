<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Support\CsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VolunteerController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.volunteers.index', [
            'volunteers' => $this->filtered($request)->latest()->paginate(25)->withQueryString(),
            'filters' => $request->only(['q', 'barrio', 'apoyo']),
            'total' => Volunteer::count(),
            'thisWeek' => Volunteer::where('created_at', '>=', now()->startOfWeek())->count(),
        ]);
    }

    public function show(Volunteer $volunteer): View
    {
        return view('admin.volunteers.show', compact('volunteer'));
    }

    public function export(Request $request): StreamedResponse
    {
        return CsvExport::download($this->filtered($request), [
            'Fecha de registro' => 'created_at',
            'Tipo de documento' => 'document_type',
            'Número de documento' => 'document_number',
            'Nombres' => 'first_names',
            'Apellidos' => 'last_names',
            'Nombre preferido' => 'preferred_name',
            'Trato' => fn ($v) => $v->treatment_label,
            'Correo' => 'email',
            'Celular' => 'phone',
            'Barrio o vereda' => 'neighborhood',
            'Tipo de apoyo' => 'help_type',
            'Origen' => 'source',
        ], 'voluntarios-'.now()->format('Y-m-d').'.csv');
    }

    public function destroy(Volunteer $volunteer): RedirectResponse
    {
        $name = $volunteer->full_name;
        $volunteer->delete();

        return redirect()->route('admin.volunteers.index')->with('status', "Se eliminaron los datos de {$name}.");
    }

    private function filtered(Request $request): Builder
    {
        return Volunteer::query()
            ->search($request->query('q'))
            ->when($request->query('barrio'), fn ($q, $v) => $q->where('neighborhood', $v))
            ->when($request->query('apoyo'), fn ($q, $v) => $q->where('help_type', $v));
    }
}
