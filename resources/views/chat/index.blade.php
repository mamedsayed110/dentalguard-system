@extends('layouts.app')

@section('content')

<div class="main-content">

    <div class="chat-page">

        <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
                <h1>AI Dental Assistant</h1>
                <p>اسأل الذكاء الاصطناعي أي سؤال عن الأسنان أو التحليل</p>
            </div>
            <button onclick="clearChat()" style="background:none;border:none;color:red;cursor:pointer">
                مسح المحادثة
            </button>
        </div>

        <div class="chat-container">

            <!-- Header -->
            <div class="chat-header">
                <div class="chat-bot-avatar">🦷</div>
                <div class="chat-bot-info">
                    <h3>DentAI Assistant</h3>
                    <span><span class="online-dot"></span> Online</span>
                </div>
            </div>

            <!-- Messages -->
            <div class="chat-messages" id="chatBox">

                <div class="message bot">
                    <div class="message-avatar">🦷</div>
                    <div>
                        <div class="message-content">
                            أهلاً 👋  
                            اسأل أي سؤال عن الأسنان أو التحاليل.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Input -->
            <div class="chat-input-area">
                <input type="text" id="chatInput" class="chat-input"
                       placeholder="اكتب سؤالك هنا..."
                       onkeydown="handleEnter(event)">
                <button class="btn-send" onclick="sendMessage()">➤</button>
            </div>

        </div>

    </div>

</div>

<script>
function sendMessage(){
    let input = document.getElementById('chatInput');
    let msg = input.value.trim();
    if(!msg) return;

    let box = document.getElementById('chatBox');

    // رسالة المستخدم
    box.innerHTML += `
        <div class="message user">
            <div class="message-avatar">👨‍⚕️</div>
            <div><div class="message-content">${escapeHtml(msg)}</div></div>
        </div>
    `;

    input.value = "";
    box.scrollTop = box.scrollHeight;

    // مؤشر الكتابة
    let typing = document.createElement("div");
    typing.className = "message bot typing";
    typing.id = "typingIndicator";
    typing.innerHTML = `
        <div class="message-avatar">🦷</div>
        <div><div class="message-content">✍️ يكتب الآن...</div></div>
    `;
    box.appendChild(typing);
    box.scrollTop = box.scrollHeight;

    // إرسال الطلب
    fetch("{{ route('chat.send') }}",{
        method:"POST",
        headers:{
            "Content-Type":"application/json",
            "X-CSRF-TOKEN":"{{ csrf_token() }}"
        },
        body: JSON.stringify({ message: msg })
    })
    .then(res => res.json())
    .then(data => {

        document.getElementById("typingIndicator")?.remove();

        box.innerHTML += `
            <div class="message bot">
                <div class="message-avatar">🦷</div>
                <div><div class="message-content">${escapeHtml(data.reply)}</div></div>
            </div>
        `;

        box.scrollTop = box.scrollHeight;
    })
    .catch(()=>{
        document.getElementById("typingIndicator")?.remove();
        box.innerHTML += `
            <div class="message bot">
                <div class="message-avatar">⚠️</div>
                <div><div class="message-content">حصل خطأ — حاول لاحقًا</div></div>
            </div>
        `;
    });
}

// Enter للإرسال
function handleEnter(e){
    if(e.key === "Enter"){
        sendMessage();
    }
}

// مسح الشات
function clearChat(){
    fetch("{{ route('chat.clear') }}",{
        method:"POST",
        headers:{
            "X-CSRF-TOKEN":"{{ csrf_token() }}"
        }
    }).then(()=> location.reload());
}

// حماية من XSS
function escapeHtml(text) {
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

@endsection
