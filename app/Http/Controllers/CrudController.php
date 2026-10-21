<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\CalendarEvent;
use App\Models\LoyaltyCard;
use App\Models\Note;
use App\Models\PasswordEntry;
use App\Models\PushSubscription;
use App\Models\Reminder;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\BackupManager;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CrudController extends Controller
{
    private function notify(?int $userId, string $type, string $title, ?string $body = null): void
    {
        if ($userId) {
            AppNotification::create(['user_id' => $userId, 'type' => $type, 'title' => $title, 'body' => $body]);
        }
    }

    private function notifyEventCreated(CalendarEvent $event): void
    {
        collect([$event->created_by, $event->assigned_user_id])
            ->filter()
            ->unique()
            ->each(fn ($userId) => $this->notify((int) $userId, 'event_created', 'Nuovo evento', $event->title));
    }

    private function sanitizeRichText(?string $value): ?string
    {
        return app(RichTextSanitizer::class)->clean($value);
    }

    private function syncUserProfilePhoto(User $user, ?UploadedFile $file): void
    {
        if (! $file) {
            return;
        }

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $file->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();
    }

    private function syncLoyaltyCardPhoto(LoyaltyCard $card, ?UploadedFile $file): void
    {
        if (! $file) {
            return;
        }

        if ($card->photo_path) {
            Storage::disk('public')->delete($card->photo_path);
        }

        $path = $file->store('loyalty-cards', 'public');
        $card->forceFill(['photo_path' => $path])->save();
    }

    public function calendar()
    {
        $user = auth()->user();
        $currentMonth = request('mese')
            ? Carbon::createFromFormat('Y-m', request('mese'))->startOfMonth()
            : today()->startOfMonth();
        $calendarStart = $currentMonth->copy()->startOfWeek();
        $calendarEnd = $currentMonth->copy()->endOfMonth()->endOfWeek();
        $days = collect();

        for ($day = $calendarStart->copy(); $day <= $calendarEnd; $day->addDay()) {
            $days->push($day->copy());
        }

        $events = CalendarEvent::with(['creator', 'assignedUser', 'child'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('visibility', 'family')->orWhere('created_by', $user->id)->orWhere('assigned_user_id', $user->id)))
            ->where('starts_at', '<=', $calendarEnd)
            ->where(fn ($q) => $q->whereNull('ends_at')->where('starts_at', '>=', $calendarStart)->orWhere('ends_at', '>=', $calendarStart))
            ->orderBy('starts_at')
            ->get();
        $todayEvents = CalendarEvent::with(['creator', 'assignedUser', 'child'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('visibility', 'family')->orWhere('created_by', $user->id)->orWhere('assigned_user_id', $user->id)))
            ->where('starts_at', '<=', today()->endOfDay())
            ->where(fn ($q) => $q->whereNull('ends_at')->whereDate('starts_at', today())->orWhere('ends_at', '>=', today()->startOfDay()))
            ->orderBy('starts_at')
            ->get();

        return view('calendar.index', [
            'events' => $events,
            'todayEvents' => $todayEvents,
            'users' => User::orderBy('name')->get(),
            'children' => User::where('category', 'figlio')->orderBy('name')->get(),
            'month' => $currentMonth,
            'days' => $days,
            'previousMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'calendarDaysPayload' => $days->map(fn ($day) => [
                'date' => $day->format('Y-m-d'),
                'day' => $day->day,
                'isCurrentMonth' => $day->isSameMonth($currentMonth),
                'isToday' => $day->isToday(),
            ])->values(),
            'calendarEventsPayload' => $events->map(fn ($event) => $this->calendarEventPayload($event))->values(),
            'todayEventsPayload' => $todayEvents->map(fn ($event) => $this->calendarEventPayload($event))->values(),
        ]);
    }

    private function calendarEventPayload(CalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => optional($event->ends_at)->format('Y-m-d\TH:i'),
            'start_day' => $event->starts_at->format('Y-m-d'),
            'end_day' => ($event->ends_at ?? $event->starts_at)->format('Y-m-d'),
            'start_time' => $event->starts_at->format('H:i'),
            'creator_name' => $event->creator->name ?? '',
            'creator_style' => $this->calendarEventStyle($event->created_by),
            'assigned_user_id' => $event->assigned_user_id,
            'child_id' => $event->child_id,
            'visibility' => $event->visibility,
            'action' => route('calendar.update', $event),
            'duplicate_action' => route('calendar.duplicate', $event),
            'delete_action' => route('calendar.destroy', $event),
        ];
    }

    public function storeEvent(Request $request)
    {
        $data = $this->eventData($request);
        $event = CalendarEvent::create($data + ['created_by' => $request->user()->id]);
        $this->notifyEventCreated($event);

        return back()->with('ok', 'Evento creato.');
    }

    public function updateEvent(Request $request, CalendarEvent $event)
    {
        $this->authorizeEventChange($request, $event);
        $event->update($this->eventData($request));

        return back()->with('ok', 'Evento aggiornato.');
    }

    public function duplicateEvent(Request $request, CalendarEvent $event)
    {
        $this->authorizeEventChange($request, $event);

        $duplicate = CalendarEvent::create([
            ...$event->only(['title', 'description', 'starts_at', 'ends_at', 'assigned_user_id', 'child_id', 'visibility']),
            'created_by' => $request->user()->id,
        ]);

        $this->notifyEventCreated($duplicate);

        return back()->with('ok', 'Evento duplicato.');
    }

    public function destroyEvent(Request $request, CalendarEvent $event)
    {
        $this->authorizeEventChange($request, $event);
        $event->delete();

        return back()->with('ok', 'Evento eliminato.');
    }

    private function calendarEventStyle(?int $creatorId): array
    {
        $palette = ['#2f6f9d', '#7a5fc7', '#bf6c2f', '#2f8a61', '#b24f6b', '#8b7a2b'];
        $accent = $palette[abs((int) $creatorId) % count($palette)];
        $rgb = sscanf(ltrim($accent, '#'), '%02x%02x%02x');
        [$red, $green, $blue] = $rgb ?: [47, 111, 157];

        $rgba = fn (float $alpha): string => "rgba({$red}, {$green}, {$blue}, {$alpha})";

        return [
            'accent' => $accent,
            'background' => $rgba(0.14),
            'background_hover' => $rgba(0.22),
            'background_span' => $rgba(0.2),
            'border' => $rgba(0.34),
            'border_span' => $rgba(0.5),
            'text' => '#18303c',
            'muted' => '#56707d',
        ];
    }

    private function eventData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'child_id' => ['nullable', 'exists:users,id'],
            'visibility' => ['required', 'in:private,family'],
        ]);
    }

    private function authorizeEventChange(Request $request, CalendarEvent $event): void
    {
        abort_unless($request->user()->isAdmin() || $event->created_by === $request->user()->id, 403);
    }

    public function shopping()
    {
        return $this->shoppingView(false);
    }

    public function shoppingArchive()
    {
        return $this->shoppingView(true);
    }

    private function shoppingView(bool $archived)
    {
        $user = auth()->user();
        $lists = ShoppingList::with(['items', 'owner'])
            ->when(
                $archived,
                fn ($q) => $q->whereNotNull('archived_at')->where('archived_at', '<=', now()),
                fn ($q) => $q->where(fn ($inner) => $inner->whereNull('archived_at')->orWhere('archived_at', '>', now()))
            )
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id))))
            ->latest()
            ->get();

        return view('shopping.index', [
            'lists' => $lists,
            'users' => User::orderBy('name')->get(),
            'archived' => $archived,
            'groceryCategories' => $this->groceryCategories(),
        ]);
    }

    private function groceryCategories(): array
    {
        return [
            'Frutta e verdura' => ['Mele', 'Banane', 'Pere', 'Arance', 'Mandarini', 'Limoni', 'Fragole', 'Kiwi', 'Uva', 'Mirtilli', 'Ananas', 'Avocado', 'Insalata', 'Rucola', 'Pomodori', 'Carote', 'Patate', 'Zucchine', 'Cipolle', 'Melanzane', 'Peperoni', 'Broccoli', 'Cavolfiore', 'Spinaci', 'Cetrioli', 'Funghi', 'Sedano', 'Finocchi', 'Aglio', 'Prezzemolo', 'Basilico'],
            'Pane e dispensa' => ['Pane', 'Pancarre', 'Piadine', 'Pasta', 'Riso', 'Farro', 'Cous cous', 'Farina', 'Lievito', 'Zucchero', 'Sale', 'Olio', 'Aceto', 'Caffe', 'Te', 'Camomilla', 'Biscotti', 'Cracker', 'Grissini', 'Fette biscottate', 'Cereali', 'Legumi', 'Passata', 'Pelati', 'Sughi pronti', 'Tonno in scatola', 'Mais', 'Olive', 'Sottaceti', 'Marmellata', 'Miele', 'Cioccolato'],
            'Frigo' => ['Latte', 'Yogurt', 'Burro', 'Uova', 'Mozzarella', 'Parmigiano', 'Grana', 'Ricotta', 'Stracchino', 'Robiola', 'Formaggio spalmabile', 'Feta', 'Prosciutto cotto', 'Prosciutto crudo', 'Salame', 'Bresaola', 'Mortadella', 'Wurstel', 'Pasta sfoglia', 'Pasta brisee', 'Pesto', 'Gnocchi', 'Tortellini', 'Sottilette'],
            'Carne e pesce' => ['Pollo', 'Tacchino', 'Manzo', 'Vitello', 'Maiale', 'Hamburger', 'Salsiccia', 'Macinato', 'Speck', 'Pancetta', 'Salmone', 'Tonno fresco', 'Merluzzo', 'Orata', 'Branzino', 'Gamberi', 'Calamari', 'Vongole', 'Affettati', 'Bastoncini di pesce'],
            'Surgelati' => ['Piselli', 'Spinaci surgelati', 'Verdure grigliate', 'Minestrone', 'Patatine', 'Pizza surgelata', 'Gelato', 'Sofficini', 'Crocchette', 'Filetti di pesce', 'Frutti di bosco'],
            'Bevande' => ['Acqua naturale', 'Acqua frizzante', 'Succhi', 'Bibite', 'Birra', 'Vino', 'Latte vegetale', 'The freddo', 'Integratori', 'Sciroppi'],
            'Casa e igiene' => ['Detersivo lavatrice', 'Ammorbidente', 'Detersivo piatti', 'Pastiglie lavastoviglie', 'Sgrassatore', 'Candeggina', 'Carta igienica', 'Scottex', 'Tovaglioli', 'Fazzoletti', 'Sapone mani', 'Shampoo', 'Balsamo', 'Bagnoschiuma', 'Dentifricio', 'Spazzolini', 'Deodorante', 'Rasoio', 'Assorbenti', 'Sacchetti spazzatura', 'Pellicola', 'Alluminio', 'Carta forno', 'Spugne'],
            'Bambini' => ['Pannolini', 'Salviette', 'Omogeneizzati', 'Merenda', 'Succhi', 'Pastine', 'Latte crescita', 'Biscotti bambini', 'Crema cambio', 'Bavaglini', 'Acqua bambini', 'Bagnetto bambini', 'Cotton fioc', 'Cerotti', 'Fisiologica'],
            'Animali' => ['Crocchette', 'Umido', 'Snack animali', 'Lettiera', 'Sacchetti igienici'],
        ];
    }

    public function storeList(Request $request)
    {
        $data = $this->shoppingListData($request);
        $items = $request->validate([
            'selected_items' => ['nullable', 'array'],
            'selected_items.*' => ['string', 'max:120'],
        ])['selected_items'] ?? [];

        $list = ShoppingList::create($data + ['owner_id' => $request->user()->id]);
        $this->syncShares($list, $data['shared_with'] ?? []);

        foreach (array_unique(array_filter($items)) as $item) {
            $list->items()->create(['name' => $item]);
        }

        return back()->with('ok', 'Lista creata.');
    }

    public function updateList(Request $request, ShoppingList $list)
    {
        $this->authorizeShoppingListChange($request, $list);
        $data = $this->shoppingListData($request);
        $list->update($data);
        $this->syncShares($list, $data['shared_with'] ?? []);

        if ($request->has('products')) {
            $products = $request->validate([
                'products' => ['nullable', 'array'],
                'products.*.name' => ['required', 'string', 'max:120'],
                'products.*.quantity' => ['nullable', 'string', 'max:40'],
                'products.*.checked' => ['nullable', 'boolean'],
            ])['products'] ?? [];

            $list->items()->delete();
            foreach ($products as $product) {
                $list->items()->create([
                    'name' => $product['name'],
                    'quantity' => $product['quantity'] ?? null,
                    'is_checked' => ! empty($product['checked']),
                    'checked_at' => ! empty($product['checked']) ? now() : null,
                ]);
            }
        }

        return back()->with('ok', 'Lista aggiornata.');
    }

    public function destroyList(Request $request, ShoppingList $list)
    {
        $this->authorizeShoppingListChange($request, $list);
        $list->delete();

        return back()->with('ok', 'Lista eliminata.');
    }

    public function duplicateList(Request $request, ShoppingList $list)
    {
        abort_unless($this->canAccessShoppingList($request->user(), $list), 403);

        $duplicate = ShoppingList::create([
            'owner_id' => $request->user()->id,
            'name' => trim($list->name.' copia'),
            'list_date' => $list->list_date,
            'shared_with' => $list->owner_id === $request->user()->id || $request->user()->isAdmin()
                ? ($list->shared_with ?? [])
                : [],
            'archived_at' => null,
        ]);
        $this->syncShares($duplicate, $duplicate->shared_with ?? []);

        foreach ($list->items as $item) {
            $duplicate->items()->create([
                'name' => $item->name,
                'quantity' => $item->quantity,
                'is_checked' => false,
                'checked_at' => null,
            ]);
        }

        return back()->with('ok', 'Lista duplicata.');
    }

    public function destroyItem(Request $request, ShoppingItem $item)
    {
        $this->authorizeShoppingListChange($request, $item->shoppingList);
        $item->delete();

        return back()->with('ok', 'Prodotto rimosso.');
    }

    private function shoppingListData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'list_date' => ['required', 'date'],
            'shared_with' => ['nullable', 'array'],
            'shared_with.*' => ['integer', 'exists:users,id'],
        ]);
        $data['shared_with'] = array_map('intval', array_values($data['shared_with'] ?? []));

        return $data;
    }

    private function authorizeShoppingListChange(Request $request, ShoppingList $list): void
    {
        abort_unless($request->user()->isAdmin() || $list->owner_id === $request->user()->id, 403);
    }

    private function canAccessShoppingList(User $user, ShoppingList $list): bool
    {
        if ($user->isAdmin() || $list->owner_id === $user->id) {
            return true;
        }

        return $list->sharedUsers()->whereKey($user->id)->exists();
    }

    public function storeItem(Request $request, ShoppingList $list)
    {
        abort_unless($request->user()->isAdmin() || $list->owner_id === $request->user()->id, 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'quantity' => ['nullable', 'string', 'max:40']]);
        $list->items()->create($data);

        return back();
    }

    public function toggleItem(Request $request, ShoppingItem $item)
    {
        abort_unless($this->canAccessShoppingList($request->user(), $item->shoppingList), 403);
        $item->update(['is_checked' => ! $item->is_checked, 'checked_at' => $item->is_checked ? null : now()]);
        $list = $item->shoppingList?->loadMissing('owner');
        if ($list && $list->items()->where('is_checked', false)->doesntExist()) {
            $archiveDays = $list->owner?->shopping_archive_days ?? auth()->user()?->shopping_archive_days ?? 7;
            $list->update(['archived_at' => now()->addDays($archiveDays)]);
        } elseif ($list) {
            $list->update(['archived_at' => null]);
        }

        return back();
    }

    public function archiveList(ShoppingList $list)
    {
        abort_unless(auth()->user()->isAdmin() || $list->owner_id === auth()->id(), 403);
        $list->update(['archived_at' => now()]);

        return back()->with('ok', 'Lista archiviata.');
    }

    public function unarchiveList(ShoppingList $list)
    {
        abort_unless(auth()->user()->isAdmin() || $list->owner_id === auth()->id(), 403);
        $list->update(['archived_at' => null]);

        return back()->with('ok', 'Lista riattivata.');
    }

    public function family()
    {
        $user = auth()->user();

        $events = CalendarEvent::with(['assignedUser', 'child'])
            ->whereBetween('starts_at', [today()->startOfDay(), today()->copy()->addDay()->endOfDay()])
            ->whereNotNull('child_id')
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('assigned_user_id', $user->id);

                    if ($user->isParent()) {
                        $inner->orWhereHas('child', fn ($child) => $child
                            ->where('mother_id', $user->id)
                            ->orWhere('father_id', $user->id));
                    }
                });
            })
            ->orderBy('starts_at')
            ->get();

        return view('family.index', ['events' => $events, 'children' => User::where('category', 'figlio')->orderBy('name')->get(), 'users' => User::where('category', '!=', 'figlio')->orderBy('name')->get()]);
    }

    public function children()
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->isParent(), 403);

        $children = User::where('category', 'figlio')
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('mother_id', $user->id)->orWhere('father_id', $user->id)))
            ->orderBy('birth_date')
            ->get();

        return view('family.children', ['children' => $children]);
    }

    public function reminders()
    {
        return $this->remindersView(false);
    }

    public function remindersArchive()
    {
        return $this->remindersView(true);
    }

    private function remindersView(bool $archived)
    {
        $user = auth()->user();

        return view('reminders.index', [
            'reminders' => Reminder::when($archived, fn ($q) => $q->whereNotNull('completed_at'), fn ($q) => $q->whereNull('completed_at'))
                ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id))))
                ->orderBy('due_at')
                ->get(),
            'users' => User::orderBy('name')->get(),
            'archived' => $archived,
        ]);
    }

    public function reminderNotifications(Request $request)
    {
        $user = $request->user();

        $reminders = Reminder::whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->where('due_at', '>=', now()->subDay())
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id))))
            ->orderBy('due_at')
            ->get()
            ->map(fn ($reminder) => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'body' => $reminder->body ? trim(strip_tags($reminder->body)) : null,
                'due_at' => $reminder->due_at?->toIso8601String(),
                'url' => route('reminders', ['open' => 'reminder-'.$reminder->id]),
            ])
            ->values();

        return response()->json(['reminders' => $reminders]);
    }

    public function storeReminder(Request $request)
    {
        $data = $this->reminderData($request);
        $reminder = Reminder::create($data + ['owner_id' => $request->user()->id]);
        $this->syncShares($reminder, $data['shared_with'] ?? []);

        return back()->with('ok', 'Promemoria salvato.');
    }

    public function updateReminder(Request $request, Reminder $reminder)
    {
        $this->authorizeOwnedOrShared($request, $reminder->owner_id);
        $data = $this->reminderData($request);
        $reminder->update($data + ['push_sent_at' => null]);
        $this->syncShares($reminder, $data['shared_with'] ?? []);

        return back()->with('ok', 'Promemoria aggiornato.');
    }

    public function completeReminder(Request $request, Reminder $reminder)
    {
        $this->authorizeOwnedOrShared($request, $reminder->owner_id);
        $reminder->update(['completed_at' => now()]);

        return back();
    }

    public function unarchiveReminder(Request $request, Reminder $reminder)
    {
        $this->authorizeOwnedOrShared($request, $reminder->owner_id);
        $reminder->update(['completed_at' => null, 'push_sent_at' => null]);

        return back()->with('ok', 'Promemoria riattivato.');
    }

    public function destroyReminder(Request $request, Reminder $reminder)
    {
        $this->authorizeOwnedOrShared($request, $reminder->owner_id);
        $reminder->delete();

        return back()->with('ok', 'Promemoria eliminato.');
    }

    private function reminderData(Request $request): array
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:140'], 'body' => ['nullable', 'string'], 'due_at' => ['nullable', 'date'], 'recurrence' => ['required', 'in:none,daily,weekly,monthly'], 'shared_with' => ['nullable', 'array'], 'shared_with.*' => ['integer', 'exists:users,id']]);
        $data['shared_with'] = array_map('intval', array_values($data['shared_with'] ?? []));
        $data['body'] = $this->sanitizeRichText($data['body'] ?? null);

        return $data;
    }

    public function notes()
    {
        return $this->notesView(false);
    }

    public function notesArchive()
    {
        return $this->notesView(true);
    }

    private function notesView(bool $archived)
    {
        $user = auth()->user();

        return view('notes.index', [
            'notes' => Note::when($archived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
                ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($inner) => $inner->where('owner_id', $user->id)->orWhereHas('sharedUsers', fn ($shared) => $shared->whereKey($user->id))))
                ->latest()
                ->get(),
            'users' => User::orderBy('name')->get(),
            'archived' => $archived,
        ]);
    }

    public function storeNote(Request $request)
    {
        $data = $this->noteData($request);
        $note = Note::create($data + ['owner_id' => $request->user()->id]);
        $this->syncShares($note, $data['shared_with'] ?? []);

        return back()->with('ok', 'Nota salvata.');
    }

    public function updateNote(Request $request, Note $note)
    {
        $this->authorizeOwnedOrShared($request, $note->owner_id);
        $data = $this->noteData($request);
        $note->update($data);
        $this->syncShares($note, $data['shared_with'] ?? []);

        return back()->with('ok', 'Nota aggiornata.');
    }

    public function archiveNote(Request $request, Note $note)
    {
        $this->authorizeOwnedOrShared($request, $note->owner_id);
        $note->update(['archived_at' => now()]);

        return back();
    }

    public function unarchiveNote(Request $request, Note $note)
    {
        $this->authorizeOwnedOrShared($request, $note->owner_id);
        $note->update(['archived_at' => null]);

        return back()->with('ok', 'Nota riattivata.');
    }

    public function destroyNote(Request $request, Note $note)
    {
        $this->authorizeOwnedOrShared($request, $note->owner_id);
        $note->delete();

        return back()->with('ok', 'Nota eliminata.');
    }

    private function noteData(Request $request): array
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'body' => ['nullable', 'string'], 'shared_with' => ['nullable', 'array'], 'shared_with.*' => ['integer', 'exists:users,id']]);
        $data['shared_with'] = array_map('intval', array_values($data['shared_with'] ?? []));
        $data['body'] = $this->sanitizeRichText($data['body'] ?? null);

        return $data;
    }

    private function authorizeOwnedOrShared(Request $request, int $ownerId): void
    {
        abort_unless($request->user()->isAdmin() || $ownerId === $request->user()->id, 403);
    }

    private function syncShares(ShoppingList|Reminder|Note $model, array $userIds): void
    {
        $model->sharedUsers()->sync(collect($userIds)->map(fn ($id) => (int) $id)->unique()->all());
    }

    public function users()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('users.index', [
            'users' => User::with(['parent', 'mother', 'father'])->orderBy('name')->get(),
            'mothers' => User::where('category', 'mamma')->orderBy('name')->get(),
            'fathers' => User::where('category', 'papa')->orderBy('name')->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:users,name'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:12'],
            'role' => ['required', 'in:admin,familiare'],
            'category' => ['required', 'in:nonno,zia,papa,mamma,figlio,altro'],
            'parent_id' => ['nullable', 'exists:users,id'],
            'mother_id' => ['nullable', 'exists:users,id'],
            'father_id' => ['nullable', 'exists:users,id'],
            'birth_date' => ['nullable', 'date'],
            'personal_info' => ['nullable', 'string'],
            'profile_photo' => ['nullable', 'image', 'max:3072'],
        ]);

        $data['personal_info'] = $this->sanitizeRichText($data['personal_info'] ?? null);
        [$data, $hasAccess] = $this->normalizeUserData($data);

        if ($hasAccess && empty($data['password'])) {
            return back()->withErrors(['password' => 'La password è obbligatoria per gli utenti che accedono al sistema.']);
        }

        $data['password'] = ! empty($data['password']) ? Hash::make($data['password']) : null;
        $user = User::create($data);
        $this->syncUserProfilePhoto($user, $request->file('profile_photo'));

        return back()->with('ok', 'Utente creato.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:users,name,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone,'.$user->id],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:12'],
            'role' => ['required', 'in:admin,familiare'],
            'category' => ['required', 'in:nonno,zia,papa,mamma,figlio,altro'],
            'parent_id' => ['nullable', 'exists:users,id'],
            'mother_id' => ['nullable', 'exists:users,id'],
            'father_id' => ['nullable', 'exists:users,id'],
            'birth_date' => ['nullable', 'date'],
            'personal_info' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'profile_photo' => ['nullable', 'image', 'max:3072'],
        ]);

        $data['personal_info'] = $this->sanitizeRichText($data['personal_info'] ?? null);
        [$data, $hasAccess] = $this->normalizeUserData($data);

        if ($hasAccess && empty($user->password) && empty($data['password'])) {
            return back()->withErrors(['password' => 'La password è obbligatoria per gli utenti che accedono al sistema.']);
        }

        if (! $hasAccess) {
            $data['password'] = null;
        } elseif (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['personal_info'] = $this->sanitizeRichText($data['personal_info'] ?? null);
        $user->update($data);
        $this->syncUserProfilePhoto($user, $request->file('profile_photo'));

        return back()->with('ok', 'Utente aggiornato.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Non puoi eliminare il tuo account mentre sei collegato.']);
        }

        if ($user->isAdmin() && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Deve rimanere almeno un admin attivo.']);
        }

        $user->delete();

        return back()->with('ok', 'Utente eliminato.');
    }

    private function normalizeUserData(array $data): array
    {
        $age = ! empty($data['birth_date']) ? now()->diffInYears(Carbon::parse($data['birth_date'])) : null;

        if (($data['category'] ?? null) === 'figlio') {
            if ($age !== null && $age < 14 && (empty($data['mother_id']) || empty($data['father_id']))) {
                throw ValidationException::withMessages(['mother_id' => 'I figli sotto i 14 anni devono essere associati a una mamma e a un papà.']);
            }
            if (($age === null || $age < 14)) {
                $data['phone'] = null;
                $data['email'] = null;
                $data['password'] = null;
            } elseif (empty($data['phone'])) {
                throw ValidationException::withMessages(['phone' => 'I figli sopra i 14 anni devono avere il proprio numero di telefono.']);
            }
        } else {
            $data['mother_id'] = null;
            $data['father_id'] = null;
            if (empty($data['phone'])) {
                throw ValidationException::withMessages(['phone' => 'Il telefono è obbligatorio per gli utenti con accesso.']);
            }
        }

        $hasAccess = ($data['category'] ?? null) !== 'figlio' || ($age !== null && $age >= 14);

        return [$data, $hasAccess];
    }

    public function settings(Request $request, BackupManager $backupManager)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('settings.index', [
            'user' => $request->user(),
            'backupFrequency' => SystemSetting::getValue('backup_frequency', 'manual'),
            'backups' => $backupManager->listBackups(),
        ]);
    }

    public function profile(Request $request)
    {
        return view('profile.index', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:users,name,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone,'.$user->id],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'birth_date' => ['nullable', 'date'],
            'personal_info' => ['nullable', 'string'],
            'favorite_team' => ['nullable', 'string', 'max:120'],
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'max:3072'],
        ]);

        if (filled($data['password'] ?? null) && blank($data['current_password'] ?? null)) {
            throw ValidationException::withMessages(['current_password' => 'Inserisci la password attuale per cambiarla.']);
        }

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['current_password']);
        $data['personal_info'] = $this->sanitizeRichText($data['personal_info'] ?? null);
        $user->update($data);
        $this->syncUserProfilePhoto($user, $request->file('profile_photo'));

        return back()->with('ok', 'Profilo aggiornato.');
    }

    public function updateSettings(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'theme' => ['required', 'in:light,dark'],
            'email_notifications' => ['nullable', 'boolean'],
            'push_notifications' => ['nullable', 'boolean'],
            'calendar_reminder_minutes' => ['required', 'integer', 'in:0,10,30,60,1440'],
            'shopping_archive_days' => ['required', 'integer', 'min:1', 'max:30'],
            'week_starts_on' => ['required', 'in:monday,sunday'],
            'compact_dashboard' => ['nullable', 'boolean'],
            'backup_frequency' => ['required', 'in:manual,daily,weekly,monthly'],
        ]);
        $data['email_notifications'] = $request->boolean('email_notifications');
        $data['push_notifications'] = $request->boolean('push_notifications');
        $data['compact_dashboard'] = $request->boolean('compact_dashboard');
        $backupFrequency = $data['backup_frequency'];
        unset($data['backup_frequency']);
        $request->user()->update($data);
        SystemSetting::setValue('backup_frequency', $backupFrequency);

        if (! $data['push_notifications']) {
            $request->user()->pushSubscriptions()->delete();
        }

        return back()->with('ok', 'Impostazioni salvate.');
    }

    public function updateTheme(Request $request)
    {
        $data = $request->validate(['theme' => ['required', 'in:light,dark']]);
        $request->user()->update($data);

        return response()->json(['theme' => $data['theme']]);
    }

    public function createBackup(Request $request, BackupManager $backupManager)
    {
        abort_unless($request->user()->isAdmin(), 403);
        try {
            $backupManager->createBackup('manual');
        } catch (\Throwable $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        return back()->with('ok', 'Backup creato.');
    }

    public function restoreBackup(Request $request, BackupManager $backupManager)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'backup_filename' => ['required', 'string'],
        ]);

        try {
            $backupManager->createBackup('pre-restore');
            $backupManager->restoreBackup($data['backup_filename']);
        } catch (\Throwable $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        return back()->with('ok', 'Backup ripristinato.');
    }

    public function translator()
    {
        return view('translator.index', [
            'languages' => $this->translatorLanguages(),
        ]);
    }

    public function currency()
    {
        return view('currency.index', [
            'currencies' => $this->currencyList(),
        ]);
    }

    public function convertCurrency(Request $request)
    {
        $codes = implode(',', array_keys($this->currencyList()));
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'in:'.$codes],
            'target' => ['required', 'string', 'different:source', 'in:'.$codes],
        ]);

        $payload = Cache::remember(
            'currency.frankfurter.'.md5($data['amount'].'|'.$data['source'].'|'.$data['target']),
            now()->endOfDay(),
            function () use ($data) {
                $source = strtolower($data['source']);
                $target = strtolower($data['target']);
                $response = Http::timeout(10)->acceptJson()->get("https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/{$source}.json");

                if (! $response->ok()) {
                    throw ValidationException::withMessages(['amount' => 'Servizio cambio valuta temporaneamente non disponibile.']);
                }

                $rate = data_get($response->json(), "{$source}.{$target}");
                if (! is_numeric($rate)) {
                    throw ValidationException::withMessages(['amount' => 'Non sono riuscito a convertire questa valuta.']);
                }

                return [
                    'amount' => (float) $data['amount'],
                    'source' => $data['source'],
                    'target' => $data['target'],
                    'converted' => round((float) $data['amount'] * (float) $rate, 6),
                    'date' => data_get($response->json(), 'date'),
                    'provider' => 'Currency API',
                ];
            }
        );

        return response()->json($payload);
    }

    public function translate(Request $request)
    {
        $data = $request->validate([
            'source' => ['required', 'string', 'in:it,en,fr,de,es,pt,nl,ar,ja'],
            'target' => ['required', 'string', 'different:source', 'in:it,en,fr,de,es,pt,nl,ar,ja'],
            'text' => ['required', 'string', 'max:3000'],
        ]);

        $text = trim($data['text']);
        $source = $data['source'];
        $target = $data['target'];

        $payload = Cache::remember(
            'translator.mymemory.'.md5($source.'|'.$target.'|'.$text),
            now()->addHours(6),
            function () use ($source, $target, $text) {
                $response = Http::timeout(12)->acceptJson()->get('https://api.mymemory.translated.net/get', [
                    'q' => $text,
                    'langpair' => "{$source}|{$target}",
                    'mt' => 1,
                ]);

                if (! $response->ok()) {
                    throw ValidationException::withMessages(['text' => 'Servizio traduzioni temporaneamente non disponibile.']);
                }

                $body = $response->json();
                $translated = trim((string) data_get($body, 'responseData.translatedText', ''));

                if ($translated === '') {
                    throw ValidationException::withMessages(['text' => 'Non sono riuscito a tradurre questo testo.']);
                }

                return [
                    'translated_text' => $translated,
                    'detected_source' => data_get($body, 'responseData.match') !== null ? $source : $source,
                    'provider' => 'MyMemory',
                ];
            }
        );

        return response()->json($payload);
    }

    private function translatorLanguages(): array
    {
        return [
            'it' => 'Italiano',
            'en' => 'Inglese',
            'fr' => 'Francese',
            'de' => 'Tedesco',
            'es' => 'Spagnolo',
            'pt' => 'Portoghese',
            'nl' => 'Olandese',
            'ar' => 'Arabo',
            'ja' => 'Giapponese',
        ];
    }

    private function currencyList(): array
    {
        return [
            'AED' => 'Dirham emiratino',
            'AUD' => 'Dollaro australiano',
            'BRL' => 'Real brasiliano',
            'CAD' => 'Dollaro canadese',
            'CHF' => 'Franco svizzero',
            'CNY' => 'Renminbi / Yuan cinese',
            'CZK' => 'Corona ceca',
            'DKK' => 'Corona danese',
            'EUR' => 'Euro',
            'USD' => 'Dollaro USA',
            'GBP' => 'Sterlina britannica',
            'HKD' => 'Dollaro di Hong Kong',
            'HUF' => 'Fiorino ungherese',
            'IDR' => 'Rupia indonesiana',
            'ILS' => 'Nuovo shekel israeliano',
            'INR' => 'Rupia indiana',
            'ISK' => 'Corona islandese',
            'JPY' => 'Yen giapponese',
            'KRW' => 'Won sudcoreano',
            'MXN' => 'Peso messicano',
            'MYR' => 'Ringgit malese',
            'NOK' => 'Corona norvegese',
            'NZD' => 'Dollaro neozelandese',
            'PHP' => 'Peso filippino',
            'PLN' => 'Zloty polacco',
            'RON' => 'Leu romeno',
            'SEK' => 'Corona svedese',
            'SGD' => 'Dollaro di Singapore',
            'THB' => 'Baht thailandese',
            'TRY' => 'Lira turca',
            'ZAR' => 'Rand sudafricano',
        ];
    }

    public function loyaltyCards(Request $request)
    {
        $user = $request->user();
        $eligibleUsers = User::query()
            ->where(function ($query) {
                $query->where('category', '!=', 'figlio')
                    ->orWhereNull('birth_date')
                    ->orWhereDate('birth_date', '<=', now()->subYears(14)->toDateString());
            })
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'birth_date']);

        $cards = LoyaltyCard::with(['owner', 'associatedUser'])
            ->orderByRaw('CASE WHEN associated_user_id = ? THEN 0 ELSE 1 END', [$user->id])
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        return view('loyalty-cards.index', [
            'cards' => $cards,
            'myCards' => $cards->where('associated_user_id', $user->id)->values(),
            'otherCards' => $cards->where('associated_user_id', '!=', $user->id)->values(),
            'favoriteCards' => $cards->filter(fn ($card) => in_array((string) $user->id, $card->favorite_user_ids ?? [], true))->values(),
            'eligibleUsers' => $eligibleUsers,
        ]);
    }

    public function passwords(Request $request)
    {
        $user = $request->user();
        $entries = PasswordEntry::with('owner')
            ->where('owner_id', $user->id)
            ->orWhereJsonContains('shared_with', (string) $user->id)
            ->orderBy('title')
            ->get();

        return view('passwords.index', [
            'myEntries' => $entries->where('owner_id', $user->id)->values(),
            'sharedEntries' => $entries->where('owner_id', '!=', $user->id)->values(),
            'users' => User::where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storePassword(Request $request)
    {
        PasswordEntry::create($this->passwordData($request) + ['owner_id' => $request->user()->id]);

        return back()->with('ok', 'Password salvata.');
    }

    public function updatePasswordEntry(Request $request, PasswordEntry $entry)
    {
        abort_unless($entry->owner_id === $request->user()->id, 403);
        $entry->update($this->passwordData($request));

        return back()->with('ok', 'Password aggiornata.');
    }

    public function destroyPasswordEntry(Request $request, PasswordEntry $entry)
    {
        abort_unless($entry->owner_id === $request->user()->id, 403);
        $entry->delete();

        return back()->with('ok', 'Password eliminata.');
    }

    private function passwordData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:190'],
            'username' => ['nullable', 'string', 'max:190'],
            'password' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'shared_with' => ['nullable', 'array'],
            'shared_with.*' => ['integer', 'exists:users,id'],
        ]);

        $data['shared_with'] = collect($data['shared_with'] ?? [])
            ->map(fn ($id) => (string) $id)
            ->reject(fn ($id) => (int) $id === $request->user()->id)
            ->unique()
            ->values()
            ->all();

        return $data;
    }

    public function storeLoyaltyCard(Request $request)
    {
        $card = LoyaltyCard::create($this->loyaltyCardData($request) + ['owner_id' => $request->user()->id]);
        $this->syncLoyaltyCardPhoto($card, $request->file('card_photo'));

        return back()->with('ok', 'Carta fedelta salvata.');
    }

    public function updateLoyaltyCard(Request $request, LoyaltyCard $card)
    {
        abort_unless($card->owner_id === $request->user()->id, 403);
        $card->update($this->loyaltyCardData($request));
        $this->syncLoyaltyCardPhoto($card, $request->file('card_photo'));

        return back()->with('ok', 'Carta fedelta aggiornata.');
    }

    public function destroyLoyaltyCard(Request $request, LoyaltyCard $card)
    {
        abort_unless($card->owner_id === $request->user()->id, 403);
        $card->delete();

        return back()->with('ok', 'Carta fedelta eliminata.');
    }

    public function toggleLoyaltyCardFavorite(Request $request, LoyaltyCard $card)
    {
        $userId = (string) $request->user()->id;
        $favorites = collect($card->favorite_user_ids ?? [])->map(fn ($id) => (string) $id);
        $isFavorite = ! $favorites->contains($userId);
        $card->update([
            'favorite_user_ids' => $isFavorite
                ? $favorites->push($userId)->unique()->values()->all()
                : $favorites->reject(fn ($id) => $id === $userId)->values()->all(),
        ]);

        return response()->json(['ok' => true, 'is_favorite' => $isFavorite]);
    }

    private function loyaltyCardData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'brand' => ['required', 'string', 'max:120'],
            'barcode_value' => ['nullable', 'string', 'max:120'],
            'barcode_format' => ['nullable', 'in:auto,EAN,CODE128'],
            'associated_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'card_photo' => ['nullable', 'image', 'max:5120'],
            'theme_color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['notes'] = $this->sanitizeRichText($data['notes'] ?? null);
        $data['name'] = filled($data['brand'] ?? null) ? $data['brand'] : ($data['name'] ?? 'Carta fedelta');
        $data['theme_color'] = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($data['theme_color'] ?? '')) ? $data['theme_color'] : '#6f9f92';
        $data['barcode_format'] = $data['barcode_format'] ?? 'auto';
        $data['card_number'] = $data['barcode_value'] ?? null;
        $data['associated_user_id'] = $this->validateLoyaltyAssociatedUser($data['associated_user_id'] ?? null);

        return $data;
    }

    private function validateLoyaltyAssociatedUser($userId): ?int
    {
        if (blank($userId)) {
            return null;
        }

        $user = User::find($userId);
        if (! $user) {
            throw ValidationException::withMessages(['associated_user_id' => 'Utente non valido.']);
        }

        $isMinorChild = $user->category === 'figlio' && $user->birth_date && $user->birth_date->gt(now()->subYears(14));
        if ($isMinorChild) {
            throw ValidationException::withMessages(['associated_user_id' => 'Non puoi associare la carta a un minore di 14 anni.']);
        }

        return $user->id;
    }

    public function syncPushSubscription(Request $request)
    {
        $data = $request->validate([
            'active' => ['required', 'boolean'],
            'subscription.endpoint' => ['required', 'string'],
            'subscription.expirationTime' => ['nullable'],
            'subscription.keys.p256dh' => ['nullable', 'string'],
            'subscription.keys.auth' => ['nullable', 'string'],
            'subscription.contentEncoding' => ['nullable', 'string'],
        ]);

        $endpoint = $data['subscription']['endpoint'];

        if (! $data['active']) {
            PushSubscription::where('endpoint', $endpoint)
                ->where('user_id', $request->user()->id)
                ->delete();

            return response()->json(['ok' => true, 'active' => false]);
        }

        PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $request->user()->id,
                'public_key' => $data['subscription']['keys']['p256dh'] ?? '',
                'auth_token' => $data['subscription']['keys']['auth'] ?? '',
                'content_encoding' => $data['subscription']['contentEncoding'] ?? 'aes128gcm',
                'expiration_time' => ! empty($data['subscription']['expirationTime']) ? Carbon::createFromTimestampMs($data['subscription']['expirationTime']) : null,
                'user_agent' => substr((string) $request->userAgent(), 0, 65535),
            ]
        );

        return response()->json(['ok' => true, 'active' => true]);
    }
}
