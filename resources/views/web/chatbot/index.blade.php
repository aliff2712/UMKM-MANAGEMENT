@extends('layouts.app')

@section('title', 'Asisten Pintar AI')
@section('page_title', 'Asisten Pintar TechneFest')

@section('content')

<div class="ai-wrap">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="ai-header">
        <div class="ai-header-left">
            <div class="ai-bot-avatar">
                <i data-lucide="bot"></i>
                <span class="ai-avatar-ring"></span>
            </div>
            <div>
                <p class="ai-bot-name">Asisten Data UMKM</p>
                <p class="ai-bot-sub"><span class="ai-dot"></span>Online &amp; Siap Analisis</p>
            </div>
        </div>
        <span class="ai-badge">
            <i data-lucide="sparkles"></i>
            Powered by Gemini
        </span>
    </div>

    {{-- ── Messages ─────────────────────────────────────────────────── --}}
    <div class="ai-messages" id="ai-messages">

        <div class="ai-msg-row ai-row-bot">
            <div class="ai-ava"><i data-lucide="bot"></i></div>
            <div class="ai-col">
                <div class="ai-bubble ai-bot">
                    Halo! Saya adalah asisten virtual <strong>TechneFest</strong>. Saya dapat menganalisis data penjualan, tingkat stok produk, dan laporan keuangan outlet Anda secara real-time.<br><br>
                    Silakan ajukan pertanyaan di bawah atau klik kartu rekomendasi!
                </div>
                <span class="ai-ts">Baru saja</span>
            </div>
        </div>

        @if(isset($result))
            <div class="ai-msg-row ai-row-user">
                <div class="ai-col">
                    <div class="ai-bubble ai-user">{{ request('question') }}</div>
                    <span class="ai-ts">{{ now()->format('H:i') }}</span>
                </div>
            </div>
            <div class="ai-msg-row ai-row-bot">
                <div class="ai-ava"><i data-lucide="bot"></i></div>
                <div class="ai-col">
                    <div class="ai-bubble ai-bot">{!! nl2br(e($result['answer'])) !!}</div>
                    <span class="ai-ts">{{ now()->format('H:i') }}</span>
                </div>
            </div>
        @endif

    </div>

    {{-- ── Template Cards ──────────────────────────────────────────── --}}
    <div class="ai-tpl-area">
        <span class="ai-tpl-label">Rekomendasi pertanyaan</span>
        <div class="ai-tpl-scroll">
            @foreach($templateQuestions as $q)
                <button class="ai-tpl" type="button" onclick="aiAsk('{{ addslashes($q['question']) }}')">
                    <div class="ai-tpl-head">
                        <i data-lucide="{{ $q['icon'] ?? 'help-circle' }}"></i>
                        <span>{{ $q['category'] ?? 'Analisis' }}</span>
                    </div>
                    <p>{{ $q['question'] }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Input Bar ───────────────────────────────────────────────── --}}
    <form id="ai-form" action="{{ route('chatbot.ask') }}" method="POST" class="ai-input-bar">
        @csrf
        <input
            type="text"
            name="question"
            id="ai-input"
            class="ai-input"
            placeholder="Tanya sesuatu, misal: 'Bagaimana tren omzet bulan ini?'"
            required autocomplete="off"
        >
        <button type="submit" class="ai-send" aria-label="Kirim">
            <i data-lucide="send"></i>
        </button>
    </form>

</div>

@endsection


@section('scripts')
<script>
(function(){
    const msgs  = document.getElementById('ai-messages');
    const form  = document.getElementById('ai-form');
    const inp   = document.getElementById('ai-input');

    const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const ts  = () => new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});

    function show(el){ requestAnimationFrame(()=>{ requestAnimationFrame(()=>el.classList.add('ai-in')); }); }

    function addMsg(role, html){
        const row = document.createElement('div');
        row.className = `ai-msg-row ai-row-${role}`;

        if(role==='bot'){
            const av = document.createElement('div');
            av.className = 'ai-ava';
            av.innerHTML = '<i data-lucide="bot"></i>';
            row.appendChild(av);
        }

        const col = document.createElement('div');
        col.className = 'ai-col';
        const bub = document.createElement('div');
        bub.className = `ai-bubble ai-${role}`;
        bub.innerHTML = html;
        const t = document.createElement('span');
        t.className = 'ai-ts';
        t.textContent = ts();
        col.appendChild(bub);
        col.appendChild(t);
        row.appendChild(col);

        msgs.appendChild(row);
        lucide.createIcons({nodes:[row]});
        msgs.scrollTop = msgs.scrollHeight;
        show(row);
        return bub;
    }

    function addTyping(){
        const row = document.createElement('div');
        row.className = 'ai-msg-row ai-row-bot';
        row.id = 'ai-typing';
        row.innerHTML = `<div class="ai-ava"><i data-lucide="bot"></i></div>
            <div class="ai-col"><div class="ai-bubble ai-bot">
                <div class="ai-dots"><span></span><span></span><span></span></div>
            </div></div>`;
        msgs.appendChild(row);
        lucide.createIcons({nodes:[row]});
        msgs.scrollTop = msgs.scrollHeight;
        show(row);
        return row;
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        const q = inp.value.trim();
        if(!q) return;

        addMsg('user', esc(q));
        inp.value = '';

        const typing = addTyping();

        fetch(form.action,{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json'
            },
            body: JSON.stringify({question:q})
        })
        .then(r=>{ if(!r.ok) throw new Error('Server error'); return r.json(); })
        .then(data=>{
            typing.remove();
            addMsg('bot', data.data.answer.replace(/\n/g,'<br>'));
        })
        .catch(err=>{
            typing.remove();
            addMsg('bot',`<span style="color:#ef4444"><i data-lucide="alert-circle" style="width:13px;height:13px;display:inline;vertical-align:-2px;margin-right:4px;"></i>Gagal: ${esc(err.message)}</span>`);
        });
    });

    window.aiAsk = function(text){ inp.value=text; form.dispatchEvent(new Event('submit',{cancelable:true})); };

    // Animate welcome bubble
    document.querySelectorAll('.ai-msg-row').forEach((el,i)=>{ setTimeout(()=>el.classList.add('ai-in'), i*100); });

    msgs.scrollTop = msgs.scrollHeight;
})();
</script>

<style>
/* ── Base wrapper ───────────────────────────────────────────────────── */
.ai-wrap {
    display: flex;
    flex-direction: column;
    /* Desktop: fill available height inside content area */
    height: calc(100vh - 120px);
    min-height: 500px;
    background: var(--glass-bg, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
}

/* ── Header ─────────────────────────────────────────────────────────── */
.ai-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    background: var(--glass-bg, #fff);
    flex-shrink: 0;
    gap: 12px;
}
.ai-header-left { display: flex; align-items: center; gap: 12px; min-width: 0; }

.ai-bot-avatar {
    position: relative;
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    flex-shrink: 0;
}
.ai-bot-avatar svg { width: 20px; height: 20px; }
.ai-avatar-ring {
    position: absolute; inset: -3px;
    border-radius: 50%;
    border: 2px solid rgba(99,102,241,.3);
    animation: ai-ring 2.5s ease-in-out infinite;
}
@keyframes ai-ring {
    0%,100% { transform: scale(1); opacity: .6; }
    50%      { transform: scale(1.12); opacity: .2; }
}

.ai-bot-name { font-size: 14px; font-weight: 600; color: var(--text-primary,#111827); margin: 0 0 2px; white-space: nowrap; }
.ai-bot-sub  { font-size: 11.5px; color: var(--text-muted,#6b7280); margin: 0; display: flex; align-items: center; gap: 5px; }
.ai-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; flex-shrink: 0;
    box-shadow: 0 0 6px rgba(34,197,94,.5);
    animation: ai-pulse 2s infinite;
}
@keyframes ai-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
    50%      { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
}

.ai-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 500; white-space: nowrap;
    padding: 5px 11px; border-radius: 99px;
    background: rgba(99,102,241,.09);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,.22);
    flex-shrink: 0;
}
.ai-badge svg { width: 11px; height: 11px; }

/* ── Messages ───────────────────────────────────────────────────────── */
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px 18px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    scroll-behavior: smooth;
}
.ai-messages::-webkit-scrollbar { width: 3px; }
.ai-messages::-webkit-scrollbar-thumb { background: var(--border-color,#e5e7eb); border-radius: 3px; }

/* Row */
.ai-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .22s ease, transform .22s ease;
}
.ai-msg-row.ai-in { opacity: 1; transform: translateY(0); }
.ai-row-bot  { flex-direction: row; }
.ai-row-user { flex-direction: row-reverse; }

/* Mini avatar */
.ai-ava {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg,#6366f1,#818cf8);
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
}
.ai-ava svg { width: 14px; height: 14px; }

.ai-col { display: flex; flex-direction: column; max-width: 75%; }
.ai-row-user .ai-col { align-items: flex-end; }
.ai-row-bot  .ai-col { align-items: flex-start; }

/* Bubbles */
.ai-bubble {
    padding: 11px 15px;
    border-radius: 18px;
    font-size: 13.5px;
    line-height: 1.65;
    word-break: break-word;
}
.ai-bot {
    background: var(--glass-secondary, #f3f4f6);
    color: var(--text-primary, #111827);
    border-bottom-left-radius: 4px;
    border: 1px solid var(--border-color, #e9eaeb);
}
.ai-user {
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    color: #fff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 3px 12px rgba(99,102,241,.3);
}
.ai-ts {
    font-size: 10.5px;
    color: var(--text-muted, #9ca3af);
    margin-top: 4px;
    padding: 0 4px;
}

/* Typing dots */
.ai-dots { display: flex; gap: 5px; padding: 3px 0; }
.ai-dots span {
    width: 7px; height: 7px; border-radius: 50%;
    background: #9ca3af;
    animation: ai-bounce 1.3s infinite ease-in-out;
}
.ai-dots span:nth-child(2){ animation-delay: .18s; }
.ai-dots span:nth-child(3){ animation-delay: .36s; }
@keyframes ai-bounce {
    0%,60%,100%{ transform: translateY(0); }
    30%        { transform: translateY(-6px); }
}

/* ── Template cards ─────────────────────────────────────────────────── */
.ai-tpl-area {
    padding: 10px 18px 6px;
    border-top: 1px solid var(--border-color, #e5e7eb);
    flex-shrink: 0;
    background: var(--glass-bg,#fff);
}
.ai-tpl-label {
    display: block;
    font-size: 9.5px; text-transform: uppercase; letter-spacing: .7px;
    color: var(--text-muted,#9ca3af); font-weight: 700;
    margin-bottom: 8px;
}
.ai-tpl-scroll {
    display: flex; gap: 8px;
    overflow-x: auto; padding-bottom: 8px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.ai-tpl-scroll::-webkit-scrollbar { display: none; }

.ai-tpl {
    flex-shrink: 0;
    width: 160px;
    background: var(--glass-secondary,#f9fafb);
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 12px;
    padding: 9px 12px;
    text-align: left; cursor: pointer;
    transition: all .18s ease;
}
.ai-tpl:hover {
    border-color: #6366f1;
    background: rgba(99,102,241,.06);
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(99,102,241,.12);
}
.ai-tpl:hover p { color: #6366f1; }
.ai-tpl-head {
    display: flex; align-items: center; gap: 5px;
    margin-bottom: 5px;
}
.ai-tpl-head svg { width: 12px; height: 12px; color: #6366f1; flex-shrink: 0; }
.ai-tpl-head span { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted,#9ca3af); font-weight: 700; }
.ai-tpl p { margin: 0; font-size: 12px; color: var(--text-secondary,#374151); line-height: 1.35; }

/* ── Input bar ──────────────────────────────────────────────────────── */
.ai-input-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px 14px;
    border-top: 1px solid var(--border-color,#e5e7eb);
    flex-shrink: 0;
    background: var(--glass-bg,#fff);
}
.ai-input {
    flex: 1; height: 42px;
    border: 1.5px solid var(--border-color,#e5e7eb);
    border-radius: 99px;
    padding: 0 18px;
    font-size: 13.5px;
    background: var(--glass-secondary,#f9fafb);
    color: var(--text-primary,#111827);
    outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
    font-family: inherit;
}
.ai-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.13);
    background: #fff;
}
.ai-input::placeholder { color: var(--text-muted,#9ca3af); font-size: 12.5px; }

.ai-send {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg,#6366f1,#818cf8);
    border: none; color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0;
    transition: transform .12s, box-shadow .15s;
    box-shadow: 0 3px 12px rgba(99,102,241,.35);
}
.ai-send:hover  { transform: scale(1.05); box-shadow: 0 5px 18px rgba(99,102,241,.45); }
.ai-send:active { transform: scale(.94); }
.ai-send svg { width: 16px; height: 16px; }

/* ── Mobile: truly full-screen ──────────────────────────────────────── */

</style>
@endsection