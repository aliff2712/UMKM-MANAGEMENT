@extends('layouts.app')

@section('title', 'Asisten Pintar AI')
@section('page_title', 'Asisten Pintar TechneFest')

@section('content')
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-primary">
                <i data-lucide="bot"></i>
                <span>Asisten Data UMKM Pintar</span>
            </h3>
            <span class="badge badge-info">Powered by Gemini</span>
        </div>

        <div class="chat-container">
            <!-- Messages Log -->
            <div class="chat-messages" id="chat-messages">
                <!-- Welcome Message -->
                <div class="chat-bubble bubble-received">
                    Halo! Saya adalah asisten virtual TechneFest. Saya dapat menganalisis data penjualan, tingkat stok produk, dan laporan keuangan outlet Anda secara real-time. 
                    <br><br>
                    Silakan ajukan pertanyaan di bawah ini atau klik salah satu kartu rekomendasi pertanyaan di bawah!
                </div>

                <!-- If form submitted normally (fallback) -->
                @if(isset($result))
                    <div class="chat-bubble bubble-sent">
                        {{ request('question') }}
                    </div>
                    <div class="chat-bubble bubble-received">
                        {!! nl2br(e($result)) !!}
                    </div>
                @endif
            </div>

            <!-- Suggestion Template Cards -->
            <div class="chat-templates">
                @foreach($templateQuestions as $q)
                    <button class="template-card" onclick="askQuestion('{{ $q }}')">
                        <strong>Tanya:</strong><br>
                        {{ $q }}
                    </button>
                @endforeach
            </div>

            <!-- Message Input Area -->
            <form id="chat-form" action="{{ route('chatbot.ask') }}" method="POST" class="chat-input-area">
                @csrf
                <input type="text" name="question" id="chat-input" class="form-control" placeholder="Ajukan pertanyaan analisis (misal: 'Bagaimana tren omzet bulan ini?')" required autocomplete="off">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="send"></i>
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');

        // Function to scroll chat to bottom
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Initialize scroll
        scrollToBottom();

        // AJAX handler for questions
        function askQuestion(questionText) {
            chatInput.value = questionText;
            chatForm.dispatchEvent(new Event('submit'));
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const question = chatInput.value.trim();
            if (!question) return;

            // 1. Append User Question Bubble
            const userBubble = document.createElement('div');
            userBubble.className = 'chat-bubble bubble-sent';
            userBubble.textContent = question;
            chatMessages.appendChild(userBubble);
            
            // Clear input
            chatInput.value = '';
            scrollToBottom();

            // 2. Append Typing Simulator Bubble
            const typingBubble = document.createElement('div');
            typingBubble.className = 'chat-bubble bubble-received';
            typingBubble.innerHTML = '<span style="opacity: 0.6">Sedang menganalisis data outlet Anda... <i data-lucide="loader" class="animate-spin" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i></span>';
            chatMessages.appendChild(typingBubble);
            lucide.createIcons();
            scrollToBottom();

            // 3. Make AJAX Post Request
            fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question: question })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal mendapatkan respon dari server.');
                }
                return response.json();
            })
            .then(res => {
                // Remove typing simulation
                chatMessages.removeChild(typingBubble);

                // Append AI Response Bubble
                const botBubble = document.createElement('div');
                botBubble.className = 'chat-bubble bubble-received';
                
                // Format response text with line breaks
                const formattedText = res.data.replace(/\n/g, '<br>');
                botBubble.innerHTML = formattedText;
                
                chatMessages.appendChild(botBubble);
                scrollToBottom();
            })
            .catch(error => {
                chatMessages.removeChild(typingBubble);
                
                const errorBubble = document.createElement('div');
                errorBubble.className = 'chat-bubble bubble-received text-error';
                errorBubble.textContent = 'Maaf, terjadi kesalahan: ' + error.message;
                chatMessages.appendChild(errorBubble);
                scrollToBottom();
            });
        });
    </script>
    
    <style>
        /* Spinning Animation for loader */
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
