<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\DiaryRepository;
use App\Repositories\MonitoringPeriodRepository;

class DiaryController
{
    private const EMOTION_OPTIONS = ['Sedih', 'Cemas', 'Marah', 'Kecewa', 'Takut', 'Malu', 'Bingung', 'Lainnya'];
    private const PHYSICAL_OPTIONS = ['Jantung berdebar', 'Sulit bernapas', 'Tegang', 'Sulit tidur', 'Pusing', 'Menangis', 'Lainnya'];
    private const GRATITUDE_SLOTS = 3;
    private const PER_PAGE = 10;

    private DiaryRepository $diaries;
    private CounselorRepository $counselors;
    private MonitoringPeriodRepository $monitoring;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->diaries = new DiaryRepository();
        $this->counselors = new CounselorRepository();
        $this->monitoring = new MonitoringPeriodRepository();
    }

    // GET /diary — list only the logged-in user's own entries
    public function index(Request $request): void
    {
        $filters = [
            'search'    => trim((string) $request->get('q', '')),
            'date_from' => $request->get('date_from') ?: null,
            'date_to'   => $request->get('date_to') ?: null,
        ];
        $sort = (string) $request->get('sort', 'entry_date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->diaries->paginatedByUserId((int) $_SESSION['user_id'], $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('diary/index', [
            'title'      => 'Diary',
            'entries'    => array_map(fn ($entry) => $entry->toArray(), $result['items']),
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // GET /diary/create
    public function create(Request $request): void
    {
        Response::view('diary/create', [
            'title' => 'Tulis Diary',
            'emotionOptions' => self::EMOTION_OPTIONS,
            'physicalOptions' => self::PHYSICAL_OPTIONS,
            'gratitudeSlots' => self::GRATITUDE_SLOTS,
            'konselors' => $this->availableKonselors(),
        ]);
    }

    // POST /diary
    public function store(Request $request): void
    {
        [$fields, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('diary/create', [
                'title' => 'Tulis Diary',
                'emotionOptions' => self::EMOTION_OPTIONS,
                'physicalOptions' => self::PHYSICAL_OPTIONS,
                'gratitudeSlots' => self::GRATITUDE_SLOTS,
                'konselors' => $this->availableKonselors(),
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->diaries->create(
            (int) $_SESSION['user_id'],
            $fields['entry_date'],
            $fields['situasi'],
            $fields['pikiran_awal'],
            $fields['emosi'],
            $fields['emosi_lainnya'],
            $fields['intensitas_emosi'],
            $fields['reaksi_fisik'],
            $fields['reaksi_fisik_lainnya'],
            $fields['perilaku'],
            $fields['self_reflection'],
            $fields['gratitude'],
            $fields['rencana_besok'],
            $fields['is_private'],
            $fields['shared_konselor_id']
        );

        $_SESSION['success'] = 'Diary berhasil ditambahkan.';
        Response::redirect('/diary');
    }

    // GET /diary/{id} — must verify the entry belongs to $_SESSION['user_id']
    public function show(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('diary/show', [
            'title' => 'Detail Diary',
            'entry' => $entry->toArray(),
            'sharedKonselor' => $entry->sharedKonselorId ? $this->counselors->findByKonselorId($entry->sharedKonselorId) : null,
        ]);
    }

    // GET /diary/{id}/edit — must verify the entry belongs to $_SESSION['user_id']
    public function edit(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('diary/edit', [
            'title' => 'Edit Diary',
            'entry' => $entry->toArray(),
            'emotionOptions' => self::EMOTION_OPTIONS,
            'physicalOptions' => self::PHYSICAL_OPTIONS,
            'gratitudeSlots' => self::GRATITUDE_SLOTS,
            'konselors' => $this->availableKonselors(),
        ]);
    }

    // POST /diary/{id} — must verify ownership before updating
    public function update(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        [$fields, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('diary/edit', [
                'title' => 'Edit Diary',
                'entry' => array_merge($entry->toArray(), $fields),
                'emotionOptions' => self::EMOTION_OPTIONS,
                'physicalOptions' => self::PHYSICAL_OPTIONS,
                'gratitudeSlots' => self::GRATITUDE_SLOTS,
                'konselors' => $this->availableKonselors(),
                'errors' => $errors,
            ]);
            return;
        }

        $this->diaries->update(
            (int) $id,
            $fields['entry_date'],
            $fields['situasi'],
            $fields['pikiran_awal'],
            $fields['emosi'],
            $fields['emosi_lainnya'],
            $fields['intensitas_emosi'],
            $fields['reaksi_fisik'],
            $fields['reaksi_fisik_lainnya'],
            $fields['perilaku'],
            $fields['self_reflection'],
            $fields['gratitude'],
            $fields['rencana_besok'],
            $fields['is_private'],
            $fields['shared_konselor_id']
        );

        $_SESSION['success'] = 'Diary berhasil diperbarui.';
        Response::redirect('/diary/' . $id);
    }

    // POST /diary/{id}/delete — must verify ownership before deleting
    public function destroy(Request $request, string $id): void
    {
        if ($this->findOwnedEntry((int) $id)) {
            $this->diaries->delete((int) $id);
            $_SESSION['success'] = 'Diary berhasil dihapus.';
        }

        Response::redirect('/diary');
    }

    private function findOwnedEntry(int $id)
    {
        $entry = $this->diaries->find($id);

        if (!$entry || $entry->userId !== (int) $_SESSION['user_id']) {
            return null;
        }

        return $entry;
    }

    // Konselor accounts a diary entry can be shared with: must have a completed profile
    // AND currently be monitoring this student (an active booking-confirmed window) —
    // sharing is one of the things a monitoring period unlocks.
    private function availableKonselors(): array
    {
        $activeIds = $this->monitoring->activeKonselorIdsForStudent((int) $_SESSION['user_id']);

        return array_values(array_filter(
            $this->counselors->all(),
            fn ($c) => (int) $c['konselor_id'] > 0 && in_array((int) $c['konselor_id'], $activeIds, true)
        ));
    }

    private function validate(Request $request): array
    {
        $entryDate = trim($request->post('entry_date', '')) ?: date('Y-m-d');

        $situasi = trim($request->post('situasi', ''));
        $pikiranAwal = trim($request->post('pikiran_awal', ''));

        $emosi = array_values(array_intersect((array) $request->post('emosi', []), self::EMOTION_OPTIONS));
        $emosiLainnya = trim($request->post('emosi_lainnya', '')) ?: null;
        $intensitasEmosi = (int) $request->post('intensitas_emosi', 0);

        $reaksiFisik = array_values(array_intersect((array) $request->post('reaksi_fisik', []), self::PHYSICAL_OPTIONS));
        $reaksiFisikLainnya = trim($request->post('reaksi_fisik_lainnya', '')) ?: null;

        $perilaku = trim($request->post('perilaku', ''));
        $selfReflection = trim($request->post('self_reflection', '')) ?: null;

        $gratitude = array_values(array_filter(array_map(
            'trim',
            array_slice((array) $request->post('gratitude', []), 0, self::GRATITUDE_SLOTS)
        ), fn ($g) => $g !== ''));

        $rencanaBesok = trim($request->post('rencana_besok', '')) ?: null;

        $visibility = $request->post('visibility', 'private');
        $isPrivate = $visibility !== 'konselor';
        $sharedKonselorId = null;
        if (!$isPrivate) {
            $sharedKonselorId = (int) $request->post('shared_konselor_id', 0) ?: null;
        }

        $fields = [
            'entry_date' => $entryDate,
            'situasi' => $situasi,
            'pikiran_awal' => $pikiranAwal,
            'emosi' => $emosi,
            'emosi_lainnya' => $emosiLainnya,
            'intensitas_emosi' => $intensitasEmosi,
            'reaksi_fisik' => $reaksiFisik,
            'reaksi_fisik_lainnya' => $reaksiFisikLainnya,
            'perilaku' => $perilaku,
            'self_reflection' => $selfReflection,
            'gratitude' => $gratitude,
            'rencana_besok' => $rencanaBesok,
            'is_private' => $isPrivate,
            'shared_konselor_id' => $sharedKonselorId,
        ];

        $errors = [];

        if ($situasi === '') {
            $errors[] = 'Situasi wajib diisi.';
        }
        if ($pikiranAwal === '') {
            $errors[] = 'Pikiran pertama wajib diisi.';
        }
        if (empty($emosi)) {
            $errors[] = 'Pilih minimal satu emosi yang dirasakan.';
        } elseif (in_array('Lainnya', $emosi, true) && $emosiLainnya === null) {
            $errors[] = 'Sebutkan emosi lainnya yang kamu maksud.';
        }
        if ($intensitasEmosi < 1 || $intensitasEmosi > 5) {
            $errors[] = 'Intensitas emosi wajib dipilih (1-5).';
        }
        if (empty($reaksiFisik)) {
            $errors[] = 'Pilih minimal satu reaksi fisik.';
        } elseif (in_array('Lainnya', $reaksiFisik, true) && $reaksiFisikLainnya === null) {
            $errors[] = 'Sebutkan reaksi fisik lainnya yang kamu maksud.';
        }
        if ($perilaku === '') {
            $errors[] = 'Perilaku wajib diisi.';
        }
        if (!$isPrivate) {
            $validKonselorIds = array_column($this->availableKonselors(), 'konselor_id');
            if (!$sharedKonselorId || !in_array($sharedKonselorId, $validKonselorIds, true)) {
                $errors[] = 'Pilih konselor tujuan berbagi yang valid.';
            }
        }

        return [$fields, $errors];
    }
}
