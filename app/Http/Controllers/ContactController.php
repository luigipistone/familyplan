<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contacts.index', [
            'contacts' => Contact::with('owner')->orderBy('surname')->orderBy('name')->get(),
            'letters' => collect(range('A', 'Z')),
        ]);
    }

    public function store(Request $request, RichTextSanitizer $sanitizer)
    {
        Contact::create($this->data($request, $sanitizer) + ['owner_id' => $request->user()->id]);

        return back()->with('ok', 'Contatto salvato.');
    }

    public function update(Request $request, Contact $contact, RichTextSanitizer $sanitizer)
    {
        $this->authorizeChange($request, $contact);
        $contact->update($this->data($request, $sanitizer));

        return back()->with('ok', 'Contatto aggiornato.');
    }

    public function destroy(Request $request, Contact $contact)
    {
        $this->authorizeChange($request, $contact);
        $contact->delete();

        return back()->with('ok', 'Contatto eliminato.');
    }

    private function data(Request $request, RichTextSanitizer $sanitizer): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'surname' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'company' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['notes'] = $sanitizer->clean($data['notes'] ?? null);

        return $data;
    }

    private function authorizeChange(Request $request, Contact $contact): void
    {
        abort_unless($request->user()->isAdmin() || $contact->owner_id === $request->user()->id, 403);
    }
}
