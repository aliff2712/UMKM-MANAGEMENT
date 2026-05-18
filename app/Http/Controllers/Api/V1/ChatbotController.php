<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ChatbotController — endpoint chatbot berbasis data real UMKM.
 */
class ChatbotController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbotService
    ) {}

    /**
     * POST /api/v1/chatbot/ask
     * Kirim pertanyaan dan dapatkan jawaban berbasis data database.
     *
     * Body: { "question": "Produk apa yang terlaris?", "outlet_id": 1 }
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question'  => ['required', 'string', 'min:3', 'max:500'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        $result = $this->chatbotService->ask(
            question: $request->question,
            outletId: (int) $request->outlet_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil diproses.',
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/v1/chatbot/questions
     * Daftar pertanyaan template untuk ditampilkan sebagai card di frontend.
     */
    public function questions(): JsonResponse
    {
        $questions = $this->chatbotService->getTemplateQuestions();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pertanyaan template berhasil dimuat.',
            'data'    => $questions,
        ]);
    }
}
