// Kết nối socket
const socket = io("http://localhost:3000");

// Đăng ký user với socket server
socket.emit("register", user_id);

// Hiển thị ban đầu từ DB
updateNotificationUI();

// Khi nhận thông báo real-time từ socket
socket.on("receive_notification", (data) => {
  if (data.type === "notification") {
    notificationCount++;
    notifications.unshift({
      id_news: data.new_id,
      id_post: data.post_id,
      message: data.message,
      created_at: data.created_at,
      is_read: 0
    });
    updateNotificationUI();
  }
});

// Render toàn bộ thông báo
function updateNotificationUI() {
  const countElem = document.getElementById("notification-count");
  const listElem = document.getElementById("notification-items");

  if (!countElem || !listElem) return;

  countElem.innerText = notificationCount;
  countElem.style.display = notificationCount > 0 ? "inline-block" : "none";

  listElem.innerHTML = "";

  notifications.forEach(n => {
    const li = document.createElement("li");
    const createdAt = n.created_at;
    const timeString = timeAgo(createdAt);

    if (n.id_news && n.id_news != 0) {
      // Nếu là bài tin tức
      li.innerHTML = `
        <a href="/galaxy/view_news.php?id=${n.id_news}">
          <i class="fa fa-newspaper"></i> ${n.message}
        </a>
        <br>
        <p class="time_up">Đăng lúc: ${timeString}</p>
      `;
    } else {
      // Nếu là bài post cộng đồng
      li.innerHTML = `
        <a href="/galaxy/post_details.php?id=${n.id_post}">
          <i class="fa fa-users"></i> ${n.message}
        </a>
        <br>
        <p class="time_up">Đăng lúc: ${timeString}</p>
      `;
    }

    // Đánh dấu in đậm nếu chưa đọc
    if (n.is_read == 0) {
      li.style.fontWeight = "bold";
    }

    listElem.appendChild(li);
  });
}

// Sự kiện click chuông thông báo
document.getElementById("notification-bell").addEventListener("click", () => {
  const list = document.getElementById("notification-list");

  if (list.style.display === "none") {
    list.style.display = "block";

    // Gửi AJAX đánh dấu đã đọc
    fetch("/galaxy/read_notifications.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ user_id: user_id })
    });

    notificationCount = 0;
    document.getElementById("notification-count").style.display = "none";

    // Đánh dấu trên giao diện (không cần reload)
    notifications = notifications.map(n => ({ ...n, is_read: 1 }));
    updateNotificationUI();

  } else {
    list.style.display = "none";
  }
});

// Hàm xử lý thời gian tương đối
function timeAgo(datetime) {
  const seconds = Math.floor((new Date() - new Date(datetime)) / 1000);

  if (seconds < 60) return seconds + " giây trước";
  if (seconds < 3600) return Math.floor(seconds / 60) + " phút trước";
  if (seconds < 86400) return Math.floor(seconds / 3600) + " giờ trước";
  if (seconds < 2592000) return Math.floor(seconds / 86400) + " ngày trước";
  if (seconds < 31104000) return Math.floor(seconds / 2592000) + " tháng trước";

  return Math.floor(seconds / 31104000) + " năm trước";
}
