// ===============================
// KONFIGURASI USER
// ===============================
const userId = window.USER_ID;
const role = window.USER_ROLE;
const studentId = window.STUDENT_ID;

// ===============================
// AMBIL DATA CHAT (REALTIME)
// ===============================
function fetchChat(){

    let url = 'fetch.php';

    // jika konselor pilih mahasiswa
    if(role === 'konselor' && studentId){
        url += '?student_id=' + studentId;
    }

    fetch(url)
    .then(response => response.json())
    .then(data => {
        renderChat(data);
    })
    .catch(err => console.error('Fetch error:', err));
}

// ===============================
// TAMPILKAN CHAT KE LAYAR
// ===============================
function renderChat(messages){

    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML = '';

    messages.forEach(msg => {

        const isMe = parseInt(msg.user_id) === parseInt(userId);

        const div = document.createElement('div');

        div.className = 'msg ' + (isMe ? 'me' : 'konselor');

        div.innerHTML = `
            <strong>${isMe ? 'Saya' : (msg.username || 'User')}</strong><br>
            ${escapeHtml(msg.message).replace(/\n/g,'<br>')}<br>
            <small>${msg.created_at}</small>
        `;

        chatBox.appendChild(div);
    });

    // auto scroll ke bawah
    chatBox.scrollTop = chatBox.scrollHeight;
}

// ===============================
// KIRIM PESAN TANPA RELOAD
// ===============================
document.addEventListener('DOMContentLoaded', function(){

    const form = document.getElementById('chatForm');

    if(form){
        form.addEventListener('submit', function(e){

            e.preventDefault(); // cegah reload

            const formData = new FormData(this);

            fetch('send.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                form.reset();
                fetchChat(); // refresh chat
            })
            .catch(err => console.error('Send error:', err));

        });
    }

});

// ===============================
// AMANKAN TEXT (ANTI XSS)
// ===============================
function escapeHtml(text){
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

// ===============================
// REALTIME LOOP (POLLING)
// ===============================
setInterval(fetchChat, 2500);

// load pertama
fetchChat();
