<a href="{{ url('/admin/chat') }}"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full
          bg-[#0591b2] hover:bg-[#30beff] shadow-lg
          flex items-center justify-center transition-all duration-300 group">

    <!-- Chat Icon -->
    <i class="fa-solid fa-comments text-white text-xl"></i>

    <!-- 🔴 CHAT BLINKING DOT -->
    <!-- 🔴 CHAT COUNT BADGE -->
<span id="chatCount"
      class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
             hidden items-center justify-center
             text-[10px] font-bold text-white
             bg-green-500 rounded-full">
</span>

<!-- 🔴 BLINK EFFECT -->
<span id="chatBlink"
      class="absolute -top-1 -right-1 w-[18px] h-[18px]
             rounded-full bg-red-500 opacity-30
             hidden animate-ping">
</span>
</a>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const chatCount = document.getElementById('chatCount');
        const chatBlink = document.getElementById('chatBlink');
        let chatUnread  = 0;

        /* ===== UPDATE CHAT INDICATOR ===== */
        function updateChatIndicator() {
            if (!chatCount || !chatBlink) return;

            if (chatUnread > 0) {
                chatCount.textContent = chatUnread > 99 ? '99+' : chatUnread;
                chatCount.classList.remove('hidden');
                chatCount.classList.add('flex');

                chatBlink.classList.remove('hidden');
            } else {
                chatCount.classList.add('hidden');
                chatBlink.classList.add('hidden');
            }
        }

        /* ===== FETCH OLD CHAT NOTIFICATIONS (DB) ===== */
        function fetchOldChatNotifications() {
            fetch('/announcements/fetch')
                .then(res => res.json())
                .then(data => {
                    if (!data.notifications) return;

                    chatUnread = data.notifications.filter(n =>
                        n.type && n.type.toLowerCase() === 'chat'
                    ).length;

                    updateChatIndicator();
                });
        }

        /* ===== PUSHER INIT ===== */
        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });

        /* ===== PRIVATE USER CHANNEL ===== */
        const channel = pusher.subscribe('private-user.{{ auth()->id() }}');

        /* ===== REALTIME CHAT LISTENER ===== */
        channel.bind('master.notification', function (data) {
            console.log('Chat notification received:', data);

            if (data.type && data.type.toLowerCase() === 'chat') {
                chatUnread++;
                updateChatIndicator();
            }
        });

        /* ===== INITIAL LOAD ===== */
        fetchOldChatNotifications();

    });
</script>