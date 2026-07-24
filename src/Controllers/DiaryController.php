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
            'counselors' => $this->availableCounselors(),
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
                'counselors' => $this->availableCounselors(),
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->diaries->create(
            (int) $_SESSION['user_id'],
            $fields['entry_date'],
            $fields['situation'],
            $fields['initial_thoughts'],
            $fields['emosi'],
            $fields['other_emotions'],
            $fields['emotion_intensity'],
            $fields['physical_reactions'],
            $fields['other_physical_reactions'],
            $fields['behavior'],
            $fields['self_reflection'],
            $fields['gratitude'],
            $fields['tomorrow_plan'],
            $fields['is_private'],
            $fields['shared_counselor_id']
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
            'sharedCounselor' => $entry->sharedCounselorId ? $this->counselors->findByCounselorId($entry->sharedCounselorId) : null,
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
            'counselors' => $this->availableCounselors(),
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
                'counselors' => $this->availableCounselors(),
                'errors' => $errors,
            ]);
            return;
        }

        $this->diaries->update(
            (int) $id,
            $fields['entry_date'],
            $fields['situation'],
            $fields['initial_thoughts'],
            $fields['emosi'],
            $fields['other_emotions'],
            $fields['emotion_intensity'],
            $fields['physical_reactions'],
            $fields['other_physical_reactions'],
            $fields['behavior'],
            $fields['self_reflection'],
            $fields['gratitude'],
            $fields['tomorrow_plan'],
            $fields['is_private'],
            $fields['shared_counselor_id']
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

    // Counselor accounts a diary entry can be shared with: must have a completed profile
    // AND currently be monitoring this student (an active booking-confirmed window) —
    // sharing is one of the things a monitoring period unlocks.
    private function availableCounselors(): array
    {
        $activeIds = $this->monitoring->activeCounselorIdsForStudent((int) $_SESSION['user_id']);

        return array_values(array_filter(
            $this->counselors->all(),
            fn ($c) => (int) $c['counselor_id'] > 0 && in_array((int) $c['counselor_id'], $activeIds, true)
        ));
    }

    private function validate(Request $request): array
    {
        $entryDate = trim($request->post('entry_date', '')) ?: date('Y-m-d');

        $situation = trim($request->post('situation', ''));
        $initialThoughts = trim($request->post('initial_thoughts', ''));

        $emosi = array_values(array_intersect((array) $request->post('emosi', []), self::EMOTION_OPTIONS));
        $otherEmotions = trim($request->post('other_emotions', '')) ?: null;
        $emotionIntensity = (int) $request->post('emotion_intensity', 0);

        $physicalReactions = array_values(array_intersect((array) $request->post('physical_reactions', []), self::PHYSICAL_OPTIONS));
        $otherPhysicalReactions = trim($request->post('other_physical_reactions', '')) ?: null;

        $behavior = trim($request->post('behavior', ''));
        $selfReflection = trim($request->post('self_reflection', '')) ?: null;

        $gratitude = array_values(array_filter(array_map(
            'trim',
            array_slice((array) $request->post('gratitude', []), 0, self::GRATITUDE_SLOTS)
        ), fn ($g) => $g !== ''));

        $tomorrowPlan = trim($request->post('tomorrow_plan', '')) ?: null;

        $visibility = $request->post('visibility', 'private');
        $isPrivate = $visibility !== 'counselor';
        $sharedCounselorId = null;
        if (!$isPrivate) {
            $sharedCounselorId = (int) $request->post('shared_counselor_id', 0) ?: null;
        }

        $fields = [
            'entry_date' => $entryDate,
            'situation' => $situation,
            'initial_thoughts' => $initialThoughts,
            'emosi' => $emosi,
            'other_emotions' => $otherEmotions,
            'emotion_intensity' => $emotionIntensity,
            'physical_reactions' => $physicalReactions,
            'other_physical_reactions' => $otherPhysicalReactions,
            'behavior' => $behavior,
            'self_reflection' => $selfReflection,
            'gratitude' => $gratitude,
            'tomorrow_plan' => $tomorrowPlan,
            'is_private' => $isPrivate,
            'shared_counselor_id' => $sharedCounselorId,
        ];

        $errors = [];

        if ($situation === '') {
            $errors[] = 'Situasi wajib diisi.';
        }
        if ($initialThoughts === '') {
            $errors[] = 'Pikiran pertama wajib diisi.';
        }
        if (empty($emosi)) {
            $errors[] = 'Pilih minimal satu emosi yang dirasakan.';
        } elseif (in_array('Lainnya', $emosi, true) && $otherEmotions === null) {
            $errors[] = 'Sebutkan emosi lainnya yang kamu maksud.';
        }
        if ($emotionIntensity < 1 || $emotionIntensity > 5) {
            $errors[] = 'Intensitas emosi wajib dipilih (1-5).';
        }
        if (empty($physicalReactions)) {
            $errors[] = 'Pilih minimal satu reaksi fisik.';
        } elseif (in_array('Lainnya', $physicalReactions, true) && $otherPhysicalReactions === null) {
            $errors[] = 'Sebutkan reaksi fisik lainnya yang kamu maksud.';
        }
        if ($behavior === '') {
            $errors[] = 'Perilaku wajib diisi.';
        }
        if (!$isPrivate) {
            $validCounselorIds = array_column($this->availableCounselors(), 'counselor_id');
            if (!$sharedCounselorId || !in_array($sharedCounselorId, $validCounselorIds, true)) {
                $errors[] = 'Pilih konselor tujuan berbagi yang valid.';
            }
        }

        return [$fields, $errors];
    }
}
