// server.js
const io = require("socket.io")(3000, {
  cors: {
    origin: "*"
  }
});

const users = {}; // user_id => socket.id

io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  socket.on("register", (user_id) => {
    users[user_id] = socket.id;
    console.log("Registered user:", user_id);
  });

  socket.on("send_message", (data) => {
    const { from_user_id, to_user_id, conversation_id, message, image_path } = data;

    // Gửi về cho chính sender
    io.to(users[from_user_id]).emit("receive_message", data);

    // Gửi về cho người nhận nếu đang online
    if (users[to_user_id]) {
      io.to(users[to_user_id]).emit("receive_message", data);
    }
  });

  socket.on("disconnect", () => {
    for (let id in users) {
      if (users[id] === socket.id) {
        delete users[id];
        break;
      }
    }
    console.log("User disconnected:", socket.id);
  });

    // Admin gửi thông báo đăng bài mới đến tất cả
  socket.on("admin_post_article", (data) => {
    const { message } = data;

    console.log("New article posted, sending to all users:", message);

    // Gửi thông báo đến tất cả user đang online
    for (let user_id in users) {
      io.to(users[user_id]).emit("receive_notification", {
        type: "new_article",
        message: message
      });
    }
  });

});

const express = require("express");
const app = express();
app.use(express.json()); // Cho phép nhận body dạng JSON

// Endpoint để PHP gọi gửi thông báo
app.post("/notify", (req, res) => {
  const { message, id, created_at } = req.body;

  
  for (let user_id in users) {
    io.to(users[user_id]).emit("receive_notification", {
      type: "new_article",
      id,
      message,
      created_at
    });
  }

  res.send({ success: true });
});

// Mở cổng API riêng (khác với socket)
app.listen(4000, () => {
  console.log("API server đang chạy tại http://localhost:4000");
});

