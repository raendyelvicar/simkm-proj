<?php

namespace App\Support;

// Static instrument metadata shared by AssessmentController (results/history)
// and AssessmentSessionController (the timed fill-in flow).
class AssessmentMeta
{
    public const TYPES = ['bdi2', 'pwb'];

    public const META = [
        'bdi2' => [
            'title'       => 'BDI-II (Beck Depression Inventory-II)',
            'short_title' => 'BDI-II',
            'description' => 'Instrumen psikologi yang dikembangkan oleh Aaron T. Beck, Robert A. Steer, dan Gregory K. Brown (1996) untuk mengukur tingkat keparahan gejala depresi berdasarkan kondisi yang dirasakan dalam dua minggu terakhir. Instrumen ini terdiri dari 21 butir pertanyaan, dengan 4 pilihan jawaban pada tiap butir yang menggambarkan tingkat keparahan suatu gejala.',
            'instructions' => 'Tidak ada jawaban benar ataupun salah. Jawablah setiap pertanyaan sesuai dengan kondisi yang paling menggambarkan diri Anda selama 2 minggu terakhir, termasuk hari ini. Hasil assessment ini bukan merupakan diagnosis medis maupun diagnosis gangguan depresi, melainkan gambaran awal berdasarkan jawaban yang Anda berikan.',
        ],
        'pwb' => [
            'title'       => 'Psychological Well-Being (PWB) — Ryff 18 Items',
            'short_title' => 'PWB',
            'description' => 'Psychological Well-Being (PWB) menurut Carol D. Ryff (1989) mengukur tingkat kesejahteraan psikologis seseorang, bukan sekadar apakah seseorang merasa bahagia atau tidak — mencakup penerimaan diri, hubungan positif dengan orang lain, otonomi, penguasaan lingkungan, tujuan hidup, dan pertumbuhan pribadi.',
            'instructions' => 'Tidak ada jawaban benar atau salah. Jawablah setiap pertanyaan sesuai kondisi diri Anda saat ini. Semua pertanyaan wajib diisi sebelum dapat mengirimkan hasil.',
        ],
    ];
}
