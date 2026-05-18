<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

/**
 * ChatbotWebController — halaman chatbot berbasis data UMKM untuk Blade.
 */
class ChatbotWebController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbotService
    ) {}

    /**
     * GET /chatbot
     * Halaman utama chatbot — tampilkan template question cards.
     */
    public function index()
    {
        $templateQuestions = $this->chatbotService->getTemplateQuestions();

        return view('web.chatbot.index', compact('templateQuestions'));
    }

    /**
     * POST /chatbot/ask
     * Proses pertanyaan dan kembalikan jawaban ke view (atau redirect dengan flash).
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $outletId = auth()->user()->outlet_id;
        $result   = $this->chatbotService->ask($request->question, $outletId);

        // Jika request dari AJAX (fetch dari Blade), kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        }

        // Jika form biasa, redirect balik dengan data di session flash
        $templateQuestions = $this->chatbotService->getTemplateQuestions();

        return view('web.chatbot.index', compact('templateQuestions', 'result'));
    }
}
